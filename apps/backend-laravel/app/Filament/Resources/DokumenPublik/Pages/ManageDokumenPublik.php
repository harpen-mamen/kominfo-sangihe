<?php

namespace App\Filament\Resources\DokumenPublik\Pages;

use App\Filament\Resources\DokumenPublik\DokumenPublikResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDokumenPublik extends ManageRecords
{
    protected static string $resource = DokumenPublikResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
