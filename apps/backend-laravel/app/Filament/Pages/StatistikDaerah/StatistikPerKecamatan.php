<?php

namespace App\Filament\Pages\StatistikDaerah;

use App\Models\Kecamatan;

class StatistikPerKecamatan extends StatistikDaerahPage
{
    protected static ?string $navigationLabel = 'Statistik Per Kecamatan';

    protected static ?int $navigationSort = 22;

    public function mount(): void
    {
        parent::mount();

        $this->kecamatanId ??= Kecamatan::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->value('id');
    }
}
