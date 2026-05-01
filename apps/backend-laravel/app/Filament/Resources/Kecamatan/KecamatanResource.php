<?php

namespace App\Filament\Resources\Kecamatan;

use App\Filament\Resources\Kecamatan\Pages\ManageKecamatan;
use App\Models\Kecamatan;
use App\Support\FilamentWorkspace;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class KecamatanResource extends Resource
{
    protected static ?string $model = Kecamatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Wilayah';

    protected static ?string $modelLabel = 'Kecamatan';

    protected static ?string $pluralModelLabel = 'Kecamatan';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::canAccessMasterData();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::canAccessMasterData();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Master Kecamatan')
                ->schema([
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
            ->columns([
                TextColumn::make('kode')->searchable()->sortable(),
                TextColumn::make('nama')->searchable()->sortable(),
                TextColumn::make('desa_count')->counts('desa')->label('Desa'),
                IconColumn::make('aktif')->boolean(),
                TextColumn::make('updated_at')->dateTime('d M Y H:i')->sortable()->toggleable(),
            ])
            ->filters([TernaryFilter::make('aktif')])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageKecamatan::route('/')];
    }
}
