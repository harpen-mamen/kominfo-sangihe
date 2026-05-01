<?php

namespace App\Filament\Resources\SumberData\Pages;

use App\Filament\Resources\SumberData\SumberDataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSumberData extends ManageRecords
{
    protected static string $resource = SumberDataResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
