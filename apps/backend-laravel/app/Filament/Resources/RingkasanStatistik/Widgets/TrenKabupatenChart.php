<?php

namespace App\Filament\Resources\RingkasanStatistik\Widgets;

use App\Filament\Resources\RingkasanStatistik\Widgets\Concerns\InteractsWithRingkasanStatistik;
use App\Models\RingkasanStatistik;
use Filament\Widgets\ChartWidget;

class TrenKabupatenChart extends ChartWidget
{
    use InteractsWithRingkasanStatistik;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Tren Hasil Olahan Statistik';

    protected ?string $description = 'Data dihitung otomatis dari pengajuan kecamatan yang sudah divalidasi Kominfo.';

    protected string $color = 'primary';

    public function mount(): void
    {
        $this->filter ??= (string) $this->defaultIndicatorId();

        parent::mount();
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return $this->indicatorFilters() ?: null;
    }

    protected function getData(): array
    {
        $indicatorId = $this->selectedIndicatorId();

        if (! $indicatorId) {
            return ['datasets' => [], 'labels' => []];
        }

        $rows = $this->scopedRingkasanQuery()
            ->with(['indikatorData', 'periodeData'])
            ->where('indikator_data_id', $indicatorId)
            ->where('tingkat_rekap', $this->aggregationLevel())
            ->orderBy('periode_data_id')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => $rows->first()?->indikatorData?->nama ?? 'Nilai total',
                    'data' => $rows->map(fn (RingkasanStatistik $row): float => (float) $row->nilai_total)->all(),
                    'borderColor' => '#0d6efd',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.16)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $rows->map(fn (RingkasanStatistik $row): string => $row->periodeData?->label ?? ('Periode ' . $row->periode_data_id))->all(),
        ];
    }
}
