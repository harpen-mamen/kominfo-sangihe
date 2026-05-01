<?php

namespace App\Filament\Resources\Kegiatan\Pages;

use App\Filament\Resources\Kegiatan\KegiatanResource;
use App\Support\AdminFormMutation;
use App\Support\FilamentWorkspace;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageKegiatan extends ManageRecords
{
    protected static string $resource = KegiatanResource::class;

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
