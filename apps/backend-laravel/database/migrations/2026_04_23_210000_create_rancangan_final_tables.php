<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kecamatan', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 150);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('desa', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('kecamatan')->cascadeOnDelete();
            $table->string('kode', 20)->unique();
            $table->string('nama', 150);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('opd', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama', 150);
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('pengguna', function (Blueprint $table): void {
            $table->id();
            $table->string('nama', 150);
            $table->string('email')->unique();
            $table->string('kata_sandi');
            $table->string('role', 40);
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatan')->nullOnDelete();
            $table->foreignId('opd_id')->nullable()->constrained('opd')->nullOnDelete();
            $table->boolean('aktif')->default(true);
            $table->timestamp('login_terakhir_pada')->nullable();
            $table->string('token_pengingat', 100)->nullable();
            $table->timestamps();
            $table->index(['role', 'aktif']);
        });

        Schema::create('periode_data', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('bulan');
            $table->string('label', 50);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('terkunci')->default(false);
            $table->timestamps();
            $table->unique(['tahun', 'bulan']);
        });

        Schema::create('indikator_data', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 80)->unique();
            $table->string('nama', 180);
            $table->string('kelompok', 80);
            $table->string('satuan', 30);
            $table->string('level_input', 30);
            $table->boolean('aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
            $table->index(['kelompok', 'aktif']);
        });

        Schema::create('sumber_data', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatan')->nullOnDelete();
            $table->foreignId('desa_id')->nullable()->constrained('desa')->nullOnDelete();
            $table->string('nama', 180);
            $table->string('jenis', 40);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->index(['jenis', 'aktif']);
            $table->index(['kecamatan_id', 'desa_id']);
        });

        Schema::create('pengajuan_data', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('kecamatan')->cascadeOnDelete();
            $table->foreignId('periode_data_id')->constrained('periode_data')->cascadeOnDelete();
            $table->foreignId('dikirim_oleh')->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->string('status', 30)->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_kirim')->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->timestamp('tanggal_terbit')->nullable();
            $table->timestamps();
            $table->index(['kecamatan_id', 'periode_data_id', 'status']);
            $table->unique(['kecamatan_id', 'periode_data_id']);
        });

        Schema::create('nilai_data_mentah', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengajuan_data_id')->constrained('pengajuan_data')->cascadeOnDelete();
            $table->foreignId('desa_id')->constrained('desa')->cascadeOnDelete();
            $table->foreignId('indikator_data_id')->constrained('indikator_data')->cascadeOnDelete();
            $table->foreignId('sumber_data_id')->nullable()->constrained('sumber_data')->nullOnDelete();
            $table->decimal('nilai', 18, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['pengajuan_data_id', 'desa_id', 'indikator_data_id', 'sumber_data_id'], 'nilai_mentah_lookup_idx');
            $table->unique(['pengajuan_data_id', 'desa_id', 'indikator_data_id', 'sumber_data_id'], 'nilai_mentah_unique');
        });

        Schema::create('ringkasan_statistik', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('periode_data_id')->constrained('periode_data')->cascadeOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatan')->nullOnDelete();
            $table->foreignId('desa_id')->nullable()->constrained('desa')->nullOnDelete();
            $table->foreignId('indikator_data_id')->constrained('indikator_data')->cascadeOnDelete();
            $table->string('tingkat_rekap', 20);
            $table->decimal('nilai_total', 18, 2)->default(0);
            $table->decimal('nilai_persen', 8, 2)->nullable();
            $table->timestamps();
            $table->index(['periode_data_id', 'tingkat_rekap', 'kecamatan_id', 'indikator_data_id'], 'ringkasan_lookup_idx');
        });

        Schema::create('konten', function (Blueprint $table): void {
            $table->id();
            $table->string('jenis_konten', 30)->default('berita');
            $table->string('judul', 200);
            $table->string('slug', 220)->unique();
            $table->text('ringkasan')->nullable();
            $table->longText('isi')->nullable();
            $table->longText('uraian')->nullable();
            $table->string('gambar_sampul')->nullable();
            $table->dateTime('mulai')->nullable();
            $table->dateTime('selesai')->nullable();
            $table->string('lokasi')->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatan')->nullOnDelete();
            $table->foreignId('opd_id')->nullable()->constrained('opd')->nullOnDelete();
            $table->foreignId('penulis_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->foreignId('pembuat_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->string('status', 30)->default('draft');
            $table->foreignId('ditinjau_oleh')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamp('tanggal_terbit')->nullable();
            $table->boolean('unggulan')->default(false);
            $table->timestamps();
            $table->index(['jenis_konten', 'status', 'tanggal_terbit'], 'konten_publikasi_idx');
            $table->index(['jenis_konten', 'mulai'], 'konten_jadwal_idx');
            $table->index(['kecamatan_id', 'opd_id'], 'konten_area_idx');
        });

        Schema::create('lapisan_peta', function (Blueprint $table): void {
            $table->id();
            $table->string('nama', 150);
            $table->string('slug', 180)->unique();
            $table->string('tipe_sumber', 30);
            $table->json('konfigurasi_json')->nullable();
            $table->boolean('aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('fitur_peta', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lapisan_peta_id')->constrained('lapisan_peta')->cascadeOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatan')->nullOnDelete();
            $table->string('nama', 180);
            $table->string('jenis_geometri', 20);
            $table->longText('geojson')->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('properti_json')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->index(['lapisan_peta_id', 'kecamatan_id', 'aktif']);
        });

        Schema::create('aktivitas_sistem', function (Blueprint $table): void {
            $table->id();
            $table->string('kategori_aktivitas', 30)->default('audit');
            $table->string('jenis_objek', 80)->nullable();
            $table->unsignedBigInteger('objek_id')->nullable();
            $table->foreignId('peninjau_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->string('aksi', 60);
            $table->text('catatan')->nullable();
            $table->foreignId('pengguna_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->string('modul', 60)->nullable();
            $table->string('jenis_target', 80)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('nilai_lama_json')->nullable();
            $table->json('nilai_baru_json')->nullable();
            $table->timestamps();
            $table->index(['kategori_aktivitas', 'aksi'], 'aktivitas_kategori_idx');
            $table->index(['jenis_objek', 'objek_id'], 'aktivitas_objek_idx');
            $table->index(['jenis_target', 'target_id'], 'aktivitas_target_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas_sistem');
        Schema::dropIfExists('fitur_peta');
        Schema::dropIfExists('lapisan_peta');
        Schema::dropIfExists('konten');
        Schema::dropIfExists('ringkasan_statistik');
        Schema::dropIfExists('nilai_data_mentah');
        Schema::dropIfExists('pengajuan_data');
        Schema::dropIfExists('sumber_data');
        Schema::dropIfExists('indikator_data');
        Schema::dropIfExists('periode_data');
        Schema::dropIfExists('pengguna');
        Schema::dropIfExists('opd');
        Schema::dropIfExists('desa');
        Schema::dropIfExists('kecamatan');
    }
};
