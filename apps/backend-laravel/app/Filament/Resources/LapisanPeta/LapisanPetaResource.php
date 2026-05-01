<?php

namespace App\Filament\Resources\LapisanPeta;

use App\Filament\Resources\LapisanPeta\Pages\ManageLapisanPeta;
use App\Models\LapisanPeta;
use App\Support\FilamentWorkspace;
use App\Support\ResourceOptions;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LapisanPetaResource extends Resource
{
    protected static ?string $model = LapisanPeta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|\UnitEnum|null $navigationGroup = 'Peta';

    protected static ?string $modelLabel = 'Lapisan Peta';

    protected static ?string $pluralModelLabel = 'Lapisan Peta';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::canAccessMapLayers();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::canAccessMapLayers();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lapisan Peta')
                ->schema([
                    TextInput::make('nama')->required()->maxLength(150),
                    TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(180),
                    TextInput::make('kategori')->required()->maxLength(80),
                    Select::make('tipe_sumber')->options(ResourceOptions::tipeLapisan())->required(),
                    TextInput::make('urutan')->numeric()->default(0),
                    Toggle::make('hanya_admin_kominfo')->default(false),
                    Toggle::make('aktif')->default(true),
                    Textarea::make('konfigurasi_json')
                        ->label('Konfigurasi JSON')
                        ->rows(6)
                        ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : null)
                        ->dehydrateStateUsing(fn (?string $state) => blank($state) ? null : json_decode($state, true))
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('kategori')->badge(),
                TextColumn::make('tipe_sumber')->badge(),
                TextColumn::make('fitur_peta_count')->counts('fiturPeta')->label('Fitur'),
                TextColumn::make('urutan')->sortable(),
                IconColumn::make('hanya_admin_kominfo')->boolean()->label('Kominfo'),
                IconColumn::make('aktif')->boolean(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageLapisanPeta::route('/')];
    }
}
