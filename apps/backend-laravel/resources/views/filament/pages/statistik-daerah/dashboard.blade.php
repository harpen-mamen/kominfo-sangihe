@php
    $isReportPage = get_class($this) === \App\Filament\Pages\LaporanIndikatorDaerah::class;
    $isIndicatorPage = get_class($this) === \App\Filament\Pages\StatistikDaerah\StatistikPerIndikator::class;
    $isDashboardPage = get_class($this) === \App\Filament\Pages\StatistikDaerah\DashboardStatistik::class;
    $description = match (true) {
        $isReportPage => 'Laporan semua indikator berdasarkan periode, OPD, dan wilayah.',
        $isIndicatorPage => 'Analisis indikator terpilih lintas kecamatan, desa, dan periode.',
        $isDashboardPage => 'Ringkasan data masuk, sebaran indikator, OPD, dan wilayah.',
        default => 'Dashboard statistik daerah berdasarkan filter aktif.',
    };
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-medium text-cyan-700 dark:text-cyan-300">{{ $this->pageKicker }}</p>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">{{ $this->pageMode }}</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $description }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-800 dark:border-cyan-800 dark:bg-cyan-950 dark:text-cyan-200">
                        Periode: {{ $this->periodLabel }}
                    </span>
                    @if ($this->selectedIndikator)
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                            {{ $this->selectedIndikator->nama }}
                        </span>
                    @endif
                </div>
            </div>
        </section>

        @include('filament.pages.statistik-daerah.partials.statistic-filter-card')

        @if (! $this->hasData)
            <section class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700 dark:bg-cyan-950 dark:text-cyan-200">
                    <x-filament::icon icon="heroicon-o-chart-bar-square" class="h-7 w-7" />
                </div>
                <h2 class="mt-4 text-lg font-semibold text-gray-950 dark:text-white">Belum ada data</h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada data statistik untuk filter yang dipilih.</p>
                <button type="button" wire:click="resetFilters" class="mt-5 rounded-lg bg-[#0f766e] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0f3b57]">
                    Reset filter
                </button>
            </section>
        @else
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($this->summaryCards as $card)
                    @include('filament.pages.statistik-daerah.partials.statistic-kpi-card', ['card' => $card])
                @endforeach
            </section>

            @if ($this->selectedIndikator || $this->selectedKecamatan || $this->selectedDesa || $this->selectedOpd)
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4">
                        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Konteks Terpilih</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan filter utama yang sedang aktif.</p>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-950"><span class="text-xs text-gray-500">Indikator</span><p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $this->selectedIndikator?->nama ?? 'Semua indikator' }}</p></div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-950"><span class="text-xs text-gray-500">OPD</span><p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $this->selectedIndikator?->opd?->nama ?? $this->selectedOpd?->nama ?? 'Semua OPD' }}</p></div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-950"><span class="text-xs text-gray-500">Satuan</span><p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $this->selectedIndikator?->satuan ?? 'Beragam satuan' }}</p></div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-950"><span class="text-xs text-gray-500">Wilayah</span><p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $this->selectedDesa?->nama ?? $this->selectedKecamatan?->nama ?? 'Semua wilayah' }}</p></div>
                    </div>
                </section>
            @endif

            <section class="grid gap-4 xl:grid-cols-2">
                @include('filament.pages.statistik-daerah.partials.statistic-chart-card', ['chart' => $this->primaryChart])
                @include('filament.pages.statistik-daerah.partials.statistic-chart-card', ['chart' => $this->secondaryChart])
                @include('filament.pages.statistik-daerah.partials.statistic-chart-card', ['chart' => $this->tertiaryChart])
                @include('filament.pages.statistik-daerah.partials.statistic-chart-card', ['chart' => $this->quaternaryChart])
            </section>

            <section class="grid gap-4 xl:grid-cols-[1fr_22rem]">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4">
                        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Insight Cepat</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pembacaan ringkas dari chart utama.</p>
                    </div>
                    <div class="grid gap-3 md:grid-cols-3">
                        <div class="rounded-lg bg-cyan-50 p-4 dark:bg-cyan-950/40">
                            <span class="text-xs font-medium text-cyan-800 dark:text-cyan-200">Tertinggi</span>
                            <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $this->insight['highest'] }}</p>
                        </div>
                        <div class="rounded-lg bg-emerald-50 p-4 dark:bg-emerald-950/30">
                            <span class="text-xs font-medium text-emerald-800 dark:text-emerald-200">Terendah</span>
                            <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $this->insight['lowest'] }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-950">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Rata-rata</span>
                            <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $this->insight['average'] }}</p>
                        </div>
                    </div>
                </div>

                @include('filament.pages.statistik-daerah.partials.statistic-ranking-list', [
                    'title' => 'Ranking Utama',
                    'description' => $this->primaryChart['title'],
                    'rows' => $this->primaryChart['rows'],
                ])
            </section>

            @include('filament.pages.statistik-daerah.partials.statistic-detail-table', [
                'title' => $isReportPage ? 'Detail Laporan Indikator Daerah' : ($isDashboardPage ? 'Recent Data' : 'Detail Per Desa'),
                'description' => $isReportPage ? 'Kolom periode, OPD, kelompok, indikator, wilayah, sumber, nilai, dan satuan.' : 'Baris terbaru dari nilai_data_mentah sesuai filter.',
                'rows' => $this->detailRows,
            ])
        @endif
    </div>
</x-filament-panels::page>
