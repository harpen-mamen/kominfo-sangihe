<x-filament-widgets::widget>
    <section class="admin-dashboard-showcase">
        <div class="admin-dashboard-showcase__hero">
            <div class="admin-dashboard-showcase__hero-copy">
                <span class="admin-dashboard-showcase__eyebrow">{{ $hero['eyebrow'] }}</span>
                <h2>{{ $hero['title'] }}</h2>
                <p>{{ $hero['description'] }}</p>

                <div class="admin-dashboard-showcase__chips">
                    @foreach ($hero['badges'] as $badge)
                        <span>{{ $badge }}</span>
                    @endforeach
                </div>
            </div>

            <div class="admin-dashboard-showcase__actions">
                @foreach ($hero['links'] as $link)
                    <a class="admin-dashboard-showcase__action" href="{{ $link['url'] }}">
                        <strong>{{ $link['label'] }}</strong>
                        <small>{{ $link['description'] }}</small>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="admin-dashboard-showcase__summary">
            @foreach ($summaryCards as $card)
                <article class="adminlte-summary-card adminlte-summary-card--{{ $card['tone'] }}">
                    <div>
                        <span>{{ $card['label'] }}</span>
                        <strong>{{ $card['value'] }}</strong>
                        <p>{{ $card['description'] }}</p>
                    </div>

                    <em aria-hidden="true">{{ $card['icon'] }}</em>
                </article>
            @endforeach
        </div>

        <div class="admin-dashboard-showcase__grid">
            <article class="adminlte-panel adminlte-panel--trend">
                <header class="adminlte-panel__header">
                    <div>
                        <span class="adminlte-panel__eyebrow">Grafik Utama</span>
                        <h3 data-admin-chart-title="trend">{{ $trend['title'] }}</h3>
                        <p data-admin-chart-description="trend">{{ $trend['description'] }}</p>
                    </div>
                </header>

                <div class="adminlte-panel__chart" wire:ignore>
                    <canvas data-admin-dashboard-trend></canvas>
                </div>

                <div class="adminlte-panel__focus" data-admin-chart-focus="trend">
                    <span data-admin-chart-focus-label="trend">Fokus Interaktif</span>
                    <strong data-admin-chart-focus-value="trend">{{ $trend['focus_indicator_label'] ?? '-' }}</strong>
                    <p data-admin-chart-focus-meta="trend">{{ $trend['stats'][1]['value'] ?? '-' }}</p>
                </div>

                <div class="adminlte-panel__stats" data-admin-chart-stats="trend">
                    @foreach ($trend['stats'] as $item)
                        <div>
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="adminlte-panel adminlte-panel--comparison">
                <header class="adminlte-panel__header">
                    <div>
                        <span class="adminlte-panel__eyebrow">Grafik Batang</span>
                        <h3 data-admin-chart-title="comparison">{{ $comparison['title'] }}</h3>
                        <p data-admin-chart-description="comparison">{{ $comparison['description'] }}</p>
                    </div>
                </header>

                <div class="adminlte-panel__chart adminlte-panel__chart--compact" wire:ignore>
                    <canvas data-admin-dashboard-comparison></canvas>
                </div>

                <div class="adminlte-panel__focus" data-admin-chart-focus="comparison">
                    <span data-admin-chart-focus-label="comparison">Wilayah Aktif</span>
                    <strong data-admin-chart-focus-value="comparison">{{ $comparison['stats'][1]['value'] ?? '-' }}</strong>
                    <p data-admin-chart-focus-meta="comparison">{{ $comparison['stats'][2]['value'] ?? '-' }}</p>
                </div>

                <div class="adminlte-panel__stats" data-admin-chart-stats="comparison">
                    @foreach ($comparison['stats'] as $item)
                        <div>
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="adminlte-panel adminlte-panel--map">
                <header class="adminlte-panel__header">
                    <div>
                        <span class="adminlte-panel__eyebrow">Peta Digital</span>
                        <h3>{{ $map['title'] }}</h3>
                        <p>{{ $map['description'] }}</p>
                    </div>
                </header>

                <div class="adminlte-map">
                    <div class="adminlte-map__stage" wire:ignore>
                        <div class="adminlte-map__canvas" data-admin-map-stage>
                            <div class="adminlte-map__toolbar">
                                <div class="adminlte-map__segmented" role="group" aria-label="Jenis peta">
                                    <button class="adminlte-map__segment is-active" type="button" data-admin-map-style="street">
                                        Peta
                                    </button>
                                    <button class="adminlte-map__segment" type="button" data-admin-map-style="satellite">
                                        Satelit
                                    </button>
                                </div>

                                <div class="adminlte-map__control-cluster">
                                    <button class="adminlte-map__control adminlte-map__control--wide" type="button" data-admin-map-sidebar-toggle aria-label="Buka informasi peta">
                                        Info
                                    </button>
                                    <button class="adminlte-map__control" type="button" data-admin-map-reset aria-label="Atur ulang tampilan peta">
                                        O
                                    </button>
                                    <button class="adminlte-map__control" type="button" data-admin-map-zoom-out aria-label="Perkecil peta">
                                        -
                                    </button>
                                    <span class="adminlte-map__zoom-level" data-admin-map-zoom-level>100%</span>
                                    <button class="adminlte-map__control" type="button" data-admin-map-zoom-in aria-label="Perbesar peta">
                                        +
                                    </button>
                                    <button class="adminlte-map__control adminlte-map__control--wide" type="button" data-admin-map-fullscreen aria-label="Buka fullscreen peta">
                                        Full
                                    </button>
                                </div>
                            </div>

                            <div class="adminlte-map__compass" aria-hidden="true">
                                <span>U</span>
                                <i></i>
                            </div>

                            <div class="adminlte-map__viewport" data-admin-dashboard-map>
                                <div class="adminlte-map__tiles" data-admin-map-tiles></div>
                                <div class="adminlte-map__overlay" data-admin-map-overlay></div>
                            </div>

                            <div class="adminlte-map__attribution" data-admin-map-attribution>
                                OpenStreetMap
                            </div>

                            <div class="adminlte-map__popup" data-admin-map-popup hidden>
                                <button class="adminlte-map__popup-close" type="button" data-admin-map-popup-close aria-label="Tutup detail peta">
                                    x
                                </button>
                                <span class="adminlte-map__popup-type" data-admin-map-popup-type>Wilayah</span>
                                <strong class="adminlte-map__popup-title" data-admin-map-popup-title>Detail wilayah</strong>
                                <p class="adminlte-map__popup-subtitle" data-admin-map-popup-subtitle></p>
                                <div class="adminlte-map__popup-stats" data-admin-map-popup-stats></div>
                            </div>
                        </div>
                        <div class="adminlte-map__detail" data-admin-map-detail>
                            {{ $map['note'] }}
                        </div>
                    </div>

                    <div class="adminlte-map__sidebar" data-admin-map-sidebar>
                        <div class="adminlte-map__insight">
                            <span class="adminlte-map__legend-title" data-admin-map-selection-type>Ringkasan Wilayah</span>
                            <strong class="adminlte-map__selection-title" data-admin-map-selection-title>{{ $map['title'] }}</strong>
                            <p class="adminlte-map__selection-subtitle" data-admin-map-selection-subtitle>{{ $map['note'] }}</p>
                            <div class="adminlte-map__meta" data-admin-map-selection-meta>
                                <span>{{ $map['status_label'] }}</span>
                            </div>
                        </div>

                        <div class="adminlte-map__filters">
                            <span class="adminlte-map__legend-title">Filter Batas</span>
                            <div class="adminlte-map__filter-group">
                                <button class="adminlte-map__filter is-active" type="button" data-admin-map-layer-toggle="kecamatan">
                                    Batas Kecamatan
                                </button>
                                <button class="adminlte-map__filter is-active" type="button" data-admin-map-layer-toggle="desa">
                                    Batas Kampung
                                </button>
                            </div>
                        </div>

                        <div class="adminlte-map__filters">
                            <span class="adminlte-map__legend-title">Sorot Kecamatan</span>
                            <label class="adminlte-map__select-wrap">
                                <select data-admin-map-district>
                                    <option value="">Semua kecamatan</option>
                                    @foreach ($map['district_options'] as $district)
                                        <option value="{{ $district }}">{{ $district }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <div class="adminlte-map__coverage" data-admin-map-stats>
                            @foreach ($map['coverage'] as $item)
                                <div>
                                    <span>{{ $item['label'] }}</span>
                                    <strong>{{ $item['value'] }}</strong>
                                </div>
                            @endforeach
                        </div>

                        <div class="adminlte-map__legend">
                            <span class="adminlte-map__legend-title">Layer Aktif</span>
                            @foreach ($map['layers'] as $layer)
                                <button class="adminlte-map__legend-item is-active" type="button" data-admin-map-layer="{{ $layer['slug'] }}">
                                    <i style="background: {{ $layer['color'] }}"></i>
                                    <span>{{ $layer['name'] }}</span>
                                    <small>{{ count($layer['features']) }}</small>
                                </button>
                            @endforeach
                        </div>

                        <div class="adminlte-map__areas">
                            <span class="adminlte-map__legend-title" data-admin-map-focus-label>Area yang ditampilkan</span>
                            <div class="adminlte-map__chips" data-admin-map-focus-areas>
                                @foreach ($map['areas'] as $area)
                                    <span>{{ $area }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <article class="adminlte-panel adminlte-panel--composition">
                <header class="adminlte-panel__header">
                    <div>
                        <span class="adminlte-panel__eyebrow">Donut Chart</span>
                        <h3 data-admin-chart-title="composition">{{ $composition['title'] }}</h3>
                        <p data-admin-chart-description="composition">{{ $composition['description'] }}</p>
                    </div>
                </header>

                <div class="adminlte-panel__chart adminlte-panel__chart--doughnut" wire:ignore>
                    <canvas data-admin-dashboard-composition></canvas>
                </div>

                <div class="adminlte-panel__focus adminlte-panel__focus--accent" data-admin-chart-focus="composition">
                    <span data-admin-chart-focus-label="composition">Irisan Aktif</span>
                    <strong data-admin-chart-focus-value="composition">{{ $composition['stats'][0]['value'] ?? '-' }}</strong>
                    <p data-admin-chart-focus-meta="composition">{{ $composition['stats'][1]['value'] ?? '-' }}</p>
                </div>

                <div class="adminlte-panel__stats" data-admin-chart-stats="composition">
                    @foreach ($composition['stats'] as $item)
                        <div>
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>
        </div>

        @php
            $showcasePayload = [
                'trend' => $trend['chart'],
                'trend_meta' => $trend,
                'comparison' => $comparison['chart'],
                'comparison_meta' => $comparison,
                'composition' => $composition['chart'],
                'composition_meta' => $composition,
                'district_charts' => $districtCharts,
                'map' => [
                    'layers' => $map['layers'],
                    'note' => $map['note'],
                ],
            ];
        @endphp

        <script type="application/json" class="admin-dashboard-showcase__payload">
            {!! json_encode($showcasePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    </section>
</x-filament-widgets::widget>
