{{-- resources/views/filament/pages/statistik-daerah/partials/statistic-ranking-list.blade.php --}}

@php
    $title = $title ?? 'Ranking Wilayah';
    $description = $description ?? 'Wilayah dengan nilai tertinggi.';
    $items = collect($items ?? []);
    $badge = $badge ?? 'Top Ranking';
    $maxValue = max((float) $items->max(fn ($item) => (float) data_get($item, 'nilai', 0)), 1);
@endphp

<div class="kom-card">
    <div class="kom-card-header">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <span class="kom-badge" style="height: 32px; width: 32px; justify-content: center; padding: 0;">
                <x-filament::icon icon="heroicon-o-trophy" style="width: 18px; height: 18px;" />
            </span>

            <div>
                <h3 class="kom-card-title">{{ $title }}</h3>
                <p class="kom-card-desc">{{ $description }}</p>
            </div>
        </div>

        <span class="kom-badge">{{ $badge }}</span>
    </div>

    <div class="kom-card-body">
        @forelse ($items as $index => $item)
            @php
                $nilai = (float) data_get($item, 'nilai', 0);
                $percent = min(100, max(4, ($nilai / $maxValue) * 100));
            @endphp

            <div style="padding: 13px 0; border-bottom: 1px solid var(--kom-border);">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                        <span class="kom-badge" style="min-width: 34px; justify-content: center;">
                            {{ $index + 1 }}
                        </span>

                        <span class="kom-text-strong" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ data_get($item, 'nama', '-') }}
                        </span>
                    </div>

                    <span class="kom-text-strong" style="white-space: nowrap;">
                        {{ number_format($nilai, 2, ',', '.') }}
                    </span>
                </div>

                <div style="height: 8px; margin-top: 10px; overflow: hidden; border-radius: 999px; background: var(--kom-card-soft);">
                    <div style="width: {{ $percent }}%; height: 100%; border-radius: 999px; background: var(--kom-primary);"></div>
                </div>
            </div>
        @empty
            <div class="kom-empty">
                <p class="kom-empty-title">Belum ada data ranking.</p>
                <p class="kom-empty-text">
                    Ranking akan tampil setelah data tersedia.
                </p>
            </div>
        @endforelse
    </div>
</div>
