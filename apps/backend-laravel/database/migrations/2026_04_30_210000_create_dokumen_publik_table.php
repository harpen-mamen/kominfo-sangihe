<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_publik', function (Blueprint $table): void {
            $table->id();
            $table->string('judul', 220);
            $table->string('slug', 240)->unique();
            $table->string('jenis_dokumen', 60);
            $table->string('tingkat_wilayah', 40);
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatan')->nullOnDelete();
            $table->foreignId('desa_id')->nullable()->constrained('desa')->nullOnDelete();
            $table->foreignId('opd_id')->nullable()->constrained('opd')->nullOnDelete();
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->string('nomor_dokumen', 120)->nullable();
            $table->date('tanggal_dokumen')->nullable();
            $table->string('file_path');
            $table->text('ringkasan')->nullable();
            $table->string('status', 30)->default('draft');
            $table->foreignId('dikirim_oleh')->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('ditinjau_oleh')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamp('tanggal_terbit')->nullable();
            $table->timestamps();

            $table->index(['status', 'jenis_dokumen', 'tahun'], 'dokumen_publik_status_idx');
            $table->index(['kecamatan_id', 'desa_id', 'opd_id'], 'dokumen_publik_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_publik');
    }
};
