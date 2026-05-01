<?php

namespace App\Filament\Resources\FiturPeta;

use App\Filament\Resources\FiturPeta\Pages\ManageFiturPeta;
use App\Models\Konten;
use App\Models\LapisanPeta;
use App\Models\FiturPeta;
use App\Models\SumberData;
use App\Support\FilamentWorkspace;
use App\Support\AdminScope;
use App\Support\ResourceOptions;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FiturPetaResource extends Resource
{
    protected static ?string $model = FiturPeta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'Peta';

    protected static ?string $modelLabel = 'Fitur Peta';

    protected static ?string $pluralModelLabel = 'Fitur Peta';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::canAccessMapFeatures();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::canAccessMapFeatures();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Objek Peta')
                ->schema([
                    Select::make('lapisan_peta_id')
                        ->options(fn (): array => LapisanPeta::query()
                            ->when(! FilamentWorkspace::isKominfo(), fn (Builder $query) => $query->where('hanya_admin_kominfo', false))
                            ->where('aktif', true)
                            ->orderBy('urutan')
                            ->pluck('nama', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->required(),
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
                    Select::make('sumber_data_id')
                        ->options(fn (): array => ($user = FilamentWorkspace::user())
                            ? AdminScope::sumberDataQuery($user)->orderBy('nama')->pluck('nama', 'id')->all()
                            : SumberData::query()->orderBy('nama')->pluck('nama', 'id')->all())
                        ->searchable()
                        ->preload(),
                    Select::make('konten_id')
                        ->options(fn (): array => Konten::query()->orderByDesc('created_at')->pluck('judul', 'id')->all())
                        ->searchable()
                        ->preload(),
                    Hidden::make('dibuat_oleh')->default(fn () => auth()->id()),
                    TextInput::make('nama')->required()->maxLength(180),
                    Select::make('jenis_geometri')
                        ->options(ResourceOptions::jenisGeometri())
                        ->default('point')
                        ->live()
                        ->required(),
                    Select::make('sumber_input')
                        ->options(ResourceOptions::sumberInput())
                        ->default('manual')
                        ->required(),
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
                    Toggle::make('aktif')->default(true),
                    SchemaView::make('filament.forms.components.map-coordinate-picker')
                        ->visible(fn (Get $get): bool => ($get('jenis_geometri') ?? 'point') === 'point')
                        ->columnSpanFull(),
                    TextInput::make('file_path')->maxLength(255),
                    Textarea::make('geojson')
                        ->label('GeoJSON Geometry')
                        ->rows(6)
                        ->extraInputAttributes(['data-map-geojson-input' => 'true'])
                        ->helperText('Untuk titik, sistem otomatis membuat geometry Point dari latitude dan longitude.')
                        ->columnSpanFull(),
                    Textarea::make('properti_json')
                        ->label('Properti JSON')
                        ->rows(5)
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
            ->modifyQueryUsing(function (Builder $query): Builder {
                $user = FilamentWorkspace::user();

                if ($user) {
                    $query->whereIn('id', AdminScope::fiturPetaQuery($user)->select('id'));
                }

                return $query->with(['lapisanPeta', 'kecamatan', 'desa', 'opd']);
            })
            ->columns([
                TextColumn::make('nama')->searchable()->sortable(),
                TextColumn::make('lapisanPeta.nama')->label('Lapisan')->sortable(),
                TextColumn::make('kecamatan.nama')->label('Kecamatan')->placeholder('-'),
                TextColumn::make('desa.nama')->label('Desa')->placeholder('-'),
                TextColumn::make('opd.nama')->label('OPD')->placeholder('-'),
                TextColumn::make('jenis_geometri')->badge(),
                TextColumn::make('sumber_input')->badge(),
                TextColumn::make('latitude')->label('Lat')->toggleable(),
                TextColumn::make('longitude')->label('Lng')->toggleable(),
                IconColumn::make('aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('lapisanPeta')->relationship('lapisanPeta', 'nama')->searchable()->preload(),
                SelectFilter::make('kecamatan')->relationship('kecamatan', 'nama')->searchable()->preload(),
                SelectFilter::make('opd')->relationship('opd', 'nama')->searchable()->preload(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageFiturPeta::route('/')];
    }
}
