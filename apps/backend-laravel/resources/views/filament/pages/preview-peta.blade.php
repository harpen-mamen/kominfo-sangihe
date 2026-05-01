<x-filament-panels::page>
    <div class="admin-dashboard-showcase">
        <article class="adminlte-panel adminlte-panel--map">
            <header class="adminlte-panel__header">
                <div>
                    <span class="adminlte-panel__eyebrow">Peta Digital</span>
                    <h3>{{ $map['title'] ?? 'Preview Peta' }}</h3>
                    <p>{{ $map['description'] ?? 'Menampilkan boundary, fasilitas publik, dan titik konten berbasis koordinat.' }}</p>
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
                        {{ $map['note'] ?? 'Pilih wilayah atau titik untuk melihat detail.' }}
                    </div>
                </div>

                <div class="adminlte-map__sidebar" data-admin-map-sidebar>
                    <div class="adminlte-map__insight">
                        <span class="adminlte-map__legend-title" data-admin-map-selection-type>Ringkasan Wilayah</span>
                        <strong class="adminlte-map__selection-title" data-admin-map-selection-title>{{ $map['title'] ?? 'Preview Peta' }}</strong>
                        <p class="adminlte-map__selection-subtitle" data-admin-map-selection-subtitle>{{ $map['note'] ?? '' }}</p>
                        <div class="adminlte-map__meta" data-admin-map-selection-meta>
                            <span>{{ $map['status_label'] ?? 'Layer aktif' }}</span>
                        </div>
                    </div>

                    <div class="adminlte-map__filters">
                        <span class="adminlte-map__legend-title">Filter Batas</span>
                        <div class="adminlte-map__filter-group">
                            <button class="adminlte-map__filter is-active" type="button" data-admin-map-layer-toggle="kecamatan">
                                Batas Kecamatan
                            </button>
                            <button class="adminlte-map__filter is-active" type="button" data-admin-map-layer-toggle="desa">
                                Batas Desa
                            </button>
                        </div>
                    </div>

                    <div class="adminlte-map__filters">
                        <span class="adminlte-map__legend-title">Sorot Kecamatan</span>
                        <label class="adminlte-map__select-wrap">
                            <select data-admin-map-district>
                                <option value="">Semua kecamatan</option>
                                @foreach (($map['district_options'] ?? []) as $district)
                                    <option value="{{ $district }}">{{ $district }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="adminlte-map__filters">
                        <span class="adminlte-map__legend-title">Filter Desa</span>
                        <label class="adminlte-map__select-wrap">
                            <select data-admin-map-village>
                                <option value="">Semua desa</option>
                                @foreach (($map['village_options'] ?? []) as $village)
                                    <option value="{{ $village }}">{{ $village }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="adminlte-map__filters">
                        <span class="adminlte-map__legend-title">Filter Kategori</span>
                        <label class="adminlte-map__select-wrap">
                            <select data-admin-map-category>
                                <option value="">Semua kategori</option>
                                @foreach (($map['category_options'] ?? []) as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="adminlte-map__filters">
                        <span class="adminlte-map__legend-title">Filter OPD</span>
                        <label class="adminlte-map__select-wrap">
                            <select data-admin-map-opd>
                                <option value="">Semua OPD</option>
                                @foreach (($map['opd_options'] ?? []) as $opd)
                                    <option value="{{ $opd }}">{{ $opd }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="adminlte-map__coverage" data-admin-map-stats>
                        @foreach (($map['coverage'] ?? []) as $item)
                            <div>
                                <span>{{ $item['label'] }}</span>
                                <strong>{{ $item['value'] }}</strong>
                            </div>
                        @endforeach
                    </div>

                    <div class="adminlte-map__legend">
                        <span class="adminlte-map__legend-title">Layer Aktif</span>
                        @foreach (($map['layers'] ?? []) as $layer)
                            <button class="adminlte-map__legend-item is-active" type="button" data-admin-map-layer="{{ $layer['slug'] }}">
                                <i style="background: {{ $layer['color'] }}"></i>
                                <span>{{ $layer['name'] }}</span>
                                <small>{{ count($layer['features'] ?? []) }}</small>
                            </button>
                        @endforeach
                    </div>

                    <div class="adminlte-map__areas">
                        <span class="adminlte-map__legend-title" data-admin-map-focus-label>Area yang ditampilkan</span>
                        <div class="adminlte-map__chips" data-admin-map-focus-areas>
                            @foreach (($map['areas'] ?? []) as $area)
                                <span>{{ $area }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <script type="application/json" class="admin-dashboard-showcase__payload">
            {!! json_encode(['map' => $map], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    </div>
</x-filament-panels::page>
