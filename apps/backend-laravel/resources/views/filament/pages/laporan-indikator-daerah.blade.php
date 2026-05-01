<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-950 dark:text-white">
                        Laporan Indikator Daerah
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Laporan seluruh indikator daerah berdasarkan periode, OPD, dan wilayah.
                    </p>
                </div>

                <div class="rounded-full bg-sky-50 px-4 py-2 text-sm font-medium text-sky-700 dark:bg-sky-950 dark:text-sky-300">
                    {{ ucfirst($modePeriode) }} • {{ $tahun }}
                    @if ($modePeriode === 'bulanan')
                        • {{ $this->bulanOptions[$bulan] ?? $bulan }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">
                    Filter Data
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Gunakan filter untuk menampilkan laporan sesuai kebutuhan.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <label class="space-y-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Periode</span>
                    <select wire:model.live="modePeriode" class="w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                        <option value="bulanan">Bulanan</option>
                        <option value="tahunan">Tahunan</option>
                    </select>
                </label>

                <label class="space-y-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Tahun</span>
                    <select wire:model.live="tahun" class="w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                        @foreach ($this->tahunOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="space-y-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Bulan</span>
                    <select wire:model.live="bulan" @disabled($modePeriode === 'tahunan') class="w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm disabled:opacity-50 dark:border-gray-700 dark:bg-gray-950">
                        @foreach ($this->bulanOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="space-y-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">OPD</span>
                    <select wire:model.live="opdId" class="w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                        <option value="">Semua OPD</option>
                        @foreach ($this->opdOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="space-y-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Kelompok Indikator</span>
                    <select wire:model.live="kelompok" class="w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                        <option value="">Semua kelompok</option>
                        @foreach ($this->kelompokOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="space-y-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Indikator</span>
                    <select wire:model.live="indikatorId" class="w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                        <option value="">Semua indikator</option>
                        @foreach ($this->indikatorOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="space-y-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Kecamatan</span>
                    <select wire:model.live="kecamatanId" class="w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                        <option value="">Semua kecamatan</option>
                        @foreach ($this->kecamatanOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="space-y-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Desa</span>
                    <select wire:model.live="desaId" class="w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                        <option value="">Semua desa</option>
                        @foreach ($this->desaOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Nilai</p>
                <h3 class="mt-3 text-3xl font-bold text-sky-700 dark:text-sky-300">
                    {{ number_format((float) ($this->totals['nilai'] ?? 0), 2, ',', '.') }}
                </h3>
                <p class="mt-2 text-xs text-gray-500">Akumulasi seluruh nilai sesuai filter.</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Indikator</p>
                <h3 class="mt-3 text-3xl font-bold text-emerald-700 dark:text-emerald-300">
                    {{ number_format((float) ($this->totals['indikator'] ?? 0), 0, ',', '.') }}
                </h3>
                <p class="mt-2 text-xs text-gray-500">Jumlah indikator yang memiliki data.</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">OPD</p>
                <h3 class="mt-3 text-3xl font-bold text-cyan-700 dark:text-cyan-300">
                    {{ number_format((float) ($this->totals['opd'] ?? 0), 0, ',', '.') }}
                </h3>
                <p class="mt-2 text-xs text-gray-500">OPD penanggung jawab data.</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Desa Terisi</p>
                <h3 class="mt-3 text-3xl font-bold text-amber-600 dark:text-amber-300">
                    {{ number_format((float) ($this->totals['desa'] ?? 0), 0, ',', '.') }}
                </h3>
                <p class="mt-2 text-xs text-gray-500">Jumlah desa yang memiliki data.</p>
            </div>
        </div>

        {{-- Prepare Chart Data --}}
        @php
            $rows = collect($this->rows ?? []);

            $indikatorChart = $rows
                ->groupBy('indikator')
                ->map(fn ($items) => (float) $items->sum('nilai'))
                ->sortDesc()
                ->take(10);

            $opdChart = $rows
                ->groupBy('opd')
                ->map(fn ($items) => (float) $items->sum('nilai'))
                ->sortDesc()
                ->take(8);

            $trendChart = $rows
                ->groupBy('periode')
                ->map(fn ($items) => (float) $items->sum('nilai'));

            $desaChart = $rows
                ->groupBy('desa')
                ->map(fn ($items) => (float) $items->sum('nilai'))
                ->sortDesc()
                ->take(10);
        @endphp

        {{-- Charts --}}
        <div class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Top 10 Indikator</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Indikator dengan total nilai tertinggi.</p>
                </div>
                <div class="h-80">
                    <canvas id="chartIndikator"></canvas>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Distribusi OPD</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sebaran nilai berdasarkan OPD penanggung jawab.</p>
                </div>
                <div class="h-80">
                    <canvas id="chartOpd"></canvas>
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Tren Data Per Periode</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pergerakan total data berdasarkan periode.</p>
                </div>
                <div class="h-80">
                    <canvas id="chartTrend"></canvas>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Ranking Wilayah</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Wilayah dengan nilai tertinggi.</p>
                </div>

                <div class="space-y-3">
                    @forelse ($this->rankingWilayah as $index => $row)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 dark:border-gray-800 dark:bg-gray-950">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-sky-100 text-xs font-bold text-sky-700 dark:bg-sky-950 dark:text-sky-300">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $row['nama'] }}
                                </span>
                            </div>
                            <span class="text-sm font-semibold text-gray-950 dark:text-white">
                                {{ number_format((float) $row['nilai'], 2, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-700">
                            Belum ada data ranking.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Top 10 Desa</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Desa dengan nilai tertinggi sesuai filter.</p>
                </div>
            </div>
            <div class="h-80">
                <canvas id="chartDesa"></canvas>
            </div>
        </div>

        {{-- Detail Table --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 p-5 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Detail Data</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Daftar data mentah yang sudah difilter.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Periode</th>
                            <th class="px-4 py-3">OPD</th>
                            <th class="px-4 py-3">Kelompok</th>
                            <th class="px-4 py-3">Indikator</th>
                            <th class="px-4 py-3">Kecamatan</th>
                            <th class="px-4 py-3">Desa</th>
                            <th class="px-4 py-3">Sumber Data</th>
                            <th class="px-4 py-3 text-right">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($this->rows as $row)
                            <tr class="hover:bg-gray-50 even:bg-gray-50/50 dark:hover:bg-gray-950 dark:even:bg-gray-950/40">
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">{{ $row['periode'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $row['opd'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $row['kelompok'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-950 dark:text-white">{{ $row['indikator'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $row['kecamatan'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $row['desa'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $row['sumber_data'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-gray-950 dark:text-white">
                                    {{ number_format((float) $row['nilai'], 2, ',', '.') }} {{ $row['satuan'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <div class="text-base font-semibold text-gray-800 dark:text-gray-200">
                                            Belum ada data
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
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

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        (() => {
            const chartInstances = {};

            const formatNumber = (value) => {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                }).format(value ?? 0);
            };

            const makeChart = (id, config) => {
                const element = document.getElementById(id);

                if (!element) {
                    return;
                }

                if (chartInstances[id]) {
                    chartInstances[id].destroy();
                }

                chartInstances[id] = new Chart(element, config);
            };

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.raw || 0;
                                return `${label ? label + ': ' : ''}${formatNumber(value)}`;
                            }
                        }
                    }
                }
            };

            makeChart('chartIndikator', {
                type: 'bar',
                data: {
                    labels: @json($indikatorChart->keys()->values()),
                    datasets: [{
                        label: 'Total Nilai',
                        data: @json($indikatorChart->values()),
                        backgroundColor: '#0F4C81',
                        borderRadius: 10,
                    }]
                },
                options: {
                    ...commonOptions,
                    indexAxis: 'y',
                    plugins: {
                        ...commonOptions.plugins,
                        legend: { display: false },
                    },
                    scales: {
                        x: {
                            ticks: {
                                callback: value => formatNumber(value),
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.18)',
                            }
                        },
                        y: {
                            grid: {
                                display: false,
                            }
                        }
                    }
                }
            });

            makeChart('chartOpd', {
                type: 'doughnut',
                data: {
                    labels: @json($opdChart->keys()->values()),
                    datasets: [{
                        label: 'Total Nilai',
                        data: @json($opdChart->values()),
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
                    }]
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
                            }
                        },
                    }
                }
            });

            makeChart('chartTrend', {
                type: 'line',
                data: {
                    labels: @json($trendChart->keys()->values()),
                    datasets: [{
                        label: 'Total Nilai',
                        data: @json($trendChart->values()),
                        borderColor: '#19B5C8',
                        backgroundColor: 'rgba(25, 181, 200, 0.15)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        ...commonOptions.plugins,
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: value => formatNumber(value),
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.18)',
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                            }
                        }
                    }
                }
            });

            makeChart('chartDesa', {
                type: 'bar',
                data: {
                    labels: @json($desaChart->keys()->values()),
                    datasets: [{
                        label: 'Total Nilai',
                        data: @json($desaChart->values()),
                        backgroundColor: '#1B8A5A',
                        borderRadius: 10,
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        ...commonOptions.plugins,
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: value => formatNumber(value),
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.18)',
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                            }
                        }
                    }
                }
            });
        })();
    </script>
</x-filament-panels::page>