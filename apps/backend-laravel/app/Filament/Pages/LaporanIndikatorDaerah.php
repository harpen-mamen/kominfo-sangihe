<?php

namespace App\Filament\Pages;

use App\Filament\Pages\StatistikDaerah\StatistikDaerahPage;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class LaporanIndikatorDaerah extends StatistikDaerahPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'Laporan Indikator Daerah';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.laporan-indikator-daerah';
}
