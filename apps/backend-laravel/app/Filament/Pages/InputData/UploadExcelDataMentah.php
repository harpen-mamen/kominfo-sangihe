<?php

namespace App\Filament\Pages\InputData;

use App\Models\Desa;
use App\Models\IndikatorData;
use App\Models\NilaiDataMentah;
use App\Models\PengajuanData;
use App\Models\PeriodeData;
use App\Models\SumberData;
use App\Support\FilamentWorkspace;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UploadExcelDataMentah extends Page
{
    use HasPageShield {
        canAccess as shieldCanAccess;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Upload Excel';

    protected static string|\UnitEnum|null $navigationGroup = 'Input & Verifikasi';

    protected static ?int $navigationSort = 31;

    protected string $view = 'filament.pages.input-data.upload-excel';

    public ?int $periodeDataId = null;

    public string $csvContent = '';

    public array $previewRows = [];

    public function mount(): void
    {
        $this->periodeDataId = PeriodeData::query()->where('terkunci', false)->orderByDesc('tahun')->orderByDesc('bulan')->value('id');
    }

    public static function canAccess(): bool
    {
        return static::shieldCanAccess() && FilamentWorkspace::canAccessWorkflow();
    }

    public function getPeriodeOptionsProperty(): array
    {
        return PeriodeData::query()->where('terkunci', false)->orderByDesc('tahun')->orderByDesc('bulan')->pluck('label', 'id')->all();
    }

    public function preview(): void
    {
        $this->previewRows = $this->parseRows();
    }

    public function save(): void
    {
        $rows = $this->previewRows ?: $this->parseRows();
        $pengajuan = $this->pengajuanFor();
        $saved = 0;

        foreach ($rows as $row) {
            if (! empty($row['error'])) {
                continue;
            }

            NilaiDataMentah::query()->updateOrCreate([
                'pengajuan_data_id' => $pengajuan->id,
                'desa_id' => $row['desa_id'],
                'indikator_data_id' => $row['indikator_id'],
                'sumber_data_id' => $row['sumber_data_id'],
            ], [
                'nilai' => $row['nilai'],
            ]);

            $saved++;
        }

        Notification::make()->title("{$saved} baris valid disimpan.")->success()->send();
    }

    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            echo "kode_desa,kode_indikator,sumber_data,nilai\n";
            echo "DESA001,jumlah_penduduk,Kantor Desa,1000\n";
        }, 'template-upload-data-mentah.csv', ['Content-Type' => 'text/csv']);
    }

    private function parseRows(): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($this->csvContent));
        $rows = [];

        foreach (array_slice($lines ?: [], 1) as $index => $line) {
            if (blank($line)) {
                continue;
            }

            $columns = str_getcsv($line);
            [$kodeDesa, $kodeIndikator, $sumberData, $nilai] = array_pad($columns, 4, null);

            $desa = Desa::query()->where('kode', trim((string) $kodeDesa))->first();
            $indikator = IndikatorData::query()->where('kode', trim((string) $kodeIndikator))->first();
            $sumber = filled($sumberData) ? SumberData::query()->where('nama', trim((string) $sumberData))->first() : null;
            $errors = [];

            if (! $desa) {
                $errors[] = 'Kode desa tidak ditemukan';
            }

            if (! $indikator) {
                $errors[] = 'Kode indikator tidak ditemukan';
            }

            if (filled($sumberData) && ! $sumber) {
                $errors[] = 'Sumber data tidak ditemukan';
            }

            if (! is_numeric($nilai)) {
                $errors[] = 'Nilai harus angka';
            }

            $rows[] = [
                'line' => $index + 2,
                'kode_desa' => $kodeDesa,
                'kode_indikator' => $kodeIndikator,
                'sumber_data' => $sumberData,
                'nilai' => (float) $nilai,
                'desa_id' => $desa?->id,
                'indikator_id' => $indikator?->id,
                'sumber_data_id' => $sumber?->id,
                'error' => implode(', ', $errors),
            ];
        }

        return $rows;
    }

    private function pengajuanFor(): PengajuanData
    {
        $user = FilamentWorkspace::user();
        $attributes = ['periode_data_id' => $this->periodeDataId];

        if (FilamentWorkspace::isDepartment()) {
            $attributes['opd_id'] = $user?->opd_id;
            $attributes['kecamatan_id'] = null;
        } else {
            $attributes['kecamatan_id'] = $user?->kecamatan_id;
            $attributes['opd_id'] = null;
        }

        return PengajuanData::query()->firstOrCreate($attributes, [
            'dikirim_oleh' => $user?->id,
            'status' => 'draft',
            'tanggal_kirim' => Carbon::now(),
        ]);
    }
}
