<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('indikator_data')) {
            Schema::table('indikator_data', function (Blueprint $table) {
                if (! Schema::hasColumn('indikator_data', 'kategori')) {
                    $table->string('kategori')->nullable()->after('nama');
                }

                if (! Schema::hasColumn('indikator_data', 'tipe_nilai')) {
                    $table->string('tipe_nilai')->default('decimal')->after('satuan');
                }

                if (! Schema::hasColumn('indikator_data', 'level_input')) {
                    $table->string('level_input')->default('desa')->after('tipe_nilai');
                }

                if (! Schema::hasColumn('indikator_data', 'metode_agregasi')) {
                    $table->string('metode_agregasi')->default('sum')->after('level_input');
                }

                if (! Schema::hasColumn('indikator_data', 'wajib_diisi')) {
                    $table->boolean('wajib_diisi')->default(false)->after('metode_agregasi');
                }

                if (! Schema::hasColumn('indikator_data', 'aktif')) {
                    $table->boolean('aktif')->default(true)->after('wajib_diisi');
                }

                if (! Schema::hasColumn('indikator_data', 'urutan_tampil')) {
                    $table->unsignedInteger('urutan_tampil')->default(0)->after('aktif');
                }

                if (! Schema::hasColumn('indikator_data', 'batas_min')) {
                    $table->decimal('batas_min', 20, 4)->nullable()->after('urutan_tampil');
                }

                if (! Schema::hasColumn('indikator_data', 'batas_max')) {
                    $table->decimal('batas_max', 20, 4)->nullable()->after('batas_min');
                }

                if (! Schema::hasColumn('indikator_data', 'petunjuk_pengisian')) {
                    $table->text('petunjuk_pengisian')->nullable()->after('batas_max');
                }

                if (! Schema::hasColumn('indikator_data', 'boleh_publikasi')) {
                    $table->boolean('boleh_publikasi')->default(true)->after('petunjuk_pengisian');
                }

                if (! Schema::hasColumn('indikator_data', 'opd_pembina_id')) {
                    $table->foreignId('opd_pembina_id')->nullable()->after('boleh_publikasi')->constrained('opd')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('nilai_data_mentah')) {
            Schema::table('nilai_data_mentah', function (Blueprint $table) {
                if (! Schema::hasColumn('nilai_data_mentah', 'nilai_decimal')) {
                    $table->decimal('nilai_decimal', 20, 4)->nullable()->after('nilai');
                }

                if (! Schema::hasColumn('nilai_data_mentah', 'nilai_text')) {
                    $table->text('nilai_text')->nullable()->after('nilai_decimal');
                }

                if (! Schema::hasColumn('nilai_data_mentah', 'is_tidak_tersedia')) {
                    $table->boolean('is_tidak_tersedia')->default(false)->after('nilai_text');
                }

                if (! Schema::hasColumn('nilai_data_mentah', 'catatan')) {
                    $table->text('catatan')->nullable()->after('is_tidak_tersedia');
                }

                if (! Schema::hasColumn('nilai_data_mentah', 'status_validasi')) {
                    $table->string('status_validasi')->nullable()->after('catatan');
                }

                if (! Schema::hasColumn('nilai_data_mentah', 'pesan_validasi')) {
                    $table->text('pesan_validasi')->nullable()->after('status_validasi');
                }
            });
        }

        if (Schema::hasTable('pengajuan_data')) {
            Schema::table('pengajuan_data', function (Blueprint $table) {
                if (! Schema::hasColumn('pengajuan_data', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable()->after('status');
                }

                if (! Schema::hasColumn('pengajuan_data', 'submitted_by')) {
                    $table->foreignId('submitted_by')->nullable()->after('submitted_at')->constrained('pengguna')->nullOnDelete();
                }

                if (! Schema::hasColumn('pengajuan_data', 'verified_at')) {
                    $table->timestamp('verified_at')->nullable()->after('submitted_by');
                }

                if (! Schema::hasColumn('pengajuan_data', 'verified_by')) {
                    $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('pengguna')->nullOnDelete();
                }

                if (! Schema::hasColumn('pengajuan_data', 'published_at')) {
                    $table->timestamp('published_at')->nullable()->after('verified_by');
                }

                if (! Schema::hasColumn('pengajuan_data', 'published_by')) {
                    $table->foreignId('published_by')->nullable()->after('published_at')->constrained('pengguna')->nullOnDelete();
                }

                if (! Schema::hasColumn('pengajuan_data', 'catatan_revisi')) {
                    $table->text('catatan_revisi')->nullable()->after('published_by');
                }

                if (! Schema::hasColumn('pengajuan_data', 'catatan_verifikasi')) {
                    $table->text('catatan_verifikasi')->nullable()->after('catatan_revisi');
                }
            });
        }

        if (Schema::hasTable('ringkasan_statistik')) {
            Schema::table('ringkasan_statistik', function (Blueprint $table) {
                if (! Schema::hasColumn('ringkasan_statistik', 'jumlah_sumber_masuk')) {
                    $table->unsignedInteger('jumlah_sumber_masuk')->default(0)->after('nilai');
                }

                if (! Schema::hasColumn('ringkasan_statistik', 'jumlah_sumber_wajib')) {
                    $table->unsignedInteger('jumlah_sumber_wajib')->default(0)->after('jumlah_sumber_masuk');
                }

                if (! Schema::hasColumn('ringkasan_statistik', 'persentase_kelengkapan')) {
                    $table->decimal('persentase_kelengkapan', 8, 2)->default(0)->after('jumlah_sumber_wajib');
                }

                if (! Schema::hasColumn('ringkasan_statistik', 'status_rekap')) {
                    $table->string('status_rekap')->default('sementara')->after('persentase_kelengkapan');
                }

                if (! Schema::hasColumn('ringkasan_statistik', 'status_publikasi')) {
                    $table->string('status_publikasi')->default('internal')->after('status_rekap');
                }

                if (! Schema::hasColumn('ringkasan_statistik', 'published_at')) {
                    $table->timestamp('published_at')->nullable()->after('status_publikasi');
                }

                if (! Schema::hasColumn('ringkasan_statistik', 'published_by')) {
                    $table->foreignId('published_by')->nullable()->after('published_at')->constrained('pengguna')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        // Sengaja tidak drop kolom agar aman untuk data produksi/dev yang sudah terisi.
    }
};