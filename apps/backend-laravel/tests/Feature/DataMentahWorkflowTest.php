<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\IndikatorData;
use App\Models\Kecamatan;
use App\Models\NilaiDataMentah;
use App\Models\PengajuanData;
use App\Models\PeriodeData;
use App\Models\RingkasanStatistik;
use App\Models\User;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DataMentahWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_kecamatan_tidak_bisa_input_kecamatan_lain(): void
    {
        [$user, , , $otherPengajuan, $otherDesa, $indicator] = $this->fixture();

        $this->actingAs($user);
        $this->expectException(ValidationException::class);

        NilaiDataMentah::create([
            'pengajuan_data_id' => $otherPengajuan->id,
            'desa_id' => $otherDesa->id,
            'indikator_data_id' => $indicator->id,
            'tipe_sumber' => 'desa',
            'sumber_id' => $otherDesa->id,
            'nilai_decimal' => 10,
        ]);
    }

    public function test_indikator_wajib_kosong_gagal_submit(): void
    {
        [$user, $pengajuan] = $this->fixture();

        $this->actingAs($user);

        $this->expectException(ValidationException::class);
        app(WorkflowService::class)->transition($pengajuan, 'diajukan', $user);
    }

    public function test_nilai_nol_valid_saat_submit(): void
    {
        [$user, $pengajuan, $desa] = $this->fixture();

        $this->actingAs($user);
        NilaiDataMentah::create([
            'pengajuan_data_id' => $pengajuan->id,
            'desa_id' => $desa->id,
            'indikator_data_id' => IndikatorData::where('kode', 'uji_sum')->value('id'),
            'tipe_sumber' => 'desa',
            'sumber_id' => $desa->id,
            'nilai_decimal' => 0,
        ]);

        $pengajuan->refresh();
        app(WorkflowService::class)->transition($pengajuan, 'diajukan', $user);

        $this->assertSame('diajukan', $pengajuan->refresh()->status);
    }

    public function test_data_diajukan_tidak_bisa_diedit(): void
    {
        [$user, $pengajuan, $desa, , , $indicator] = $this->fixture();

        $this->actingAs($user);
        $nilai = NilaiDataMentah::create([
            'pengajuan_data_id' => $pengajuan->id,
            'desa_id' => $desa->id,
            'indikator_data_id' => $indicator->id,
            'tipe_sumber' => 'desa',
            'sumber_id' => $desa->id,
            'nilai_decimal' => 0,
        ]);
        app(WorkflowService::class)->transition($pengajuan->refresh(), 'diajukan', $user);

        $this->expectException(ValidationException::class);
        $nilai->update(['nilai_decimal' => 5]);
    }

    public function test_verifikasi_dan_terbit_mengagregasi_serta_public_api_hanya_data_terbit(): void
    {
        [$user, $pengajuan, $desa, , , $sumIndicator] = $this->fixture();
        $averageIndicator = IndikatorData::create([
            'kode' => 'uji_avg',
            'nama' => 'Indikator Average',
            'kelompok' => 'kesehatan',
            'kategori' => 'kesehatan',
            'satuan' => 'persen',
            'tipe_nilai' => 'decimal',
            'level_input' => 'desa',
            'metode_agregasi' => 'average',
            'wajib_diisi' => false,
            'boleh_diinput_kecamatan' => true,
            'boleh_diinput_opd' => false,
            'boleh_publikasi' => true,
            'aktif' => true,
        ]);
        $adminKominfo = User::withoutEvents(fn () => User::create([
            'nama' => 'Admin Kominfo',
            'email' => 'kominfo@example.test',
            'kata_sandi' => 'password',
            'role' => 'admin_kominfo',
            'aktif' => true,
        ]));

        $this->actingAs($user);
        NilaiDataMentah::create([
            'pengajuan_data_id' => $pengajuan->id,
            'desa_id' => $desa->id,
            'indikator_data_id' => $sumIndicator->id,
            'tipe_sumber' => 'desa',
            'sumber_id' => $desa->id,
            'nilai_decimal' => 10,
        ]);
        NilaiDataMentah::create([
            'pengajuan_data_id' => $pengajuan->id,
            'desa_id' => $desa->id,
            'indikator_data_id' => $averageIndicator->id,
            'tipe_sumber' => 'desa',
            'sumber_id' => $desa->id,
            'nilai_decimal' => 20,
        ]);
        app(WorkflowService::class)->transition($pengajuan->refresh(), 'diajukan', $user);

        $this->actingAs($adminKominfo);
        app(WorkflowService::class)->transition($pengajuan->refresh(), 'terverifikasi', $adminKominfo);

        $this->assertDatabaseHas('ringkasan_statistik', [
            'indikator_data_id' => $sumIndicator->id,
            'nilai_total' => 10,
            'status_publikasi' => 'internal',
        ]);

        $this->getJson('/api/public/statistik')
            ->assertOk()
            ->assertJsonPath('table', []);

        app(WorkflowService::class)->transition($pengajuan->refresh(), 'terbit', $adminKominfo);

        $this->assertSame(10.0, (float) RingkasanStatistik::where('indikator_data_id', $sumIndicator->id)->where('tingkat_rekap', 'kecamatan')->latest('id')->value('nilai_total'));
        $this->assertSame(20.0, (float) RingkasanStatistik::where('indikator_data_id', $averageIndicator->id)->where('tingkat_rekap', 'kecamatan')->latest('id')->value('nilai_total'));

        $this->getJson('/api/public/statistik')
            ->assertOk()
            ->assertJsonCount(6, 'table')
            ->assertJsonFragment(['indikator' => 'Indikator Sum']);
    }

    private function fixture(): array
    {
        $kecamatan = Kecamatan::create(['kode' => 'K01', 'nama' => 'Tahuna', 'aktif' => true]);
        $otherKecamatan = Kecamatan::create(['kode' => 'K02', 'nama' => 'Manganitu', 'aktif' => true]);
        $desa = Desa::create(['kecamatan_id' => $kecamatan->id, 'kode' => 'D01', 'nama' => 'Desa Satu', 'aktif' => true]);
        $otherDesa = Desa::create(['kecamatan_id' => $otherKecamatan->id, 'kode' => 'D02', 'nama' => 'Desa Dua', 'aktif' => true]);
        $periode = PeriodeData::create([
            'tahun' => 2026,
            'bulan' => 4,
            'label' => 'April 2026',
            'tanggal_mulai' => '2026-04-01',
            'tanggal_selesai' => '2026-04-30',
            'terkunci' => false,
        ]);
        $user = User::withoutEvents(fn () => User::create([
            'nama' => 'Admin Kecamatan',
            'email' => 'kecamatan@example.test',
            'kata_sandi' => 'password',
            'role' => 'admin_kecamatan',
            'kecamatan_id' => $kecamatan->id,
            'aktif' => true,
        ]));
        $indicator = IndikatorData::create([
            'kode' => 'uji_sum',
            'nama' => 'Indikator Sum',
            'kelompok' => 'kesehatan',
            'kategori' => 'kesehatan',
            'satuan' => 'orang',
            'tipe_nilai' => 'integer',
            'level_input' => 'desa',
            'metode_agregasi' => 'sum',
            'wajib_diisi' => true,
            'boleh_diinput_kecamatan' => true,
            'boleh_diinput_opd' => false,
            'boleh_publikasi' => true,
            'aktif' => true,
        ]);
        $pengajuan = PengajuanData::withoutEvents(fn () => PengajuanData::create([
            'kecamatan_id' => $kecamatan->id,
            'periode_data_id' => $periode->id,
            'dikirim_oleh' => $user->id,
            'status' => 'draft',
        ]));
        $otherPengajuan = PengajuanData::withoutEvents(fn () => PengajuanData::create([
            'kecamatan_id' => $otherKecamatan->id,
            'periode_data_id' => $periode->id,
            'dikirim_oleh' => $user->id,
            'status' => 'draft',
        ]));

        return [$user, $pengajuan, $desa, $otherPengajuan, $otherDesa, $indicator];
    }
}
