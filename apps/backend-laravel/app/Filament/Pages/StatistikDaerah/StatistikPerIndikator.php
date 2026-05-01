<?php

namespace App\Filament\Pages\StatistikDaerah;

use App\Models\IndikatorData;

class StatistikPerIndikator extends StatistikDaerahPage
{
    protected static ?string $navigationLabel = 'Statistik Per Indikator';

    protected static ?int $navigationSort = 21;

    public function mount(): void
    {
        parent::mount();

        $this->indikatorId ??= IndikatorData::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->value('id');
    }
}
