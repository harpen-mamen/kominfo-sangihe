<?php

namespace App\Filament\Pages;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\NilaiDataMentah;
use App\Models\Opd;
use App\Models\PeriodeData;
use App\Support\FilamentWorkspace;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanKependudukanDukcapil extends Page
{
    use HasPageShield {
        canAccess as shieldCanAccess;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'Laporan Kependudukan Dukcapil';

    protected static string|\UnitEnum|null $navigationGroup = 'Statistik';

    protected static ?int $navigationSort = 70;

    protected string $view = 'filament.pages.laporan-kependudukan-dukcapil';

    public ?int $periodeDataId = null;

    public ?int $kecamatanId = null;

    public ?int $desaId = null;

    public function mount(): void
    {
        $this->periodeDataId = PeriodeData::query()
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->value('id');
    }

    public static function canAccess(): bool
    {
        return static::shieldCanAccess() && FilamentWorkspace::canAccessDukcapilReport();
    }

    public function updatedKecamatanId(): void
    {
        $this->desaId = null;
    }

    /**
     * @return array<int|string, string>
     */
    public function getPeriodeOptionsProperty(): array
    {
        return PeriodeData::query()
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->pluck('label', 'id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function getKecamatanOptionsProperty(): array
    {
        return Kecamatan::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function getDesaOptionsProperty(): array
    {
        return Desa::query()
            ->where('aktif', true)
            ->when($this->kecamatanId, fn ($query) => $query->where('kecamatan_id', $this->kecamatanId))
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRowsProperty(): array
    {
        $values = $this->dukcapilValues();

        if ($values->isEmpty()) {
            return [];
        }

        $grouped = $values
            ->groupBy('desa_id')
            ->map(fn (Collection $items): array => $items->pluck('total', 'kode')->all());

        return Desa::query()
            ->with('kecamatan')
            ->whereIn('id', $grouped->keys()->all())
            ->orderBy('nama')
            ->get()
            ->map(function (Desa $desa) use ($grouped): array {
                $metrics = $grouped[$desa->id] ?? [];
                $penduduk = (float) ($metrics['jumlah_penduduk'] ?? 0);
                $kelahiran = (float) ($metrics['jumlah_kelahiran'] ?? 0);
                $kematian = (float) ($metrics['jumlah_kematian'] ?? 0);

                return [
                    'kecamatan' => $desa->kecamatan?->nama ?? '-',
                    'desa' => $desa->nama,
                    'jumlah_penduduk' => $penduduk,
                    'jumlah_kelahiran' => $kelahiran,
                    'jumlah_kematian' => $kematian,
                    'rasio_kematian' => $penduduk > 0 ? ($kematian / $penduduk) * 1000 : null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, float|null>
     */
    public function getTotalsProperty(): array
    {
        $rows = collect($this->rows);
        $penduduk = (float) $rows->sum('jumlah_penduduk');
        $kematian = (float) $rows->sum('jumlah_kematian');

        return [
            'jumlah_penduduk' => $penduduk,
            'jumlah_kelahiran' => (float) $rows->sum('jumlah_kelahiran'),
            'jumlah_kematian' => $kematian,
            'rasio_kematian' => $penduduk > 0 ? ($kematian / $penduduk) * 1000 : null,
        ];
    }

    private function dukcapilValues(): Collection
    {
        $dukcapilId = Opd::query()
            ->whereRaw('LOWER(kode) = ?', ['dukcapil'])
            ->orWhereRaw('LOWER(nama) LIKE ?', ['%kependudukan%'])
            ->value('id');

        return NilaiDataMentah::query()
            ->join('indikator_data', 'indikator_data.id', '=', 'nilai_data_mentah.indikator_data_id')
            ->join('pengajuan_data', 'pengajuan_data.id', '=', 'nilai_data_mentah.pengajuan_data_id')
            ->join('desa', 'desa.id', '=', 'nilai_data_mentah.desa_id')
            ->whereIn('indikator_data.kode', ['jumlah_penduduk', 'jumlah_kelahiran', 'jumlah_kematian'])
            ->when($dukcapilId, fn ($query) => $query->where('indikator_data.opd_id', $dukcapilId))
            ->when($this->periodeDataId, fn ($query) => $query->where('pengajuan_data.periode_data_id', $this->periodeDataId))
            ->when($this->kecamatanId, fn ($query) => $query->where('desa.kecamatan_id', $this->kecamatanId))
            ->when($this->desaId, fn ($query) => $query->where('nilai_data_mentah.desa_id', $this->desaId))
            ->select([
                'nilai_data_mentah.desa_id',
                'indikator_data.kode',
                DB::raw('SUM(nilai_data_mentah.nilai) as total'),
            ])
            ->groupBy('nilai_data_mentah.desa_id', 'indikator_data.kode')
            ->get();
    }
}
