<?php

namespace App\Filament\Resources\PengajuanData\Pages;

use App\Filament\Resources\PengajuanData\PengajuanDataResource;
use App\Models\PengajuanData;
use App\Support\FilamentWorkspace;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ManagePengajuanData extends ManageRecords
{
    protected static string $resource = PengajuanDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pending_review_notice')
                ->label(fn (): string => PengajuanDataResource::countForStatus('diajukan') . ' menunggu peninjauan')
                ->icon('heroicon-o-bell-alert')
                ->color('warning')
                ->url(fn (): string => PengajuanDataResource::getUrl('index', ['tab' => 'diajukan']))
                ->visible(fn (): bool => FilamentWorkspace::isKominfo() && PengajuanDataResource::countForStatus('diajukan') > 0),

            CreateAction::make()
                ->visible(fn (): bool => FilamentWorkspace::isSubdistrict() || FilamentWorkspace::isDepartment())
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

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn (): int => PengajuanDataResource::countForStatus()),

            'diajukan' => Tab::make('Menunggu Peninjauan')
                ->icon('heroicon-o-bell-alert')
                ->badge(fn (): int => PengajuanDataResource::countForStatus('diajukan'))
                ->badgeColor(fn (): string => PengajuanDataResource::countForStatus('diajukan') > 0 ? 'warning' : 'gray')
                ->query(fn (Builder $query): Builder => $query->where('status', 'diajukan')),

            'revisi' => Tab::make('Revisi')
                ->badge(fn (): int => PengajuanDataResource::countForStatus('revisi'))
                ->badgeColor('warning')
                ->query(fn (Builder $query): Builder => $query->where('status', 'revisi')),

            'terverifikasi' => Tab::make('Terverifikasi')
                ->badge(fn (): int => PengajuanDataResource::countForStatus('terverifikasi'))
                ->badgeColor('success')
                ->query(fn (Builder $query): Builder => $query->where('status', 'terverifikasi')),

            'terbit' => Tab::make('Terbit')
                ->badge(fn (): int => PengajuanDataResource::countForStatus('terbit'))
                ->badgeColor('primary')
                ->query(fn (Builder $query): Builder => $query->where('status', 'terbit')),
        ];
    }
}
