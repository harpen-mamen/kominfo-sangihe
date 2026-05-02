<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->updateIndikatorData();
        $this->updatePengajuanData();
        $this->updateNilaiDataMentah();
        $this->updateRingkasanStatistik();
    }

    public function down(): void
    {
        $this->rollbackRingkasanStatistik();
        $this->rollbackNilaiDataMentah();
        $this->rollbackPengajuanData();
        $this->rollbackIndikatorData();
    }

    private function updateIndikatorData(): void
    {
        if (! Schema::hasTable('indikator_data')) {
            return;
        }

        Schema::table('indikator_data', function (Blueprint $table): void {
            if (! Schema::hasColumn('indikator_data', 'kategori')) {
                $table
                    ->string('kategori', 120)
                    ->nullable()
                    ->after($this->afterExistingColumn('indikator_data', ['kelompok', 'nama', 'kode']));
            }

            if (! Schema::hasColumn('indikator_data', 'tipe_nilai')) {
                $table
                    ->string('tipe_nilai', 30)
                    ->default('decimal')
                    ->after($this->afterExistingColumn('indikator_data', ['satuan', 'kategori', 'nama']));
            }

            if (! Schema::hasColumn('indikator_data', 'level_input')) {
                $table
                    ->string('level_input', 30)
                    ->default('desa')
                    ->after($this->afterExistingColumn('indikator_data', ['tipe_nilai', 'satuan', 'kategori']));
            }

            if (! Schema::hasColumn('indikator_data', 'metode_agregasi')) {
                $table
                    ->string('metode_agregasi', 40)
                    ->default('sum')
                    ->after($this->afterExistingColumn('indikator_data', ['level_input', 'tipe_nilai', 'satuan']));
            }

            if (! Schema::hasColumn('indikator_data', 'wajib_diisi')) {
                $table
                    ->boolean('wajib_diisi')
                    ->default(true)
                    ->after($this->afterExistingColumn('indikator_data', ['metode_agregasi', 'level_input', 'tipe_nilai']));
            }

            if (! Schema::hasColumn('indikator_data', 'aktif')) {
                $table
                    ->boolean('aktif')
                    ->default(true)
                    ->after($this->afterExistingColumn('indikator_data', ['wajib_diisi', 'metode_agregasi', 'level_input']));
            }

            if (! Schema::hasColumn('indikator_data', 'urutan_tampil')) {
                $table
                    ->integer('urutan_tampil')
                    ->default(0)
                    ->after($this->afterExistingColumn('indikator_data', ['urutan', 'aktif', 'wajib_diisi']));
            }

            if (! Schema::hasColumn('indikator_data', 'batas_min')) {
                $table
                    ->decimal('batas_min', 18, 4)
                    ->nullable()
                    ->after($this->afterExistingColumn('indikator_data', ['urutan_tampil', 'urutan', 'aktif']));
            }

            if (! Schema::hasColumn('indikator_data', 'batas_max')) {
                $table
                    ->decimal('batas_max', 18, 4)
                    ->nullable()
                    ->after($this->afterExistingColumn('indikator_data', ['batas_min', 'urutan_tampil', 'urutan']));
            }

            if (! Schema::hasColumn('indikator_data', 'petunjuk_pengisian')) {
                $table
                    ->text('petunjuk_pengisian')
                    ->nullable()
                    ->after($this->afterExistingColumn('indikator_data', ['batas_max', 'batas_min', 'urutan_tampil']));
            }

            if (! Schema::hasColumn('indikator_data', 'boleh_publikasi')) {
                $table
                    ->boolean('boleh_publikasi')
                    ->default(true)
                    ->after($this->afterExistingColumn('indikator_data', ['aktif', 'petunjuk_pengisian', 'wajib_diisi']));
            }

            if (! Schema::hasColumn('indikator_data', 'opd_pembina_id')) {
                $table
                    ->foreignId('opd_pembina_id')
                    ->nullable()
                    ->after($this->afterExistingColumn('indikator_data', ['opd_id', 'boleh_publikasi', 'aktif']))
                    ->constrained('opd')
                    ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('indikator_data', 'kategori') && Schema::hasColumn('indikator_data', 'kelompok')) {
            DB::table('indikator_data')
                ->whereNull('kategori')
                ->update(['kategori' => DB::raw('kelompok')]);
        }

        if (Schema::hasColumn('indikator_data', 'urutan_tampil') && Schema::hasColumn('indikator_data', 'urutan')) {
            DB::table('indikator_data')
                ->where('urutan_tampil', 0)
                ->update(['urutan_tampil' => DB::raw('urutan')]);
        }

        if (
            Schema::hasColumn('indikator_data', 'aktif')
            && Schema::hasColumn('indikator_data', 'boleh_publikasi')
            && Schema::hasColumn('indikator_data', 'kategori')
            && ! $this->indexExists('indikator_data', 'indikator_publik_kategori_idx')
        ) {
            Schema::table('indikator_data', function (Blueprint $table): void {
                $table->index(['aktif', 'boleh_publikasi', 'kategori'], 'indikator_publik_kategori_idx');
            });
        }
    }

    private function updatePengajuanData(): void
    {
        if (! Schema::hasTable('pengajuan_data')) {
            return;
        }

        Schema::table('pengajuan_data', function (Blueprint $table): void {
            if (! Schema::hasColumn('pengajuan_data', 'submitted_at')) {
                $table
                    ->timestamp('submitted_at')
                    ->nullable()
                    ->after($this->afterExistingColumn('pengajuan_data', ['tanggal_kirim', 'status', 'periode_data_id']));
            }

            if (! Schema::hasColumn('pengajuan_data', 'submitted_by')) {
                $table
                    ->foreignId('submitted_by')
                    ->nullable()
                    ->after($this->afterExistingColumn('pengajuan_data', ['submitted_at', 'tanggal_kirim', 'status']))
                    ->constrained('pengguna')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('pengajuan_data', 'verified_at')) {
                $table
                    ->timestamp('verified_at')
                    ->nullable()
                    ->after($this->afterExistingColumn('pengajuan_data', ['tanggal_verifikasi', 'submitted_by', 'submitted_at']));
            }

            if (! Schema::hasColumn('pengajuan_data', 'verified_by')) {
                $table
                    ->foreignId('verified_by')
                    ->nullable()
                    ->after($this->afterExistingColumn('pengajuan_data', ['verified_at', 'tanggal_verifikasi', 'submitted_by']))
                    ->constrained('pengguna')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('pengajuan_data', 'published_at')) {
                $table
                    ->timestamp('published_at')
                    ->nullable()
                    ->after($this->afterExistingColumn('pengajuan_data', ['tanggal_terbit', 'verified_by', 'verified_at']));
            }

            if (! Schema::hasColumn('pengajuan_data', 'published_by')) {
                $table
                    ->foreignId('published_by')
                    ->nullable()
                    ->after($this->afterExistingColumn('pengajuan_data', ['published_at', 'tanggal_terbit', 'verified_by']))
                    ->constrained('pengguna')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('pengajuan_data', 'catatan_revisi')) {
                $table
                    ->text('catatan_revisi')
                    ->nullable()
                    ->after($this->afterExistingColumn('pengajuan_data', ['catatan', 'published_by', 'published_at']));
            }

            if (! Schema::hasColumn('pengajuan_data', 'catatan_verifikasi')) {
                $table
                    ->text('catatan_verifikasi')
                    ->nullable()
                    ->after($this->afterExistingColumn('pengajuan_data', ['catatan_revisi', 'catatan', 'published_by']));
            }

            if (! Schema::hasColumn('pengajuan_data', 'kelompok_indikator')) {
                $table
                    ->string('kelompok_indikator', 120)
                    ->nullable()
                    ->after($this->afterExistingColumn('pengajuan_data', ['periode_data_id', 'kecamatan_id', 'status']));
            }
        });

        if (
            Schema::hasColumn('pengajuan_data', 'submitted_at')
            && Schema::hasColumn('pengajuan_data', 'tanggal_kirim')
        ) {
            $payload = ['submitted_at' => DB::raw('tanggal_kirim')];

            if (Schema::hasColumn('pengajuan_data', 'submitted_by') && Schema::hasColumn('pengajuan_data', 'dikirim_oleh')) {
                $payload['submitted_by'] = DB::raw('dikirim_oleh');
            }

            DB::table('pengajuan_data')
                ->whereNull('submitted_at')
                ->whereNotNull('tanggal_kirim')
                ->update($payload);
        }

        if (
            Schema::hasColumn('pengajuan_data', 'verified_at')
            && Schema::hasColumn('pengajuan_data', 'tanggal_verifikasi')
        ) {
            $payload = ['verified_at' => DB::raw('tanggal_verifikasi')];

            if (Schema::hasColumn('pengajuan_data', 'verified_by') && Schema::hasColumn('pengajuan_data', 'diverifikasi_oleh')) {
                $payload['verified_by'] = DB::raw('diverifikasi_oleh');
            }

            DB::table('pengajuan_data')
                ->whereNull('verified_at')
                ->whereNotNull('tanggal_verifikasi')
                ->update($payload);
        }

        if (
            Schema::hasColumn('pengajuan_data', 'published_at')
            && Schema::hasColumn('pengajuan_data', 'tanggal_terbit')
        ) {
            DB::table('pengajuan_data')
                ->whereNull('published_at')
                ->whereNotNull('tanggal_terbit')
                ->update(['published_at' => DB::raw('tanggal_terbit')]);
        }
    }

    private function updateNilaiDataMentah(): void
    {
        if (! Schema::hasTable('nilai_data_mentah')) {
            return;
        }

        Schema::table('nilai_data_mentah', function (Blueprint $table): void {
            if (Schema::hasColumn('nilai_data_mentah', 'desa_id')) {
                try {
                    $table->foreignId('desa_id')->nullable()->change();
                } catch (\Throwable) {
                    // Abaikan jika DBAL tidak tersedia atau FK lokal berbeda.
                }
            }

            if (! Schema::hasColumn('nilai_data_mentah', 'tipe_sumber')) {
                $table
                    ->string('tipe_sumber', 40)
                    ->default('desa')
                    ->after($this->afterExistingColumn('nilai_data_mentah', ['indikator_data_id', 'desa_id', 'pengajuan_data_id']));
            }

            if (! Schema::hasColumn('nilai_data_mentah', 'sumber_id')) {
                $table
                    ->unsignedBigInteger('sumber_id')
                    ->nullable()
                    ->after($this->afterExistingColumn('nilai_data_mentah', ['tipe_sumber', 'desa_id', 'indikator_data_id']));
            }

            if (! Schema::hasColumn('nilai_data_mentah', 'nilai_decimal')) {
                $table
                    ->decimal('nilai_decimal', 18, 4)
                    ->nullable()
                    ->after($this->afterExistingColumn('nilai_data_mentah', ['nilai', 'sumber_id', 'desa_id']));
            }

            if (! Schema::hasColumn('nilai_data_mentah', 'nilai_text')) {
                $table
                    ->text('nilai_text')
                    ->nullable()
                    ->after($this->afterExistingColumn('nilai_data_mentah', ['nilai_decimal', 'nilai', 'sumber_id']));
            }

            if (! Schema::hasColumn('nilai_data_mentah', 'is_tidak_tersedia')) {
                $table
                    ->boolean('is_tidak_tersedia')
                    ->default(false)
                    ->after($this->afterExistingColumn('nilai_data_mentah', ['nilai_text', 'nilai_decimal', 'nilai']));
            }

            if (! Schema::hasColumn('nilai_data_mentah', 'status_validasi')) {
                $table
                    ->string('status_validasi', 40)
                    ->nullable()
                    ->after($this->afterExistingColumn('nilai_data_mentah', ['catatan', 'is_tidak_tersedia', 'nilai_text']));
            }

            if (! Schema::hasColumn('nilai_data_mentah', 'pesan_validasi')) {
                $table
                    ->text('pesan_validasi')
                    ->nullable()
                    ->after($this->afterExistingColumn('nilai_data_mentah', ['status_validasi', 'catatan', 'is_tidak_tersedia']));
            }
        });

        if (
            Schema::hasColumn('nilai_data_mentah', 'nilai_decimal')
            && Schema::hasColumn('nilai_data_mentah', 'nilai')
        ) {
            $payload = ['nilai_decimal' => DB::raw('nilai')];

            if (Schema::hasColumn('nilai_data_mentah', 'tipe_sumber')) {
                $payload['tipe_sumber'] = 'desa';
            }

            if (
                Schema::hasColumn('nilai_data_mentah', 'sumber_id')
                && Schema::hasColumn('nilai_data_mentah', 'desa_id')
            ) {
                $payload['sumber_id'] = DB::raw('desa_id');
            }

            DB::table('nilai_data_mentah')
                ->whereNull('nilai_decimal')
                ->update($payload);
        }

        if (
            Schema::hasColumn('nilai_data_mentah', 'pengajuan_data_id')
            && Schema::hasColumn('nilai_data_mentah', 'indikator_data_id')
            && Schema::hasColumn('nilai_data_mentah', 'tipe_sumber')
            && Schema::hasColumn('nilai_data_mentah', 'sumber_id')
            && ! $this->indexExists('nilai_data_mentah', 'nilai_mentah_pengajuan_indikator_sumber_unique')
        ) {
            try {
                Schema::table('nilai_data_mentah', function (Blueprint $table): void {
                    $table->unique(
                        ['pengajuan_data_id', 'indikator_data_id', 'tipe_sumber', 'sumber_id'],
                        'nilai_mentah_pengajuan_indikator_sumber_unique'
                    );
                });
            } catch (\Throwable) {
                // Jika data lokal masih duplikat, biarkan migration tetap lanjut.
                // Validasi model/service tetap akan menjaga input baru.
            }
        }
    }

    private function updateRingkasanStatistik(): void
    {
        if (! Schema::hasTable('ringkasan_statistik')) {
            return;
        }

        Schema::table('ringkasan_statistik', function (Blueprint $table): void {
            if (! Schema::hasColumn('ringkasan_statistik', 'jumlah_sumber_masuk')) {
                $table
                    ->unsignedInteger('jumlah_sumber_masuk')
                    ->default(0)
                    ->after($this->afterExistingColumn('ringkasan_statistik', ['nilai_persen', 'nilai', 'indikator_data_id']));
            }

            if (! Schema::hasColumn('ringkasan_statistik', 'jumlah_sumber_wajib')) {
                $table
                    ->unsignedInteger('jumlah_sumber_wajib')
                    ->default(0)
                    ->after($this->afterExistingColumn('ringkasan_statistik', ['jumlah_sumber_masuk', 'nilai_persen', 'nilai']));
            }

            if (! Schema::hasColumn('ringkasan_statistik', 'persentase_kelengkapan')) {
                $table
                    ->decimal('persentase_kelengkapan', 5, 2)
                    ->default(0)
                    ->after($this->afterExistingColumn('ringkasan_statistik', ['jumlah_sumber_wajib', 'jumlah_sumber_masuk', 'nilai_persen']));
            }

            if (! Schema::hasColumn('ringkasan_statistik', 'status_rekap')) {
                $table
                    ->string('status_rekap', 30)
                    ->default('sementara')
                    ->after($this->afterExistingColumn('ringkasan_statistik', ['persentase_kelengkapan', 'jumlah_sumber_wajib', 'jumlah_sumber_masuk']));
            }

            if (! Schema::hasColumn('ringkasan_statistik', 'status_publikasi')) {
                $table
                    ->string('status_publikasi', 30)
                    ->default('internal')
                    ->after($this->afterExistingColumn('ringkasan_statistik', ['status_rekap', 'persentase_kelengkapan', 'jumlah_sumber_wajib']));
            }

            if (! Schema::hasColumn('ringkasan_statistik', 'published_at')) {
                $table
                    ->timestamp('published_at')
                    ->nullable()
                    ->after($this->afterExistingColumn('ringkasan_statistik', ['status_publikasi', 'status_rekap', 'persentase_kelengkapan']));
            }
        });

        if (
            Schema::hasColumn('ringkasan_statistik', 'status_publikasi')
            && Schema::hasColumn('ringkasan_statistik', 'status_rekap')
            && Schema::hasColumn('ringkasan_statistik', 'periode_data_id')
            && ! $this->indexExists('ringkasan_statistik', 'ringkasan_publik_idx')
        ) {
            Schema::table('ringkasan_statistik', function (Blueprint $table): void {
                $table->index(['status_publikasi', 'status_rekap', 'periode_data_id'], 'ringkasan_publik_idx');
            });
        }
    }

    private function rollbackRingkasanStatistik(): void
    {
        if (! Schema::hasTable('ringkasan_statistik')) {
            return;
        }

        if ($this->indexExists('ringkasan_statistik', 'ringkasan_publik_idx')) {
            try {
                Schema::table('ringkasan_statistik', function (Blueprint $table): void {
                    $table->dropIndex('ringkasan_publik_idx');
                });
            } catch (\Throwable) {
                // no-op
            }
        }

        Schema::table('ringkasan_statistik', function (Blueprint $table): void {
            foreach ([
                'published_at',
                'status_publikasi',
                'status_rekap',
                'persentase_kelengkapan',
                'jumlah_sumber_wajib',
                'jumlah_sumber_masuk',
            ] as $column) {
                if (Schema::hasColumn('ringkasan_statistik', $column)) {
                    try {
                        $table->dropColumn($column);
                    } catch (\Throwable) {
                        // no-op
                    }
                }
            }
        });
    }

    private function rollbackNilaiDataMentah(): void
    {
        if (! Schema::hasTable('nilai_data_mentah')) {
            return;
        }

        if ($this->indexExists('nilai_data_mentah', 'nilai_mentah_pengajuan_indikator_sumber_unique')) {
            try {
                Schema::table('nilai_data_mentah', function (Blueprint $table): void {
                    $table->dropUnique('nilai_mentah_pengajuan_indikator_sumber_unique');
                });
            } catch (\Throwable) {
                // no-op
            }
        }

        Schema::table('nilai_data_mentah', function (Blueprint $table): void {
            foreach ([
                'pesan_validasi',
                'status_validasi',
                'is_tidak_tersedia',
                'nilai_text',
                'nilai_decimal',
                'sumber_id',
                'tipe_sumber',
            ] as $column) {
                if (Schema::hasColumn('nilai_data_mentah', $column)) {
                    try {
                        $table->dropColumn($column);
                    } catch (\Throwable) {
                        // no-op
                    }
                }
            }
        });
    }

    private function rollbackPengajuanData(): void
    {
        if (! Schema::hasTable('pengajuan_data')) {
            return;
        }

        Schema::table('pengajuan_data', function (Blueprint $table): void {
            foreach ([
                'published_by',
                'verified_by',
                'submitted_by',
            ] as $column) {
                if (Schema::hasColumn('pengajuan_data', $column)) {
                    try {
                        $table->dropConstrainedForeignId($column);
                    } catch (\Throwable) {
                        try {
                            $table->dropColumn($column);
                        } catch (\Throwable) {
                            // no-op
                        }
                    }
                }
            }

            foreach ([
                'kelompok_indikator',
                'catatan_verifikasi',
                'catatan_revisi',
                'published_at',
                'verified_at',
                'submitted_at',
            ] as $column) {
                if (Schema::hasColumn('pengajuan_data', $column)) {
                    try {
                        $table->dropColumn($column);
                    } catch (\Throwable) {
                        // no-op
                    }
                }
            }
        });
    }

    private function rollbackIndikatorData(): void
    {
        if (! Schema::hasTable('indikator_data')) {
            return;
        }

        if ($this->indexExists('indikator_data', 'indikator_publik_kategori_idx')) {
            try {
                Schema::table('indikator_data', function (Blueprint $table): void {
                    $table->dropIndex('indikator_publik_kategori_idx');
                });
            } catch (\Throwable) {
                // no-op
            }
        }

        Schema::table('indikator_data', function (Blueprint $table): void {
            if (Schema::hasColumn('indikator_data', 'opd_pembina_id')) {
                try {
                    $table->dropConstrainedForeignId('opd_pembina_id');
                } catch (\Throwable) {
                    try {
                        $table->dropColumn('opd_pembina_id');
                    } catch (\Throwable) {
                        // no-op
                    }
                }
            }

            foreach ([
                'boleh_publikasi',
                'petunjuk_pengisian',
                'batas_max',
                'batas_min',
                'urutan_tampil',
                'wajib_diisi',
                'metode_agregasi',
                'level_input',
                'tipe_nilai',
                'kategori',
            ] as $column) {
                if (Schema::hasColumn('indikator_data', $column)) {
                    try {
                        $table->dropColumn($column);
                    } catch (\Throwable) {
                        // no-op
                    }
                }
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        try {
            $database = DB::getDatabaseName();

            return DB::table('information_schema.statistics')
                ->where('table_schema', $database)
                ->where('table_name', $table)
                ->where('index_name', $index)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    private function afterExistingColumn(string $table, array $candidates): string
    {
        foreach ($candidates as $candidate) {
            if (Schema::hasColumn($table, $candidate)) {
                return $candidate;
            }
        }

        return 'id';
    }
};