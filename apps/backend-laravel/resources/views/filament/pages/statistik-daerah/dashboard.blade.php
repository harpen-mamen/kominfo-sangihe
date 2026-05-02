{{-- resources/views/filament/pages/statistik-daerah/dashboard.blade.php --}}

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

    $hasProp = function (string $name) {
        try {
            return property_exists($this, $name);
        } catch (\Throwable $e) {
            return false;
        }
    };

    $pageClass = get_class($this);

    $isReportPage = $pageClass === \App\Filament\Pages\LaporanIndikatorDaerah::class;
    $isIndicatorPage = $pageClass === \App\Filament\Pages\StatistikDaerah\StatistikPerIndikator::class;
    $isDashboardPage = $pageClass === \App\Filament\Pages\StatistikDaerah\DashboardStatistik::class;

    $description = match (true) {
        $isReportPage => 'Laporan semua indikator berdasarkan periode, OPD, dan wilayah.',
        $isIndicatorPage => 'Analisis indikator terpilih lintas kecamatan, desa, dan periode.',
        $isDashboardPage => 'Ringkasan data masuk, sebaran indikator, OPD, dan wilayah.',
        default => 'Dashboard statistik daerah berdasarkan filter aktif.',
    };

    $pageKicker = $getProp('pageKicker', 'Statistik Daerah');
    $title = $getProp('title', $isReportPage ? 'Laporan Indikator Daerah' : 'Dashboard Statistik Daerah');

    $modePeriode = $getProp('modePeriode', 'bulanan');
    $tahun = $getProp('tahun', now()->year);
    $bulan = $getProp('bulan', now()->month);

    $tahunOptions = $getProp('tahunOptions', []);
    $bulanOptions = $getProp('bulanOptions', [
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
    ]);

    if (empty($tahunOptions)) {
        $tahunOptions = collect(range(now()->year, now()->year - 5))
            ->mapWithKeys(fn ($year) => [$year => $year])
            ->all();
    }

    $opdOptions = $getProp('opdOptions', []);
    $kelompokOptions = $getProp('kelompokOptions', []);
    $indikatorOptions = $getProp('indikatorOptions', []);
    $kecamatanOptions = $getProp('kecamatanOptions', []);
    $desaOptions = $getProp('desaOptions', []);

    $bulanLabel = $bulanOptions[$bulan] ?? $bulan;
    $periodLabel = $getProp('periodLabel', $modePeriode === 'bulanan' ? "{$bulanLabel} {$tahun}" : (string) $tahun);

    $rowsRaw = $getProp('rows', []);
    $rows = is_object($rowsRaw) && method_exists($rowsRaw, 'items')
        ? collect($rowsRaw->items())
        : collect($rowsRaw);

    $totals = $getProp('totals', []);

    $totalNilai = (float) data_get($totals, 'nilai', $rows->sum(fn ($row) => (float) data_get($row, 'nilai', 0)));
    $totalIndikator = (int) data_get($totals, 'indikator', $rows->pluck('indikator')->filter()->unique()->count());
    $totalOpd = (int) data_get($totals, 'opd', $rows->pluck('opd')->filter()->unique()->count());
    $totalDesa = (int) data_get($totals, 'desa', $rows->pluck('desa')->filter()->unique()->count());

    $kpiCards = collect($getProp('kpiCards', []));
    if ($kpiCards->isEmpty()) {
        $kpiCards = collect([
            [
                'label' => 'Total Nilai',
                'value' => number_format($totalNilai, 2, ',', '.'),
                'note' => 'Akumulasi seluruh nilai sesuai filter.',
                'tone' => 'blue',
            ],
            [
                'label' => 'Indikator',
                'value' => number_format($totalIndikator, 0, ',', '.'),
                'note' => 'Jumlah indikator yang memiliki data.',
                'tone' => 'green',
            ],
            [
                'label' => 'OPD',
                'value' => number_format($totalOpd, 0, ',', '.'),
                'note' => 'OPD penanggung jawab data.',
                'tone' => 'cyan',
            ],
            [
                'label' => 'Desa Terisi',
                'value' => number_format($totalDesa, 0, ',', '.'),
                'note' => 'Jumlah desa yang memiliki data.',
                'tone' => 'amber',
            ],
        ]);
    }

    $rankingWilayah = collect($getProp('rankingWilayah', []));
    if ($rankingWilayah->isEmpty()) {
        $rankingWilayah = $rows
            ->groupBy(fn ($row) => data_get($row, 'kecamatan') ?: data_get($row, 'desa') ?: 'Tidak diketahui')
            ->map(fn ($items, $nama) => [
                'nama' => $nama,
                'nilai' => (float) collect($items)->sum(fn ($row) => (float) data_get($row, 'nilai', 0)),
            ])
            ->sortByDesc('nilai')
            ->values()
            ->take(10);
    }

    $indikatorChart = $rows
        ->groupBy(fn ($row) => data_get($row, 'indikator', 'Tidak diketahui'))
        ->map(fn ($items) => (float) collect($items)->sum(fn ($row) => (float) data_get($row, 'nilai', 0)))
        ->sortDesc()
        ->take(10);

    $opdChart = $rows
        ->groupBy(fn ($row) => data_get($row, 'opd', 'Tidak diketahui'))
        ->map(fn ($items) => (float) collect($items)->sum(fn ($row) => (float) data_get($row, 'nilai', 0)))
        ->sortDesc()
        ->take(8);

    $trendChart = $rows
        ->groupBy(fn ($row) => data_get($row, 'periode', 'Tidak diketahui'))
        ->map(fn ($items) => (float) collect($items)->sum(fn ($row) => (float) data_get($row, 'nilai', 0)));

    $desaChart = $rows
        ->groupBy(fn ($row) => data_get($row, 'desa', 'Tidak diketahui'))
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
@endphp

