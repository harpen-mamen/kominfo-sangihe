<?php

namespace App\Filament\Resources\Kecamatan\Pages;

use App\Filament\Resources\Kecamatan\KecamatanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageKecamatan extends ManageRecords
{
    protected static string $resource = KecamatanResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
