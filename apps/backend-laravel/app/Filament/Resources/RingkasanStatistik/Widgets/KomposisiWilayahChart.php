<?php

namespace App\Filament\Resources\RingkasanStatistik\Widgets;

use App\Filament\Resources\RingkasanStatistik\Widgets\Concerns\InteractsWithRingkasanStatistik;
use App\Models\RingkasanStatistik;
use Filament\Widgets\ChartWidget;

class KomposisiWilayahChart extends ChartWidget
{
    use InteractsWithRingkasanStatistik;

    protected ?string $heading = 'Komposisi Statistik';

    protected ?string $description = 'Diagram kontribusi wilayah pada periode terbaru.';

    protected string $color = 'success';

    public function mount(): void
    {
        $this->filter ??= (string) $this->defaultIndicatorId();

        parent::mount();
    }

    protected function getType(): string
    {
        return 'doughnut';
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
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Komposisi',
                    'data' => $rows->map(fn (RingkasanStatistik $row): float => (float) $row->nilai_total)->all(),
                    'backgroundColor' => [
                        '#0d6efd',
                        '#20c997',
                        '#ffc107',
                        '#dc3545',
                        '#6f42c1',
                        '#0dcaf0',
                        '#198754',
                        '#fd7e14',
                    ],
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
