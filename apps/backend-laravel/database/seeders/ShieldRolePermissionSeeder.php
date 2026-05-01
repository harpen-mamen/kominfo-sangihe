<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ShieldRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions')) {
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = Permission::query()->pluck('name');

        $this->syncRole('super_admin', $allPermissions);
        $this->syncRole('admin_kominfo', $allPermissions);

        $sharedDashboard = collect([
            'View:AdminDashboardShowcase',
            'View:AdminOverview',
            'View:PreviewPeta',
        ]);

        $inputPages = collect([
            'View:InputCepat',
            'View:UploadExcelDataMentah',
            'View:InputPerIndikator',
        ]);

        $regionalStatisticsPages = collect([
            'View:LaporanIndikatorDaerah',
            'View:StatistikDaerah',
            'View:DashboardStatistik',
            'View:StatistikPerIndikator',
            'View:StatistikPerKecamatan',
            'View:StatistikPerDesa',
            'View:StatistikPerOpd',
            'View:PerbandinganWilayah',
            'View:TrenBulananTahunan',
            'View:Infografis',
        ]);

        $statisticsOperator = $this->resourcePermissions([
            'PeriodeData' => ['ViewAny', 'View'],
            'IndikatorData' => ['ViewAny', 'View'],
            'PengajuanData' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
            'NilaiDataMentah' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
            'RingkasanStatistik' => ['ViewAny', 'View'],
            'DokumenPublik' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
        ]);

        $departmentStatisticsOperator = $this->resourcePermissions([
            'PeriodeData' => ['ViewAny', 'View'],
            'IndikatorData' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
            'PengajuanData' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
            'NilaiDataMentah' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
            'RingkasanStatistik' => ['ViewAny', 'View'],
            'DokumenPublik' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
        ]);

        $contentOperator = $this->resourcePermissions([
            'Berita' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
            'Kegiatan' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
            'Konten' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
            'FiturPeta' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
            'SumberData' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
        ]);

        $this->syncRole(
            'admin_kecamatan',
            $sharedDashboard
                ->merge($inputPages)
                ->merge($regionalStatisticsPages)
                ->merge($statisticsOperator)
                ->merge($contentOperator)
                ->merge($this->resourcePermissions([
                    'Desa' => ['ViewAny', 'View'],
                ])),
        );

        $this->syncRole(
            'admin_opd',
            $sharedDashboard
                ->merge($inputPages)
                ->merge($regionalStatisticsPages)
                ->merge($departmentStatisticsOperator)
                ->merge($contentOperator),
        );
    }

    /**
     * @param  array<string, array<int, string>>  $map
     */
    private function resourcePermissions(array $map): Collection
    {
        return collect($map)
            ->flatMap(fn (array $verbs, string $resource): array => array_map(
                fn (string $verb): string => "{$verb}:{$resource}",
                $verbs,
            ));
    }

    private function syncRole(string $roleName, Collection $permissionNames): void
    {
        $role = Role::query()->firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        $permissions = Permission::query()
            ->whereIn('name', $permissionNames->unique()->values())
            ->pluck('name');

        $role->syncPermissions($permissions);
    }
}
