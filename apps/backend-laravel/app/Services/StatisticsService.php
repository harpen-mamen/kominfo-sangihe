<?php

namespace App\Services;

use App\Models\IndikatorData;
use App\Models\RingkasanStatistik;
use Illuminate\Support\Collection;

class StatisticsService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboardSummary(): array
    {
        $latest = RingkasanStatistik::query()
            ->with(['indikatorData', 'periodeData'])
            ->whereIn('tingkat_rekap', ['kecamatan', 'opd'])
            ->latest('periode_data_id')
            ->get();

        return [
            'total_indicators' => IndikatorData::query()->where('aktif', true)->count(),
            'latest_period' => $latest->first()?->periodeData?->tahun,
            'kpis' => $latest
                ->take(4)
                ->map(fn (RingkasanStatistik $row): array => [
                    'indicator' => $row->indikatorData?->nama,
                    'value' => (float) $row->nilai_total,
                    'period_year' => $row->periodeData?->tahun,
                ])
                ->values(),
        ];
    }

    /**
     * @return Collection<int, RingkasanStatistik>
     */
    public function publishedSnapshots(?int $indicatorId = null): Collection
    {
        return RingkasanStatistik::query()
            ->with(['indikatorData', 'periodeData', 'kecamatan', 'desa'])
            ->when($indicatorId, fn ($query) => $query->where('indikator_data_id', $indicatorId))
            ->orderBy('periode_data_id')
            ->orderBy('tingkat_rekap')
            ->get();
    }
}
