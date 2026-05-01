<?php

namespace App\Filament\Resources\Opd\Pages;

use App\Filament\Resources\Opd\OpdResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOpd extends ManageRecords
{
    protected static string $resource = OpdResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
