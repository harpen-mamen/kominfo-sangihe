<?php

namespace App\Filament\Resources\RingkasanStatistik\Pages;

use App\Filament\Resources\RingkasanStatistik\RingkasanStatistikResource;
use App\Filament\Resources\RingkasanStatistik\Widgets\KomposisiWilayahChart;
use App\Filament\Resources\RingkasanStatistik\Widgets\PerbandinganWilayahChart;
use App\Filament\Resources\RingkasanStatistik\Widgets\RingkasanStatistikKpi;
use App\Filament\Resources\RingkasanStatistik\Widgets\TrenKabupatenChart;
use Filament\Resources\Pages\ManageRecords;

class ManageRingkasanStatistik extends ManageRecords
{
    protected static string $resource = RingkasanStatistikResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            RingkasanStatistikKpi::class,
            TrenKabupatenChart::class,
            PerbandinganWilayahChart::class,
            KomposisiWilayahChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'xl' => 2,
        ];
    }
}
