<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\SumberData;
use Illuminate\Database\Seeder;

class SumberDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->getOutput()->writeln('Sinkronisasi sumber_data untuk desa dan kecamatan...');

        Kecamatan::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->cursor()
            ->each(function (Kecamatan $kecamatan): void {
                $sumberData = SumberData::firstOrNew([
                    'jenis' => 'kecamatan',
                    'kecamatan_id' => $kecamatan->id,
                    'desa_id' => null,
                ]);

                $sumberData->nama = $kecamatan->nama;
                $sumberData->aktif = true;
                $sumberData->save();
            });

        Desa::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->cursor()
            ->each(function (Desa $desa): void {
                $sumberData = SumberData::firstOrNew([
                    'jenis' => 'desa',
                    'desa_id' => $desa->id,
                ]);

                $sumberData->kecamatan_id = $desa->kecamatan_id;
                $sumberData->nama = $desa->nama;
                $sumberData->aktif = true;
                $sumberData->save();
            });
    }
}
