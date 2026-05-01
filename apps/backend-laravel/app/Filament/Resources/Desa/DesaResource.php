<?php

namespace App\Filament\Resources\Desa;

use App\Filament\Resources\Desa\Pages\ManageDesa;
use App\Models\Desa;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
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

class DesaResource extends Resource
{
    protected static ?string $model = Desa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Wilayah';

    protected static ?string $modelLabel = 'Desa';

    protected static ?string $pluralModelLabel = 'Desa/Kelurahan';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::canAccessMasterData() || FilamentWorkspace::isSubdistrict();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::canAccessMasterData();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Master Desa/Kelurahan')
                ->schema([
                    Select::make('kecamatan_id')
                        ->relationship('kecamatan', 'nama')
                        ->default(fn () => auth()->user()?->kecamatan_id)
                        ->disabled(fn (): bool => FilamentWorkspace::isSubdistrict())
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('kode')->required()->unique(ignoreRecord: true)->maxLength(20),
                    TextInput::make('nama')->required()->maxLength(150),
                    Toggle::make('aktif')->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $user = FilamentWorkspace::user();

                if ($user && FilamentWorkspace::isSubdistrict()) {
                    $query->whereIn('id', AdminScope::desaQuery($user)->select('id'));
                }

                return $query->with('kecamatan');
            })
            ->columns([
                TextColumn::make('kode')->searchable()->sortable(),
                TextColumn::make('nama')->searchable()->sortable(),
                TextColumn::make('kecamatan.nama')->label('Kecamatan')->searchable()->sortable(),
                IconColumn::make('aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('kecamatan')->relationship('kecamatan', 'nama')->searchable()->preload(),
                TernaryFilter::make('aktif'),
            ])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageDesa::route('/')];
    }
}
