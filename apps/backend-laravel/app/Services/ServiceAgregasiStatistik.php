<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ServiceAgregasiStatistik
{
    /**
     * Kompatibilitas untuk PengajuanDataObserver.
     * Observer lama memanggil sinkronkan($pengajuan).
     */
    public function sinkronkan(object $pengajuan): void
    {
        $status = (string) data_get($pengajuan, 'status', '');

        if (! in_array($status, ['terverifikasi', 'terbit'], true)) {
            $this->hapusRekap($pengajuan);

            return;
        }

        $this->regenerasiUntukPengajuan((int) data_get($pengajuan, 'id'), $status === 'terbit');
    }

    /**
     * Kompatibilitas untuk PengajuanDataObserver.
     * Observer lama memanggil hapusRekap($pengajuan).
     */
    public function hapusRekap(object $pengajuan): void
    {
        if (! Schema::hasTable('ringkasan_statistik')) {
            return;
        }

        $periodeDataId = data_get($pengajuan, 'periode_data_id');
        $kecamatanId = data_get($pengajuan, 'kecamatan_id');
        $opdId = data_get($pengajuan, 'opd_id');

        if (! $periodeDataId) {
            return;
        }

        $query = DB::table('ringkasan_statistik')
            ->where('periode_data_id', $periodeDataId);

        $query->where(function ($subQuery) use ($kecamatanId, $opdId) {
            $hasAnyScope = false;

            if ($kecamatanId && Schema::hasColumn('ringkasan_statistik', 'kecamatan_id')) {
                $subQuery->where('kecamatan_id', $kecamatanId);
                $hasAnyScope = true;
            }

            if ($opdId && Schema::hasColumn('ringkasan_statistik', 'opd_id')) {
                if ($hasAnyScope) {
                    $subQuery->orWhere('opd_id', $opdId);
                } else {
                    $subQuery->where('opd_id', $opdId);
                }

                $hasAnyScope = true;
            }

            if (Schema::hasColumn('ringkasan_statistik', 'tingkat_rekap')) {
                if ($hasAnyScope) {
                    $subQuery->orWhere('tingkat_rekap', 'kabupaten');
                } else {
                    $subQuery->where('tingkat_rekap', 'kabupaten');
                }
            }
        });

        $query->delete();
    }

    public function regenerasiUntukPengajuan(int $pengajuanId, bool $untukPublik = false): void
    {
        if (! Schema::hasTable('pengajuan_data')) {
            return;
        }

        $pengajuan = DB::table('pengajuan_data')->where('id', $pengajuanId)->first();

        if (! $pengajuan) {
            return;
        }

        $status = (string) data_get($pengajuan, 'status', '');

        if ($untukPublik && $status !== 'terbit') {
            return;
        }

        if (! in_array($status, ['terverifikasi', 'terbit'], true)) {
            $this->hapusRekap($pengajuan);

            return;
        }

        DB::transaction(function () use ($pengajuan, $untukPublik) {
            $this->hapusRekap($pengajuan);
            $this->buatRekapDesaDanKecamatan($pengajuan, $untukPublik);
            $this->buatRekapKabupaten((int) data_get($pengajuan, 'periode_data_id'), $untukPublik);
        });
    }

    public function regenerasiPeriode(int $periodeDataId, bool $untukPublik = false): void
    {
        if (! Schema::hasTable('pengajuan_data')) {
            return;
        }

        $query = DB::table('pengajuan_data')
            ->where('periode_data_id', $periodeDataId);

        if ($untukPublik) {
            $query->where('status', 'terbit');
        } else {
            $query->whereIn('status', ['terverifikasi', 'terbit']);
        }

        foreach ($query->pluck('id') as $pengajuanId) {
            $this->regenerasiUntukPengajuan((int) $pengajuanId, $untukPublik);
        }
    }

    protected function buatRekapDesaDanKecamatan(object $pengajuan, bool $untukPublik): void
    {
        $nilaiRows = $this->nilaiMentahUntukPengajuan((int) data_get($pengajuan, 'id'));

        if ($nilaiRows->isEmpty()) {
            return;
        }

        $indikatorMap = $this->indikatorMap($nilaiRows->pluck('indikator_data_id')->unique()->values());

        $desaWajib = Schema::hasTable('desa') && data_get($pengajuan, 'kecamatan_id')
            ? DB::table('desa')->where('kecamatan_id', data_get($pengajuan, 'kecamatan_id'))->count()
            : 1;

        $desaWajib = max((int) $desaWajib, 1);

        $nilaiRows
            ->where('tipe_sumber', 'desa')
            ->groupBy(fn ($row) => data_get($row, 'indikator_data_id') . ':' . data_get($row, 'sumber_id'))
            ->each(function (Collection $rows) use ($pengajuan, $indikatorMap, $untukPublik) {
                $first = $rows->first();
                $indikator = $indikatorMap->get((int) data_get($first, 'indikator_data_id'));
                $nilai = $this->hitungAgregasi($rows, $indikator);
                $masuk = $this->jumlahNilaiMasuk($rows);

                $this->simpanRingkasan([
                    'periode_data_id' => data_get($pengajuan, 'periode_data_id'),
                    'indikator_data_id' => data_get($first, 'indikator_data_id'),
                    'tingkat_rekap' => 'desa',
                    'kecamatan_id' => data_get($pengajuan, 'kecamatan_id'),
                    'opd_id' => data_get($pengajuan, 'opd_id'),
                    'desa_id' => data_get($first, 'sumber_id'),
                    'nilai' => $nilai,
                    'satuan' => data_get($indikator, 'satuan'),
                    'jumlah_sumber_masuk' => $masuk,
                    'jumlah_sumber_wajib' => 1,
                    'persentase_kelengkapan' => $this->persentase($masuk, 1),
                    'status_rekap' => $masuk >= 1 ? 'final' : 'sementara',
                    'status_publikasi' => $untukPublik ? 'publik' : 'internal',
                    'published_at' => $untukPublik ? now() : null,
                    'published_by' => $untukPublik ? data_get($pengajuan, 'published_by') : null,
                ]);
            });

        $nilaiRows
            ->groupBy('indikator_data_id')
            ->each(function (Collection $rows) use ($pengajuan, $indikatorMap, $desaWajib, $untukPublik) {
                $first = $rows->first();
                $indikator = $indikatorMap->get((int) data_get($first, 'indikator_data_id'));
                $nilai = $this->hitungAgregasi($rows, $indikator);
                $masuk = $this->jumlahSumberUnikMasuk($rows);

                $this->simpanRingkasan([
                    'periode_data_id' => data_get($pengajuan, 'periode_data_id'),
                    'indikator_data_id' => data_get($first, 'indikator_data_id'),
                    'tingkat_rekap' => 'kecamatan',
                    'kecamatan_id' => data_get($pengajuan, 'kecamatan_id'),
                    'opd_id' => data_get($pengajuan, 'opd_id'),
                    'desa_id' => null,
                    'nilai' => $nilai,
                    'satuan' => data_get($indikator, 'satuan'),
                    'jumlah_sumber_masuk' => $masuk,
                    'jumlah_sumber_wajib' => $desaWajib,
                    'persentase_kelengkapan' => $this->persentase($masuk, $desaWajib),
                    'status_rekap' => $masuk >= $desaWajib ? 'final' : 'sementara',
                    'status_publikasi' => $untukPublik ? 'publik' : 'internal',
                    'published_at' => $untukPublik ? now() : null,
                    'published_by' => $untukPublik ? data_get($pengajuan, 'published_by') : null,
                ]);
            });
    }

    protected function buatRekapKabupaten(int $periodeDataId, bool $untukPublik): void
    {
        if (! Schema::hasTable('pengajuan_data')) {
            return;
        }

        $statusPengajuan = $untukPublik ? ['terbit'] : ['terverifikasi', 'terbit'];

        $pengajuanIds = DB::table('pengajuan_data')
            ->where('periode_data_id', $periodeDataId)
            ->whereIn('status', $statusPengajuan)
            ->pluck('id');

        if ($pengajuanIds->isEmpty()) {
            return;
        }

        $rows = $this->nilaiMentahQuery()
            ->whereIn('ndm.pengajuan_data_id', $pengajuanIds)
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $indikatorMap = $this->indikatorMap($rows->pluck('indikator_data_id')->unique()->values());

        $jumlahKecamatanWajib = Schema::hasTable('kecamatan')
            ? max((int) DB::table('kecamatan')->count(), 1)
            : 1;

        $rows
            ->groupBy('indikator_data_id')
            ->each(function (Collection $groupedRows) use ($periodeDataId, $indikatorMap, $jumlahKecamatanWajib, $untukPublik) {
                $first = $groupedRows->first();
                $indikator = $indikatorMap->get((int) data_get($first, 'indikator_data_id'));
                $nilai = $this->hitungAgregasi($groupedRows, $indikator);
                $masuk = $groupedRows->pluck('kecamatan_id')->filter()->unique()->count();

                $this->simpanRingkasan([
                    'periode_data_id' => $periodeDataId,
                    'indikator_data_id' => data_get($first, 'indikator_data_id'),
                    'tingkat_rekap' => 'kabupaten',
                    'kecamatan_id' => null,
                    'opd_id' => null,
                    'desa_id' => null,
                    'nilai' => $nilai,
                    'satuan' => data_get($indikator, 'satuan'),
                    'jumlah_sumber_masuk' => $masuk,
                    'jumlah_sumber_wajib' => $jumlahKecamatanWajib,
                    'persentase_kelengkapan' => $this->persentase($masuk, $jumlahKecamatanWajib),
                    'status_rekap' => $masuk >= $jumlahKecamatanWajib ? 'final' : 'sementara',
                    'status_publikasi' => $untukPublik ? 'publik' : 'internal',
                    'published_at' => $untukPublik ? now() : null,
                    'published_by' => null,
                ]);
            });
    }

    protected function nilaiMentahUntukPengajuan(int $pengajuanId): Collection
    {
        return $this->nilaiMentahQuery()
            ->where('ndm.pengajuan_data_id', $pengajuanId)
            ->get();
    }

    protected function nilaiMentahQuery()
    {
        $nilaiColumn = $this->nilaiMentahExpression();

        $query = DB::table('nilai_data_mentah as ndm')
            ->join('pengajuan_data as pd', 'pd.id', '=', 'ndm.pengajuan_data_id');

        $select = [
            'ndm.id',
            'ndm.pengajuan_data_id',
            'ndm.indikator_data_id',
            'pd.periode_data_id',
            'pd.kecamatan_id',
            DB::raw($nilaiColumn . ' as nilai_agregasi'),
        ];

        if (Schema::hasColumn('nilai_data_mentah', 'tipe_sumber')) {
            $select[] = 'ndm.tipe_sumber';
        } else {
            $select[] = DB::raw("'desa' as tipe_sumber");
        }

        if (Schema::hasColumn('nilai_data_mentah', 'sumber_id')) {
            $select[] = 'ndm.sumber_id';
        } elseif (Schema::hasColumn('nilai_data_mentah', 'desa_id')) {
            $select[] = DB::raw('ndm.desa_id as sumber_id');
        } else {
            $select[] = DB::raw('NULL as sumber_id');
        }

        return $query
            ->select($select)
            ->when(Schema::hasColumn('nilai_data_mentah', 'is_tidak_tersedia'), function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('ndm.is_tidak_tersedia')
                        ->orWhere('ndm.is_tidak_tersedia', false);
                });
            });
    }

    protected function nilaiMentahExpression(): string
    {
        $candidates = [
            'nilai_decimal',
            'nilai',
            'nilai_total',
            'total_nilai',
            'jumlah',
            'value',
        ];

        $parts = [];

        foreach ($candidates as $column) {
            if (Schema::hasColumn('nilai_data_mentah', $column)) {
                $parts[] = "ndm.{$column}";
            }
        }

        if ($parts === []) {
            return '0';
        }

        return 'COALESCE(' . implode(', ', $parts) . ', 0)';
    }

    protected function indikatorMap(Collection $ids): Collection
    {
        if (! Schema::hasTable('indikator_data') || $ids->isEmpty()) {
            return collect();
        }

        return DB::table('indikator_data')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }

    protected function hitungAgregasi(Collection $rows, ?object $indikator): float
    {
        $values = $rows
            ->map(fn ($row) => data_get($row, 'nilai_agregasi'))
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (float) $value)
            ->values();

        if ($values->isEmpty()) {
            return 0.0;
        }

        $metode = strtolower((string) data_get($indikator, 'metode_agregasi', 'sum'));

        return match ($metode) {
            'average', 'avg', 'weighted_average' => round((float) $values->avg(), 4),
            'latest' => (float) $values->last(),
            'count' => (float) $values->count(),
            'formula' => 0.0,
            default => round((float) $values->sum(), 4),
        };
    }

    protected function jumlahNilaiMasuk(Collection $rows): int
    {
        return $rows
            ->filter(fn ($row) => data_get($row, 'nilai_agregasi') !== null && data_get($row, 'nilai_agregasi') !== '')
            ->count();
    }

    protected function jumlahSumberUnikMasuk(Collection $rows): int
    {
        return $rows
            ->filter(fn ($row) => data_get($row, 'nilai_agregasi') !== null && data_get($row, 'nilai_agregasi') !== '')
            ->map(fn ($row) => data_get($row, 'tipe_sumber') . ':' . data_get($row, 'sumber_id'))
            ->unique()
            ->count();
    }

    protected function persentase(int $masuk, int $wajib): float
    {
        if ($wajib <= 0) {
            return 0;
        }

        return round(min(100, ($masuk / $wajib) * 100), 2);
    }

    protected function simpanRingkasan(array $data): void
    {
        if (! Schema::hasTable('ringkasan_statistik')) {
            return;
        }

        $columns = Schema::getColumnListing('ringkasan_statistik');

        $keys = [];

        foreach (['periode_data_id', 'indikator_data_id', 'tingkat_rekap', 'kecamatan_id', 'desa_id'] as $key) {
            if (in_array($key, $columns, true)) {
                $keys[$key] = $data[$key] ?? null;
            }
        }

        if (in_array('opd_id', $columns, true) && array_key_exists('opd_id', $data)) {
            $keys['opd_id'] = $data['opd_id'];
        }

        if ($keys === []) {
            return;
        }

        $payload = [];

        foreach ($data as $key => $value) {
            if ($key === 'nilai') {
                $this->isiKolomNilaiRingkasan($payload, (float) $value);

                continue;
            }

            if (in_array($key, $columns, true)) {
                $payload[$key] = $value;
            }
        }

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        if (in_array('created_at', $columns, true) && ! DB::table('ringkasan_statistik')->where($keys)->exists()) {
            $payload['created_at'] = now();
        }

        DB::table('ringkasan_statistik')->updateOrInsert($keys, $payload);
    }

    protected function isiKolomNilaiRingkasan(array &$payload, float $nilai): void
    {
        $candidates = [
            'nilai',
            'nilai_total',
            'total_nilai',
            'jumlah_nilai',
            'nilai_persen',
            'jumlah',
            'total',
            'value',
        ];

        foreach ($candidates as $column) {
            if (Schema::hasColumn('ringkasan_statistik', $column)) {
                $payload[$column] = $nilai;

                return;
            }
        }
    }
}