<?php

namespace App\Services;

use App\Models\RingkasanStatistik;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatisticsService
{
    public function ringkasan(array $filters = [], bool $publik = false): array
    {
        $rows = $this->rows($filters, $publik);

        return [
            'totals' => $this->totals($rows),
            'rows' => $rows->values(),
            'rankingWilayah' => $this->rankingWilayah($rows),
            'charts' => $this->charts($rows),
        ];
    }

    public function adminStatistics(array $filters = []): array
    {
        return $this->ringkasan($filters, false);
    }

    public function publicStatistics(array $filters = []): array
    {
        return $this->ringkasan($filters, true);
    }

    public function dashboardSummary(array $filters = []): array
    {
        $rows = $this->rows($filters, true);

        return [
            'totals' => $this->totals($rows),
            'rankingWilayah' => $this->rankingWilayah($rows),
            'charts' => $this->charts($rows),
        ];
    }

    public function publishedSnapshots(int $indikatorId): Collection
    {
        if (! Schema::hasTable('ringkasan_statistik')) {
            return collect();
        }

        $query = RingkasanStatistik::query()
            ->with('periodeData')
            ->where('indikator_data_id', $indikatorId);

        if (Schema::hasColumn('ringkasan_statistik', 'status_publikasi')) {
            $query->where('status_publikasi', 'publik');
        }

        if (Schema::hasColumn('ringkasan_statistik', 'periode_data_id')) {
            $query->orderBy('periode_data_id');
        }

        return $query->get();
    }

    public function rows(array $filters = [], bool $publik = false): Collection
    {
        if (! Schema::hasTable('ringkasan_statistik')) {
            return collect();
        }

        $query = DB::table('ringkasan_statistik as rs')
            ->leftJoin('indikator_data as i', 'i.id', '=', 'rs.indikator_data_id');

        if (Schema::hasTable('periode_data') && Schema::hasColumn('ringkasan_statistik', 'periode_data_id')) {
            $query->leftJoin('periode_data as p', 'p.id', '=', 'rs.periode_data_id');
        }

        if (Schema::hasTable('kecamatan') && Schema::hasColumn('ringkasan_statistik', 'kecamatan_id')) {
            $query->leftJoin('kecamatan as k', 'k.id', '=', 'rs.kecamatan_id');
        }

        if (Schema::hasTable('desa') && Schema::hasColumn('ringkasan_statistik', 'desa_id')) {
            $query->leftJoin('desa as d', 'd.id', '=', 'rs.desa_id');
        }

        if (
            Schema::hasTable('opd')
            && Schema::hasColumn('indikator_data', 'opd_pembina_id')
        ) {
            $query->leftJoin('opd as o', 'o.id', '=', 'i.opd_pembina_id');
        }

        if ($publik && Schema::hasColumn('ringkasan_statistik', 'status_publikasi')) {
            $query->where('rs.status_publikasi', 'publik');
        }

        if ($publik && Schema::hasColumn('indikator_data', 'boleh_publikasi')) {
            $query->where(function ($q) {
                $q->whereNull('i.boleh_publikasi')
                    ->orWhere('i.boleh_publikasi', true);
            });
        }

        if (! empty($filters['periode_data_id']) && Schema::hasColumn('ringkasan_statistik', 'periode_data_id')) {
            $query->where('rs.periode_data_id', $filters['periode_data_id']);
        }

        if (! empty($filters['tahun']) && Schema::hasTable('periode_data') && Schema::hasColumn('periode_data', 'tahun')) {
            $query->where('p.tahun', $filters['tahun']);
        }

        if (! empty($filters['bulan']) && Schema::hasTable('periode_data') && Schema::hasColumn('periode_data', 'bulan')) {
            $query->where('p.bulan', $filters['bulan']);
        }

        if (! empty($filters['indikator_id']) && Schema::hasColumn('ringkasan_statistik', 'indikator_data_id')) {
            $query->where('rs.indikator_data_id', $filters['indikator_id']);
        }

        if (! empty($filters['kategori']) && Schema::hasColumn('indikator_data', 'kategori')) {
            $query->where('i.kategori', $filters['kategori']);
        }

        if (! empty($filters['kecamatan_id']) && Schema::hasColumn('ringkasan_statistik', 'kecamatan_id')) {
            $query->where('rs.kecamatan_id', $filters['kecamatan_id']);
        }

        if (! empty($filters['desa_id']) && Schema::hasColumn('ringkasan_statistik', 'desa_id')) {
            $query->where('rs.desa_id', $filters['desa_id']);
        }

        if (! empty($filters['tingkat_rekap']) && Schema::hasColumn('ringkasan_statistik', 'tingkat_rekap')) {
            $query->where('rs.tingkat_rekap', $filters['tingkat_rekap']);
        }

        $select = $this->buildSelectColumns();

        return $query
            ->select($select)
            ->when(Schema::hasColumn('ringkasan_statistik', 'tingkat_rekap'), function ($query) {
                $query->orderBy('rs.tingkat_rekap');
            })
            ->orderBy('indikator')
            ->get()
            ->map(fn ($row) => (array) $row);
    }

    protected function buildSelectColumns(): array
    {
        $select = [];

        if (Schema::hasColumn('ringkasan_statistik', 'id')) {
            $select[] = 'rs.id';
        } else {
            $select[] = DB::raw('NULL as id');
        }

        if (Schema::hasColumn('ringkasan_statistik', 'periode_data_id')) {
            $select[] = 'rs.periode_data_id';
        } else {
            $select[] = DB::raw('NULL as periode_data_id');
        }

        if (Schema::hasColumn('ringkasan_statistik', 'indikator_data_id')) {
            $select[] = 'rs.indikator_data_id';
        } else {
            $select[] = DB::raw('NULL as indikator_data_id');
        }

        if (Schema::hasColumn('ringkasan_statistik', 'tingkat_rekap')) {
            $select[] = 'rs.tingkat_rekap';
        } else {
            $select[] = DB::raw("'kabupaten' as tingkat_rekap");
        }

        if (Schema::hasColumn('ringkasan_statistik', 'kecamatan_id')) {
            $select[] = 'rs.kecamatan_id';
        } else {
            $select[] = DB::raw('NULL as kecamatan_id');
        }

        if (Schema::hasColumn('ringkasan_statistik', 'desa_id')) {
            $select[] = 'rs.desa_id';
        } else {
            $select[] = DB::raw('NULL as desa_id');
        }

        $select[] = DB::raw($this->nilaiExpression() . ' as nilai');

        if (Schema::hasColumn('ringkasan_statistik', 'created_at')) {
            $select[] = 'rs.created_at';
        } else {
            $select[] = DB::raw('NULL as created_at');
        }

        if (Schema::hasColumn('ringkasan_statistik', 'updated_at')) {
            $select[] = 'rs.updated_at';
        } else {
            $select[] = DB::raw('NULL as updated_at');
        }

        if (Schema::hasColumn('indikator_data', 'nama')) {
            $select[] = DB::raw("COALESCE(i.nama, '-') as indikator");
        } else {
            $select[] = DB::raw("'-' as indikator");
        }

        if (Schema::hasColumn('indikator_data', 'satuan') && Schema::hasColumn('ringkasan_statistik', 'satuan')) {
            $select[] = DB::raw("COALESCE(i.satuan, rs.satuan, '') as satuan");
        } elseif (Schema::hasColumn('indikator_data', 'satuan')) {
            $select[] = DB::raw("COALESCE(i.satuan, '') as satuan");
        } elseif (Schema::hasColumn('ringkasan_statistik', 'satuan')) {
            $select[] = DB::raw("COALESCE(rs.satuan, '') as satuan");
        } else {
            $select[] = DB::raw("'' as satuan");
        }

        if (Schema::hasTable('kecamatan') && Schema::hasColumn('ringkasan_statistik', 'kecamatan_id')) {
            $select[] = DB::raw("COALESCE(k.nama, '-') as kecamatan");
        } else {
            $select[] = DB::raw("'-' as kecamatan");
        }

        if (Schema::hasTable('desa') && Schema::hasColumn('ringkasan_statistik', 'desa_id')) {
            $select[] = DB::raw("COALESCE(d.nama, '-') as desa");
        } else {
            $select[] = DB::raw("'-' as desa");
        }

        if (
            Schema::hasTable('periode_data')
            && Schema::hasColumn('periode_data', 'tahun')
            && Schema::hasColumn('periode_data', 'bulan')
            && Schema::hasColumn('ringkasan_statistik', 'periode_data_id')
        ) {
            $periodExpression = DB::connection()->getDriverName() === 'sqlite'
                ? "COALESCE(p.bulan, '') || '/' || COALESCE(p.tahun, '')"
                : "CONCAT(COALESCE(p.bulan, ''), '/', COALESCE(p.tahun, ''))";

            $select[] = DB::raw("{$periodExpression} as periode");
        } elseif (
            Schema::hasTable('periode_data')
            && Schema::hasColumn('periode_data', 'nama')
            && Schema::hasColumn('ringkasan_statistik', 'periode_data_id')
        ) {
            $select[] = DB::raw("COALESCE(p.nama, '-') as periode");
        } elseif (Schema::hasColumn('ringkasan_statistik', 'periode_data_id')) {
            $select[] = DB::raw("CAST(rs.periode_data_id AS CHAR) as periode");
        } else {
            $select[] = DB::raw("'-' as periode");
        }

        if (Schema::hasColumn('indikator_data', 'kategori')) {
            $select[] = DB::raw("COALESCE(i.kategori, '-') as kelompok");
        } elseif (Schema::hasColumn('indikator_data', 'kelompok')) {
            $select[] = DB::raw("COALESCE(i.kelompok, '-') as kelompok");
        } else {
            $select[] = DB::raw("'-' as kelompok");
        }

        if (Schema::hasColumn('indikator_data', 'metode_agregasi')) {
            $select[] = DB::raw("COALESCE(i.metode_agregasi, 'sum') as metode_agregasi");
        } else {
            $select[] = DB::raw("'sum' as metode_agregasi");
        }

        if (
            Schema::hasTable('opd')
            && Schema::hasColumn('indikator_data', 'opd_pembina_id')
        ) {
            $select[] = DB::raw("COALESCE(o.nama, '-') as opd");
        } else {
            $select[] = DB::raw("'-' as opd");
        }

        foreach ([
            'jumlah_sumber_masuk',
            'jumlah_sumber_wajib',
            'persentase_kelengkapan',
            'status_rekap',
            'status_publikasi',
        ] as $column) {
            if (Schema::hasColumn('ringkasan_statistik', $column)) {
                $select[] = "rs.{$column}";
            } else {
                $select[] = DB::raw($this->defaultSelectForMissingColumn($column));
            }
        }

        return $select;
    }

    protected function nilaiExpression(): string
    {
        $candidates = [
            'nilai',
            'nilai_total',
            'total_nilai',
            'jumlah_nilai',
            'nilai_persen',
            'jumlah',
            'total',
            'value',
        ];

        foreach ($candidates as $column) {
            if (Schema::hasColumn('ringkasan_statistik', $column)) {
                return "COALESCE(rs.{$column}, 0)";
            }
        }

        return '0';
    }

    protected function defaultSelectForMissingColumn(string $column): string
    {
        return match ($column) {
            'jumlah_sumber_masuk' => '0 as jumlah_sumber_masuk',
            'jumlah_sumber_wajib' => '0 as jumlah_sumber_wajib',
            'persentase_kelengkapan' => '0 as persentase_kelengkapan',
            'status_rekap' => "'sementara' as status_rekap",
            'status_publikasi' => "'internal' as status_publikasi",
            default => "NULL as {$column}",
        };
    }

    public function totals(Collection $rows): array
    {
        return [
            'nilai' => round((float) $rows->sum(fn ($row) => (float) data_get($row, 'nilai', 0)), 4),
            'indikator' => $rows->pluck('indikator_data_id')->filter()->unique()->count(),
            'opd' => $rows->pluck('opd')->filter(fn ($value) => $value && $value !== '-')->unique()->count(),
            'desa' => $rows->pluck('desa_id')->filter()->unique()->count(),
            'baris' => $rows->count(),
        ];
    }

    public function rankingWilayah(Collection $rows): Collection
    {
        return $rows
            ->filter(fn ($row) => data_get($row, 'tingkat_rekap') !== 'kabupaten')
            ->groupBy(function ($row) {
                $desa = data_get($row, 'desa');
                $kecamatan = data_get($row, 'kecamatan');

                if ($desa && $desa !== '-') {
                    return $desa;
                }

                if ($kecamatan && $kecamatan !== '-') {
                    return $kecamatan;
                }

                return 'Tidak diketahui';
            })
            ->map(fn ($items, $nama) => [
                'nama' => $nama,
                'nilai' => round((float) collect($items)->sum(fn ($row) => (float) data_get($row, 'nilai', 0)), 4),
            ])
            ->sortByDesc('nilai')
            ->values()
            ->take(10);
    }

    public function charts(Collection $rows): array
    {
        return [
            'indikator' => $this->chartGroup($rows, 'indikator', 10),
            'opd' => $this->chartGroup($rows, 'opd', 8),
            'desa' => $this->chartGroup($rows, 'desa', 10),
            'periode' => $this->chartGroup($rows, 'periode', 12),
        ];
    }

    protected function chartGroup(Collection $rows, string $key, int $limit): array
    {
        $grouped = $rows
            ->groupBy(fn ($row) => data_get($row, $key, 'Tidak diketahui') ?: 'Tidak diketahui')
            ->map(fn ($items) => round((float) collect($items)->sum(fn ($row) => (float) data_get($row, 'nilai', 0)), 4))
            ->sortDesc()
            ->take($limit);

        return [
            'labels' => $grouped->keys()->values()->all(),
            'values' => $grouped->values()->values()->all(),
        ];
    }
}
