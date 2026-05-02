{{-- resources/views/filament/pages/laporan-indikator-daerah.blade.php --}}

<x-filament-panels::page>
    @includeIf('filament.pages._kominfo-page-style')

    @php
        $vars = get_defined_vars();

        $getProp = function (string $name, $default = null) use ($vars) {
            if (array_key_exists($name, $vars)) {
                return $vars[$name];
            }

            try {
                return $this->{$name} ?? $default;
            } catch (\Throwable $e) {
                return $default;
            }
        };

        $modePeriode = $getProp('modePeriode', 'bulanan');
        $tahun = $getProp('tahun', now()->year);
        $bulan = $getProp('bulan', now()->month);

        $tahunOptions = $getProp('tahunOptions', []);
        $bulanOptions = $getProp('bulanOptions', []);
        $opdOptions = $getProp('opdOptions', []);
        $kelompokOptions = $getProp('kelompokOptions', []);
        $indikatorOptions = $getProp('indikatorOptions', []);
        $kecamatanOptions = $getProp('kecamatanOptions', []);
        $desaOptions = $getProp('desaOptions', []);

        if (empty($tahunOptions)) {
            $tahunOptions = collect(range(now()->year, now()->year - 5))
                ->mapWithKeys(fn ($year) => [$year => $year])
                ->all();
        }

        if (empty($bulanOptions)) {
            $bulanOptions = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ];
        }

        $totals = $getProp('totals', []);
        $rows = collect($getProp('rows', []));
        $rankingWilayah = collect($getProp('rankingWilayah', []));

        $getRowValue = function ($row, string $key, $default = null) {
            return data_get($row, $key, $default);
        };

        $indikatorChart = $rows
            ->groupBy(fn ($row) => $getRowValue($row, 'indikator', 'Tidak diketahui'))
            ->map(fn ($items) => (float) collect($items)->sum(fn ($row) => (float) data_get($row, 'nilai', 0)))
            ->sortDesc()
            ->take(10);

        $opdChart = $rows
            ->groupBy(fn ($row) => $getRowValue($row, 'opd', 'Tidak diketahui'))
            ->map(fn ($items) => (float) collect($items)->sum(fn ($row) => (float) data_get($row, 'nilai', 0)))
            ->sortDesc()
            ->take(8);

        $trendChart = $rows
            ->groupBy(fn ($row) => $getRowValue($row, 'periode', 'Tidak diketahui'))
            ->map(fn ($items) => (float) collect($items)->sum(fn ($row) => (float) data_get($row, 'nilai', 0)));

        $desaChart = $rows
            ->groupBy(fn ($row) => $getRowValue($row, 'desa', 'Tidak diketahui'))
            ->map(fn ($items) => (float) collect($items)->sum(fn ($row) => (float) data_get($row, 'nilai', 0)))
            ->sortDesc()
            ->take(10);

        $chartPayload = [
            'indikator' => [
                'labels' => $indikatorChart->keys()->values()->all(),
                'values' => $indikatorChart->values()->values()->all(),
            ],
            'opd' => [
                'labels' => $opdChart->keys()->values()->all(),
                'values' => $opdChart->values()->values()->all(),
            ],
            'trend' => [
                'labels' => $trendChart->keys()->values()->all(),
                'values' => $trendChart->values()->values()->all(),
            ],
            'desa' => [
                'labels' => $desaChart->keys()->values()->all(),
                'values' => $desaChart->values()->values()->all(),
            ],
        ];

        $totalNilai = (float) data_get($totals, 'nilai', $rows->sum(fn ($row) => (float) data_get($row, 'nilai', 0)));
        $totalIndikator = (float) data_get($totals, 'indikator', $rows->pluck('indikator')->filter()->unique()->count());
        $totalOpd = (float) data_get($totals, 'opd', $rows->pluck('opd')->filter()->unique()->count());
        $totalDesa = (float) data_get($totals, 'desa', $rows->pluck('desa')->filter()->unique()->count());

        $bulanLabel = $bulanOptions[$bulan] ?? $bulan;
    @endphp

    <div class="kom-page">
        <div class="kom-stack">
            {{-- Header --}}
            <div class="kom-page-header">
                <div>
                    <p class="kom-eyebrow">Statistik Daerah</p>
                    <h1 class="kom-title">Laporan Indikator Daerah</h1>
                    <p class="kom-subtitle">
                        Laporan seluruh indikator daerah berdasarkan periode, OPD, kelompok indikator, kecamatan, dan desa.
                    </p>
                </div>

                <div class="kom-summary">
                    <div class="kom-summary-item">
                        <span class="kom-summary-label">Periode</span>
                        <span class="kom-summary-value">
                            {{ ucfirst((string) $modePeriode) }}
                        </span>
                    </div>

                    <div class="kom-summary-item">
                        <span class="kom-summary-label">Tahun</span>
                        <span class="kom-summary-value">
                            {{ $tahun }}

                            @if ($modePeriode === 'bulanan')
                                · {{ $bulanLabel }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Filter --}}
            <div class="kom-card">
                <div class="kom-card-header">
                    <div>
                        <h2 class="kom-card-title">Filter Data</h2>
                        <p class="kom-card-desc">
                            Gunakan filter berikut untuk menampilkan laporan sesuai kebutuhan.
                        </p>
                    </div>
                </div>

                <div class="kom-card-body">
                    <div class="kom-grid-3">
                        <label>
                            <span>Periode</span>
                            <select wire:model.live="modePeriode">
                                <option value="bulanan">Bulanan</option>
                                <option value="tahunan">Tahunan</option>
                            </select>
                        </label>

                        <label>
                            <span>Tahun</span>
                            <select wire:model.live="tahun">
                                @foreach ($tahunOptions as $id => $label)
                                    <option value="{{ $id }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Bulan</span>
                            <select wire:model.live="bulan" @disabled($modePeriode === 'tahunan')>
                                @foreach ($bulanOptions as $id => $label)
                                    <option value="{{ $id }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>OPD</span>
                            <select wire:model.live="opdId">
                                <option value="">Semua OPD</option>

                                @foreach ($opdOptions as $id => $label)
                                    <option value="{{ $id }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Kelompok Indikator</span>
                            <select wire:model.live="kelompok">
                                <option value="">Semua kelompok</option>

                                @foreach ($kelompokOptions as $id => $label)
                                    <option value="{{ $id }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Indikator</span>
                            <select wire:model.live="indikatorId">
                                <option value="">Semua indikator</option>

                                @foreach ($indikatorOptions as $id => $label)
                                    <option value="{{ $id }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Kecamatan</span>
                            <select wire:model.live="kecamatanId">
                                <option value="">Semua kecamatan</option>

                                @foreach ($kecamatanOptions as $id => $label)
                                    <option value="{{ $id }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Desa</span>
                            <select wire:model.live="desaId">
                                <option value="">Semua desa</option>

                                @foreach ($desaOptions as $id => $label)
                                    <option value="{{ $id }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="kom-grid-3">
                <div class="kom-summary-item">
                    <span class="kom-summary-label">Total Nilai</span>
                    <span class="kom-summary-value" style="font-size: 28px; color: #0369a1;">
                        {{ number_format($totalNilai, 2, ',', '.') }}
                    </span>
                    <p class="kom-text-muted">Akumulasi seluruh nilai sesuai filter.</p>
                </div>

                <div class="kom-summary-item">
                    <span class="kom-summary-label">Indikator</span>
                    <span class="kom-summary-value" style="font-size: 28px; color: #047857;">
                        {{ number_format($totalIndikator, 0, ',', '.') }}
                    </span>
                    <p class="kom-text-muted">Jumlah indikator yang memiliki data.</p>
                </div>

                <div class="kom-summary-item">
                    <span class="kom-summary-label">OPD</span>
                    <span class="kom-summary-value" style="font-size: 28px; color: #0e7490;">
                        {{ number_format($totalOpd, 0, ',', '.') }}
                    </span>
                    <p class="kom-text-muted">OPD penanggung jawab data.</p>
                </div>

                <div class="kom-summary-item">
                    <span class="kom-summary-label">Desa Terisi</span>
                    <span class="kom-summary-value" style="font-size: 28px; color: #d97706;">
                        {{ number_format($totalDesa, 0, ',', '.') }}
                    </span>
                    <p class="kom-text-muted">Jumlah desa yang memiliki data.</p>
                </div>
            </div>

            {{-- Charts --}}
            <div class="kom-grid">
                <div class="kom-card">
                    <div class="kom-card-header">
                        <div>
                            <h3 class="kom-card-title">Top 10 Indikator</h3>
                            <p class="kom-card-desc">Indikator dengan total nilai tertinggi.</p>
                        </div>
                    </div>

                    <div class="kom-card-body">
                        <div class="kom-chart-box">
                            <canvas id="chartIndikator"></canvas>
                        </div>
                    </div>
                </div>

                <div class="kom-card">
                    <div class="kom-card-header">
                        <div>
                            <h3 class="kom-card-title">Distribusi OPD</h3>
                            <p class="kom-card-desc">Sebaran nilai berdasarkan OPD penanggung jawab.</p>
                        </div>
                    </div>

                    <div class="kom-card-body">
                        <div class="kom-chart-box">
                            <canvas id="chartOpd"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kom-grid">
                <div class="kom-card">
                    <div class="kom-card-header">
                        <div>
                            <h3 class="kom-card-title">Tren Data Per Periode</h3>
                            <p class="kom-card-desc">Pergerakan total data berdasarkan periode.</p>
                        </div>
                    </div>

                    <div class="kom-card-body">
                        <div class="kom-chart-box">
                            <canvas id="chartTrend"></canvas>
                        </div>
                    </div>
                </div>

                <div class="kom-card">
                    <div class="kom-card-header">
                        <div>
                            <h3 class="kom-card-title">Ranking Wilayah</h3>
                            <p class="kom-card-desc">Wilayah dengan nilai tertinggi.</p>
                        </div>
                    </div>

                    <div class="kom-card-body">
                        @forelse ($rankingWilayah as $index => $row)
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--kom-border);">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <span class="kom-badge">
                                        {{ $index + 1 }}
                                    </span>

                                    <span class="kom-text-strong">
                                        {{ data_get($row, 'nama', '-') }}
                                    </span>
                                </div>

                                <span class="kom-text-strong">
                                    {{ number_format((float) data_get($row, 'nilai', 0), 2, ',', '.') }}
                                </span>
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
            </div>

            <div class="kom-card">
                <div class="kom-card-header">
                    <div>
                        <h3 class="kom-card-title">Top 10 Desa</h3>
                        <p class="kom-card-desc">Desa dengan nilai tertinggi sesuai filter.</p>
                    </div>
                </div>

                <div class="kom-card-body">
                    <div class="kom-chart-box">
                        <canvas id="chartDesa"></canvas>
                    </div>
                </div>
            </div>

            {{-- Detail Table --}}
            <div class="kom-card">
                <div class="kom-card-header">
                    <div>
                        <h3 class="kom-card-title">Detail Data</h3>
                        <p class="kom-card-desc">Daftar data mentah yang sudah difilter.</p>
                    </div>
                </div>

                <div class="kom-card-body">
                    <div class="kom-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Periode</th>
                                    <th>OPD</th>
                                    <th>Kelompok</th>
                                    <th>Indikator</th>
                                    <th>Kecamatan</th>
                                    <th>Desa</th>
                                    <th>Sumber Data</th>
                                    <th style="text-align: right;">Nilai</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($rows as $row)
                                    <tr>
                                        <td>{{ data_get($row, 'periode', '-') }}</td>
                                        <td>{{ data_get($row, 'opd', '-') }}</td>
                                        <td>
                                            <span class="kom-badge">
                                                {{ data_get($row, 'kelompok', '-') }}
                                            </span>
                                        </td>
                                        <td class="kom-text-strong">
                                            {{ data_get($row, 'indikator', '-') }}
                                        </td>
                                        <td>{{ data_get($row, 'kecamatan', '-') }}</td>
                                        <td>{{ data_get($row, 'desa', '-') }}</td>
                                        <td>{{ data_get($row, 'sumber_data', '-') }}</td>
                                        <td style="text-align: right;" class="kom-text-strong">
                                            {{ number_format((float) data_get($row, 'nilai', 0), 2, ',', '.') }}
                                            {{ data_get($row, 'satuan', '') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="kom-empty">
                                                <p class="kom-empty-title">Belum ada data</p>
                                                <p class="kom-empty-text">
                                                    Tidak ada data statistik untuk filter yang dipilih.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Data --}}
    <script type="application/json" id="laporan-indikator-chart-data">
        {!! json_encode($chartPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        (() => {
            let renderTimer = null;

            const renderCharts = () => {
                if (typeof Chart === 'undefined') {
                    return;
                }

                const payloadElement = document.getElementById('laporan-indikator-chart-data');

                if (!payloadElement) {
                    return;
                }

                let payload = {};

                try {
                    payload = JSON.parse(payloadElement.textContent || '{}');
                } catch (error) {
                    console.error('Gagal membaca data chart laporan indikator:', error);
                    payload = {};
                }

                window.kominfoChartInstances = window.kominfoChartInstances || {};

                const chartInstances = window.kominfoChartInstances;

                const formatNumber = (value) => {
                    return new Intl.NumberFormat('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2,
                    }).format(value ?? 0);
                };

                const destroyChart = (id) => {
                    if (chartInstances[id]) {
                        chartInstances[id].destroy();
                        delete chartInstances[id];
                    }
                };

                const makeChart = (id, config) => {
                    const element = document.getElementById(id);

                    if (!element) {
                        return;
                    }

                    destroyChart(id);

                    chartInstances[id] = new Chart(element, config);
                };

                const commonOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.dataset.label || '';
                                    const value = context.raw || 0;

                                    return `${label ? label + ': ' : ''}${formatNumber(value)}`;
                                },
                            },
                        },
                    },
                };

                const indikator = payload.indikator || { labels: [], values: [] };
                const opd = payload.opd || { labels: [], values: [] };
                const trend = payload.trend || { labels: [], values: [] };
                const desa = payload.desa || { labels: [], values: [] };

                makeChart('chartIndikator', {
                    type: 'bar',
                    data: {
                        labels: indikator.labels,
                        datasets: [
                            {
                                label: 'Total Nilai',
                                data: indikator.values,
                                backgroundColor: '#0F4C81',
                                borderRadius: 10,
                            },
                        ],
                    },
                    options: {
                        ...commonOptions,
                        indexAxis: 'y',
                        plugins: {
                            ...commonOptions.plugins,
                            legend: {
                                display: false,
                            },
                        },
                        scales: {
                            x: {
                                ticks: {
                                    callback: value => formatNumber(value),
                                },
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.18)',
                                },
                            },
                            y: {
                                grid: {
                                    display: false,
                                },
                            },
                        },
                    },
                });

                makeChart('chartOpd', {
                    type: 'doughnut',
                    data: {
                        labels: opd.labels,
                        datasets: [
                            {
                                label: 'Total Nilai',
                                data: opd.values,
                                backgroundColor: [
                                    '#0F4C81',
                                    '#19B5C8',
                                    '#1B8A5A',
                                    '#F59E0B',
                                    '#8B5CF6',
                                    '#EF4444',
                                    '#14B8A6',
                                    '#64748B',
                                ],
                                borderWidth: 0,
                            },
                        ],
                    },
                    options: {
                        ...commonOptions,
                        cutout: '65%',
                        plugins: {
                            ...commonOptions.plugins,
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    usePointStyle: true,
                                },
                            },
                        },
                    },
                });

                makeChart('chartTrend', {
                    type: 'line',
                    data: {
                        labels: trend.labels,
                        datasets: [
                            {
                                label: 'Total Nilai',
                                data: trend.values,
                                borderColor: '#19B5C8',
                                backgroundColor: 'rgba(25, 181, 200, 0.15)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            },
                        ],
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            ...commonOptions.plugins,
                            legend: {
                                display: false,
                            },
                        },
                        scales: {
                            y: {
                                ticks: {
                                    callback: value => formatNumber(value),
                                },
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.18)',
                                },
                            },
                            x: {
                                grid: {
                                    display: false,
                                },
                            },
                        },
                    },
                });

                makeChart('chartDesa', {
                    type: 'bar',
                    data: {
                        labels: desa.labels,
                        datasets: [
                            {
                                label: 'Total Nilai',
                                data: desa.values,
                                backgroundColor: '#1B8A5A',
                                borderRadius: 10,
                            },
                        ],
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            ...commonOptions.plugins,
                            legend: {
                                display: false,
                            },
                        },
                        scales: {
                            y: {
                                ticks: {
                                    callback: value => formatNumber(value),
                                },
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.18)',
                                },
                            },
                            x: {
                                grid: {
                                    display: false,
                                },
                            },
                        },
                    },
                });
            };

            const queueRenderCharts = () => {
                window.clearTimeout(renderTimer);
                renderTimer = window.setTimeout(renderCharts, 80);
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', queueRenderCharts);
            } else {
                queueRenderCharts();
            }

            document.addEventListener('livewire:navigated', queueRenderCharts);
            document.addEventListener('livewire:initialized', () => {
                if (! window.Livewire?.hook) {
                    return;
                }

                window.Livewire.hook('morph.updated', queueRenderCharts);
            });
        })();
    </script>
</x-filament-panels::page>
