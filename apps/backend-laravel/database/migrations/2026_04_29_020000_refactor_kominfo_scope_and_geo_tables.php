<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createPermissionTables();
        $this->updateSumberDataTable();
        $this->updateLapisanPetaTable();
        $this->updateKontenTable();
        $this->updateFiturPetaTable();
        $this->backfillLapisanDefaults();
    }

    public function down(): void
    {
        Schema::table('fitur_peta', function (Blueprint $table): void {
            foreach (['file_path', 'sumber_input', 'dibuat_oleh', 'konten_id', 'sumber_data_id', 'opd_id', 'desa_id'] as $column) {
                if (Schema::hasColumn('fitur_peta', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('konten', function (Blueprint $table): void {
            if (Schema::hasColumn('konten', 'desa_id')) {
                $table->dropColumn('desa_id');
            }
        });

        Schema::table('lapisan_peta', function (Blueprint $table): void {
            foreach (['hanya_admin_kominfo', 'kategori'] as $column) {
                if (Schema::hasColumn('lapisan_peta', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('sumber_data', function (Blueprint $table): void {
            foreach (['keterangan', 'kontak', 'longitude', 'latitude', 'alamat', 'opd_id'] as $column) {
                if (Schema::hasColumn('sumber_data', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function createPermissionTables(): void
    {
        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (! Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
                $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
                $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_primary');
            });
        }

        if (! Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table): void {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
                $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
                $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_primary');
            });
        }

        if (! Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
                $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
                $table->primary(['permission_id', 'role_id'], 'role_has_permissions_primary');
            });
        }
    }

    private function updateSumberDataTable(): void
    {
        Schema::table('sumber_data', function (Blueprint $table): void {
            if (! Schema::hasColumn('sumber_data', 'opd_id')) {
                $table->foreignId('opd_id')->nullable()->constrained('opd')->nullOnDelete();
            }

            if (! Schema::hasColumn('sumber_data', 'alamat')) {
                $table->text('alamat')->nullable();
            }

            if (! Schema::hasColumn('sumber_data', 'latitude')) {
                $table->decimal('latitude', 11, 8)->nullable();
            }

            if (! Schema::hasColumn('sumber_data', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable();
            }

            if (! Schema::hasColumn('sumber_data', 'kontak')) {
                $table->string('kontak', 150)->nullable();
            }

            if (! Schema::hasColumn('sumber_data', 'keterangan')) {
                $table->text('keterangan')->nullable();
            }
        });
    }

    private function updateLapisanPetaTable(): void
    {
        Schema::table('lapisan_peta', function (Blueprint $table): void {
            if (! Schema::hasColumn('lapisan_peta', 'kategori')) {
                $table->string('kategori', 80)->default('umum');
            }

            if (! Schema::hasColumn('lapisan_peta', 'hanya_admin_kominfo')) {
                $table->boolean('hanya_admin_kominfo')->default(false);
            }
        });
    }

    private function updateKontenTable(): void
    {
        Schema::table('konten', function (Blueprint $table): void {
            if (! Schema::hasColumn('konten', 'desa_id')) {
                $table->foreignId('desa_id')->nullable()->constrained('desa')->nullOnDelete();
            }
        });
    }

    private function updateFiturPetaTable(): void
    {
        Schema::table('fitur_peta', function (Blueprint $table): void {
            if (! Schema::hasColumn('fitur_peta', 'desa_id')) {
                $table->foreignId('desa_id')->nullable()->constrained('desa')->nullOnDelete();
            }

            if (! Schema::hasColumn('fitur_peta', 'opd_id')) {
                $table->foreignId('opd_id')->nullable()->constrained('opd')->nullOnDelete();
            }

            if (! Schema::hasColumn('fitur_peta', 'sumber_data_id')) {
                $table->foreignId('sumber_data_id')->nullable()->constrained('sumber_data')->nullOnDelete();
            }

            if (! Schema::hasColumn('fitur_peta', 'konten_id')) {
                $table->foreignId('konten_id')->nullable()->constrained('konten')->nullOnDelete();
            }

            if (! Schema::hasColumn('fitur_peta', 'dibuat_oleh')) {
                $table->foreignId('dibuat_oleh')->nullable()->constrained('pengguna')->nullOnDelete();
            }

            if (! Schema::hasColumn('fitur_peta', 'sumber_input')) {
                $table->string('sumber_input', 20)->default('manual');
            }

            if (! Schema::hasColumn('fitur_peta', 'file_path')) {
                $table->string('file_path')->nullable();
            }
        });
    }

    private function backfillLapisanDefaults(): void
    {
        if (! Schema::hasTable('lapisan_peta')) {
            return;
        }

        DB::table('lapisan_peta')
            ->whereIn('slug', ['batas-kecamatan', 'batas-kecamatan-sangihe'])
            ->update([
                'kategori' => 'batas_wilayah',
                'hanya_admin_kominfo' => true,
            ]);

        DB::table('lapisan_peta')
            ->whereIn('slug', ['batas-desa', 'batas-desa-sangihe'])
            ->update([
                'kategori' => 'batas_wilayah',
                'hanya_admin_kominfo' => true,
            ]);

        DB::table('lapisan_peta')
            ->whereIn('slug', ['fasilitas-publik', 'berita-kegiatan', 'statistik-tematik'])
            ->update([
                'kategori' => DB::raw("CASE slug
                    WHEN 'fasilitas-publik' THEN 'fasilitas'
                    WHEN 'berita-kegiatan' THEN 'konten'
                    WHEN 'statistik-tematik' THEN 'statistik'
                    ELSE kategori
                END"),
            ]);
    }
};
