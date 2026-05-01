<?php

namespace App\Filament\Pages\InputData;

use App\Models\IndikatorData;
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
        $this->indikatorId = IndikatorData::query()->where('aktif', true)->orderBy('urutan')->value('id');
    }

    public function getIndikatorColumnsProperty()
    {
        return IndikatorData::query()->whereKey($this->indikatorId)->get();
    }

    public function getAllIndikatorOptionsProperty(): array
    {
        return parent::getIndikatorColumnsProperty()->pluck('nama', 'id')->all();
    }
}
