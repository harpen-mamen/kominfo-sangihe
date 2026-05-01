<?php

namespace App\Filament\Pages\StatistikDaerah;

class TrenBulananTahunan extends StatistikDaerahPage
{
    protected static ?string $navigationLabel = 'Tren Bulanan/Tahunan';

    protected static ?int $navigationSort = 26;

    public function mount(): void
    {
        parent::mount();

        $this->periodeDataId = null;
    }
}
