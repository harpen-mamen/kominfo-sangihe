<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class WorkflowService
{
    public function __construct(
        protected ServiceAgregasiStatistik $agregasiStatistik
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public function transitions(): array
    {
        return [
            'draft' => ['diajukan'],
            'diajukan' => ['revisi', 'terverifikasi', 'ditolak'],
            'revisi' => ['diajukan'],
            'terverifikasi' => ['terbit'],
            'terbit' => ['terverifikasi'],
            'ditolak' => [],
        ];
    }

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, $this->transitions()[$from] ?? [], true);
    }

    public function transition(object $pengajuan, string $nextStatus, ?object $actor = null, ?string $catatan = null): object
    {
        $pengajuanId = (int) data_get($pengajuan, 'id');
        $currentStatus = (string) data_get($pengajuan, 'status');

        if (! $this->canTransition($currentStatus, $nextStatus)) {
            throw ValidationException::withMessages([
                'status' => "Transisi {$currentStatus} ke {$nextStatus} tidak diizinkan.",
            ]);
        }

        match ($nextStatus) {
            'diajukan' => $this->ajukan($pengajuanId),
            'revisi' => $this->mintaRevisi($pengajuanId, $catatan ?: 'Pengajuan perlu direvisi.'),
            'terverifikasi' => $currentStatus === 'terbit'
                ? $this->tarikPublikasi($pengajuanId, $catatan)
                : $this->verifikasi($pengajuanId, $catatan),
            'ditolak' => $this->tolak($pengajuanId, $catatan ?: 'Pengajuan ditolak.'),
            'terbit' => $this->terbitkan($pengajuanId),
            default => null,
        };

        return method_exists($pengajuan, 'refresh')
            ? $pengajuan->refresh()
            : $this->pengajuan($pengajuanId);
    }

    public function ajukan(int $pengajuanId): void
    {
        DB::transaction(function () use ($pengajuanId) {
            $pengajuan = $this->pengajuan($pengajuanId);

            if (! in_array($pengajuan->status, ['draft', 'revisi'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan hanya bisa diajukan dari status draft atau revisi.',
                ]);
            }

            $this->pastikanDataWajibLengkap($pengajuan);

            $this->updatePengajuan($pengajuanId, [
                'status' => 'diajukan',
                'submitted_at' => now(),
                'submitted_by' => $this->userId(),
                'tanggal_kirim' => now(),
                'dikirim_oleh' => $this->userId(),
            ]);

            $this->catatAktivitas($pengajuanId, 'ajukan', 'Pengajuan data mentah diajukan ke Kominfo.');
        });
    }

    public function mintaRevisi(int $pengajuanId, string $catatan): void
    {
        $catatan = trim($catatan);

        if ($catatan === '') {
            throw ValidationException::withMessages([
                'catatan' => 'Catatan revisi wajib diisi.',
            ]);
        }

        DB::transaction(function () use ($pengajuanId, $catatan) {
            $pengajuan = $this->pengajuan($pengajuanId);

            if (! in_array($pengajuan->status, ['diajukan', 'terverifikasi'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Revisi hanya bisa diminta untuk pengajuan berstatus diajukan atau terverifikasi.',
                ]);
            }

            $this->updatePengajuan($pengajuanId, [
                'status' => 'revisi',
                'catatan_revisi' => $catatan,
            ]);

            $this->catatAktivitas($pengajuanId, 'revisi', $catatan);
        });
    }

    public function verifikasi(int $pengajuanId, ?string $catatan = null): void
    {
        DB::transaction(function () use ($pengajuanId, $catatan) {
            $pengajuan = $this->pengajuan($pengajuanId);

            if (! in_array($pengajuan->status, ['diajukan', 'revisi'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan hanya bisa diverifikasi dari status diajukan atau revisi.',
                ]);
            }

            $this->updatePengajuan($pengajuanId, [
                'status' => 'terverifikasi',
                'verified_at' => now(),
                'verified_by' => $this->userId(),
                'tanggal_verifikasi' => now(),
                'diverifikasi_oleh' => $this->userId(),
                'catatan_verifikasi' => $catatan,
            ]);

            $this->agregasiStatistik->regenerasiUntukPengajuan($pengajuanId, false);

            $this->catatAktivitas($pengajuanId, 'verifikasi', $catatan ?: 'Pengajuan data mentah diverifikasi.');
        });
    }

    public function tolak(int $pengajuanId, string $catatan): void
    {
        $catatan = trim($catatan);

        if ($catatan === '') {
            throw ValidationException::withMessages([
                'catatan' => 'Catatan penolakan wajib diisi.',
            ]);
        }

        DB::transaction(function () use ($pengajuanId, $catatan) {
            $pengajuan = $this->pengajuan($pengajuanId);

            if (! in_array($pengajuan->status, ['diajukan', 'revisi'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan hanya bisa ditolak dari status diajukan atau revisi.',
                ]);
            }

            $this->updatePengajuan($pengajuanId, [
                'status' => 'ditolak',
                'catatan_revisi' => $catatan,
            ]);

            $this->catatAktivitas($pengajuanId, 'tolak', $catatan);
        });
    }

    public function terbitkan(int $pengajuanId): void
    {
        DB::transaction(function () use ($pengajuanId) {
            $pengajuan = $this->pengajuan($pengajuanId);

            if ($pengajuan->status !== 'terverifikasi') {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan hanya bisa diterbitkan setelah terverifikasi.',
                ]);
            }

            $this->updatePengajuan($pengajuanId, [
                'status' => 'terbit',
                'published_at' => now(),
                'published_by' => $this->userId(),
                'tanggal_terbit' => now(),
            ]);

            $this->agregasiStatistik->regenerasiUntukPengajuan($pengajuanId, true);

            $this->catatAktivitas($pengajuanId, 'terbit', 'Pengajuan data mentah diterbitkan ke statistik publik.');
        });
    }

    public function tarikPublikasi(int $pengajuanId, ?string $catatan = null): void
    {
        DB::transaction(function () use ($pengajuanId, $catatan) {
            $pengajuan = $this->pengajuan($pengajuanId);

            if ($pengajuan->status !== 'terbit') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pengajuan berstatus terbit yang bisa ditarik.',
                ]);
            }

            $this->updatePengajuan($pengajuanId, [
                'status' => 'terverifikasi',
            ]);

            if (Schema::hasTable('ringkasan_statistik')) {
                $query = DB::table('ringkasan_statistik');

                if (Schema::hasColumn('ringkasan_statistik', 'periode_data_id')) {
                    $query->where('periode_data_id', $pengajuan->periode_data_id);
                }

                if (Schema::hasColumn('ringkasan_statistik', 'kecamatan_id')) {
                    $query->where('kecamatan_id', $pengajuan->kecamatan_id);
                }

                if (Schema::hasColumn('ringkasan_statistik', 'status_publikasi')) {
                    $query->where('status_publikasi', 'publik')
                        ->update([
                            'status_publikasi' => 'internal',
                            'updated_at' => now(),
                        ]);
                }
            }

            $this->catatAktivitas($pengajuanId, 'tarik_publikasi', $catatan ?: 'Publikasi pengajuan ditarik.');
        });
    }

    protected function pengajuan(int $pengajuanId): object
    {
        $pengajuan = DB::table('pengajuan_data')->where('id', $pengajuanId)->first();

        if (! $pengajuan) {
            throw ValidationException::withMessages([
                'pengajuan' => 'Pengajuan tidak ditemukan.',
            ]);
        }

        return $pengajuan;
    }

    protected function pastikanDataWajibLengkap(object $pengajuan): void
    {
        if (! Schema::hasTable('indikator_data') || ! Schema::hasTable('nilai_data_mentah')) {
            return;
        }

        $indikatorQuery = DB::table('indikator_data');

        if (Schema::hasColumn('indikator_data', 'aktif')) {
            $indikatorQuery->where('aktif', true);
        }

        if (! Schema::hasColumn('indikator_data', 'wajib_diisi')) {
            return;
        }

        $indikatorQuery->where('wajib_diisi', true);

        if (Schema::hasColumn('indikator_data', 'input_kecamatan')) {
            $indikatorQuery->where('input_kecamatan', true);
        } elseif (Schema::hasColumn('indikator_data', 'boleh_diinput_kecamatan')) {
            $indikatorQuery->where('boleh_diinput_kecamatan', true);
        }

        if (Schema::hasColumn('indikator_data', 'level_input')) {
            $indikatorQuery->where(function ($query) {
                $query
                    ->whereNull('level_input')
                    ->orWhere('level_input', 'desa');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi berdasarkan kelompok pengajuan
        |--------------------------------------------------------------------------
        | Jika pengajuan memilih kelompok_indikator, maka yang wajib dicek hanya
        | indikator pada kelompok itu. Contoh: pengajuan "pekerjaan" tidak akan
        | dipaksa mengisi indikator wajib dari kelompok "kependudukan".
        */
        $kelompokIndikator = data_get($pengajuan, 'kelompok_indikator');

        if ($kelompokIndikator) {
            $kelompokColumns = array_values(array_filter(
                ['kategori', 'kelompok', 'kelompok_indikator'],
                fn (string $column): bool => Schema::hasColumn('indikator_data', $column)
            ));

            if ($kelompokColumns !== []) {
                $indikatorQuery->where(function ($query) use ($kelompokColumns, $kelompokIndikator) {
                    foreach ($kelompokColumns as $index => $column) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $query->{$method}($column, $kelompokIndikator);
                    }
                });
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Jika pengajuan milik OPD, batasi indikator berdasarkan OPD bila kolomnya ada.
        */
        if (data_get($pengajuan, 'opd_id')) {
            $opdColumns = array_values(array_filter(
                ['opd_id', 'opd_pembina_id'],
                fn (string $column): bool => Schema::hasColumn('indikator_data', $column)
            ));

            if ($opdColumns !== []) {
                $indikatorQuery->where(function ($query) use ($opdColumns, $pengajuan) {
                    foreach ($opdColumns as $index => $column) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $query->{$method}($column, data_get($pengajuan, 'opd_id'));
                    }
                });
            }
        }

        $indikatorWajib = $indikatorQuery->pluck('id');

        if ($indikatorWajib->isEmpty()) {
            return;
        }

        if (! Schema::hasTable('desa')) {
            return;
        }

        $desaQuery = DB::table('desa')
            ->where('kecamatan_id', data_get($pengajuan, 'kecamatan_id'));

        if (Schema::hasColumn('desa', 'aktif')) {
            $desaQuery->where('aktif', true);
        }

        $desaIds = $desaQuery->pluck('id');

        if ($desaIds->isEmpty()) {
            return;
        }

        $missing = [];

        foreach ($indikatorWajib as $indikatorId) {
            foreach ($desaIds as $desaId) {
                $exists = $this->nilaiWajibSudahAda(
                    pengajuanId: (int) data_get($pengajuan, 'id'),
                    indikatorId: (int) $indikatorId,
                    desaId: (int) $desaId,
                );

                if (! $exists) {
                    $missing[] = [
                        'indikator_id' => $indikatorId,
                        'desa_id' => $desaId,
                    ];
                }
            }
        }

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'kelengkapan' => 'Masih ada indikator wajib yang belum diisi. Total kurang: ' . count($missing),
            ]);
        }
    }

    protected function nilaiWajibSudahAda(int $pengajuanId, int $indikatorId, int $desaId): bool
    {
        $nilaiColumns = Schema::getColumnListing('nilai_data_mentah');

        if (! in_array('sumber_id', $nilaiColumns, true) && ! in_array('desa_id', $nilaiColumns, true)) {
            return false;
        }

        $query = DB::table('nilai_data_mentah')
            ->where('pengajuan_data_id', $pengajuanId)
            ->where('indikator_data_id', $indikatorId);

        if (in_array('tipe_sumber', $nilaiColumns, true)) {
            $query->where('tipe_sumber', 'desa');
        }

        $query->where(function ($sourceQuery) use ($desaId, $nilaiColumns) {
            $hasSourceColumn = false;

            if (in_array('sumber_id', $nilaiColumns, true)) {
                $sourceQuery->where('sumber_id', $desaId);
                $hasSourceColumn = true;
            }

            if (in_array('desa_id', $nilaiColumns, true)) {
                if ($hasSourceColumn) {
                    $sourceQuery->orWhere('desa_id', $desaId);
                } else {
                    $sourceQuery->where('desa_id', $desaId);
                }
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Nilai 0 harus valid.
        |--------------------------------------------------------------------------
        | Yang dianggap belum isi hanya NULL atau string kosong.
        */
        $nilaiTerisiColumns = array_values(array_intersect(
            ['nilai_decimal', 'nilai', 'nilai_text'],
            $nilaiColumns
        ));

        if ($nilaiTerisiColumns === []) {
            return false;
        }

        $query->where(function ($nilaiQuery) use ($nilaiTerisiColumns) {
            foreach ($nilaiTerisiColumns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';

                $nilaiQuery->{$method}(function ($q) use ($column) {
                    $q->whereNotNull($column);
                    $q->where($column, '!=', '');
                });
            }
        });

        if (in_array('is_tidak_tersedia', $nilaiColumns, true)) {
            $query->where(function ($q) {
                $q->whereNull('is_tidak_tersedia')
                    ->orWhere('is_tidak_tersedia', false);
            });
        }

        return $query->exists();
    }

    protected function updatePengajuan(int $pengajuanId, array $data): void
    {
        $columns = Schema::getColumnListing('pengajuan_data');

        $payload = collect($data)
            ->filter(fn ($value, $key) => in_array($key, $columns, true))
            ->all();

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        if ($payload === []) {
            return;
        }

        DB::table('pengajuan_data')
            ->where('id', $pengajuanId)
            ->update($payload);
    }

    protected function catatAktivitas(int $pengajuanId, string $aksi, string $deskripsi): void
    {
        if (! Schema::hasTable('aktivitas_sistem')) {
            return;
        }

        $columns = Schema::getColumnListing('aktivitas_sistem');

        $payload = [
            'pengguna_id' => $this->userId(),
            'kategori_aktivitas' => 'workflow_pengajuan_data',
            'aksi' => $aksi,
            'tabel' => 'pengajuan_data',
            'record_id' => $pengajuanId,
            'deskripsi' => $deskripsi,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $payload = collect($payload)
            ->filter(fn ($value, $key) => in_array($key, $columns, true))
            ->all();

        if ($payload !== []) {
            DB::table('aktivitas_sistem')->insert($payload);
        }
    }

    protected function userId(): ?int
    {
        return Auth::id();
    }
}
