<?php

namespace App\Services;

use App\Models\NilaiDataMentah;
use App\Models\PengajuanData;
use App\Models\RingkasanStatistik;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ServiceAgregasiStatistik
{
    public function sinkronkan(PengajuanData $pengajuan): void
    {
        if (! $this->bolehDirekap($pengajuan)) {
            $this->hapusRekap($pengajuan);

            return;
        }

        $this->regenerasi($pengajuan);
    }

    public function hapusRekap(PengajuanData $pengajuan): void
    {
        $this->hapusRekapUntuk(
            periodeDataId: (int) $pengajuan->periode_data_id,
            kecamatanId: $pengajuan->kecamatan_id ? (int) $pengajuan->kecamatan_id : null,
            opdId: $pengajuan->opd_id ? (int) $pengajuan->opd_id : null,
        );
    }

    public function hapusRekapUntuk(int $periodeDataId, ?int $kecamatanId = null, ?int $opdId = null): void
    {
        DB::transaction(function () use ($periodeDataId, $kecamatanId, $opdId): void {
            $this->hapusRekapWilayah($periodeDataId, $kecamatanId, $opdId);
        });
    }

    public function regenerasi(PengajuanData $pengajuan): void
    {
        DB::transaction(function () use ($pengajuan): void {
            $pengajuan->loadMissing('nilaiDataMentah');

            $this->hapusRekapWilayah(
                periodeDataId: (int) $pengajuan->periode_data_id,
                kecamatanId: $pengajuan->kecamatan_id ? (int) $pengajuan->kecamatan_id : null,
                opdId: $pengajuan->opd_id ? (int) $pengajuan->opd_id : null,
            );

            $this->buatRekapDesa($pengajuan);

            if ($pengajuan->kecamatan_id) {
                $this->buatRekapKecamatan($pengajuan);
            }

            if ($pengajuan->opd_id) {
                $this->buatRekapOpd($pengajuan);
            }
        });
    }

    public function regenerasiSemuaTerverifikasi(): int
    {
        $total = 0;
        $periodeIds = PengajuanData::query()
            ->whereIn('status', ['terverifikasi', 'terbit'])
            ->distinct()
            ->pluck('periode_data_id');

        if ($periodeIds->isEmpty()) {
            return 0;
        }

        RingkasanStatistik::query()
            ->whereIn('periode_data_id', $periodeIds)
            ->delete();

        PengajuanData::query()
            ->whereIn('status', ['terverifikasi', 'terbit'])
            ->with('nilaiDataMentah')
            ->chunkById(50, function (Collection $pengajuanList) use (&$total): void {
                $pengajuanList->each(function (PengajuanData $pengajuan) use (&$total): void {
                    $this->regenerasi($pengajuan);
                    $total++;
                });
            });

        return $total;
    }

    private function buatRekapDesa(PengajuanData $pengajuan): void
    {
        $this->nilaiDesa($pengajuan)
            ->groupBy(fn (NilaiDataMentah $nilai): string => $nilai->desa_id . ':' . $nilai->indikator_data_id)
            ->each(function (Collection $items) use ($pengajuan): void {
                $first = $items->first();

                RingkasanStatistik::create([
                    'periode_data_id' => $pengajuan->periode_data_id,
                    'kecamatan_id' => $pengajuan->kecamatan_id,
                    'opd_id' => $pengajuan->opd_id,
                    'desa_id' => $first->desa_id,
                    'indikator_data_id' => $first->indikator_data_id,
                    'tingkat_rekap' => 'desa',
                    'nilai_total' => $items->sum(fn (NilaiDataMentah $item): float => (float) $item->nilai),
                ]);
            });
    }

    private function buatRekapKecamatan(PengajuanData $pengajuan): void
    {
        $pengajuan->nilaiDataMentah
            ->groupBy('indikator_data_id')
            ->each(function (Collection $items, int $indikatorId) use ($pengajuan): void {
                RingkasanStatistik::create([
                    'periode_data_id' => $pengajuan->periode_data_id,
                    'kecamatan_id' => $pengajuan->kecamatan_id,
                    'opd_id' => null,
                    'desa_id' => null,
                    'indikator_data_id' => $indikatorId,
                    'tingkat_rekap' => 'kecamatan',
                    'nilai_total' => $items->sum(fn (NilaiDataMentah $item): float => (float) $item->nilai),
                ]);
            });
    }

    private function buatRekapOpd(PengajuanData $pengajuan): void
    {
        $pengajuan->nilaiDataMentah
            ->groupBy('indikator_data_id')
            ->each(function (Collection $items, int $indikatorId) use ($pengajuan): void {
                RingkasanStatistik::create([
                    'periode_data_id' => $pengajuan->periode_data_id,
                    'kecamatan_id' => null,
                    'opd_id' => $pengajuan->opd_id,
                    'desa_id' => null,
                    'indikator_data_id' => $indikatorId,
                    'tingkat_rekap' => 'opd',
                    'nilai_total' => $items->sum(fn (NilaiDataMentah $item): float => (float) $item->nilai),
                ]);
            });
    }

    private function hapusRekapWilayah(int $periodeDataId, ?int $kecamatanId = null, ?int $opdId = null): void
    {
        RingkasanStatistik::query()
            ->where('periode_data_id', $periodeDataId)
            ->when($kecamatanId, fn ($query) => $query->where('kecamatan_id', $kecamatanId))
            ->when($opdId, fn ($query) => $query->where('opd_id', $opdId))
            ->delete();
    }

    private function bolehDirekap(PengajuanData $pengajuan): bool
    {
        return in_array($pengajuan->status, ['terverifikasi', 'terbit'], true);
    }

    /**
     * @return Collection<int, NilaiDataMentah>
     */
    private function nilaiDesa(PengajuanData $pengajuan): Collection
    {
        return $pengajuan->nilaiDataMentah
            ->filter(fn (NilaiDataMentah $nilai): bool => filled($nilai->desa_id))
            ->values();
    }
}
