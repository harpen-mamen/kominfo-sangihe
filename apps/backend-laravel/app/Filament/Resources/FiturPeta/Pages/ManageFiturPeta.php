<?php

namespace App\Filament\Resources\FiturPeta\Pages;

use App\Filament\Resources\FiturPeta\FiturPetaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFiturPeta extends ManageRecords
{
    protected static string $resource = FiturPetaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
