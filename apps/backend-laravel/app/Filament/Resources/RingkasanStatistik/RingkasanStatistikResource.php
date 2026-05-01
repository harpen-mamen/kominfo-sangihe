<?php

namespace App\Filament\Resources\RingkasanStatistik;

use App\Filament\Resources\RingkasanStatistik\Pages\ManageRingkasanStatistik;
use App\Models\RingkasanStatistik;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
use App\Support\ResourceOptions;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RingkasanStatistikResource extends Resource
{
    protected static ?string $model = RingkasanStatistik::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static string|\UnitEnum|null $navigationGroup = 'Statistik Daerah';

    protected static ?string $modelLabel = 'Statistik Daerah';

    protected static ?string $pluralModelLabel = 'Statistik Daerah';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::canAccessStatisticsSummary();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::canAccessStatisticsSummary();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $user = FilamentWorkspace::user();

                if ($user && ! FilamentWorkspace::isKominfo()) {
                    $query->whereIn('id', AdminScope::ringkasanStatistikQuery($user)->select('id'));
                }

                return $query->with(['periodeData', 'kecamatan', 'desa', 'opd', 'indikatorData']);
            })
            ->columns([
                TextColumn::make('periodeData.label')->label('Periode')->sortable(),
                TextColumn::make('indikatorData.nama')->label('Indikator')->searchable(),
                TextColumn::make('tingkat_rekap')->badge(),
                TextColumn::make('kecamatan.nama')->label('Kecamatan')->placeholder('Kabupaten'),
                TextColumn::make('opd.nama')->label('OPD')->placeholder('-'),
                TextColumn::make('desa.nama')->label('Desa')->placeholder('-'),
                TextColumn::make('nilai_total')->numeric(decimalPlaces: 2)->sortable(),
                TextColumn::make('nilai_persen')->numeric(decimalPlaces: 2)->toggleable(),
            ])
            ->filters([
                SelectFilter::make('tingkat_rekap')->options(ResourceOptions::tingkatRekap()),
                SelectFilter::make('periodeData')->relationship('periodeData', 'label')->searchable()->preload(),
                SelectFilter::make('kecamatan')->relationship('kecamatan', 'nama')->searchable()->preload(),
                SelectFilter::make('opd')->relationship('opd', 'nama')->searchable()->preload(),
                SelectFilter::make('desa')->relationship('desa', 'nama')->searchable()->preload(),
                SelectFilter::make('indikatorData')->relationship('indikatorData', 'nama')->searchable()->preload(),
            ])
            ->recordActions([ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageRingkasanStatistik::route('/')];
    }
}
