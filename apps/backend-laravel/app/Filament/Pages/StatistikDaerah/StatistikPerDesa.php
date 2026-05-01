<?php

namespace App\Filament\Pages\StatistikDaerah;

use App\Models\Desa;

class StatistikPerDesa extends StatistikDaerahPage
{
    protected static ?string $navigationLabel = 'Statistik Per Desa';

    protected static ?int $navigationSort = 23;

    public function mount(): void
    {
        parent::mount();

        $desa = Desa::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->first();

        $this->desaId ??= $desa?->id;
        $this->kecamatanId ??= $desa?->kecamatan_id;
    }
}
