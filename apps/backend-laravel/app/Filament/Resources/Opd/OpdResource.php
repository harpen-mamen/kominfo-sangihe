<?php

namespace App\Filament\Resources\Opd;

use App\Filament\Resources\Opd\Pages\ManageOpd;
use App\Models\Opd;
use App\Support\FilamentWorkspace;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
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

class OpdResource extends Resource
{
    protected static ?string $model = Opd::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $modelLabel = 'OPD';

    protected static ?string $pluralModelLabel = 'OPD';

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
            Section::make('Master OPD')
                ->schema([
                    TextInput::make('kode')->required()->unique(ignoreRecord: true)->maxLength(30),
                    TextInput::make('nama')->required()->maxLength(150),
                    Toggle::make('aktif')->default(true),
                    Textarea::make('keterangan')->rows(4)->columnSpanFull(),
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
                IconColumn::make('aktif')->boolean(),
                TextColumn::make('updated_at')->dateTime('d M Y H:i')->sortable()->toggleable(),
            ])
            ->filters([TernaryFilter::make('aktif')])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageOpd::route('/')];
    }
}
