<?php

namespace App\Filament\Pages\InputData;

use App\Models\IndikatorData;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
use Filament\Support\Icons\Heroicon;

class InputPerIndikator extends InputCepat
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Input Per Indikator';

    protected static ?int $navigationSort = 32;

    protected string $view = 'filament.pages.input-data.input-per-indikator';

    public ?int $indikatorId = null;

    public function mount(): void
    {
        parent::mount();
        $user = FilamentWorkspace::user();
        $query = $user
            ? AdminScope::indikatorDataQuery($user, forInput: true)
            : IndikatorData::query()->where('aktif', true);

        $this->indikatorId = AdminScope::orderIndikatorQuery($query)->value('id');
    }

    public function getIndikatorColumnsProperty()
    {
        if (! $this->indikatorId) {
            return collect();
        }

        $user = FilamentWorkspace::user();
        $query = $user
            ? AdminScope::indikatorDataQuery($user, forInput: true)
            : IndikatorData::query()->where('aktif', true);

        return $query->whereKey($this->indikatorId)->get();
    }

    public function getIndikatorsProperty()
    {
        return parent::getIndikatorColumnsProperty();
    }

    public function getAllIndikatorOptionsProperty(): array
    {
        return parent::getIndikatorColumnsProperty()->pluck('nama', 'id')->all();
    }

    public function save(): void
    {
        if (! $this->indikatorId) {
            parent::save();

            return;
        }

        $nilai = [];

        foreach ($this->nilai as $desaId => $value) {
            $nilai[$desaId] = is_array($value)
                ? $value
                : [$this->indikatorId => $value];
        }

        $this->nilai = $nilai;

        parent::save();
    }
}
