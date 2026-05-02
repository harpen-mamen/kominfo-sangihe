<?php

namespace App\Filament\Resources\PengajuanData\Pages;

use App\Filament\Resources\PengajuanData\PengajuanDataResource;
use App\Models\Desa;
use App\Models\IndikatorData;
use App\Models\NilaiDataMentah;
use App\Models\PengajuanData;
use App\Services\WorkflowService;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class InputDataPengajuan extends Page
{
    use InteractsWithRecord {
        getRecord as getBaseRecord;
        resolveRecord as getBaseResolveRecord;
    }

    protected static string $resource = PengajuanDataResource::class;

    protected string $view = 'filament.resources.pengajuan-data.pages.input-data-pengajuan';

    public array $nilai = [];

    public array $catatanNilai = [];

    public array $tidakTersedia = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->authorizeAccess();
        $this->record->loadMissing(['kecamatan', 'periodeData', 'nilaiDataMentah']);
        $this->loadExistingValues();
    }

    public function getTitle(): string
    {
        return 'Input Data Pengajuan';
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

        abort_if(AdminScope::isKominfo($user), 403);
    }

    public function getRecord(): PengajuanData
    {
        /** @var PengajuanData $record */
        $record = $this->getBaseRecord();

        return $record;
    }

    public function getIsReadOnlyProperty(): bool
    {
        return ! $this->getRecord()->canInputValues();
    }

    public function getDesaRowsProperty(): Collection
    {
        $record = $this->getRecord();

        return Desa::query()
            ->where('kecamatan_id', $record->kecamatan_id)
            ->where('aktif', true)
            ->orderBy('nama')
            ->get();
    }

    public function getIndicatorsProperty(): Collection
    {
        $record = $this->getRecord();
        $user = FilamentWorkspace::user();
        $query = $user
            ? AdminScope::indikatorDataQuery($user, forInput: true, kelompokIndikator: $record->kelompok_indikator)
            : IndikatorData::query()->where('aktif', true);

        if (! $user) {
            AdminScope::applyKelompokIndikatorFilter($query, $record->kelompok_indikator);
        }

        return AdminScope::orderIndikatorQuery($query)
            ->with('opdPembina')
            ->get();
    }

    public function getKecamatanIndicatorsProperty(): Collection
    {
        return $this->indicators->where('level_input', 'kecamatan')->values();
    }

    public function getDesaIndicatorsProperty(): Collection
    {
        return $this->indicators
            ->filter(fn (IndikatorData $indicator): bool => blank($indicator->level_input) || $indicator->level_input === 'desa')
            ->values();
    }

    public function getProgressProperty(): array
    {
        $total = 0;
        $filled = 0;

        foreach ($this->indicators->where('wajib_diisi', true) as $indicator) {
            if (blank($indicator->level_input) || $indicator->level_input === 'desa') {
                foreach ($this->desaRows as $desa) {
                    $total++;
                    $filled += $this->hasInputValue('desa', (int) $desa->id, (int) $indicator->id) ? 1 : 0;
                }

                continue;
            }

            if ($indicator->level_input === 'kecamatan') {
                $total++;
                $filled += $this->hasInputValue('kecamatan', (int) $this->getRecord()->kecamatan_id, (int) $indicator->id) ? 1 : 0;
            }
        }

        return [
            'filled' => $filled,
            'total' => $total,
            'percent' => $total > 0 ? round(($filled / $total) * 100) : 100,
        ];
    }

    public function saveDraft(): void
    {
        if ($this->isReadOnly) {
            Notification::make()->title('Pengajuan tidak dapat diedit pada status ini.')->danger()->send();

            return;
        }

        try {
            $saved = $this->persistValues();
            Notification::make()->title("{$saved} nilai disimpan sebagai draft.")->success()->send();
        } catch (\Throwable $exception) {
            Notification::make()->title('Gagal menyimpan draft.')->body($exception->getMessage())->danger()->send();
        }
    }

    public function submit(): void
    {
        if ($this->isReadOnly) {
            Notification::make()->title('Pengajuan tidak dapat diajukan pada status ini.')->danger()->send();

            return;
        }

        try {
            $this->persistValues();
            app(WorkflowService::class)->ajukan((int) $this->getRecord()->id);
            Notification::make()->title('Pengajuan dikirim ke Kominfo.')->success()->send();
            $this->redirect(PengajuanDataResource::getUrl());
        } catch (\Throwable $exception) {
            Notification::make()->title('Pengajuan belum dapat dikirim.')->body($exception->getMessage())->danger()->send();
        }
    }

    private function persistValues(): int
    {
        $saved = 0;

        foreach ($this->kecamatanIndicators as $indicator) {
            $saved += $this->persistOne('kecamatan', (int) $this->getRecord()->kecamatan_id, $indicator, null);
        }

        foreach ($this->desaRows as $desa) {
            foreach ($this->desaIndicators as $indicator) {
                $saved += $this->persistOne('desa', (int) $desa->id, $indicator, $desa);
            }
        }

        $this->record = $this->getRecord()->refresh();
        $this->loadExistingValues();

        return $saved;
    }

    private function persistOne(string $tipeSumber, int $sumberId, IndikatorData $indicator, ?Desa $desa): int
    {
        $rawValue = $this->nilai[$tipeSumber][$sumberId][$indicator->id] ?? null;
        $catatan = $this->catatanNilai[$tipeSumber][$sumberId][$indicator->id] ?? null;
        $isTidakTersedia = (bool) ($this->tidakTersedia[$tipeSumber][$sumberId][$indicator->id] ?? false);

        if (! $isTidakTersedia && ($rawValue === null || $rawValue === '') && blank($catatan)) {
            return 0;
        }

        NilaiDataMentah::query()->updateOrCreate([
            'pengajuan_data_id' => $this->getRecord()->id,
            'indikator_data_id' => $indicator->id,
            'tipe_sumber' => $tipeSumber,
            'sumber_id' => $sumberId,
        ], [
            'desa_id' => $desa?->id,
            'sumber_data_id' => null,
            'nilai_decimal' => $indicator->tipe_nilai === 'text' ? null : ($rawValue === '' ? null : $rawValue),
            'nilai_text' => $indicator->tipe_nilai === 'text' ? ($rawValue === '' ? null : $rawValue) : null,
            'is_tidak_tersedia' => $isTidakTersedia,
            'catatan' => $catatan,
            'status_validasi' => $isTidakTersedia ? 'tidak_tersedia' : 'valid',
            'pesan_validasi' => null,
        ]);

        return 1;
    }

    private function loadExistingValues(): void
    {
        $this->nilai = [];
        $this->catatanNilai = [];
        $this->tidakTersedia = [];

        $this->getRecord()
            ->nilaiDataMentah()
            ->get()
            ->each(function (NilaiDataMentah $nilai): void {
                $tipeSumber = $nilai->tipe_sumber ?: ($nilai->desa_id ? 'desa' : 'kecamatan');
                $sumberId = (int) ($nilai->sumber_id ?: $nilai->desa_id ?: $this->getRecord()->kecamatan_id);
                $value = $nilai->nilai_text ?? $nilai->nilai_decimal;

                $this->nilai[$tipeSumber][$sumberId][$nilai->indikator_data_id] = $value;
                $this->catatanNilai[$tipeSumber][$sumberId][$nilai->indikator_data_id] = $nilai->catatan;
                $this->tidakTersedia[$tipeSumber][$sumberId][$nilai->indikator_data_id] = (bool) $nilai->is_tidak_tersedia;
            });
    }

    private function hasInputValue(string $tipeSumber, int $sumberId, int $indikatorId): bool
    {
        if ((bool) ($this->tidakTersedia[$tipeSumber][$sumberId][$indikatorId] ?? false)) {
            return filled($this->catatanNilai[$tipeSumber][$sumberId][$indikatorId] ?? null);
        }

        $value = $this->nilai[$tipeSumber][$sumberId][$indikatorId] ?? null;

        return $value !== null && $value !== '';
    }

    protected function resolveRecord(int|string $key): Model
    {
        $record = $this->getBaseResolveRecord($key);

        abort_unless($record instanceof PengajuanData, 404);

        return $record;
    }
}
