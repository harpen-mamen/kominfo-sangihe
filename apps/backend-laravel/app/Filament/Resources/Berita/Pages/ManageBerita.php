<?php

namespace App\Filament\Resources\Berita\Pages;

use App\Filament\Resources\Berita\BeritaResource;
use App\Support\AdminFormMutation;
use App\Support\FilamentWorkspace;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBerita extends ManageRecords
{
    protected static string $resource = BeritaResource::class;

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
