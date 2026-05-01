<?php

namespace App\Filament\Resources\PengajuanData\Pages;

use App\Filament\Resources\PengajuanData\PengajuanDataResource;
use App\Models\PengajuanData;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ManagePengajuanData extends ManageRecords
{
    protected static string $resource = PengajuanDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(fn (array $data): array => PengajuanDataResource::mutatePengajuanData($data))
                ->using(fn (array $data, string $model): Model => PengajuanData::updateOrCreate(
                    [
                        'kecamatan_id' => $data['kecamatan_id'] ?? null,
                        'opd_id' => $data['opd_id'] ?? null,
                        'periode_data_id' => $data['periode_data_id'],
                    ],
                    Arr::except($data, ['nilaiDataMentah']),
                )),
        ];
    }
}
