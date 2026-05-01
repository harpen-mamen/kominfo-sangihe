<?php

namespace App\Filament\Pages\StatistikDaerah;

use App\Models\Opd;

class StatistikPerOpd extends StatistikDaerahPage
{
    protected static ?string $navigationLabel = 'Statistik Per OPD';

    protected static ?int $navigationSort = 24;

    public function mount(): void
    {
        parent::mount();

        $this->opdId ??= Opd::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->value('id');
    }
}
