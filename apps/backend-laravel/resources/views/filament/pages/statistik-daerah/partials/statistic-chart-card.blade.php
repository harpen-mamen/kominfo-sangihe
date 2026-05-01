@php
    use Filament\Support\Facades\FilamentAsset;

    $palette = ['#0f3b57', '#075985', '#0ea5a8', '#14b8a6', '#15803d', '#2563eb', '#06b6d4', '#22c55e'];
    $rows = collect($chart['rows'] ?? [])->values();
    $type = match ($chart['type'] ?? 'bar') {
        'donut' => 'doughnut',
        default => $chart['type'] ?? 'bar',
    };
    $labels = $rows->pluck('label')->all();
    $values = $rows->pluck('value')->map(fn ($value) => round((float) $value, 2))->all();
    $colors = collect($values)->keys()->map(fn ($index) => $palette[$index % count($palette)])->all();
    $chartData = [
        'labels' => $labels,
        'datasets' => [[
            'label' => $chart['title'] ?? 'Statistik',
            'data' => $values,
            'backgroundColor' => $type === 'line' ? 'rgba(14, 165, 168, 0.16)' : $colors,
            'borderColor' => $type === 'line' ? '#0f766e' : ($type === 'doughnut' ? '#ffffff' : 'transparent'),
            'borderWidth' => $type === 'doughnut' ? 2 : 1,
            'fill' => $type === 'line',
            'tension' => 0.36,
            'pointBackgroundColor' => '#0f3b57',
            'pointBorderColor' => '#ffffff',
            'pointBorderWidth' => 2,
        ]],
    ];
    $options = [
        'responsive' => true,
        'maintainAspectRatio' => false,
        'plugins' => [
            'legend' => ['display' => $type === 'doughnut', 'position' => 'bottom'],
            'tooltip' => ['enabled' => true],
        ],
        'scales' => $type === 'doughnut' ? [] : [
            'x' => ['grid' => ['display' => false], 'ticks' => ['maxRotation' => 0, 'autoSkip' => true]],
            'y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(148, 163, 184, 0.22)']],
        ],
    ];
    $chartKey = md5(json_encode([$chart['title'] ?? '', $type, $values, $labels]));
@endphp

<article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="mb-5 flex items-start justify-between gap-4">
        <div class="min-w-0">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ $chart['title'] ?? 'Chart Statistik' }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $chart['description'] ?? 'Visualisasi data sesuai filter aktif.' }}</p>
        </div>
        <span class="shrink-0 rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700 dark:bg-cyan-950 dark:text-cyan-200">{{ strtoupper($type) }}</span>
    </div>

    @if ($rows->isEmpty())
        <div class="flex h-72 items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-950/40">
            Belum ada data untuk chart ini.
        </div>
    @else
        <div
            wire:key="stat-chart-{{ $chartKey }}"
            x-load
            x-load-src="{{ FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
            x-data="chart({ cachedData: @js($chartData), maxHeight: '320px', options: @js($options), type: @js($type) })"
            class="fi-wi-chart-canvas-ctn fi-wi-chart-canvas-ctn-no-aspect-ratio h-80"
        >
            <canvas x-ref="canvas" style="max-height: 320px"></canvas>
            <span x-ref="backgroundColorElement" class="fi-wi-chart-bg-color"></span>
            <span x-ref="borderColorElement" class="fi-wi-chart-border-color"></span>
            <span x-ref="gridColorElement" class="fi-wi-chart-grid-color"></span>
            <span x-ref="textColorElement" class="fi-wi-chart-text-color"></span>
        </div>
    @endif
</article>
