<?php

namespace App\Filament\Resources\NilaiDataMentah\Pages;

use App\Filament\Resources\NilaiDataMentah\NilaiDataMentahResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNilaiDataMentah extends ManageRecords
{
    protected static string $resource = NilaiDataMentahResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
