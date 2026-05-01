<?php

namespace App\Filament\Pages\StatistikDaerah;

use App\Models\Desa;
use App\Models\IndikatorData;
use App\Models\Kecamatan;
use App\Models\NilaiDataMentah;
use App\Models\Opd;
use App\Models\PeriodeData;
use App\Support\FilamentWorkspace;
use App\Support\ResourceOptions;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class StatistikDaerahPage extends Page
{
    use HasPageShield {
        canAccess as shieldCanAccess;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static string|\UnitEnum|null $navigationGroup = 'Statistik Daerah';

    protected string $view = 'filament.pages.statistik-daerah.dashboard';

    public ?int $tahun = null;

    public ?int $bulan = null;

    public string $modePeriode = 'bulanan';

    public ?int $periodeDataId = null;

    public ?int $opdId = null;

    public ?string $kelompok = null;

    public ?int $indikatorId = null;

    public ?int $kecamatanId = null;

    public ?int $desaId = null;

    public function mount(): void
    {
        $periode = PeriodeData::query()->orderByDesc('tahun')->orderByDesc('bulan')->first();

        $this->periodeDataId = $periode?->id;
        $this->tahun = $periode?->tahun ? (int) $periode->tahun : now()->year;
        $this->bulan = $periode?->bulan ? (int) $periode->bulan : now()->month;
    }

    public static function canAccess(): bool
    {
        return static::shieldCanAccess() && FilamentWorkspace::canAccessStatisticsSummary();
    }

    public function updatedKecamatanId(): void
    {
        $this->desaId = null;
    }

    public function updatedOpdId(): void
    {
        $this->indikatorId = null;
    }

    public function updatedKelompok(): void
    {
        $this->indikatorId = null;
    }

    public function getPageModeProperty(): string
    {
        return static::$navigationLabel ?? 'Statistik Daerah';
    }

    public function getPageKickerProperty(): string
    {
        return match (static::class) {
            \App\Filament\Pages\LaporanIndikatorDaerah::class => 'Dashboard laporan indikator daerah',
            DashboardStatistik::class => 'Pusat kendali statistik daerah',
            StatistikPerIndikator::class => 'Analisis detail indikator lintas wilayah',
            StatistikPerKecamatan::class => 'Profil data dan ranking kecamatan',
            StatistikPerDesa::class => 'Profil indikator tingkat desa',
            StatistikPerOpd::class => 'Kinerja data sektoral per OPD',
            PerbandinganWilayah::class => 'Komparasi kecamatan dan desa',
            TrenBulananTahunan::class => 'Pergerakan data dari waktu ke waktu',
            Infografis::class => 'Ringkasan visual siap presentasi',
            default => 'Overview statistik daerah',
        };
    }

    public function getTahunOptionsProperty(): array
    {
        return PeriodeData::query()->select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun', 'tahun')->all();
    }

    public function getBulanOptionsProperty(): array
    {
        return collect(range(1, 12))
            ->mapWithKeys(fn (int $month): array => [$month => now()->month($month)->translatedFormat('F')])
            ->all();
    }

    public function getPeriodeOptionsProperty(): array
    {
        return PeriodeData::query()
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->pluck('label', 'id')
            ->all();
    }

    public function getOpdOptionsProperty(): array
    {
        return Opd::query()->where('aktif', true)->orderBy('nama')->pluck('nama', 'id')->all();
    }

    public function getKelompokOptionsProperty(): array
    {
        return IndikatorData::query()
            ->whereNotNull('kelompok')
            ->select('kelompok')
            ->distinct()
            ->orderBy('kelompok')
            ->pluck('kelompok', 'kelompok')
            ->all() ?: ResourceOptions::kelompokIndikator();
    }

    public function getIndikatorOptionsProperty(): array
    {
        return IndikatorData::query()
            ->where('aktif', true)
            ->when($this->opdId, fn ($query) => $query->where('opd_id', $this->opdId))
            ->when($this->kelompok, fn ($query) => $query->where('kelompok', $this->kelompok))
            ->orderBy('urutan')
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->all();
    }

    public function getKecamatanOptionsProperty(): array
    {
        return Kecamatan::query()->where('aktif', true)->orderBy('nama')->pluck('nama', 'id')->all();
    }

    public function getDesaOptionsProperty(): array
    {
        return Desa::query()
            ->where('aktif', true)
            ->when($this->kecamatanId, fn ($query) => $query->where('kecamatan_id', $this->kecamatanId))
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->all();
    }

    public function getSelectedIndikatorProperty(): ?IndikatorData
    {
        return $this->indikatorId
            ? IndikatorData::query()->with('opd')->find($this->indikatorId)
            : null;
    }

    public function getSelectedKecamatanProperty(): ?Kecamatan
    {
        return $this->kecamatanId ? Kecamatan::query()->find($this->kecamatanId) : null;
    }

    public function getSelectedDesaProperty(): ?Desa
    {
        return $this->desaId ? Desa::query()->with('kecamatan')->find($this->desaId) : null;
    }

    public function getSelectedOpdProperty(): ?Opd
    {
        return $this->opdId ? Opd::query()->find($this->opdId) : null;
    }

    public function getPeriodLabelProperty(): string
    {
        if ($this->periodeDataId) {
            return PeriodeData::query()->whereKey($this->periodeDataId)->value('label') ?: 'Periode terpilih';
        }

        if ($this->modePeriode === 'tahunan') {
            return (string) ($this->tahun ?: 'Semua tahun');
        }

        $month = $this->bulan ? ($this->bulanOptions[$this->bulan] ?? $this->bulan) : 'Semua bulan';

        return trim("{$month} {$this->tahun}");
    }

    public function getHasDataProperty(): bool
    {
        return $this->scopedValues()->exists();
    }

    public function getSummaryCardsProperty(): array
    {
        return match (static::class) {
            \App\Filament\Pages\LaporanIndikatorDaerah::class => $this->reportCards(),
            StatistikPerIndikator::class => $this->indicatorCards(),
            StatistikPerKecamatan::class => $this->kecamatanCards(),
            StatistikPerDesa::class => $this->desaCards(),
            StatistikPerOpd::class => $this->opdCards(),
            TrenBulananTahunan::class => $this->trendCards(),
            default => $this->dashboardCards(),
        };
    }

    public function getPrimaryChartProperty(): array
    {
        return match (static::class) {
            \App\Filament\Pages\LaporanIndikatorDaerah::class => ['title' => 'Top 10 Kecamatan', 'description' => 'Akumulasi nilai terbesar berdasarkan kecamatan.', 'type' => 'bar', 'rows' => $this->groupedTotals('kecamatan_nama', 10)],
            DashboardStatistik::class => ['title' => 'Tren Data Masuk', 'description' => 'Jumlah baris nilai data mentah per periode.', 'type' => 'line', 'rows' => $this->dataCountTrendRows()],
            StatistikPerKecamatan::class => ['title' => 'Indikator Dalam Kecamatan', 'type' => 'bar', 'rows' => $this->groupedTotals('indikator_nama', 10)],
            StatistikPerDesa::class => ['title' => 'Indikator Desa', 'type' => 'bar', 'rows' => $this->groupedTotals('indikator_nama', 12)],
            StatistikPerOpd::class => ['title' => 'Indikator OPD', 'type' => 'bar', 'rows' => $this->groupedTotals('indikator_nama', 10)],
            PerbandinganWilayah::class => ['title' => 'Perbandingan Antar Wilayah', 'type' => 'bar', 'rows' => $this->groupedTotals($this->desaId ? 'desa_nama' : 'kecamatan_nama', 12)],
            TrenBulananTahunan::class => ['title' => 'Line Chart Tren', 'type' => 'line', 'rows' => $this->trendRows()],
            Infografis::class => ['title' => 'Highlight Kelompok Indikator', 'type' => 'donut', 'rows' => $this->groupedTotals('kelompok', 8)],
            default => ['title' => 'Top 10 Kecamatan', 'type' => 'bar', 'rows' => $this->groupedTotals('kecamatan_nama', 10)],
        };
    }

    public function getSecondaryChartProperty(): array
    {
        return match (static::class) {
            \App\Filament\Pages\LaporanIndikatorDaerah::class => ['title' => 'Distribusi OPD', 'description' => 'Komposisi nilai berdasarkan OPD pengampu indikator.', 'type' => 'doughnut', 'rows' => $this->groupedTotals('opd_nama', 8)],
            DashboardStatistik::class => ['title' => 'Distribusi Kelompok Indikator', 'description' => 'Komposisi nilai berdasarkan kelompok indikator.', 'type' => 'doughnut', 'rows' => $this->groupedTotals('kelompok', 8)],
            StatistikPerIndikator::class => ['title' => 'Top 10 Desa', 'type' => 'bar', 'rows' => $this->groupedTotals('desa_nama', 10)],
            StatistikPerKecamatan::class => ['title' => 'Nilai Per Desa', 'type' => 'bar', 'rows' => $this->groupedTotals('desa_nama', 10)],
            StatistikPerDesa::class => ['title' => 'Tren Bulanan Desa', 'type' => 'line', 'rows' => $this->trendRows()],
            StatistikPerOpd::class => ['title' => 'Ranking Wilayah OPD', 'type' => 'bar', 'rows' => $this->groupedTotals('kecamatan_nama', 10)],
            PerbandinganWilayah::class => ['title' => 'Tren Perbandingan', 'type' => 'line', 'rows' => $this->trendRows()],
            TrenBulananTahunan::class => ['title' => 'Perubahan Per Periode', 'type' => 'bar', 'rows' => $this->changeRows()],
            Infografis::class => ['title' => 'Ranking Kecamatan/Desa', 'type' => 'bar', 'rows' => $this->groupedTotals('kecamatan_nama', 10)],
            default => ['title' => 'Distribusi OPD/Kelompok', 'type' => 'donut', 'rows' => $this->groupedTotals($this->opdId ? 'kelompok' : 'opd_nama', 8)],
        };
    }

    public function getTertiaryChartProperty(): array
    {
        return match (static::class) {
            \App\Filament\Pages\LaporanIndikatorDaerah::class => ['title' => 'Tren per Bulan', 'description' => 'Pergerakan total nilai pada periode tersedia.', 'type' => 'line', 'rows' => $this->trendRows()],
            DashboardStatistik::class => ['title' => 'Top OPD', 'description' => 'OPD dengan akumulasi nilai tertinggi.', 'type' => 'bar', 'rows' => $this->groupedTotals('opd_nama', 10)],
            StatistikPerIndikator::class => ['title' => 'Tren Indikator', 'type' => 'line', 'rows' => $this->trendRows()],
            StatistikPerKecamatan::class => ['title' => 'Kelompok Indikator', 'type' => 'donut', 'rows' => $this->groupedTotals('kelompok', 8)],
            StatistikPerDesa::class => ['title' => 'Komposisi Kelompok Desa', 'type' => 'donut', 'rows' => $this->groupedTotals('kelompok', 8)],
            StatistikPerOpd::class => ['title' => 'Kelompok Data OPD', 'type' => 'donut', 'rows' => $this->groupedTotals('kelompok', 8)],
            default => ['title' => 'Tren Data Per Bulan', 'type' => 'line', 'rows' => $this->trendRows()],
        };
    }

    public function getQuaternaryChartProperty(): array
    {
        return match (static::class) {
            \App\Filament\Pages\LaporanIndikatorDaerah::class => ['title' => 'Top 10 Desa', 'description' => 'Desa dengan akumulasi nilai terbesar.', 'type' => 'bar', 'rows' => $this->groupedTotals('desa_nama', 10)],
            DashboardStatistik::class => ['title' => 'Top Kecamatan', 'description' => 'Kecamatan dengan akumulasi nilai tertinggi.', 'type' => 'bar', 'rows' => $this->groupedTotals('kecamatan_nama', 10)],
            StatistikPerIndikator::class => ['title' => 'Distribusi Kecamatan', 'description' => 'Komposisi nilai indikator per kecamatan.', 'type' => 'doughnut', 'rows' => $this->groupedTotals('kecamatan_nama', 8)],
            default => ['title' => 'Distribusi Sumber Data', 'description' => 'Komposisi berdasarkan sumber data.', 'type' => 'doughnut', 'rows' => $this->groupedTotals('sumber_data_nama', 8)],
        };
    }

    public function getDetailRowsProperty(): array
    {
        return $this->scopedValues()
            ->select([
                'periode_data.label as periode',
                'indikator_data.kelompok',
                'indikator_data.nama as indikator',
                'indikator_data.satuan',
                'opd.nama as opd',
                'kecamatan.nama as kecamatan',
                'desa.nama as desa',
                'sumber_data.nama as sumber_data',
                DB::raw('SUM(nilai_data_mentah.nilai) as nilai'),
                DB::raw('MAX(nilai_data_mentah.updated_at) as terakhir_update'),
            ])
            ->groupBy('periode_data.label', 'indikator_data.kelompok', 'indikator_data.nama', 'indikator_data.satuan', 'opd.nama', 'kecamatan.nama', 'desa.nama', 'sumber_data.nama')
            ->orderByDesc(DB::raw('MAX(nilai_data_mentah.updated_at)'))
            ->limit(30)
            ->get()
            ->map(fn ($row): array => [
                'periode' => $row->periode,
                'kelompok' => $row->kelompok ?: '-',
                'indikator' => $row->indikator,
                'opd' => $row->opd ?: '-',
                'kecamatan' => $row->kecamatan ?: '-',
                'desa' => $row->desa ?: '-',
                'sumber_data' => $row->sumber_data ?: '-',
                'nilai' => (float) $row->nilai,
                'satuan' => $row->satuan,
            ])
            ->all();
    }

    public function resetFilters(): void
    {
        $this->opdId = null;
        $this->kelompok = null;
        $this->indikatorId = null;
        $this->kecamatanId = null;
        $this->desaId = null;
        $this->periodeDataId = null;
        $this->modePeriode = 'bulanan';
        $this->tahun = now()->year;
        $this->bulan = now()->month;
    }

    public function getInsightProperty(): array
    {
        $rows = collect($this->primaryChart['rows']);
        $highest = $rows->sortByDesc('value')->first();
        $lowest = $rows->where('value', '>', 0)->sortBy('value')->first();
        $average = $rows->avg('value') ?: 0;

        return [
            'highest' => $highest ? "{$highest['label']} ({$this->formatNumber($highest['value'])})" : '-',
            'lowest' => $lowest ? "{$lowest['label']} ({$this->formatNumber($lowest['value'])})" : '-',
            'average' => $this->formatNumber($average),
        ];
    }

    public function formatNumber(float|int|null $value, int|string|null $decimals = 2, ?string $unit = null): string
    {
        if (is_string($decimals)) {
            $unit = $decimals;
            $decimals = 2;
        }

        $formatted = number_format((float) $value, (int) $decimals, ',', '.');

        return filled($unit) ? "{$formatted} {$unit}" : $formatted;
    }

    public function formatPercent(float|int|null $value, int $decimals = 1): string
    {
        return number_format((float) $value, $decimals, ',', '.') . '%';
    }

    public function formatShortNumber(float|int|null $value): string
    {
        $number = (float) $value;
        $absolute = abs($number);

        if ($absolute >= 1000000000) {
            return number_format($number / 1000000000, 1, ',', '.') . ' M';
        }

        if ($absolute >= 1000000) {
            return number_format($number / 1000000, 1, ',', '.') . ' jt';
        }

        if ($absolute >= 1000) {
            return number_format($number / 1000, 1, ',', '.') . ' rb';
        }

        return number_format($number, 0, ',', '.');
    }

    public function formatInteger(float|int|null $value): string
    {
        return number_format((float) $value, 0, ',', '.');
    }

    public function exportCsv(): StreamedResponse
    {
        $rows = $this->detailRows;
        $filename = Str::slug($this->pageMode) . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Periode', 'OPD', 'Kelompok', 'Indikator', 'Kecamatan', 'Desa', 'Sumber Data', 'Nilai', 'Satuan']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['periode'],
                    $row['opd'],
                    $row['kelompok'],
                    $row['indikator'],
                    $row['kecamatan'],
                    $row['desa'],
                    $row['sumber_data'],
                    number_format((float) $row['nilai'], 2, ',', '.'),
                    $row['satuan'],
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function scopedValues(): Builder
    {
        return DB::table('nilai_data_mentah')
            ->join('pengajuan_data', 'pengajuan_data.id', '=', 'nilai_data_mentah.pengajuan_data_id')
            ->join('periode_data', 'periode_data.id', '=', 'pengajuan_data.periode_data_id')
            ->join('indikator_data', 'indikator_data.id', '=', 'nilai_data_mentah.indikator_data_id')
            ->leftJoin('opd', 'opd.id', '=', 'indikator_data.opd_id')
            ->leftJoin('desa', 'desa.id', '=', 'nilai_data_mentah.desa_id')
            ->leftJoin('kecamatan', 'kecamatan.id', '=', 'desa.kecamatan_id')
            ->leftJoin('sumber_data', 'sumber_data.id', '=', 'nilai_data_mentah.sumber_data_id')
            ->when($this->periodeDataId, fn ($query) => $query->where('pengajuan_data.periode_data_id', $this->periodeDataId))
            ->when(! $this->periodeDataId && $this->tahun, fn ($query) => $query->where('periode_data.tahun', $this->tahun))
            ->when(! $this->periodeDataId && $this->modePeriode === 'bulanan' && $this->bulan, fn ($query) => $query->where('periode_data.bulan', $this->bulan))
            ->when($this->opdId, fn ($query) => $query->where('indikator_data.opd_id', $this->opdId))
            ->when($this->kelompok, fn ($query) => $query->where('indikator_data.kelompok', $this->kelompok))
            ->when($this->indikatorId, fn ($query) => $query->where('nilai_data_mentah.indikator_data_id', $this->indikatorId))
            ->when($this->kecamatanId, fn ($query) => $query->where('desa.kecamatan_id', $this->kecamatanId))
            ->when($this->desaId, fn ($query) => $query->where('nilai_data_mentah.desa_id', $this->desaId));
    }

    private function dashboardCards(): array
    {
        $stats = $this->scopedValues()
            ->selectRaw('
                COALESCE(SUM(nilai_data_mentah.nilai), 0) as total_nilai,
                COUNT(*) as jumlah_data,
                COUNT(DISTINCT nilai_data_mentah.indikator_data_id) as indikator_terisi,
                COUNT(DISTINCT desa.kecamatan_id) as kecamatan_terisi,
                COUNT(DISTINCT nilai_data_mentah.desa_id) as desa_terisi,
                COUNT(DISTINCT indikator_data.opd_id) as opd_penginput,
                COUNT(DISTINCT nilai_data_mentah.sumber_data_id) as sumber_data
            ')
            ->first();

        return [
            ['label' => 'Total Data Masuk', 'value' => $this->formatInteger($stats->jumlah_data ?? 0), 'caption' => 'Baris nilai data mentah', 'tone' => 'navy', 'icon' => 'heroicon-o-inbox-stack'],
            ['label' => 'Indikator Aktif', 'value' => $this->formatInteger(IndikatorData::query()->where('aktif', true)->count()), 'caption' => "{$this->formatInteger($stats->indikator_terisi ?? 0)} terisi", 'tone' => 'blue', 'icon' => 'heroicon-o-chart-bar'],
            ['label' => 'OPD Terlibat', 'value' => $this->formatInteger($stats->opd_penginput ?? 0), 'caption' => 'OPD pada data terpilih', 'tone' => 'teal', 'icon' => 'heroicon-o-building-office-2'],
            ['label' => 'Kecamatan', 'value' => $this->formatInteger($stats->kecamatan_terisi ?? 0), 'caption' => 'Kecamatan memiliki data', 'tone' => 'green', 'icon' => 'heroicon-o-map'],
            ['label' => 'Desa', 'value' => $this->formatInteger($stats->desa_terisi ?? 0), 'caption' => 'Desa memiliki data', 'tone' => 'blue', 'icon' => 'heroicon-o-home-modern'],
            ['label' => 'Sumber Data', 'value' => $this->formatInteger($stats->sumber_data ?? 0), 'caption' => 'Sumber data tercatat', 'tone' => 'teal', 'icon' => 'heroicon-o-circle-stack'],
        ];
    }

    private function reportCards(): array
    {
        $stats = $this->scopedValues()
            ->selectRaw('
                COALESCE(SUM(nilai_data_mentah.nilai), 0) as total_nilai,
                COUNT(*) as data_masuk,
                COUNT(DISTINCT nilai_data_mentah.indikator_data_id) as jumlah_indikator,
                COUNT(DISTINCT indikator_data.opd_id) as jumlah_opd,
                COUNT(DISTINCT desa.kecamatan_id) as jumlah_kecamatan,
                COUNT(DISTINCT nilai_data_mentah.desa_id) as jumlah_desa
            ')
            ->first();

        return [
            ['label' => 'Total Nilai', 'value' => $this->formatNumber($stats->total_nilai ?? 0), 'caption' => 'Akumulasi seluruh nilai', 'tone' => 'navy', 'icon' => 'heroicon-o-calculator'],
            ['label' => 'Jumlah Indikator', 'value' => $this->formatInteger($stats->jumlah_indikator ?? 0), 'caption' => 'Indikator dalam laporan', 'tone' => 'blue', 'icon' => 'heroicon-o-chart-bar'],
            ['label' => 'Jumlah OPD', 'value' => $this->formatInteger($stats->jumlah_opd ?? 0), 'caption' => 'OPD pengampu data', 'tone' => 'teal', 'icon' => 'heroicon-o-building-office-2'],
            ['label' => 'Jumlah Kecamatan', 'value' => $this->formatInteger($stats->jumlah_kecamatan ?? 0), 'caption' => 'Kecamatan terisi', 'tone' => 'green', 'icon' => 'heroicon-o-map'],
            ['label' => 'Jumlah Desa Terisi', 'value' => $this->formatInteger($stats->jumlah_desa ?? 0), 'caption' => 'Desa memiliki nilai', 'tone' => 'blue', 'icon' => 'heroicon-o-home-modern'],
            ['label' => 'Data Masuk', 'value' => $this->formatInteger($stats->data_masuk ?? 0), 'caption' => 'Baris nilai data mentah', 'tone' => 'teal', 'icon' => 'heroicon-o-inbox-stack'],
        ];
    }

    private function indicatorCards(): array
    {
        $stats = $this->scopedValues()
            ->selectRaw('COALESCE(SUM(nilai_data_mentah.nilai), 0) total, AVG(nilai_data_mentah.nilai) rata, MAX(nilai_data_mentah.nilai) tertinggi, MIN(nilai_data_mentah.nilai) terendah, COUNT(DISTINCT nilai_data_mentah.desa_id) desa_terisi')
            ->first();
        $unit = $this->selectedIndikator?->satuan;

        return [
            ['label' => 'Total Nilai', 'value' => $this->formatNumber($stats->total ?? 0, $unit), 'caption' => $this->selectedIndikator?->nama ?? 'Semua indikator', 'tone' => 'navy'],
            ['label' => 'Rata-rata per Desa', 'value' => $this->formatNumber($stats->rata ?? 0, $unit), 'caption' => 'Rerata nilai masuk', 'tone' => 'blue'],
            ['label' => 'Nilai Tertinggi', 'value' => $this->formatNumber($stats->tertinggi ?? 0, $unit), 'caption' => 'Puncak data wilayah', 'tone' => 'teal'],
            ['label' => 'Nilai Terendah', 'value' => $this->formatNumber($stats->terendah ?? 0, $unit), 'caption' => 'Nilai minimum terisi', 'tone' => 'green'],
            ['label' => 'Desa Terisi', 'value' => $this->formatInteger($stats->desa_terisi ?? 0), 'caption' => 'Desa dengan nilai', 'tone' => 'blue'],
        ];
    }

    private function kecamatanCards(): array
    {
        $stats = $this->scopedValues()
            ->selectRaw('COUNT(DISTINCT nilai_data_mentah.indikator_data_id) indikator, COUNT(DISTINCT nilai_data_mentah.desa_id) desa, COALESCE(SUM(nilai_data_mentah.nilai), 0) total, COUNT(DISTINCT nilai_data_mentah.sumber_data_id) sumber')
            ->first();

        return [
            ['label' => 'Indikator Terisi', 'value' => $this->formatInteger($stats->indikator ?? 0), 'caption' => $this->selectedKecamatan?->nama ?? 'Semua kecamatan', 'tone' => 'navy'],
            ['label' => 'Total Desa', 'value' => $this->formatInteger($stats->desa ?? 0), 'caption' => 'Desa dengan data', 'tone' => 'blue'],
            ['label' => 'Total Nilai', 'value' => $this->formatNumber($stats->total ?? 0), 'caption' => 'Indikator terpilih', 'tone' => 'teal'],
            ['label' => 'Sumber Data', 'value' => $this->formatInteger($stats->sumber ?? 0), 'caption' => 'Asal laporan tercatat', 'tone' => 'green'],
        ];
    }

    private function desaCards(): array
    {
        $stats = $this->scopedValues()
            ->selectRaw('COUNT(DISTINCT nilai_data_mentah.indikator_data_id) indikator, COALESCE(SUM(nilai_data_mentah.nilai), 0) total, MAX(nilai_data_mentah.updated_at) terakhir, COUNT(DISTINCT nilai_data_mentah.sumber_data_id) sumber')
            ->first();

        return [
            ['label' => 'Total Indikator', 'value' => $this->formatInteger($stats->indikator ?? 0), 'caption' => $this->selectedDesa?->nama ?? 'Semua desa', 'tone' => 'navy'],
            ['label' => 'Total Nilai Utama', 'value' => $this->formatNumber($stats->total ?? 0), 'caption' => 'Sesuai filter indikator', 'tone' => 'blue'],
            ['label' => 'Data Masuk Terakhir', 'value' => $stats?->terakhir ? date('d/m/Y', strtotime((string) $stats->terakhir)) : '-', 'caption' => 'Update nilai terbaru', 'tone' => 'teal'],
            ['label' => 'Sumber Data', 'value' => $this->formatInteger($stats->sumber ?? 0), 'caption' => 'Asal laporan', 'tone' => 'green'],
        ];
    }

    private function opdCards(): array
    {
        $stats = $this->scopedValues()
            ->selectRaw('COUNT(DISTINCT nilai_data_mentah.indikator_data_id) indikator, COUNT(*) data_masuk, COUNT(DISTINCT desa.kecamatan_id) kecamatan, COUNT(DISTINCT nilai_data_mentah.desa_id) desa')
            ->first();

        return [
            ['label' => 'Indikator OPD', 'value' => $this->formatInteger($stats->indikator ?? 0), 'caption' => $this->selectedOpd?->nama ?? 'Semua OPD', 'tone' => 'navy'],
            ['label' => 'Data Masuk', 'value' => $this->formatInteger($stats->data_masuk ?? 0), 'caption' => 'Baris nilai mentah', 'tone' => 'blue'],
            ['label' => 'Kecamatan Terisi', 'value' => $this->formatInteger($stats->kecamatan ?? 0), 'caption' => 'Sebaran wilayah', 'tone' => 'teal'],
            ['label' => 'Desa Terisi', 'value' => $this->formatInteger($stats->desa ?? 0), 'caption' => 'Cakupan desa', 'tone' => 'green'],
        ];
    }

    private function trendCards(): array
    {
        $trend = collect($this->trendRows());
        $first = (float) ($trend->first()['value'] ?? 0);
        $last = (float) ($trend->last()['value'] ?? 0);
        $change = $last - $first;
        $percent = $first != 0.0 ? ($change / $first) * 100 : 0;

        return [
            ['label' => 'Nilai Awal', 'value' => $this->formatNumber($first), 'caption' => $trend->first()['label'] ?? '-', 'tone' => 'navy'],
            ['label' => 'Nilai Akhir', 'value' => $this->formatNumber($last), 'caption' => $trend->last()['label'] ?? '-', 'tone' => 'blue'],
            ['label' => 'Perubahan', 'value' => $this->formatNumber($change), 'caption' => $change >= 0 ? 'Naik' : 'Turun', 'tone' => $change >= 0 ? 'green' : 'teal'],
            ['label' => 'Persentase', 'value' => number_format($percent, 2, ',', '.') . '%', 'caption' => 'Dari nilai awal', 'tone' => 'teal'],
        ];
    }

    private function groupedTotals(string $column, int $limit = 10): array
    {
        $expression = $this->groupColumnExpression($column);

        return $this->scopedValues()
            ->selectRaw("COALESCE({$expression}, '-') as label, COALESCE(SUM(nilai_data_mentah.nilai), 0) as value")
            ->groupBy(DB::raw("COALESCE({$expression}, '-')"))
            ->orderByDesc('value')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => ['label' => (string) $row->label, 'value' => (float) $row->value])
            ->all();
    }

    private function groupColumnExpression(string $column): string
    {
        return match ($column) {
            'indikator_nama' => 'indikator_data.nama',
            'desa_nama' => 'desa.nama',
            'kecamatan_nama' => 'kecamatan.nama',
            'opd_nama' => 'opd.nama',
            'kelompok' => 'indikator_data.kelompok',
            'sumber_data_nama' => 'sumber_data.nama',
            default => 'indikator_data.nama',
        };
    }

    private function trendRows(): array
    {
        return $this->scopedValues()
            ->selectRaw('periode_data.tahun, periode_data.bulan, COALESCE(SUM(nilai_data_mentah.nilai), 0) as value')
            ->groupBy('periode_data.tahun', 'periode_data.bulan')
            ->orderBy('periode_data.tahun')
            ->orderBy('periode_data.bulan')
            ->limit(24)
            ->get()
            ->map(fn ($row): array => [
                'label' => sprintf('%s-%02d', $row->tahun, $row->bulan),
                'value' => (float) $row->value,
            ])
            ->all();
    }

    private function dataCountTrendRows(): array
    {
        return $this->scopedValues()
            ->selectRaw('periode_data.tahun, periode_data.bulan, COUNT(*) as value')
            ->groupBy('periode_data.tahun', 'periode_data.bulan')
            ->orderBy('periode_data.tahun')
            ->orderBy('periode_data.bulan')
            ->limit(24)
            ->get()
            ->map(fn ($row): array => [
                'label' => sprintf('%s-%02d', $row->tahun, $row->bulan),
                'value' => (float) $row->value,
            ])
            ->all();
    }

    private function changeRows(): array
    {
        $previous = null;

        return collect($this->trendRows())
            ->map(function (array $row) use (&$previous): array {
                $change = is_null($previous) ? 0 : $row['value'] - $previous;
                $previous = $row['value'];

                return ['label' => $row['label'], 'value' => $change];
            })
            ->all();
    }
}
