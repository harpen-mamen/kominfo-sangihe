<?php

namespace App\Filament\Resources\RingkasanStatistik\Widgets;

use App\Filament\Resources\RingkasanStatistik\Widgets\Concerns\InteractsWithRingkasanStatistik;
use App\Models\PengajuanData;
use App\Support\FilamentWorkspace;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class RingkasanStatistikKpi extends StatsOverviewWidget
{
    use InteractsWithRingkasanStatistik;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = FilamentWorkspace::user();

        $validatedSubmissions = PengajuanData::query()
            ->when(
                $user && FilamentWorkspace::isSubdistrict() && $user->kecamatan_id,
                fn (Builder $query): Builder => $query->where('kecamatan_id', $user->kecamatan_id),
            )
            ->whereIn('status', ['terverifikasi', 'terbit'])
            ->count();

        $latest = $this->scopedRingkasanQuery()
            ->with('periodeData')
            ->orderByDesc('periode_data_id')
            ->first();

        return [
            Stat::make('Pengajuan Valid', $validatedSubmissions)
                ->description('Sumber data terverifikasi/terbit')
                ->descriptionIcon('heroicon-m-check-badge', IconPosition::Before)
                ->color('success'),
            Stat::make('Indikator Terolah', $this->scopedRingkasanQuery()->distinct('indikator_data_id')->count('indikator_data_id'))
                ->description('Indikator dengan hasil agregasi')
                ->descriptionIcon('heroicon-m-chart-bar-square', IconPosition::Before)
                ->color('primary'),
            Stat::make('Baris Rekap', $this->scopedRingkasanQuery()->count())
                ->description('Desa, kecamatan, dan kabupaten')
                ->descriptionIcon('heroicon-m-table-cells', IconPosition::Before)
                ->color('info'),
            Stat::make('Periode Terbaru', $latest?->periodeData?->label ?? '-')
                ->description('Terakhir dihitung sistem')
                ->descriptionIcon('heroicon-m-calendar-days', IconPosition::Before)
                ->color('warning'),
        ];
    }
}
