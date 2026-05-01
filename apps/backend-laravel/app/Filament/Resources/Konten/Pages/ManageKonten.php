<?php

namespace App\Filament\Resources\Konten\Pages;

use App\Filament\Resources\Konten\KontenResource;
use App\Support\AdminFormMutation;
use App\Support\FilamentWorkspace;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageKonten extends ManageRecords
{
    protected static string $resource = KontenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(fn (array $data): array => AdminFormMutation::sanitizeKontenPublik(
                    $data,
                    ! FilamentWorkspace::isKominfo(),
                )),
        ];
    }
}
