<?php

namespace App\Filament\Resources\IndikatorData;

use App\Filament\Resources\IndikatorData\Pages\ManageIndikatorData;
use App\Models\IndikatorData;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
use App\Support\ResourceOptions;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class IndikatorDataResource extends Resource
{
    protected static ?string $model = IndikatorData::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $modelLabel = 'Indikator Data';

    protected static ?string $pluralModelLabel = 'Indikator Data';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function canViewAny(): bool
    {
        return (bool) FilamentWorkspace::user()?->can('ViewAny:IndikatorData')
            && FilamentWorkspace::canAccessIndicators();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return (bool) FilamentWorkspace::user()?->can('Create:IndikatorData')
            && FilamentWorkspace::canManageIndicators();
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) FilamentWorkspace::user()?->can('Update:IndikatorData')
            && (FilamentWorkspace::isKominfo()
                || (FilamentWorkspace::isDepartment() && (int) $record->opd_id === (int) auth()->user()?->opd_id));
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) FilamentWorkspace::user()?->can('Delete:IndikatorData')
            && (FilamentWorkspace::isKominfo()
                || (FilamentWorkspace::isDepartment() && (int) $record->opd_id === (int) auth()->user()?->opd_id));
    }

    public static function canDeleteAny(): bool
    {
        return (bool) FilamentWorkspace::user()?->can('DeleteAny:IndikatorData')
            && FilamentWorkspace::canManageIndicators();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Indikator Master Data Generik')
                ->schema([
                    TextInput::make('kode')->required()->unique(ignoreRecord: true)->maxLength(80),
                    TextInput::make('nama')->required()->maxLength(180),
                    Select::make('kelompok')->options(ResourceOptions::kelompokIndikator())->required(),
                    TextInput::make('satuan')->required()->maxLength(30),
                    Select::make('level_input')->options(ResourceOptions::levelInput())->default('desa')->required(),
                    Select::make('opd_id')
                        ->label('OPD Pemilik')
                        ->relationship('opd', 'nama')
                        ->default(fn () => auth()->user()?->opd_id)
                        ->disabled(fn (): bool => FilamentWorkspace::isDepartment())
                        ->dehydrated()
                        ->searchable()
                        ->preload()
                        ->visible(fn (): bool => FilamentWorkspace::isKominfo() || FilamentWorkspace::isDepartment()),
                    Toggle::make('boleh_diinput_opd')
                        ->label('Boleh Diinput OPD')
                        ->default(true),
                    Toggle::make('boleh_diinput_kecamatan')
                        ->label('Boleh Diinput Kecamatan')
                        ->default(false),
                    TextInput::make('urutan')->numeric()->default(0),
                    Toggle::make('aktif')->default(true),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $user = FilamentWorkspace::user();

                if ($user && ! FilamentWorkspace::isKominfo()) {
                    $query->whereIn('id', AdminScope::indikatorDataQuery($user)->select('id'));
                }

                return $query->with('opd');
            })
            ->columns([
                TextColumn::make('kode')->searchable()->sortable(),
                TextColumn::make('nama')->searchable()->sortable(),
                TextColumn::make('opd.nama')->label('OPD')->placeholder('Umum')->searchable()->sortable(),
                TextColumn::make('kelompok')->badge()->sortable(),
                TextColumn::make('satuan')->badge(),
                TextColumn::make('level_input')->badge(),
                IconColumn::make('boleh_diinput_opd')->label('Input OPD')->boolean(),
                IconColumn::make('boleh_diinput_kecamatan')->label('Input Kecamatan')->boolean(),
                IconColumn::make('aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('opd')->relationship('opd', 'nama')->searchable()->preload(),
                SelectFilter::make('kelompok')->options(ResourceOptions::kelompokIndikator()),
                TernaryFilter::make('aktif'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => static::mutateIndikatorData($data))
                    ->visible(fn (IndikatorData $record): bool => static::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (IndikatorData $record): bool => static::canDelete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => static::canDeleteAny()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageIndikatorData::route('/')];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mutateIndikatorData(array $data): array
    {
        if (FilamentWorkspace::isDepartment() && auth()->user()?->opd_id) {
            $data['opd_id'] = auth()->user()->opd_id;
        }

        return $data;
    }
}
