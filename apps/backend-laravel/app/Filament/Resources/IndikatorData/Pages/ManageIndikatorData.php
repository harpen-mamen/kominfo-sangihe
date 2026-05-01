<?php

namespace App\Filament\Resources\IndikatorData\Pages;

use App\Filament\Resources\IndikatorData\IndikatorDataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageIndikatorData extends ManageRecords
{
    protected static string $resource = IndikatorDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => IndikatorDataResource::canCreate())
                ->mutateDataUsing(fn (array $data): array => IndikatorDataResource::mutateIndikatorData($data)),
        ];
    }
}
