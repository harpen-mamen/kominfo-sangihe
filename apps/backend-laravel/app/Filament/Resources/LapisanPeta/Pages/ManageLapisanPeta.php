<?php

namespace App\Filament\Resources\LapisanPeta\Pages;

use App\Filament\Resources\LapisanPeta\LapisanPetaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLapisanPeta extends ManageRecords
{
    protected static string $resource = LapisanPetaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
