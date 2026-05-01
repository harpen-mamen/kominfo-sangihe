<?php

namespace App\Filament\Resources\Desa\Pages;

use App\Filament\Resources\Desa\DesaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDesa extends ManageRecords
{
    protected static string $resource = DesaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
