{{-- resources/views/filament/pages/statistik-daerah/partials/statistic-chart-card.blade.php --}}

@php
    $title = $title ?? 'Grafik Statistik';
    $description = $description ?? 'Visualisasi data statistik sesuai filter aktif.';
    $canvasId = $canvasId ?? 'chartStatistik';
    $badge = $badge ?? 'Grafik';
@endphp

<div class="kom-card">
    <div class="kom-card-header">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <span class="kom-badge" style="height: 32px; width: 32px; justify-content: center; padding: 0;">
                <x-filament::icon icon="heroicon-o-chart-bar" style="width: 18px; height: 18px;" />
            </span>

            <div>
                <h3 class="kom-card-title">{{ $title }}</h3>

                @if (filled($description))
                    <p class="kom-card-desc">{{ $description }}</p>
                @endif
            </div>
        </div>

        @if (filled($badge))
            <span class="kom-badge">{{ $badge }}</span>
        @endif
    </div>

    <div class="kom-card-body">
        <div class="kom-chart-box" wire:ignore>
            <canvas id="{{ $canvasId }}"></canvas>
        </div>
    </div>
</div>
