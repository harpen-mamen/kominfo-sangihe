<?php

namespace App\Filament\Pages\InputData;

use App\Models\Desa;
use App\Models\IndikatorData;
use App\Models\NilaiDataMentah;
use App\Models\PengajuanData;
use App\Models\PeriodeData;
use App\Models\SumberData;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Livewire\WithFileUploads;
use ZipArchive;

class UploadExcelDataMentah extends Page
{
    use HasPageShield {
        canAccess as shieldCanAccess;
    }
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Upload Excel';

    protected static string|\UnitEnum|null $navigationGroup = 'Input & Verifikasi';

    protected static ?int $navigationSort = 31;

    protected string $view = 'filament.pages.input-data.upload-excel';

    public ?int $periodeId = null;

    public ?int $periodeDataId = null;

    public mixed $file = null;

    public string $csvContent = '';

    public array $previewRows = [];

    public function mount(): void
    {
        $this->periodeDataId = $this->periodeQuery()->value('id');
        $this->periodeId = $this->periodeDataId;
    }

    public static function canAccess(): bool
    {
        return static::shieldCanAccess() && (FilamentWorkspace::isSubdistrict() || FilamentWorkspace::isDepartment());
    }

    public function getPeriodeOptionsProperty(): array
    {
        return $this->periodeQuery()->pluck('label', 'id')->all();
    }

    public function getPeriodesProperty()
    {
        return $this->periodeQuery()->get();
    }

    public function getIndikatorsProperty()
    {
        $user = FilamentWorkspace::user();
        $query = $user
            ? AdminScope::indikatorDataQuery($user, forInput: true)
            : IndikatorData::query()->where('aktif', true);

        return AdminScope::orderIndikatorQuery($query)->get();
    }

    public function updatedPeriodeId(?int $value): void
    {
        $this->periodeDataId = $value;
    }

    public function updatedPeriodeDataId(?int $value): void
    {
        $this->periodeId = $value;
    }

    public function import(): void
    {
        $this->save();
    }

    public function preview(): void
    {
        $this->previewRows = $this->parseRows();
    }

    public function save(): void
    {
        $this->periodeDataId = $this->periodeId ?: $this->periodeDataId;

        if (! $this->periodeDataId) {
            Notification::make()->title('Pilih periode terlebih dahulu.')->danger()->send();

            return;
        }

        $rows = $this->previewRows ?: $this->parseRows();

        if ($rows === []) {
            Notification::make()->title('Tidak ada baris valid untuk diproses.')->danger()->send();

            return;
        }

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
                'tipe_sumber' => 'desa',
                'sumber_id' => $row['desa_id'],
            ], [
                'nilai_decimal' => $row['nilai'],
                'nilai_text' => $row['catatan'] ?? null,
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
        if ($this->file) {
            return $this->parseUploadedFile();
        }

        return $this->parseCsvContent($this->csvContent);
    }

    private function parseUploadedFile(): array
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $path = $this->file->getRealPath();
        $extension = strtolower((string) $this->file->getClientOriginalExtension());

        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->parseCsvContent((string) file_get_contents($path));
        }

        if ($extension === 'xlsx') {
            return $this->parseMatrix($this->readXlsxRows($path));
        }

        Notification::make()
            ->title('Format .xls belum bisa diproses otomatis.')
            ->body('Simpan ulang file sebagai .xlsx atau .csv, lalu upload kembali.')
            ->danger()
            ->send();

        return [];
    }

    private function parseCsvContent(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        $matrix = [];

        foreach ($lines ?: [] as $line) {
            if (blank($line)) {
                continue;
            }

            $matrix[] = str_getcsv($line);
        }

        return $this->parseMatrix($matrix);
    }

    private function parseMatrix(array $matrix): array
    {
        if ($matrix === []) {
            return [];
        }

        $headers = array_map(fn ($header): string => $this->normalizeHeader((string) $header), array_shift($matrix));
        $rows = [];

        foreach ($matrix as $index => $columns) {
            $row = [];

            foreach ($headers as $columnIndex => $header) {
                $row[$header] = $columns[$columnIndex] ?? null;
            }

            $kodeDesa = $row['kode_desa'] ?? null;
            $namaDesa = $row['nama_desa'] ?? $row['desa'] ?? null;
            $kodeIndikator = $row['kode_indikator'] ?? $row['indikator'] ?? null;
            $sumberData = $row['sumber_data'] ?? null;
            $nilai = $row['nilai'] ?? null;
            $catatan = $row['catatan'] ?? null;

            $desa = filled($kodeDesa)
                ? Desa::query()->where('kode', trim((string) $kodeDesa))->first()
                : Desa::query()->where('nama', trim((string) $namaDesa))->first();
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
                'catatan' => $catatan,
                'desa_id' => $desa?->id,
                'indikator_id' => $indikator?->id,
                'sumber_data_id' => $sumber?->id,
                'error' => implode(', ', $errors),
            ];
        }

        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        return str($header)
            ->lower()
            ->replace([' ', '-'], '_')
            ->replaceMatches('/[^a-z0-9_]/', '')
            ->value();
    }

    private function readXlsxRows(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $worksheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($worksheetXml === false) {
            return [];
        }

        $worksheet = simplexml_load_string($worksheetXml);

        if (! $worksheet) {
            return [];
        }

        $rows = [];

        foreach ($worksheet->sheetData->row as $sheetRow) {
            $row = [];

            foreach ($sheetRow->c as $cell) {
                $reference = (string) $cell['r'];
                $columnIndex = $this->columnIndexFromReference($reference);
                $type = (string) $cell['t'];
                $value = (string) ($cell->v ?? '');

                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) ($cell->is->t ?? '');
                }

                $row[$columnIndex] = $value;
            }

            if ($row !== []) {
                ksort($row);
                $rows[] = array_values($row);
            }
        }

        return $rows;
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $shared = simplexml_load_string($xml);

        if (! $shared) {
            return [];
        }

        $strings = [];

        foreach ($shared->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $strings[] = collect($item->r ?? [])
                ->map(fn ($run): string => (string) ($run->t ?? ''))
                ->implode('');
        }

        return $strings;
    }

    private function columnIndexFromReference(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference)) ?: 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
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

    private function periodeQuery(): Builder
    {
        return PeriodeData::query()
            ->where(function (Builder $query): void {
                $query
                    ->where('terkunci', false)
                    ->orWhereNull('terkunci');
            })
            ->orderByDesc('tahun')
            ->orderByDesc('bulan');
    }
}
