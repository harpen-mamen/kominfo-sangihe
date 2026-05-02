{{-- resources/views/filament/pages/statistik-daerah/partials/statistic-kpi-card.blade.php --}}

@php
    $cards = collect($cards ?? []);

    $toneColor = function ($tone) {
        return match ($tone) {
            'green' => '#047857',
            'teal', 'cyan' => '#0e7490',
            'amber', 'yellow' => '#d97706',
            'red' => '#dc2626',
            'purple' => '#7c3aed',
            'navy' => '#0f4c81',
            default => '#0369a1',
        };
    };

    $iconFor = function ($icon) {
        return filled($icon) && str_starts_with((string) $icon, 'heroicon-')
            ? (string) $icon
            : 'heroicon-o-chart-bar';
    };
@endphp

@if ($cards->isEmpty())
    <div class="kom-alert kom-alert-warning">
        Belum ada ringkasan statistik untuk ditampilkan.
    </div>
@else
    <div class="kom-grid-3">
        @foreach ($cards as $card)
            @php
                $tone = data_get($card, 'tone', 'blue');
                $color = $toneColor($tone);
                $icon = $iconFor(data_get($card, 'icon'));
            @endphp

            <div class="kom-summary-item" style="display: flex; gap: 14px; align-items: flex-start; min-height: 118px;">
                <span
                    class="kom-badge"
                    style="height: 42px; width: 42px; justify-content: center; padding: 0; color: {{ $color }}; background: color-mix(in srgb, {{ $color }} 12%, white);"
                >
                    <x-filament::icon :icon="$icon" style="width: 22px; height: 22px;" />
                </span>

                <span style="display: block; min-width: 0;">
                    <span class="kom-summary-label">
                        {{ data_get($card, 'label', '-') }}
                    </span>

                    <span class="kom-summary-value" style="font-size: 24px; color: {{ $color }};">
                        {{ data_get($card, 'value', 0) }}
                    </span>

                    @if (data_get($card, 'note'))
                        <span class="kom-text-muted" style="display: block; margin-top: 6px;">
                            {{ data_get($card, 'note') }}
                        </span>
                    @endif
                </span>
            </div>
        @endforeach
    </div>
@endif
