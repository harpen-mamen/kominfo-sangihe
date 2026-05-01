<?php

namespace App\Filament\Resources\PeriodeData\Pages;

use App\Filament\Resources\PeriodeData\PeriodeDataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePeriodeData extends ManageRecords
{
    protected static string $resource = PeriodeDataResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
