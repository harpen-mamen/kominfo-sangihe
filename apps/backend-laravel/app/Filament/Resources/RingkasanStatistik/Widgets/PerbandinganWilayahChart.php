<?php

namespace App\Filament\Resources\RingkasanStatistik\Widgets;

use App\Filament\Resources\RingkasanStatistik\Widgets\Concerns\InteractsWithRingkasanStatistik;
use App\Models\RingkasanStatistik;
use Filament\Widgets\ChartWidget;

class PerbandinganWilayahChart extends ChartWidget
{
    use InteractsWithRingkasanStatistik;

    protected ?string $heading = 'Perbandingan Wilayah';

    protected ?string $description = 'Nilai terbaru per wilayah sesuai hasil rekap sistem.';

    protected string $color = 'info';

    public function mount(): void
    {
        $this->filter ??= (string) $this->defaultIndicatorId();

        parent::mount();
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return $this->indicatorFilters() ?: null;
    }

    protected function getData(): array
    {
        $indicatorId = $this->selectedIndicatorId();
        $level = $this->comparisonLevel();
        $periodId = $this->latestPeriodId($indicatorId, $level);

        if (! $indicatorId || ! $periodId) {
            return ['datasets' => [], 'labels' => []];
        }

        $rows = $this->scopedRingkasanQuery()
            ->with(['kecamatan', 'desa'])
            ->where('indikator_data_id', $indicatorId)
            ->where('periode_data_id', $periodId)
            ->where('tingkat_rekap', $level)
            ->orderByDesc('nilai_total')
            ->limit(12)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Nilai total',
                    'data' => $rows->map(fn (RingkasanStatistik $row): float => (float) $row->nilai_total)->all(),
                    'backgroundColor' => '#0dcaf0',
                    'borderColor' => '#0aa2c0',
                ],
            ],
            'labels' => $rows
                ->map(fn (RingkasanStatistik $row): string => $level === 'desa'
                    ? ($row->desa?->nama ?? '-')
                    : ($row->kecamatan?->nama ?? 'Kabupaten'))
                ->all(),
        ];
    }
}
