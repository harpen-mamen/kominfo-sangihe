<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_data', function (Blueprint $table): void {
            if (! Schema::hasColumn('pengajuan_data', 'opd_id')) {
                $table->foreignId('opd_id')->nullable()->after('kecamatan_id')->constrained('opd')->nullOnDelete();
            }
        });

        Schema::table('pengajuan_data', function (Blueprint $table): void {
            $table->foreignId('kecamatan_id')->nullable()->change();
        });

        if (Schema::hasTable('pengajuan_data')) {
            Schema::table('pengajuan_data', function (Blueprint $table): void {
                try {
                    $table->dropUnique('pengajuan_data_kecamatan_id_periode_data_id_unique');
                } catch (Throwable) {
                    // Keep migration idempotent when the legacy unique index is already absent.
                }

                $table->index(['periode_data_id', 'kecamatan_id', 'opd_id'], 'pengajuan_scope_lookup_idx');
            });
        }

        Schema::table('ringkasan_statistik', function (Blueprint $table): void {
            if (! Schema::hasColumn('ringkasan_statistik', 'opd_id')) {
                $table->foreignId('opd_id')->nullable()->after('kecamatan_id')->constrained('opd')->nullOnDelete();
            }
        });

        Schema::table('ringkasan_statistik', function (Blueprint $table): void {
            $table->index(['periode_data_id', 'tingkat_rekap', 'opd_id', 'indikator_data_id'], 'ringkasan_opd_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ringkasan_statistik', function (Blueprint $table): void {
            try {
                $table->dropIndex('ringkasan_opd_lookup_idx');
            } catch (Throwable) {
                // no-op
            }

            if (Schema::hasColumn('ringkasan_statistik', 'opd_id')) {
                $table->dropConstrainedForeignId('opd_id');
            }
        });

        Schema::table('pengajuan_data', function (Blueprint $table): void {
            try {
                $table->dropIndex('pengajuan_scope_lookup_idx');
            } catch (Throwable) {
                // no-op
            }

            $table->unique(['kecamatan_id', 'periode_data_id']);

            if (Schema::hasColumn('pengajuan_data', 'opd_id')) {
                $table->dropConstrainedForeignId('opd_id');
            }
        });

        Schema::table('pengajuan_data', function (Blueprint $table): void {
            $table->foreignId('kecamatan_id')->nullable(false)->change();
        });
    }
};
