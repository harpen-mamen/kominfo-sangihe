<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indikator_data', function (Blueprint $table): void {
            if (! Schema::hasColumn('indikator_data', 'opd_id')) {
                $table->foreignId('opd_id')
                    ->nullable()
                    ->after('level_input')
                    ->constrained('opd')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('indikator_data', 'boleh_diinput_kecamatan')) {
                $table->boolean('boleh_diinput_kecamatan')
                    ->default(false)
                    ->after('opd_id');
            }

            if (! Schema::hasColumn('indikator_data', 'boleh_diinput_opd')) {
                $table->boolean('boleh_diinput_opd')
                    ->default(true)
                    ->after('boleh_diinput_kecamatan');
            }
        });

        Schema::table('indikator_data', function (Blueprint $table): void {
            $table->index(['opd_id', 'aktif'], 'indikator_data_opd_aktif_idx');
            $table->index(['aktif', 'boleh_diinput_kecamatan'], 'indikator_data_kecamatan_input_idx');
        });
    }

    public function down(): void
    {
        Schema::table('indikator_data', function (Blueprint $table): void {
            try {
                $table->dropIndex('indikator_data_kecamatan_input_idx');
            } catch (Throwable) {
                // no-op
            }

            try {
                $table->dropIndex('indikator_data_opd_aktif_idx');
            } catch (Throwable) {
                // no-op
            }

            foreach (['boleh_diinput_opd', 'boleh_diinput_kecamatan'] as $column) {
                if (Schema::hasColumn('indikator_data', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('indikator_data', 'opd_id')) {
                $table->dropConstrainedForeignId('opd_id');
            }
        });
    }
};