<x-filament-panels::page>
    @includeIf('filament.pages._kominfo-page-style')

    <div class="kom-page">
        <div class="kom-stack">
            <section class="kom-page-header">
                <div>
                    <p class="kom-eyebrow">{{ $pageKicker }}</p>
                    <h1 class="kom-title">{{ $title }}</h1>
                    <p class="kom-subtitle">{{ $description }}</p>
                </div>

                <div class="kom-summary">
                    <div class="kom-summary-item">
                        <span class="kom-summary-label">Periode</span>
                        <span class="kom-summary-value">{{ ucfirst((string) $modePeriode) }}</span>
                    </div>

                    <div class="kom-summary-item">
                        <span class="kom-summary-label">Rentang Data</span>
                        <span class="kom-summary-value">{{ $periodLabel }}</span>
                    </div>
                </div>
            </section>

            @include('filament.pages.statistik-daerah.partials.statistic-filter-card', [
                'modePeriode' => $modePeriode,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'tahunOptions' => $tahunOptions,
                'bulanOptions' => $bulanOptions,
                'opdOptions' => $opdOptions,
                'kelompokOptions' => $kelompokOptions,
                'indikatorOptions' => $indikatorOptions,
                'kecamatanOptions' => $kecamatanOptions,
                'desaOptions' => $desaOptions,
                'hasModePeriode' => $hasProp('modePeriode'),
                'hasTahun' => $hasProp('tahun'),
                'hasBulan' => $hasProp('bulan'),
                'hasOpd' => $hasProp('opdId'),
                'hasKelompok' => $hasProp('kelompok'),
                'hasIndikator' => $hasProp('indikatorId'),
                'hasKecamatan' => $hasProp('kecamatanId'),
                'hasDesa' => $hasProp('desaId'),
            ])

            @include('filament.pages.statistik-daerah.partials.statistic-kpi-card', [
                'cards' => $kpiCards,
            ])

            <div class="kom-grid">
                @include('filament.pages.statistik-daerah.partials.statistic-chart-card', [
                    'title' => 'Top 10 Indikator',
                    'description' => 'Indikator dengan total nilai tertinggi.',
                    'canvasId' => 'chartIndikator',
                ])

                @include('filament.pages.statistik-daerah.partials.statistic-chart-card', [
                    'title' => 'Distribusi OPD',
                    'description' => 'Sebaran nilai berdasarkan OPD penanggung jawab.',
                    'canvasId' => 'chartOpd',
                ])
            </div>

            <div class="kom-grid">
                @include('filament.pages.statistik-daerah.partials.statistic-chart-card', [
                    'title' => 'Tren Data Per Periode',
                    'description' => 'Pergerakan total data berdasarkan periode.',
                    'canvasId' => 'chartTrend',
                ])

                @include('filament.pages.statistik-daerah.partials.statistic-ranking-list', [
                    'title' => 'Ranking Wilayah',
                    'description' => 'Wilayah dengan nilai tertinggi sesuai filter.',
                    'items' => $rankingWilayah,
                ])
            </div>

            @include('filament.pages.statistik-daerah.partials.statistic-chart-card', [
                'title' => 'Top 10 Desa',
                'description' => 'Desa dengan nilai tertinggi sesuai filter.',
                'canvasId' => 'chartDesa',
            ])

            @include('filament.pages.statistik-daerah.partials.statistic-detail-table', [
                'rows' => $rows,
            ])
        </div>
    </div>

    <script type="application/json" id="statistik-daerah-chart-data">
        {!! json_encode($chartPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        (() => {
            let renderTimer = null;

            const renderCharts = () => {
                if (typeof Chart === 'undefined') {
                    return;
                }

                const payloadElement = document.getElementById('statistik-daerah-chart-data');

                if (!payloadElement) {
                    return;
                }

                let payload = {};

                try {
                    payload = JSON.parse(payloadElement.textContent || '{}');
                } catch (error) {
                    console.error('Gagal membaca data chart statistik daerah:', error);
                    payload = {};
                }

                window.kominfoStatistikCharts = window.kominfoStatistikCharts || {};

                const chartInstances = window.kominfoStatistikCharts;

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
