<?php

namespace Database\Seeders;

use App\Models\Berita;
use App\Models\Desa;
use App\Models\FiturPeta;
use App\Models\Kecamatan;
use App\Models\Kegiatan;
use App\Models\LapisanPeta;
use App\Models\Opd;
use App\Models\SumberData;
use App\Models\User;
use App\Services\SangiheRegionSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CoreReferenceSeeder extends Seeder
{
    public function run(): void
    {
        app(SangiheRegionSyncService::class)->sync();

        Kecamatan::query()->whereIn('kode', ['KEC-A', 'KEC-B'])->update(['aktif' => false]);
        Desa::query()->whereIn('kode', ['DESA-A1', 'DESA-A2', 'DESA-B1', 'DESA-B2'])->update(['aktif' => false]);

        $kominfo = $this->upsertOpd(
            kode: 'kominfo',
            nama: 'Dinas Kominfo',
            keterangan: 'Pengelola master data dan verifikasi statistik.',
            legacyCodes: ['DISKOMINFO'],
        );

        $dukcapil = $this->upsertOpd(
            kode: 'dukcapil',
            nama: 'Dinas Kependudukan dan Pencatatan Sipil',
            keterangan: 'Pengelola indikator kependudukan dan pencatatan sipil.',
        );

        $dinkes = $this->upsertOpd(
            kode: 'dinkes',
            nama: 'Dinas Kesehatan',
            keterangan: 'Pengelola indikator kesehatan dan layanan kesehatan.',
            legacyCodes: ['DINKES'],
        );

        $this->upsertOpd(
            kode: 'disdik',
            nama: 'Dinas Pendidikan',
            keterangan: 'Pengelola indikator pendidikan.',
            legacyCodes: ['DISDIK'],
        );

        $kecamatanTahuna = Kecamatan::query()
            ->where('nama', 'Tahuna')
            ->first()
            ?? Kecamatan::query()->where('aktif', true)->orderBy('nama')->firstOrFail();

        $desaTahuna = Desa::query()
            ->where('kecamatan_id', $kecamatanTahuna->id)
            ->orderByRaw("CASE WHEN nama = 'Apeng Sembeka' THEN 0 ELSE 1 END")
            ->orderBy('nama')
            ->firstOrFail();

        $admin = User::updateOrCreate(
            ['email' => 'admin@kominfo.test'],
            [
                'nama' => 'Admin Kominfo',
                'kata_sandi' => 'password',
                'role' => 'admin_kominfo',
                'kecamatan_id' => null,
                'opd_id' => null,
                'aktif' => true,
            ],
        );

        $this->seedRolesAndAssignments();
        $admin->refresh();
        $admin->syncRoles(['super_admin', 'admin_kominfo']);
        $this->seedDefaultLayers();

        SumberData::updateOrCreate(
            ['nama' => 'Dukcapil'],
            ['jenis' => 'dukcapil', 'kecamatan_id' => null, 'desa_id' => null, 'opd_id' => $dukcapil->id, 'aktif' => true],
        );

        SumberData::updateOrCreate(
            ['nama' => 'Puskesmas Tahuna'],
            [
                'jenis' => 'puskesmas',
                'kecamatan_id' => $kecamatanTahuna->id,
                'desa_id' => null,
                'alamat' => 'Kawasan layanan kesehatan Kecamatan Tahuna',
                'latitude' => 3.6208,
                'longitude' => 125.5006,
                'aktif' => true,
            ],
        );

        $sdA1 = SumberData::updateOrCreate(
            ['nama' => 'SD Negeri 1 '.$desaTahuna->nama],
            [
                'jenis' => 'sekolah',
                'kecamatan_id' => $kecamatanTahuna->id,
                'desa_id' => $desaTahuna->id,
                'alamat' => 'Kawasan pelayanan pendidikan '.$desaTahuna->nama,
                'latitude' => 3.6086,
                'longitude' => 125.4852,
                'aktif' => true,
            ],
        );

        SumberData::updateOrCreate(
            ['nama' => 'SMP Negeri 1 '.$desaTahuna->nama],
            [
                'jenis' => 'sekolah',
                'kecamatan_id' => $kecamatanTahuna->id,
                'desa_id' => $desaTahuna->id,
                'alamat' => 'Kawasan layanan pendidikan '.$desaTahuna->nama,
                'latitude' => 3.6091,
                'longitude' => 125.4862,
                'aktif' => true,
            ],
        );

        $dukcapil = SumberData::query()->where('nama', 'Dukcapil')->first();
        $fasilitasLayer = LapisanPeta::query()->where('slug', 'fasilitas-publik')->first();

        if ($fasilitasLayer && $sdA1) {
            FiturPeta::query()->updateOrCreate(
                ['lapisan_peta_id' => $fasilitasLayer->id, 'nama' => 'Titik SD Negeri 1 '.$desaTahuna->nama],
                [
                    'kecamatan_id' => $kecamatanTahuna->id,
                    'desa_id' => $desaTahuna->id,
                    'sumber_data_id' => $sdA1->id,
                    'nama' => 'Titik SD Negeri 1 '.$desaTahuna->nama,
                    'jenis_geometri' => 'point',
                    'latitude' => 3.6086,
                    'longitude' => 125.4852,
                    'properti_json' => ['kategori' => 'sekolah', 'sumber' => 'seed'],
                    'sumber_input' => 'manual',
                    'aktif' => true,
                ],
            );
        }

        $berita = Berita::query()->updateOrCreate(
            ['slug' => Str::slug('Pusat Layanan Data Tahuna')],
            [
                'judul' => 'Pusat Layanan Data Tahuna',
                'ringkasan' => 'Contoh berita berkoordinat untuk pengujian layer peta konten.',
                'isi' => 'Berita ini digunakan untuk memastikan marker konten tampil pada preview map Filament.',
                'lokasi' => 'Tahuna',
                'latitude' => 3.6079,
                'longitude' => 125.4849,
                'kecamatan_id' => $kecamatanTahuna->id,
                'opd_id' => $kominfo->id,
                'penulis_id' => $admin->id,
                'pembuat_id' => $admin->id,
                'status' => 'terbit',
                'tanggal_terbit' => now()->subDay(),
                'unggulan' => true,
            ],
        );

        Kegiatan::query()->withoutGlobalScopes()->updateOrCreate(
            ['slug' => Str::slug('Layanan Kesehatan Keliling '.$desaTahuna->nama)],
            [
                'jenis_konten' => 'kegiatan',
                'judul' => 'Layanan Kesehatan Keliling '.$desaTahuna->nama,
                'ringkasan' => 'Contoh kegiatan OPD berkoordinat untuk pengujian dashboard OPD.',
                'uraian' => 'Agenda lapangan ini dipakai sebagai data contoh untuk preview peta dan pembatasan scope OPD.',
                'mulai' => now()->addDays(3)->setTime(9, 0),
                'selesai' => now()->addDays(3)->setTime(12, 0),
                'lokasi' => $desaTahuna->nama,
                'latitude' => 3.6088,
                'longitude' => 125.4858,
                'kecamatan_id' => $kecamatanTahuna->id,
                'opd_id' => $dinkes->id,
                'penulis_id' => $admin->id,
                'pembuat_id' => $admin->id,
                'status' => 'terbit',
                'tanggal_terbit' => now(),
                'unggulan' => false,
            ],
        );
    }

    private function seedRolesAndAssignments(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('model_has_roles')) {
            return;
        }

        $now = now();

        foreach (['super_admin', 'admin_kominfo', 'admin_kecamatan', 'admin_opd'] as $roleName) {
            DB::table('roles')->updateOrInsert(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['updated_at' => $now, 'created_at' => $now],
            );
        }

        $adminId = User::query()->where('email', 'admin@kominfo.test')->value('id');

        if (! $adminId) {
            return;
        }

        $roleIds = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereIn('name', ['super_admin', 'admin_kominfo'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('model_has_roles')->updateOrInsert(
                [
                    'role_id' => $roleId,
                    'model_type' => User::class,
                    'model_id' => $adminId,
                ],
                [],
            );
        }
    }

    /**
     * @param  array<int, string>  $legacyCodes
     */
    private function upsertOpd(string $kode, string $nama, string $keterangan, array $legacyCodes = []): Opd
    {
        $opd = Opd::query()->where('kode', $kode)->first()
            ?? Opd::query()->whereIn('kode', $legacyCodes)->first()
            ?? Opd::query()->where('nama', $nama)->first()
            ?? new Opd;

        $opd->fill([
            'kode' => $kode,
            'nama' => $nama,
            'keterangan' => $keterangan,
            'aktif' => true,
        ]);

        $opd->save();

        return $opd;
    }

    private function seedDefaultLayers(): void
    {
        $layers = [
            [
                'slug' => 'batas-kecamatan',
                'nama' => 'Batas Kecamatan',
                'kategori' => 'batas_wilayah',
                'tipe_sumber' => 'geojson',
                'hanya_admin_kominfo' => true,
                'aktif' => true,
                'urutan' => 10,
                'konfigurasi_json' => ['color' => '#0d6efd', 'fillOpacity' => 0.14, 'weight' => 2],
            ],
            [
                'slug' => 'batas-desa',
                'nama' => 'Batas Desa',
                'kategori' => 'batas_wilayah',
                'tipe_sumber' => 'geojson',
                'hanya_admin_kominfo' => true,
                'aktif' => true,
                'urutan' => 20,
                'konfigurasi_json' => ['color' => '#20c997', 'fillOpacity' => 0.12, 'weight' => 1.2],
            ],
            [
                'slug' => 'fasilitas-publik',
                'nama' => 'Fasilitas Publik',
                'kategori' => 'fasilitas',
                'tipe_sumber' => 'manual',
                'hanya_admin_kominfo' => false,
                'aktif' => true,
                'urutan' => 30,
                'konfigurasi_json' => ['color' => '#f59e0b', 'fillOpacity' => 0.2, 'weight' => 1.4],
            ],
            [
                'slug' => 'berita-kegiatan',
                'nama' => 'Berita/Kegiatan',
                'kategori' => 'konten',
                'tipe_sumber' => 'manual',
                'hanya_admin_kominfo' => false,
                'aktif' => true,
                'urutan' => 40,
                'konfigurasi_json' => ['color' => '#dc2626', 'fillOpacity' => 0.2, 'weight' => 1.2],
            ],
            [
                'slug' => 'statistik-tematik',
                'nama' => 'Statistik Tematik',
                'kategori' => 'statistik',
                'tipe_sumber' => 'statistik',
                'hanya_admin_kominfo' => false,
                'aktif' => true,
                'urutan' => 50,
                'konfigurasi_json' => ['color' => '#2563eb', 'fillOpacity' => 0.2, 'weight' => 1.2],
            ],
        ];

        foreach ($layers as $layer) {
            LapisanPeta::query()->updateOrCreate(
                ['slug' => $layer['slug']],
                $layer,
            );
        }
    }
}
