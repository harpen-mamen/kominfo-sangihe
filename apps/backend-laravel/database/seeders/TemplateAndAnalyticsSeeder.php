<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\IndikatorData;
use App\Models\Kecamatan;
use App\Models\NilaiDataMentah;
use App\Models\Opd;
use App\Models\PengajuanData;
use App\Models\PeriodeData;
use App\Models\SumberData;
use App\Models\User;
use App\Services\ServiceAgregasiStatistik;
use Illuminate\Database\Seeder;

class TemplateAndAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $periode = PeriodeData::updateOrCreate(
            ['tahun' => 2026, 'bulan' => 1],
            [
                'label' => 'Januari 2026',
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-01-31',
                'terkunci' => false,
            ],
        );

        $opd = Opd::query()->whereIn('kode', ['dukcapil', 'dinkes', 'disdik'])->pluck('id', 'kode');

        $indicators = collect([
            ['kode' => 'jumlah_penduduk', 'nama' => 'Jumlah Penduduk', 'kelompok' => 'kependudukan', 'satuan' => 'jiwa', 'level_input' => 'desa', 'opd_id' => $opd['dukcapil'] ?? null, 'boleh_diinput_opd' => true, 'boleh_diinput_kecamatan' => false, 'urutan' => 1],
            ['kode' => 'jumlah_kelahiran', 'nama' => 'Jumlah Kelahiran', 'kelompok' => 'kependudukan', 'satuan' => 'jiwa', 'level_input' => 'desa', 'opd_id' => $opd['dukcapil'] ?? null, 'boleh_diinput_opd' => true, 'boleh_diinput_kecamatan' => false, 'urutan' => 2],
            ['kode' => 'jumlah_kematian', 'nama' => 'Jumlah Kematian', 'kelompok' => 'kependudukan', 'satuan' => 'jiwa', 'level_input' => 'desa', 'opd_id' => $opd['dukcapil'] ?? null, 'boleh_diinput_opd' => true, 'boleh_diinput_kecamatan' => false, 'urutan' => 3],
            ['kode' => 'kasus_dbd', 'nama' => 'Kasus DBD', 'kelompok' => 'kesehatan', 'satuan' => 'kasus', 'level_input' => 'desa', 'opd_id' => $opd['dinkes'] ?? null, 'boleh_diinput_opd' => true, 'boleh_diinput_kecamatan' => false, 'urutan' => 4],
            ['kode' => 'jumlah_siswa', 'nama' => 'Jumlah Siswa', 'kelompok' => 'pendidikan', 'satuan' => 'siswa', 'level_input' => 'desa', 'opd_id' => $opd['disdik'] ?? null, 'boleh_diinput_opd' => true, 'boleh_diinput_kecamatan' => false, 'urutan' => 5],
        ])->mapWithKeys(fn (array $data): array => [
            $data['kode'] => IndikatorData::updateOrCreate(['kode' => $data['kode']], [...$data, 'aktif' => true]),
        ]);

        $admin = User::where('email', 'admin@kominfo.test')->firstOrFail();
        $dukcapil = SumberData::where('nama', 'Dukcapil')->first();

        Kecamatan::query()->orderBy('nama')->get()->each(function (Kecamatan $kecamatan) use ($periode, $indicators, $admin, $dukcapil): void {
            $pengajuan = PengajuanData::updateOrCreate(
                [
                    'kecamatan_id' => $kecamatan->id,
                    'periode_data_id' => $periode->id,
                ],
                [
                    'dikirim_oleh' => $admin->id,
                    'diverifikasi_oleh' => $admin->id,
                    'status' => 'terbit',
                    'catatan' => 'Data contoh awal untuk statistik desa dan kecamatan.',
                    'tanggal_kirim' => now()->subDays(3),
                    'tanggal_verifikasi' => now()->subDays(2),
                    'tanggal_terbit' => now()->subDay(),
                ],
            );

            Desa::where('kecamatan_id', $kecamatan->id)
                ->orderBy('nama')
                ->get()
                ->each(function (Desa $desa, int $index) use ($pengajuan, $indicators, $dukcapil): void {
                    $base = 1000 + ($desa->id * 125) + ($index * 25);

                    $values = [
                        'jumlah_penduduk' => $base,
                        'jumlah_kelahiran' => 8 + $index,
                        'jumlah_kematian' => 2 + $index,
                        'kasus_dbd' => 1 + $index,
                        'jumlah_siswa' => 180 + ($index * 35),
                    ];

                    foreach ($values as $kode => $nilai) {
                        NilaiDataMentah::updateOrCreate(
                            [
                                'pengajuan_data_id' => $pengajuan->id,
                                'desa_id' => $desa->id,
                                'indikator_data_id' => $indicators[$kode]->id,
                                'sumber_data_id' => in_array($kode, ['jumlah_penduduk', 'jumlah_kelahiran', 'jumlah_kematian'], true) ? $dukcapil?->id : null,
                            ],
                            [
                                'nilai' => $nilai,
                                'catatan' => 'Data seed '.$desa->nama,
                            ],
                        );
                    }
                });

            app(ServiceAgregasiStatistik::class)->regenerasi($pengajuan);
        });
    }
}
