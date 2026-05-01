<?php

namespace App\Filament\Resources\PeriodeData;

use App\Filament\Resources\PeriodeData\Pages\ManagePeriodeData;
use App\Models\PeriodeData;
use App\Support\FilamentWorkspace;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PeriodeDataResource extends Resource
{
    protected static ?string $model = PeriodeData::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $modelLabel = 'Periode Data';

    protected static ?string $pluralModelLabel = 'Periode Data';

    protected static ?string $recordTitleAttribute = 'label';

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::isKominfo();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::isKominfo();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Periode Bulanan')
                ->schema([
                    TextInput::make('tahun')->numeric()->required()->minValue(2000)->maxValue(2100),
                    TextInput::make('bulan')->numeric()->required()->minValue(1)->maxValue(12),
                    TextInput::make('label')->required()->maxLength(50),
                    DatePicker::make('tanggal_mulai')->required(),
                    DatePicker::make('tanggal_selesai')->required(),
                    Toggle::make('terkunci')->default(false),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->searchable()->sortable(),
                TextColumn::make('tahun')->sortable(),
                TextColumn::make('bulan')->sortable(),
                TextColumn::make('tanggal_mulai')->date('d M Y'),
                TextColumn::make('tanggal_selesai')->date('d M Y'),
                IconColumn::make('terkunci')->boolean(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePeriodeData::route('/')];
    }
}
