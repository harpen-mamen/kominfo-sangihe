<?php

namespace App\Filament\Pages\InputData;

use App\Models\Desa;
use App\Models\IndikatorData;
use App\Models\NilaiDataMentah;
use App\Models\PengajuanData;
use App\Models\PeriodeData;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class InputCepat extends Page
{
    use HasPageShield {
        canAccess as shieldCanAccess;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?string $navigationLabel = 'Input Cepat';

    protected static string|\UnitEnum|null $navigationGroup = 'Input & Verifikasi';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.input-data.input-cepat';

    public ?int $periodeDataId = null;

    public ?string $kelompok = null;

    public array $nilai = [];

    public function mount(): void
    {
        $this->periodeDataId = PeriodeData::query()->where('terkunci', false)->orderByDesc('tahun')->orderByDesc('bulan')->value('id');
        $this->kelompok = IndikatorData::query()->where('aktif', true)->orderBy('kelompok')->value('kelompok');
    }

    public static function canAccess(): bool
    {
        return static::shieldCanAccess() && FilamentWorkspace::canAccessWorkflow();
    }

    public function getPeriodeOptionsProperty(): array
    {
        return PeriodeData::query()->where('terkunci', false)->orderByDesc('tahun')->orderByDesc('bulan')->pluck('label', 'id')->all();
    }

    public function getKelompokOptionsProperty(): array
    {
        return IndikatorData::query()->where('aktif', true)->select('kelompok')->distinct()->orderBy('kelompok')->pluck('kelompok', 'kelompok')->all();
    }

    public function getDesaRowsProperty()
    {
        $user = FilamentWorkspace::user();
        $query = $user ? AdminScope::desaQuery($user) : Desa::query();

        return $query->where('aktif', true)->with('kecamatan')->orderBy('nama')->get();
    }

    public function getIndikatorColumnsProperty()
    {
        $user = FilamentWorkspace::user();
        $query = $user ? AdminScope::indikatorDataQuery($user, forInput: true) : IndikatorData::query()->where('aktif', true);

        return $query->when($this->kelompok, fn ($builder) => $builder->where('kelompok', $this->kelompok))
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();
    }

    public function save(): void
    {
        $user = FilamentWorkspace::user();

        if (! $user || ! $this->periodeDataId) {
            Notification::make()->title('Periode atau pengguna tidak valid.')->danger()->send();
            return;
        }

        $pengajuan = $this->pengajuanFor($this->periodeDataId);
        $saved = 0;

        foreach ($this->nilai as $desaId => $indikators) {
            foreach ((array) $indikators as $indikatorId => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                NilaiDataMentah::query()->updateOrCreate([
                    'pengajuan_data_id' => $pengajuan->id,
                    'desa_id' => (int) $desaId,
                    'indikator_data_id' => (int) $indikatorId,
                    'sumber_data_id' => null,
                ], [
                    'nilai' => (float) $value,
                ]);

                $saved++;
            }
        }

        Notification::make()->title("{$saved} nilai disimpan.")->success()->send();
    }

    protected function pengajuanFor(int $periodeDataId): PengajuanData
    {
        $user = FilamentWorkspace::user();
        $attributes = ['periode_data_id' => $periodeDataId];

        if (FilamentWorkspace::isDepartment()) {
            $attributes['opd_id'] = $user?->opd_id;
            $attributes['kecamatan_id'] = null;
        } else {
            $attributes['kecamatan_id'] = $user?->kecamatan_id ?: $this->desaRows->first()?->kecamatan_id;
            $attributes['opd_id'] = null;
        }

        return PengajuanData::query()->firstOrCreate($attributes, [
            'dikirim_oleh' => $user?->id,
            'status' => 'draft',
            'tanggal_kirim' => Carbon::now(),
        ]);
    }
}
