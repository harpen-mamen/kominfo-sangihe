<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\IndikatorData;
use App\Models\Kecamatan;
use App\Models\Opd;
use App\Models\PeriodeData;
use App\Models\RingkasanStatistik;
use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatisticsController extends Controller
{
    public function __construct(
        private StatisticsService $statisticsService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $snapshots = $this->statistikQuery($request)
            ->orderBy('periode_data_id')
            ->orderBy('tingkat_rekap')
            ->get();

        return response()->json([
            'summary' => $this->statisticsService->dashboardSummary(),
            'filters' => $this->filtersPayload(),
            'summary_cards' => $this->summaryCards($snapshots),
            'trend' => $this->trendPayload($snapshots),
            'comparison' => $this->comparisonPayload($snapshots),
            'table' => $snapshots->map(fn (RingkasanStatistik $snapshot): array => $this->serializeSnapshot($snapshot))->values(),
            'data' => $snapshots
                ->groupBy('indikatorData.nama')
                ->map(fn ($items) => $items->map(fn ($snapshot) => [
                    'period_year' => $snapshot->periodeData?->tahun,
                    'period_month' => $snapshot->periodeData?->bulan,
                    'value' => (float) $snapshot->nilai_total,
                    'area_id' => $snapshot->kecamatan_id,
                ])->values()),
        ]);
    }

    public function wilayah(Request $request): JsonResponse
    {
        $snapshots = $this->statistikQuery($request)
            ->when($request->integer('desa_id'), fn ($query, int $id) => $query->where('desa_id', $id))
            ->when($request->integer('kecamatan_id') && ! $request->integer('desa_id'), fn ($query, int $id) => $query->where('kecamatan_id', $id))
            ->orderBy('indikator_data_id')
            ->get();

        $wilayah = $request->integer('desa_id')
            ? Desa::query()->with('kecamatan')->find($request->integer('desa_id'))
            : Kecamatan::query()->find($request->integer('kecamatan_id'));

        return response()->json([
            'data' => [
                'wilayah' => $wilayah ? [
                    'id' => $wilayah->id,
                    'nama' => $wilayah->nama,
                    'jenis' => $wilayah instanceof Desa ? 'desa' : 'kecamatan',
                    'kecamatan' => $wilayah instanceof Desa ? $wilayah->kecamatan?->nama : null,
                ] : [
                    'id' => null,
                    'nama' => 'Kabupaten Kepulauan Sangihe',
                    'jenis' => 'kabupaten',
                    'kecamatan' => null,
                ],
                'periode' => $snapshots->first()?->periodeData?->label,
                'indikator' => $snapshots->map(fn (RingkasanStatistik $snapshot): array => $this->serializeSnapshot($snapshot))->values(),
            ],
        ]);
    }

    public function openData(Request $request): JsonResponse
    {
        $rows = $this->statistikQuery($request)
            ->orderByDesc('periode_data_id')
            ->orderBy('tingkat_rekap')
            ->limit(1000)
            ->get()
            ->map(fn (RingkasanStatistik $snapshot): array => $this->serializeSnapshot($snapshot))
            ->values();

        return response()->json([
            'meta' => [
                'title' => 'Dataset Statistik Agregat Kabupaten Kepulauan Sangihe',
                'license' => 'Data publik pemerintah daerah',
                'download_csv_url' => url('/api/public/open-data.csv?' . http_build_query($request->query())),
            ],
            'filters' => $this->filtersPayload(),
            'data' => $rows,
        ]);
    }

    public function openDataCsv(Request $request): StreamedResponse
    {
        $rows = $this->statistikQuery($request)
            ->orderByDesc('periode_data_id')
            ->orderBy('tingkat_rekap')
            ->limit(5000)
            ->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'periode',
                'tahun',
                'bulan',
                'tingkat_rekap',
                'kecamatan',
                'desa',
                'opd',
                'indikator',
                'satuan',
                'nilai_total',
                'nilai_persen',
            ]);

            $rows->each(function (RingkasanStatistik $row) use ($handle): void {
                fputcsv($handle, [
                    $row->periodeData?->label,
                    $row->periodeData?->tahun,
                    $row->periodeData?->bulan,
                    $row->tingkat_rekap,
                    $row->kecamatan?->nama,
                    $row->desa?->nama,
                    $row->opd?->nama,
                    $row->indikatorData?->nama,
                    $row->indikatorData?->satuan,
                    (float) $row->nilai_total,
                    $row->nilai_persen !== null ? (float) $row->nilai_persen : null,
                ]);
            });

            fclose($handle);
        }, 'open-data-sangihe.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show(IndikatorData $indicator): JsonResponse
    {
        abort_unless($indicator->aktif, 404);

        return response()->json([
            'indicator' => [
                'id' => $indicator->id,
                'name' => $indicator->nama,
                'slug' => $indicator->kode,
                'unit' => $indicator->satuan,
            ],
            'series' => $this->statisticsService->publishedSnapshots($indicator->id)
                ->map(fn ($snapshot) => [
                    'period_year' => $snapshot->periodeData?->tahun,
                    'period_month' => $snapshot->periodeData?->bulan,
                    'value' => (float) $snapshot->nilai_total,
                    'percentage' => $snapshot->nilai_persen ? (float) $snapshot->nilai_persen : null,
                    'rank_order' => null,
                ]),
        ]);
    }

    private function statistikQuery(Request $request)
    {
        return RingkasanStatistik::query()
            ->with(['indikatorData.opd', 'periodeData', 'kecamatan', 'desa', 'opd'])
            ->when($request->integer('periode_id'), fn ($query, int $id) => $query->where('periode_data_id', $id))
            ->when($request->integer('indikator_id'), fn ($query, int $id) => $query->where('indikator_data_id', $id))
            ->when($request->integer('opd_id'), fn ($query, int $id) => $query->where(function ($builder) use ($id): void {
                $builder
                    ->where('opd_id', $id)
                    ->orWhereHas('indikatorData', fn ($indicatorQuery) => $indicatorQuery->where('opd_id', $id));
            }))
            ->when($request->integer('kecamatan_id'), fn ($query, int $id) => $query->where('kecamatan_id', $id))
            ->when($request->integer('desa_id'), fn ($query, int $id) => $query->where('desa_id', $id));
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersPayload(): array
    {
        return [
            'periode' => PeriodeData::query()->orderByDesc('tahun')->orderByDesc('bulan')->get(['id', 'label', 'tahun', 'bulan']),
            'kecamatan' => Kecamatan::query()->where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
            'desa' => Desa::query()->where('aktif', true)->orderBy('nama')->get(['id', 'kecamatan_id', 'nama']),
            'opd' => Opd::query()->where('aktif', true)->orderBy('nama')->get(['id', 'kode', 'nama']),
            'indikator' => IndikatorData::query()->where('aktif', true)->orderBy('urutan')->orderBy('nama')->get(['id', 'opd_id', 'kode', 'nama', 'satuan', 'kelompok']),
        ];
    }

    private function summaryCards($snapshots)
    {
        $latestPeriod = $snapshots->max('periode_data_id');

        return $snapshots
            ->when($latestPeriod, fn ($items) => $items->where('periode_data_id', $latestPeriod))
            ->groupBy('indikator_data_id')
            ->map(function ($items): array {
                $first = $items->first();

                return [
                    'indicator' => $first?->indikatorData?->nama,
                    'unit' => $first?->indikatorData?->satuan,
                    'value' => (float) $items->sum('nilai_total'),
                    'period' => $first?->periodeData?->label,
                ];
            })
            ->take(6)
            ->values();
    }

    private function trendPayload($snapshots)
    {
        return $snapshots
            ->groupBy(fn (RingkasanStatistik $row): string => (string) $row->periode_data_id)
            ->map(function ($items): array {
                $first = $items->first();

                return [
                    'period' => $first?->periodeData?->label ?? 'Periode',
                    'value' => (float) $items->sum('nilai_total'),
                ];
            })
            ->values();
    }

    private function comparisonPayload($snapshots)
    {
        $latestPeriod = $snapshots->max('periode_data_id');

        return $snapshots
            ->when($latestPeriod, fn ($items) => $items->where('periode_data_id', $latestPeriod))
            ->groupBy(fn (RingkasanStatistik $row): string => $row->desa?->nama ?? $row->kecamatan?->nama ?? $row->opd?->nama ?? 'Kabupaten')
            ->map(fn ($items, string $area): array => [
                'area' => $area,
                'value' => (float) $items->sum('nilai_total'),
            ])
            ->sortByDesc('value')
            ->take(12)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeSnapshot(RingkasanStatistik $snapshot): array
    {
        return [
            'id' => $snapshot->id,
            'periode_id' => $snapshot->periode_data_id,
            'periode' => $snapshot->periodeData?->label,
            'tahun' => $snapshot->periodeData?->tahun,
            'bulan' => $snapshot->periodeData?->bulan,
            'tingkat_rekap' => $snapshot->tingkat_rekap,
            'kecamatan_id' => $snapshot->kecamatan_id,
            'kecamatan' => $snapshot->kecamatan?->nama,
            'desa_id' => $snapshot->desa_id,
            'desa' => $snapshot->desa?->nama,
            'opd_id' => $snapshot->opd_id ?? $snapshot->indikatorData?->opd_id,
            'opd' => $snapshot->opd?->nama ?? $snapshot->indikatorData?->opd?->nama,
            'indikator_id' => $snapshot->indikator_data_id,
            'indikator' => $snapshot->indikatorData?->nama,
            'indikator_kode' => $snapshot->indikatorData?->kode,
            'satuan' => $snapshot->indikatorData?->satuan,
            'nilai_total' => (float) $snapshot->nilai_total,
            'nilai_persen' => $snapshot->nilai_persen !== null ? (float) $snapshot->nilai_persen : null,
        ];
    }
}
