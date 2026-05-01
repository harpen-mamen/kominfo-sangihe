<?php

namespace App\Services;

use App\Filament\Resources\Berita\BeritaResource;
use App\Filament\Resources\Kegiatan\KegiatanResource;
use App\Filament\Resources\LapisanPeta\LapisanPetaResource;
use App\Filament\Resources\PengajuanData\PengajuanDataResource;
use App\Filament\Resources\Pengguna\PenggunaResource;
use App\Filament\Resources\RingkasanStatistik\RingkasanStatistikResource;
use App\Models\Berita;
use App\Models\Desa;
use App\Models\FiturPeta;
use App\Models\IndikatorData;
use App\Models\Kecamatan;
use App\Models\Kegiatan;
use App\Models\LapisanPeta;
use App\Models\PengajuanData;
use App\Models\RingkasanStatistik;
use App\Models\User;
use App\Support\AdminScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function build(?User $user): array
    {
        [$workspaceKey, $scopeLabel] = $this->workspaceContext($user);

        $map = $this->mapData($user, $workspaceKey);
        $trend = $this->trendChartData($user, $workspaceKey);
        $comparison = $this->comparisonChartData($user, $workspaceKey, $trend['focus_indicator_id'] ?? null);
        $composition = $this->compositionChartData($user, $workspaceKey);

        return [
            'hero' => $this->heroData($workspaceKey, $scopeLabel),
            'summaryCards' => $this->summaryCards($user, $workspaceKey, $map),
            'trend' => $trend,
            'comparison' => $comparison,
            'composition' => $composition,
            'districtCharts' => $this->districtChartPayloads($user, $workspaceKey, $map['district_options'] ?? []),
            'map' => $map,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mapPreview(?User $user): array
    {
        [$workspaceKey] = $this->workspaceContext($user);

        return $this->mapData($user, $workspaceKey);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function workspaceContext(?User $user): array
    {
        if (! $user) {
            return ['kominfo', 'Kabupaten Kepulauan Sangihe'];
        }

        return [AdminScope::workspaceKey($user), AdminScope::scopeLabel($user)];
    }

    /**
     * @return array<string, mixed>
     */
    private function heroData(string $workspaceKey, string $scopeLabel): array
    {
        return match ($workspaceKey) {
            'kecamatan' => [
                'eyebrow' => 'Dashboard Kecamatan',
                'title' => 'Peta digital dan statistik kerja harian',
                'description' => "Pantau pengajuan, ringkasan statistik, dan sebaran data desa untuk {$scopeLabel} dalam satu dashboard responsif.",
                'badges' => [
                    "Cakupan: {$scopeLabel}",
                    'Tampilan: AdminLTE x Filament',
                    'Fokus: desa, pengajuan, dan validasi',
                ],
                'links' => [
                    ['label' => 'Pengajuan Data', 'description' => 'Rapikan dan kirim data wilayah.', 'url' => PengajuanDataResource::getUrl()],
                    ['label' => 'Ringkasan Statistik', 'description' => 'Lihat hasil rekap kecamatan.', 'url' => RingkasanStatistikResource::getUrl()],
                    ['label' => 'Agenda Wilayah', 'description' => 'Kelola kegiatan publik kecamatan.', 'url' => KegiatanResource::getUrl()],
                ],
            ],
            'opd' => [
                'eyebrow' => 'Dashboard OPD',
                'title' => 'Statistik sektoral dengan peta digital',
                'description' => "Dashboard ini menempatkan grafik prioritas, peta digital, dan antrean publikasi untuk {$scopeLabel} dalam pola visual AdminLTE.",
                'badges' => [
                    "Cakupan: {$scopeLabel}",
                    'Fokus: konten sektoral dan tren indikator',
                    'Responsif untuk desktop dan mobile',
                ],
                'links' => [
                    ['label' => 'Ringkasan Statistik', 'description' => 'Tinjau indikator sektoral terbaru.', 'url' => RingkasanStatistikResource::getUrl()],
                    ['label' => 'Berita OPD', 'description' => 'Kelola berita resmi perangkat daerah.', 'url' => BeritaResource::getUrl()],
                    ['label' => 'Agenda OPD', 'description' => 'Jadwalkan kegiatan publik.', 'url' => KegiatanResource::getUrl()],
                ],
            ],
            default => [
                'eyebrow' => 'Dashboard Kominfo',
                'title' => 'Panel utama statistik dan peta digital Sangihe',
                'description' => 'Layout dashboard disusun mengikuti pola AdminLTE dengan KPI besar, grafik utama, perbandingan wilayah, dan peta digital yang siap menampilkan batas kecamatan maupun desa.',
                'badges' => [
                    "Cakupan: {$scopeLabel}",
                    'Fokus: monitoring lintas wilayah',
                    'Siap menampilkan layer polygon dan titik layanan',
                ],
                'links' => [
                    ['label' => 'Monitoring Pengajuan', 'description' => 'Cek antrian data masuk lintas wilayah.', 'url' => PengajuanDataResource::getUrl()],
                    ['label' => 'Peta Digital', 'description' => 'Kelola layer aktif dan fitur peta.', 'url' => LapisanPetaResource::getUrl()],
                    ['label' => 'Kelola Pengguna', 'description' => 'Atur akses admin dan operator.', 'url' => PenggunaResource::getUrl()],
                ],
            ],
        };
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function summaryCards(?User $user, string $workspaceKey, array $map): array
    {
        $pendingSubmissions = $this->submissionQuery($user)->where('status', 'diajukan')->count();
        $publishedContent = $this->newsQuery($user)->where('status', 'terbit')->count()
            + $this->eventQuery($user)->where('status', 'terbit')->count();
        $activeLayers = LapisanPeta::query()->where('aktif', true)->count();
        $scopeKecamatan = $workspaceKey === 'kecamatan' && $user?->kecamatan_id
            ? 1
            : Kecamatan::query()->where('aktif', true)->count();
        $scopeDesa = $workspaceKey === 'kecamatan' && $user?->kecamatan_id
            ? Desa::query()->where('aktif', true)->where('kecamatan_id', $user->kecamatan_id)->count()
            : Desa::query()->where('aktif', true)->count();

        return [
            [
                'label' => 'Review Queue',
                'value' => (string) $pendingSubmissions,
                'description' => 'Pengajuan data yang menunggu verifikasi',
                'tone' => 'warning',
                'icon' => 'RQ',
            ],
            [
                'label' => 'Konten Terbit',
                'value' => (string) $publishedContent,
                'description' => 'Berita dan agenda yang sudah tayang',
                'tone' => 'success',
                'icon' => 'KT',
            ],
            [
                'label' => 'Layer Aktif',
                'value' => (string) $activeLayers,
                'description' => $map['status_label'],
                'tone' => 'info',
                'icon' => 'LP',
            ],
            [
                'label' => 'Wilayah Tercakup',
                'value' => $scopeKecamatan.' / '.$scopeDesa,
                'description' => 'Kecamatan dan desa aktif pada scope dashboard',
                'tone' => 'primary',
                'icon' => 'WT',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function trendChartData(?User $user, string $workspaceKey, ?int $districtId = null, ?string $districtLabel = null): array
    {
        $isDistrictScoped = $districtId !== null && $workspaceKey !== 'kecamatan';
        $aggregationLevel = $workspaceKey === 'opd' ? 'opd' : 'kecamatan';
        $baseQuery = $this->ringkasanQuery($user)
            ->where('tingkat_rekap', $aggregationLevel)
            ->when($isDistrictScoped, fn (Builder $query) => $query->where('kecamatan_id', $districtId));

        $periodCount = (clone $baseQuery)
            ->select('periode_data_id')
            ->distinct()
            ->count('periode_data_id');

        $focusIndicatorQuery = $user
            ? AdminScope::indikatorDataQuery($user)
            : IndikatorData::query();

        $focusIndicator = $focusIndicatorQuery->where('aktif', true)->orderBy('urutan')->first();
        $chartTitle = $isDistrictScoped ? "Tren {$districtLabel}" : 'Sorotan Indikator Prioritas';
        $chartDescription = $isDistrictScoped
            ? "Nilai indikator utama {$districtLabel} ditampilkan sebagai sorotan grafik yang hidup dan responsif."
            : 'Nilai ringkasan terbaru ditampilkan seperti kartu statistik utama pada referensi AdminLTE.';
        $labels = [];
        $data = [];
        $focusIndicatorLabel = $focusIndicator?->nama ?? 'Indikator';
        $latestPeriodLabel = '-';

        if ($periodCount > 1 && $focusIndicator) {
            $rows = (clone $baseQuery)
                ->with(['periodeData', 'indikatorData'])
                ->where('indikator_data_id', $focusIndicator->id)
                ->orderBy('periode_data_id')
                ->get();

            $labels = $rows->map(fn (RingkasanStatistik $row): string => $row->periodeData?->label ?? ('Periode '.$row->periode_data_id))->all();
            $data = $rows->map(fn (RingkasanStatistik $row): float => (float) $row->nilai_total)->all();
            $latestPeriodLabel = $rows->last()?->periodeData?->label ?? '-';
            $chartTitle = $isDistrictScoped ? "Tren Statistik {$districtLabel}" : 'Tren Statistik Utama';
            $chartDescription = $isDistrictScoped
                ? "Grafik area utama membaca seri waktu indikator prioritas khusus untuk {$districtLabel}."
                : 'Grafik area utama mengikuti pola AdminLTE dan membaca seri waktu indikator prioritas.';
        } else {
            $latestPeriodId = (clone $baseQuery)->max('periode_data_id');

            $rows = (clone $baseQuery)
                ->with(['indikatorData', 'periodeData'])
                ->when($latestPeriodId, fn (Builder $query) => $query->where('periode_data_id', $latestPeriodId))
                ->orderBy('indikator_data_id')
                ->get()
                ->sortBy(fn (RingkasanStatistik $row): int => $row->indikatorData?->urutan ?? 9999)
                ->take(6)
                ->values();

            $labels = $rows
                ->map(fn (RingkasanStatistik $row): string => Str::limit($row->indikatorData?->nama ?? 'Indikator', 20, '...'))
                ->all();
            $data = $rows->map(fn (RingkasanStatistik $row): float => (float) $row->nilai_total)->all();
            $focusIndicator = $rows->first()?->indikatorData ?? $focusIndicator;
            $focusIndicatorLabel = $focusIndicator?->nama ?? 'Indikator';
            $latestPeriodLabel = $rows->first()?->periodeData?->label ?? '-';
        }

        $peakIndex = count($data) ? array_keys($data, max($data))[0] : null;
        $peakLabel = $peakIndex !== null ? ($labels[$peakIndex] ?? '-') : '-';
        $peakValue = $peakIndex !== null ? $this->formatNumber((float) ($data[$peakIndex] ?? 0)) : '0';

        return [
            'title' => $chartTitle,
            'description' => $chartDescription,
            'scope_label' => $districtLabel,
            'focus_indicator_id' => $focusIndicator?->id,
            'focus_indicator_label' => $focusIndicatorLabel,
            'stats' => [
                ['label' => $isDistrictScoped ? 'Kecamatan' : 'Fokus', 'value' => $isDistrictScoped ? ($districtLabel ?? '-') : $focusIndicatorLabel],
                ['label' => 'Periode', 'value' => $latestPeriodLabel],
                ['label' => 'Nilai Tertinggi', 'value' => $peakLabel.' · '.$peakValue],
            ],
            'chart' => [
                'type' => 'line',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $isDistrictScoped ? "{$focusIndicatorLabel} - {$districtLabel}" : $focusIndicatorLabel,
                        'data' => $data,
                        'borderColor' => '#0d6efd',
                        'backgroundColor' => 'rgba(13, 110, 253, 0.18)',
                        'fill' => true,
                        'tension' => 0.38,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function comparisonChartData(?User $user, string $workspaceKey, ?int $indicatorId, ?int $districtId = null, ?string $districtLabel = null): array
    {
        $isDistrictScoped = $districtId !== null && $workspaceKey !== 'kecamatan';
        $comparisonLevel = in_array($workspaceKey, ['kecamatan', 'opd'], true) || $isDistrictScoped ? 'desa' : 'kecamatan';
        $query = $this->ringkasanQuery($user)
            ->where('tingkat_rekap', $comparisonLevel)
            ->when($isDistrictScoped, fn (Builder $builder) => $builder->where('kecamatan_id', $districtId));
        $latestPeriodId = (clone $query)->max('periode_data_id');

        $rows = (clone $query)
            ->with(['kecamatan', 'desa', 'indikatorData', 'periodeData'])
            ->when($indicatorId, fn (Builder $builder) => $builder->where('indikator_data_id', $indicatorId))
            ->when($latestPeriodId, fn (Builder $builder) => $builder->where('periode_data_id', $latestPeriodId))
            ->orderByDesc('nilai_total')
            ->limit($comparisonLevel === 'desa' ? 8 : 10)
            ->get();

        $labels = $rows->map(
            fn (RingkasanStatistik $row): string => $comparisonLevel === 'desa'
                ? ($row->desa?->nama ?? 'Desa')
                : ($row->kecamatan?->nama ?? 'Kabupaten')
        )->all();
        $values = $rows->map(fn (RingkasanStatistik $row): float => (float) $row->nilai_total)->all();
        $leader = $rows->first();

        return [
            'title' => $comparisonLevel === 'desa'
                ? ($isDistrictScoped ? "Perbandingan Desa {$districtLabel}" : 'Perbandingan Desa')
                : 'Perbandingan Kecamatan',
            'description' => $isDistrictScoped
                ? "Grafik batang kanan membandingkan desa di {$districtLabel} untuk indikator fokus terbaru."
                : 'Grafik batang kanan mengambil indikator fokus terbaru untuk membandingkan capaian antarwilayah.',
            'scope_label' => $districtLabel,
            'stats' => [
                ['label' => 'Cakupan', 'value' => count($labels).' wilayah'],
                ['label' => 'Peringkat Teratas', 'value' => $comparisonLevel === 'desa' ? ($leader?->desa?->nama ?? '-') : ($leader?->kecamatan?->nama ?? '-')],
                ['label' => 'Nilai', 'value' => $this->formatNumber((float) ($leader?->nilai_total ?? 0))],
            ],
            'chart' => [
                'type' => 'bar',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Nilai total',
                        'data' => $values,
                        'backgroundColor' => [
                            '#0d6efd',
                            '#0dcaf0',
                            '#20c997',
                            '#ffc107',
                            '#fd7e14',
                            '#6f42c1',
                            '#198754',
                            '#dc3545',
                            '#6610f2',
                            '#6c757d',
                        ],
                        'borderRadius' => 8,
                        'maxBarThickness' => 22,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function compositionChartData(?User $user, string $workspaceKey, ?int $districtId = null, ?string $districtLabel = null): array
    {
        $isDistrictScoped = $districtId !== null && $workspaceKey !== 'kecamatan';
        $aggregationLevel = $workspaceKey === 'opd' ? 'opd' : 'kecamatan';
        $query = $this->ringkasanQuery($user)
            ->where('tingkat_rekap', $aggregationLevel)
            ->when($isDistrictScoped, fn (Builder $builder) => $builder->where('kecamatan_id', $districtId));
        $latestPeriodId = (clone $query)->max('periode_data_id');

        $rows = (clone $query)
            ->with(['indikatorData'])
            ->when($latestPeriodId, fn (Builder $builder) => $builder->where('periode_data_id', $latestPeriodId))
            ->orderByDesc('nilai_total')
            ->take(6)
            ->get();

        $labels = $rows->map(fn (RingkasanStatistik $row): string => Str::limit($row->indikatorData?->nama ?? 'Indikator', 18, '...'))->all();
        $values = $rows->map(fn (RingkasanStatistik $row): float => (float) $row->nilai_total)->all();
        $leader = $rows->first();

        return [
            'title' => $isDistrictScoped ? "Komposisi {$districtLabel}" : 'Komposisi Indikator',
            'description' => $isDistrictScoped
                ? "Donut chart menampilkan komposisi indikator terbaru dari {$districtLabel} agar sinkron dengan pilihan peta."
                : 'Donut chart menonjolkan proporsi indikator teratas pada periode terbaru agar selaras dengan pola grafik referensi.',
            'scope_label' => $districtLabel,
            'stats' => [
                ['label' => $isDistrictScoped ? 'Kecamatan' : 'Indikator Dominan', 'value' => $isDistrictScoped ? ($districtLabel ?? '-') : ($leader?->indikatorData?->nama ?? '-')],
                ['label' => 'Nilai Dominan', 'value' => $this->formatNumber((float) ($leader?->nilai_total ?? 0))],
                ['label' => 'Kategori Aktif', 'value' => count($labels).' indikator'],
            ],
            'chart' => [
                'type' => 'doughnut',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Komposisi',
                        'data' => $values,
                        'backgroundColor' => [
                            '#0d6efd',
                            '#20c997',
                            '#ffc107',
                            '#dc3545',
                            '#6f42c1',
                            '#0dcaf0',
                        ],
                        'borderColor' => '#ffffff',
                        'borderWidth' => 2,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<int, string>  $districtOptions
     * @return array<string, array<string, mixed>>
     */
    private function districtChartPayloads(?User $user, string $workspaceKey, array $districtOptions): array
    {
        if ($workspaceKey === 'kecamatan' || count($districtOptions) === 0) {
            return [];
        }

        $districts = Kecamatan::query()
            ->whereIn('nama', $districtOptions)
            ->orderBy('nama')
            ->get(['id', 'nama']);

        return $districts->mapWithKeys(function (Kecamatan $district) use ($user, $workspaceKey): array {
            $trend = $this->trendChartData($user, $workspaceKey, $district->id, $district->nama);

            return [
                $district->nama => [
                    'trend' => $trend,
                    'comparison' => $this->comparisonChartData($user, $workspaceKey, $trend['focus_indicator_id'] ?? null, $district->id, $district->nama),
                    'composition' => $this->compositionChartData($user, $workspaceKey, $district->id, $district->nama),
                ],
            ];
        })->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapData(?User $user, string $workspaceKey): array
    {
        $layers = LapisanPeta::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->with(['fiturPeta' => function ($query) use ($workspaceKey, $user): void {
                $query
                    ->where('aktif', true)
                    ->with(['kecamatan', 'desa', 'opd'])
                    ->when(
                        $workspaceKey === 'kecamatan' && $user?->kecamatan_id,
                        fn (Builder $builder) => $builder->where('kecamatan_id', $user->kecamatan_id)
                    )
                    ->when(
                        $workspaceKey === 'opd' && $user?->opd_id,
                        fn (Builder $builder) => $builder->where(function (Builder $scoped) use ($user): void {
                            $scoped
                                ->where('opd_id', $user->opd_id)
                                ->orWhereIn('lapisan_peta_id', LapisanPeta::query()
                                    ->whereIn('slug', ['batas-kecamatan', 'batas-desa'])
                                    ->pluck('id'));
                        })
                    );
            }])
            ->get();

        $serializedLayers = $layers->map(function (LapisanPeta $layer): array {
            $color = (string) data_get($layer->konfigurasi_json, 'color', '#0d6efd');
            $fillOpacity = (float) data_get($layer->konfigurasi_json, 'fillOpacity', 0.18);
            $weight = (float) data_get($layer->konfigurasi_json, 'weight', 1.2);

            return [
                'name' => $layer->nama,
                'slug' => $layer->slug,
                'category' => $layer->kategori,
                'color' => $color,
                'fill_opacity' => $fillOpacity,
                'weight' => $weight,
                'features' => $layer->fiturPeta
                    ->map(fn (FiturPeta $feature): array => $this->serializeFeature($feature, $layer, $color, $fillOpacity, $weight))
                    ->filter(fn (array $feature): bool => $feature['geometry'] !== null)
                    ->values()
                    ->all(),
            ];
        })->filter(fn (array $layer): bool => count($layer['features']) > 0)->values();

        $contentLayers = collect([
            $this->serializeContentLayer(
                slug: 'berita-publik',
                name: 'Berita/Kegiatan',
                color: '#dc2626',
                records: $this->newsQuery($user)
                    ->with(['kecamatan', 'opd'])
                    ->where('status', 'terbit')
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->get(),
            ),
            $this->serializeContentLayer(
                slug: 'kegiatan-publik',
                name: 'Kegiatan Lapangan',
                color: '#0f766e',
                records: $this->eventQuery($user)
                    ->with(['kecamatan', 'opd'])
                    ->where('status', 'terbit')
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->get(),
            ),
        ])->filter();

        $serializedLayers = $serializedLayers->merge($contentLayers)->values();

        $districtOptions = collect($serializedLayers)
            ->flatMap(fn (array $layer): array => $layer['features'])
            ->filter(fn (array $feature): bool => in_array((string) ($feature['boundary_type'] ?? ''), ['kecamatan', 'desa'], true))
            ->map(function (array $feature): string {
                if (($feature['boundary_type'] ?? null) === 'kecamatan') {
                    return (string) ($feature['name'] ?? '');
                }

                return (string) ($feature['district'] ?? '');
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $villageOptions = collect($serializedLayers)
            ->flatMap(fn (array $layer): array => $layer['features'])
            ->map(fn (array $feature): ?string => $feature['boundary_type'] === 'desa'
                ? (string) ($feature['name'] ?? '')
                : (filled($feature['village'] ?? null) ? (string) $feature['village'] : null))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $opdOptions = collect($serializedLayers)
            ->flatMap(fn (array $layer): array => $layer['features'])
            ->map(fn (array $feature): ?string => filled($feature['opd'] ?? null) ? (string) $feature['opd'] : null)
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $categoryOptions = collect($serializedLayers)
            ->map(fn (array $layer): ?string => filled($layer['category'] ?? null) ? (string) $layer['category'] : null)
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $polygonCount = $serializedLayers
            ->flatMap(fn (array $layer): array => $layer['features'])
            ->filter(fn (array $feature): bool => in_array(strtolower((string) data_get($feature, 'geometry.type')), ['polygon', 'multipolygon'], true))
            ->count();

        $pointCount = $serializedLayers
            ->flatMap(fn (array $layer): array => $layer['features'])
            ->filter(fn (array $feature): bool => strtolower((string) data_get($feature, 'geometry.type')) === 'point')
            ->count();

        $kecamatanBoundaryFeatures = collect($serializedLayers)
            ->firstWhere('slug', 'batas-kecamatan')['features'] ?? [];
        $desaBoundaryFeatures = collect($serializedLayers)
            ->firstWhere('slug', 'batas-desa')['features'] ?? [];
        $kecamatanCount = count($kecamatanBoundaryFeatures) ?: ($workspaceKey === 'kecamatan' && $user?->kecamatan_id
            ? 1
            : Kecamatan::query()->where('aktif', true)->count());
        $desaCount = count($desaBoundaryFeatures) ?: ($workspaceKey === 'kecamatan' && $user?->kecamatan_id
            ? Desa::query()->where('aktif', true)->where('kecamatan_id', $user->kecamatan_id)->count()
            : Desa::query()->where('aktif', true)->count());

        return [
            'title' => 'Peta Digital Wilayah',
            'description' => 'Panel peta mengikuti komposisi kartu besar AdminLTE dan otomatis memvisualisasikan layer aktif dari modul peta.',
            'status_label' => $polygonCount > 0
                ? "Boundary aktif: {$polygonCount} fitur"
                : 'Boundary polygon belum diunggah',
            'note' => $polygonCount > 0
                ? 'Layer polygon aktif akan tampil sebagai batas kecamatan atau desa sesuai data GeoJSON yang tersedia.'
                : 'Saat ini database hanya berisi titik fasilitas. Setelah layer GeoJSON batas kecamatan/desa diunggah ke modul peta, dashboard ini akan menampilkannya otomatis.',
            'coverage' => [
                ['label' => 'Kecamatan', 'value' => (string) $kecamatanCount],
                ['label' => 'Desa', 'value' => (string) $desaCount],
                ['label' => 'Fitur Titik', 'value' => (string) $pointCount],
                ['label' => 'Boundary', 'value' => (string) $polygonCount],
            ],
            'areas' => $this->mapAreaList($workspaceKey, $user, $serializedLayers->all()),
            'district_options' => $districtOptions,
            'village_options' => $villageOptions,
            'opd_options' => $opdOptions,
            'category_options' => $categoryOptions,
            'layers' => $serializedLayers->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeFeature(FiturPeta $feature, LapisanPeta $layer, string $color, float $fillOpacity, float $weight): array
    {
        $geometry = $feature->geojson ? json_decode($feature->geojson, true) : null;
        $properties = $feature->properti_json ?? [];

        if (! is_array($geometry) && $feature->latitude !== null && $feature->longitude !== null) {
            $geometry = [
                'type' => 'Point',
                'coordinates' => [(float) $feature->longitude, (float) $feature->latitude],
            ];
        }

        return [
            'name' => $feature->nama,
            'layer' => $layer->nama,
            'layer_slug' => $layer->slug,
            'color' => $color,
            'fill_opacity' => $fillOpacity,
            'weight' => $weight,
            'scope' => $feature->opd?->nama ?? $feature->kecamatan?->nama ?? 'Kabupaten Kepulauan Sangihe',
            'boundary_type' => data_get($properties, 'boundary_type'),
            'district' => data_get($properties, 'district') ?: $feature->kecamatan?->nama,
            'village' => data_get($properties, 'village') ?: $feature->desa?->nama,
            'opd' => $feature->opd?->nama,
            'category' => $layer->kategori,
            'properties' => $properties,
            'geometry' => $geometry,
        ];
    }

    /**
     * @param  Collection<int, Berita|Kegiatan>  $records
     * @return array<string, mixed>|null
     */
    private function serializeContentLayer(string $slug, string $name, string $color, Collection $records): ?array
    {
        $features = $records
            ->map(function (Berita|Kegiatan $record) use ($name, $slug, $color): array {
                $summary = $record instanceof Berita
                    ? $record->ringkasan
                    : ($record->uraian ?? $record->ringkasan);

                return [
                    'name' => $record->judul,
                    'layer' => $name,
                    'layer_slug' => $slug,
                    'color' => $color,
                    'fill_opacity' => 0.18,
                    'weight' => 1.2,
                    'scope' => $record->kecamatan?->nama ?? $record->opd?->nama ?? 'Kabupaten Kepulauan Sangihe',
                    'boundary_type' => null,
                    'district' => $record->kecamatan?->nama,
                    'properties' => [
                        'summary' => $summary,
                        'location_label' => $record->lokasi,
                        'opd' => $record->opd?->nama,
                        'published_at' => optional($record->tanggal_terbit)?->toIso8601String(),
                        'jenis_konten' => $record->jenis_konten,
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            (float) $record->longitude,
                            (float) $record->latitude,
                        ],
                    ],
                ];
            })
            ->filter(fn (array $feature): bool => isset($feature['geometry']['coordinates'][0], $feature['geometry']['coordinates'][1]))
            ->values()
            ->all();

        if (! count($features)) {
            return null;
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'category' => 'konten',
            'color' => $color,
            'fill_opacity' => 0.18,
            'weight' => 1.2,
            'features' => $features,
        ];
    }

    /**
     * @return array<int, string>
     */
    /**
     * @param  array<int, array<string, mixed>>  $layers
     * @return array<int, string>
     */
    private function mapAreaList(string $workspaceKey, ?User $user, array $layers): array
    {
        $boundaryLayer = collect($layers)->firstWhere('slug', $workspaceKey === 'kecamatan' ? 'batas-desa' : 'batas-kecamatan');

        if (is_array($boundaryLayer) && is_array($boundaryLayer['features'] ?? null) && count($boundaryLayer['features'])) {
            return collect($boundaryLayer['features'])
                ->map(fn (array $feature): string => (string) ($feature['name'] ?? '-'))
                ->filter()
                ->take($workspaceKey === 'kecamatan' ? 8 : 10)
                ->values()
                ->all();
        }

        if ($workspaceKey === 'kecamatan' && $user?->kecamatan_id) {
            return Desa::query()
                ->where('aktif', true)
                ->where('kecamatan_id', $user->kecamatan_id)
                ->orderBy('nama')
                ->pluck('nama')
                ->take(8)
                ->all();
        }

        return Kecamatan::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->pluck('nama')
            ->take(10)
            ->all();
    }

    private function submissionQuery(?User $user): Builder
    {
        return $user ? AdminScope::pengajuanDataQuery($user) : PengajuanData::query();
    }

    private function newsQuery(?User $user): Builder
    {
        return $user ? AdminScope::beritaQuery($user) : Berita::query();
    }

    private function eventQuery(?User $user): Builder
    {
        return $user ? AdminScope::kegiatanQuery($user) : Kegiatan::query();
    }

    private function ringkasanQuery(?User $user): Builder
    {
        return $user ? AdminScope::ringkasanStatistikQuery($user) : RingkasanStatistik::query();
    }

    private function formatNumber(float $value): string
    {
        return number_format($value, fmod($value, 1.0) === 0.0 ? 0 : 2, ',', '.');
    }
}
