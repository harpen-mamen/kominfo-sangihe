<?php

namespace App\Filament\Resources\SumberData;

use App\Filament\Resources\SumberData\Pages\ManageSumberData;
use App\Models\SumberData;
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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SumberDataResource extends Resource
{
    protected static ?string $model = SumberData::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $modelLabel = 'Sumber Data';

    protected static ?string $pluralModelLabel = 'Sumber Data';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::canAccessReferenceSources();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::canAccessReferenceSources();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Master Sumber Data')
                ->schema([
                    TextInput::make('nama')->required()->maxLength(180),
                    Select::make('jenis')->options(ResourceOptions::jenisSumberData())->required(),
                    Select::make('kecamatan_id')
                        ->relationship('kecamatan', 'nama')
                        ->default(fn () => auth()->user()?->kecamatan_id)
                        ->disabled(fn (): bool => FilamentWorkspace::isSubdistrict())
                        ->searchable()
                        ->preload(),
                    Select::make('desa_id')->relationship('desa', 'nama')->searchable()->preload(),
                    Select::make('opd_id')
                        ->relationship('opd', 'nama')
                        ->default(fn () => auth()->user()?->opd_id)
                        ->disabled(fn (): bool => FilamentWorkspace::isDepartment())
                        ->searchable()
                        ->preload(),
                    Textarea::make('alamat')->rows(3)->columnSpanFull(),
                    TextInput::make('kontak')->maxLength(150),
                    TextInput::make('latitude')
                        ->numeric()
                        ->minValue(-90)
                        ->maxValue(90)
                        ->extraInputAttributes([
                            'data-map-latitude-input' => 'true',
                            'step' => '0.00000001',
                        ]),
                    TextInput::make('longitude')
                        ->numeric()
                        ->minValue(-180)
                        ->maxValue(180)
                        ->extraInputAttributes([
                            'data-map-longitude-input' => 'true',
                            'step' => '0.00000001',
                        ]),
                    SchemaView::make('filament.forms.components.map-coordinate-picker')->columnSpanFull(),
                    Textarea::make('keterangan')->rows(4)->columnSpanFull(),
                    Toggle::make('aktif')->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $user = FilamentWorkspace::user();

                if ($user) {
                    $query->whereIn('id', AdminScope::sumberDataQuery($user)->select('id'));
                }

                return $query->with(['kecamatan', 'desa', 'opd']);
            })
            ->columns([
                TextColumn::make('nama')->searchable()->sortable(),
                TextColumn::make('jenis')->badge()->sortable(),
                TextColumn::make('kecamatan.nama')->label('Kecamatan')->placeholder('-')->sortable(),
                TextColumn::make('desa.nama')->label('Desa')->placeholder('-')->sortable(),
                TextColumn::make('opd.nama')->label('OPD')->placeholder('-')->sortable(),
                TextColumn::make('kontak')->toggleable(),
                IconColumn::make('aktif')->boolean(),
                TextColumn::make('updated_at')->dateTime('d M Y H:i')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('jenis')->options(ResourceOptions::jenisSumberData()),
                SelectFilter::make('kecamatan')->relationship('kecamatan', 'nama')->searchable()->preload(),
                SelectFilter::make('opd')->relationship('opd', 'nama')->searchable()->preload(),
                TernaryFilter::make('aktif'),
            ])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSumberData::route('/')];
    }
}
