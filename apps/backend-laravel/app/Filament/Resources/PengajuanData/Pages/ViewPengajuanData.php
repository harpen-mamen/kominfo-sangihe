<?php

namespace App\Filament\Resources\PengajuanData\Pages;

use App\Filament\Resources\PengajuanData\PengajuanDataResource;
use App\Models\Desa;
use App\Models\IndikatorData;
use App\Models\NilaiDataMentah;
use App\Models\PengajuanData;
use App\Models\PeriodeData;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ViewPengajuanData extends Page
{
    use InteractsWithRecord {
        getRecord as getBaseRecord;
        resolveRecord as getBaseResolveRecord;
    }

    protected static string $resource = PengajuanDataResource::class;

    protected string $view = 'filament.resources.pengajuan-data.pages.view-pengajuan-data';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->authorizeAccess();
        $this->getRecord()->loadMissing([
            'kecamatan',
            'opd',
            'periodeData',
            'dikirimOleh',
            'diverifikasiOleh',
            'submittedBy',
            'verifiedBy',
            'publishedBy',
        ]);
    }

    public function getTitle(): string
    {
        return 'Tinjauan Pengajuan Data Mentah';
    }

    protected function authorizeAccess(): void
    {
        $user = FilamentWorkspace::user();
        $record = $this->getRecord();

        abort_unless($user, 403);

        if (AdminScope::isSubdistrict($user)) {
            abort_unless((int) $record->kecamatan_id === (int) $user->kecamatan_id, 403);
        }

        if (AdminScope::isDepartment($user)) {
            abort_unless((int) $record->opd_id === (int) $user->opd_id, 403);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->workflowHeaderAction('minta_revisi', 'Minta Revisi', 'warning', true)
                ->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn (): bool => PengajuanDataResource::canKominfoVerifyForPage()
                    && in_array($this->getRecord()->status, ['diajukan', 'terverifikasi'], true)),

            $this->workflowHeaderAction('verifikasi', 'Verifikasi', 'success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->visible(fn (): bool => PengajuanDataResource::canKominfoVerifyForPage()
                    && in_array($this->getRecord()->status, ['diajukan', 'revisi'], true)),

            $this->workflowHeaderAction('tolak', 'Tolak', 'danger', true)
                ->icon('heroicon-o-x-circle')
                ->visible(fn (): bool => PengajuanDataResource::canKominfoVerifyForPage()
                    && in_array($this->getRecord()->status, ['diajukan', 'revisi'], true)),

            $this->workflowHeaderAction('terbitkan', 'Terbitkan', 'primary')
                ->icon('heroicon-o-globe-alt')
                ->requiresConfirmation()
                ->visible(fn (): bool => PengajuanDataResource::canKominfoPublishForPage()
                    && $this->getRecord()->status === 'terverifikasi'),

            $this->workflowHeaderAction('tarik_publikasi', 'Tarik Publikasi', 'gray', true)
                ->icon('heroicon-o-archive-box-x-mark')
                ->visible(fn (): bool => PengajuanDataResource::canKominfoPublishForPage()
                    && $this->getRecord()->status === 'terbit'),
        ];
    }

    protected function workflowHeaderAction(string $name, string $label, string $color, bool $needsNote = false): Action
    {
        return PengajuanDataResource::workflowAction($name, $label, $color, $needsNote)
            ->record(fn (): PengajuanData => $this->getRecord())
            ->after(function (): void {
                $this->record = $this->getRecord()->refresh();
            });
    }

    public function getRecord(): PengajuanData
    {
        /** @var PengajuanData $record */
        $record = $this->getBaseRecord();

        return $record;
    }

    /**
     * @return array<string, mixed>
     */
    public function getReviewDataProperty(): array
    {
        $record = $this->getRecord();
        $desaRows = $this->desaRows();
        $indicators = $this->indicatorRows(requiredOnly: false);
        $requiredIndicators = $this->indicatorRows(requiredOnly: true);
        $currentValues = $this->valuesForPengajuan((int) $record->id);
        $previousValues = $this->previousValues();

        $detailRows = $this->buildDetailRows($desaRows, $indicators, $currentValues, $previousValues);
        $requiredRows = $this->buildDetailRows($desaRows, $requiredIndicators, $currentValues, $previousValues);
        $filledRequired = $requiredRows->filter(fn (array $row): bool => $row['is_filled'])->count();
        $totalRequired = $desaRows->count() * $requiredIndicators->count();

        return [
            'desa_count' => $desaRows->count(),
            'required_indicator_count' => $requiredIndicators->count(),
            'total_required' => $totalRequired,
            'filled_required' => $filledRequired,
            'missing_required' => max(0, $totalRequired - $filledRequired),
            'percent' => $totalRequired > 0 ? round(($filledRequired / $totalRequired) * 100, 1) : 100,
            'detail_rows' => $detailRows,
            'findings' => $this->findings($requiredRows, $detailRows),
            'comparisons' => $detailRows
                ->filter(fn (array $row): bool => $row['previous_value'] !== null && $row['previous_value'] !== '')
                ->take(100)
                ->values(),
            'has_previous_period' => $previousValues->isNotEmpty(),
        ];
    }

    protected function desaRows(): Collection
    {
        $record = $this->getRecord();

        if (blank($record->kecamatan_id) || ! Schema::hasTable('desa')) {
            return collect();
        }

        $query = Desa::query()->where('kecamatan_id', $record->kecamatan_id);

        if (Schema::hasColumn('desa', 'aktif')) {
            $query->where('aktif', true);
        }

        return $query->orderBy('nama')->get();
    }

    protected function indicatorRows(bool $requiredOnly): Collection
    {
        if (! Schema::hasTable('indikator_data')) {
            return collect();
        }

        if ($requiredOnly && ! Schema::hasColumn('indikator_data', 'wajib_diisi')) {
            return collect();
        }

        $query = IndikatorData::query();

        if (Schema::hasColumn('indikator_data', 'aktif')) {
            $query->where('aktif', true);
        }

        if ($requiredOnly) {
            $query->where('wajib_diisi', true);
        }

        if (Schema::hasColumn('indikator_data', 'input_kecamatan')) {
            $query->where('input_kecamatan', true);
        } elseif (Schema::hasColumn('indikator_data', 'boleh_diinput_kecamatan')) {
            $query->where('boleh_diinput_kecamatan', true);
        }

        if (Schema::hasColumn('indikator_data', 'level_input')) {
            $query->where(function (Builder $builder): void {
                $builder
                    ->whereNull('level_input')
                    ->orWhere('level_input', 'desa');
            });
        }

        AdminScope::applyKelompokIndikatorFilter($query, $this->getRecord()->kelompok_indikator);

        return AdminScope::orderIndikatorQuery($query)->get();
    }

    protected function valuesForPengajuan(int $pengajuanId): Collection
    {
        if (! Schema::hasTable('nilai_data_mentah')) {
            return collect();
        }

        return NilaiDataMentah::query()
            ->where('pengajuan_data_id', $pengajuanId)
            ->with(['desa', 'indikatorData'])
            ->get()
            ->keyBy(function (NilaiDataMentah $nilai): string {
                $desaId = $this->valueDesaId($nilai);

                return $nilai->indikator_data_id . ':' . $desaId;
            });
    }

    protected function previousValues(): Collection
    {
        $previousPeriodId = $this->previousPeriodId();

        if (! $previousPeriodId) {
            return collect();
        }

        $record = $this->getRecord();
        $query = PengajuanData::query()
            ->where('periode_data_id', $previousPeriodId);

        if (filled($record->kecamatan_id)) {
            $query->where('kecamatan_id', $record->kecamatan_id);
        }

        if (filled($record->opd_id)) {
            $query->where('opd_id', $record->opd_id);
        }

        if (filled($record->kelompok_indikator) && Schema::hasColumn('pengajuan_data', 'kelompok_indikator')) {
            $query->where('kelompok_indikator', $record->kelompok_indikator);
        }

        $previousPengajuan = $query->latest('id')->first();

        return $previousPengajuan
            ? $this->valuesForPengajuan((int) $previousPengajuan->id)
            : collect();
    }

    protected function previousPeriodId(): ?int
    {
        $period = $this->getRecord()->periodeData;

        if (! $period || ! Schema::hasTable('periode_data') || ! Schema::hasColumn('periode_data', 'tahun')) {
            return null;
        }

        $query = PeriodeData::query();

        if (Schema::hasColumn('periode_data', 'bulan')) {
            $query->where(function (Builder $builder) use ($period): void {
                $builder
                    ->where('tahun', '<', $period->tahun)
                    ->orWhere(function (Builder $nested) use ($period): void {
                        $nested
                            ->where('tahun', $period->tahun)
                            ->where('bulan', '<', $period->bulan);
                    });
            })->orderByDesc('tahun')->orderByDesc('bulan');
        } else {
            $query->where('tahun', '<', $period->tahun)->orderByDesc('tahun');
        }

        return $query->value('id');
    }

    protected function buildDetailRows(Collection $desaRows, Collection $indicators, Collection $currentValues, Collection $previousValues): Collection
    {
        $rows = collect();

        foreach ($desaRows as $desa) {
            foreach ($indicators as $indicator) {
                $key = $indicator->id . ':' . $desa->id;
                $nilai = $currentValues->get($key);
                $previous = $previousValues->get($key);
                $value = $nilai ? $this->displayValue($nilai) : null;
                $previousValue = $previous ? $this->displayValue($previous) : null;

                $rows->push([
                    'indicator_id' => (int) $indicator->id,
                    'indicator' => $indicator->nama,
                    'category' => $this->indicatorCategory($indicator),
                    'desa_id' => (int) $desa->id,
                    'desa' => $desa->nama,
                    'value' => $value,
                    'previous_value' => $previousValue,
                    'difference' => $this->numericDifference($value, $previousValue),
                    'percent_change' => $this->percentChange($value, $previousValue),
                    'satuan' => $indicator->satuan,
                    'status' => $this->valueStatus($nilai),
                    'is_filled' => $this->isFilled($nilai),
                    'catatan' => $nilai && Schema::hasColumn('nilai_data_mentah', 'catatan') ? $nilai->catatan : null,
                    'updated_at' => $nilai?->updated_at,
                    'batas_min' => Schema::hasColumn('indikator_data', 'batas_min') ? $indicator->batas_min : null,
                    'batas_max' => Schema::hasColumn('indikator_data', 'batas_max') ? $indicator->batas_max : null,
                ]);
            }
        }

        return $rows;
    }

    protected function findings(Collection $requiredRows, Collection $detailRows): Collection
    {
        $findings = collect();

        $requiredRows
            ->filter(fn (array $row): bool => ! $row['is_filled'])
            ->take(50)
            ->each(fn (array $row) => $findings->push("{$row['desa']} - {$row['indicator']} belum diisi."));

        $requiredRows
            ->groupBy('desa')
            ->filter(fn (Collection $rows): bool => $rows->where('is_filled', true)->isEmpty() && $rows->isNotEmpty())
            ->keys()
            ->each(fn (string $desa) => $findings->push("{$desa} belum punya nilai wajib."));

        $detailRows->each(function (array $row) use ($findings): void {
            if (! is_numeric($row['value'])) {
                return;
            }

            $value = (float) $row['value'];

            if ($row['batas_min'] !== null && $value < (float) $row['batas_min']) {
                $findings->push("{$row['desa']} - {$row['indicator']} di bawah batas minimum {$row['batas_min']}.");
            } elseif ((float) ($row['batas_min'] ?? 0) >= 0 && $value < 0) {
                $findings->push("{$row['desa']} - {$row['indicator']} bernilai negatif.");
            }

            if ($row['batas_max'] !== null && $value > (float) $row['batas_max']) {
                $findings->push("{$row['desa']} - {$row['indicator']} di atas batas maksimum {$row['batas_max']}.");
            }
        });

        return $findings->unique()->values();
    }

    protected function valueDesaId(NilaiDataMentah $nilai): ?int
    {
        if (Schema::hasColumn('nilai_data_mentah', 'desa_id') && filled($nilai->desa_id)) {
            return (int) $nilai->desa_id;
        }

        if (
            Schema::hasColumn('nilai_data_mentah', 'sumber_id')
            && filled($nilai->sumber_id)
            && (! Schema::hasColumn('nilai_data_mentah', 'tipe_sumber') || ($nilai->tipe_sumber ?? null) === 'desa')
        ) {
            return (int) $nilai->sumber_id;
        }

        return null;
    }

    protected function displayValue(NilaiDataMentah $nilai): mixed
    {
        foreach (['nilai_decimal', 'nilai', 'nilai_text'] as $column) {
            if (! Schema::hasColumn('nilai_data_mentah', $column)) {
                continue;
            }

            $value = $nilai->getAttribute($column);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function valueStatus(?NilaiDataMentah $nilai): string
    {
        if (! $nilai) {
            return 'Kosong';
        }

        if (Schema::hasColumn('nilai_data_mentah', 'is_tidak_tersedia') && (bool) $nilai->is_tidak_tersedia) {
            return 'Tidak tersedia';
        }

        $value = $this->displayValue($nilai);

        if ($value === null || $value === '') {
            return 'Kosong';
        }

        return is_numeric($value) && (float) $value === 0.0 ? 'Nol' : 'Terisi';
    }

    protected function isFilled(?NilaiDataMentah $nilai): bool
    {
        if (! $nilai) {
            return false;
        }

        if (Schema::hasColumn('nilai_data_mentah', 'is_tidak_tersedia') && (bool) $nilai->is_tidak_tersedia) {
            return false;
        }

        $value = $this->displayValue($nilai);

        return $value !== null && $value !== '';
    }

    protected function indicatorCategory(IndikatorData $indicator): ?string
    {
        foreach (['kelompok_indikator', 'kelompok', 'kategori'] as $column) {
            if (Schema::hasColumn('indikator_data', $column) && filled($indicator->getAttribute($column))) {
                return (string) $indicator->getAttribute($column);
            }
        }

        return null;
    }

    protected function numericDifference(mixed $current, mixed $previous): ?float
    {
        return is_numeric($current) && is_numeric($previous)
            ? round((float) $current - (float) $previous, 4)
            : null;
    }

    protected function percentChange(mixed $current, mixed $previous): ?float
    {
        if (! is_numeric($current) || ! is_numeric($previous) || (float) $previous === 0.0) {
            return null;
        }

        return round((((float) $current - (float) $previous) / abs((float) $previous)) * 100, 2);
    }

    protected function resolveRecord(int|string $key): Model
    {
        $record = $this->getBaseResolveRecord($key);

        abort_unless($record instanceof PengajuanData, 404);

        return $record;
    }
}
