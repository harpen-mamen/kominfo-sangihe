<?php

namespace App\Filament\Resources\Berita;

use App\Filament\Resources\Berita\Pages\ManageBerita;
use App\Models\Berita;
use App\Support\AdminFormMutation;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
use App\Support\ResourceOptions;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BeritaResource extends Resource
{
    protected static ?string $model = Berita::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten Publik';

    protected static ?string $modelLabel = 'Berita';

    protected static ?string $pluralModelLabel = 'Berita';

    protected static ?string $recordTitleAttribute = 'judul';

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::canManageNews();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::canManageNews();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Konten Berita')
                ->schema([
                    TextInput::make('judul')->required()->maxLength(200),
                    TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(220),
                    Textarea::make('ringkasan')->rows(3)->columnSpanFull(),
                    Textarea::make('isi')->required()->rows(8)->columnSpanFull(),
                    TextInput::make('gambar_sampul')->url()->maxLength(255)->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Publikasi dan Lokasi')
                ->schema([
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
                    Hidden::make('penulis_id')->default(fn () => auth()->id()),
                    Select::make('status')
                        ->options(fn (): array => ! FilamentWorkspace::isKominfo()
                            ? ['diajukan' => ResourceOptions::statusData()['diajukan']]
                            : ResourceOptions::statusData())
                        ->default(fn (): string => ! FilamentWorkspace::isKominfo() ? 'diajukan' : 'draft')
                        ->formatStateUsing(fn (?string $state): string => ! FilamentWorkspace::isKominfo() ? 'diajukan' : ($state ?: 'draft'))
                        ->dehydrateStateUsing(fn (?string $state): string => ! FilamentWorkspace::isKominfo() ? 'diajukan' : ($state ?: 'draft'))
                        ->disabled(fn (): bool => ! FilamentWorkspace::isKominfo())
                        ->helperText(fn (): ?string => ! FilamentWorkspace::isKominfo()
                            ? 'Konten dari kecamatan dan OPD dikirim untuk ditinjau Kominfo.'
                            : null)
                        ->required(),
                    Select::make('ditinjau_oleh')
                        ->relationship('ditinjauOleh', 'nama')
                        ->searchable()
                        ->preload()
                        ->visible(fn (): bool => FilamentWorkspace::isKominfo()),
                    DateTimePicker::make('tanggal_terbit')
                        ->visible(fn (): bool => FilamentWorkspace::isKominfo()),
                    Toggle::make('unggulan')
                        ->default(false)
                        ->visible(fn (): bool => FilamentWorkspace::isKominfo()),
                    TextInput::make('lokasi')->maxLength(255),
                    TextInput::make('latitude')
                        ->numeric()
                        ->extraInputAttributes([
                            'data-map-latitude-input' => 'true',
                            'step' => '0.00000001',
                        ]),
                    TextInput::make('longitude')
                        ->numeric()
                        ->extraInputAttributes([
                            'data-map-longitude-input' => 'true',
                            'step' => '0.00000001',
                        ]),
                    SchemaView::make('filament.forms.components.map-coordinate-picker')->columnSpanFull(),
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
                    $query->whereIn('id', AdminScope::beritaQuery($user)->select('id'));
                }

                return $query->with(['kecamatan', 'opd', 'penulis']);
            })
            ->columns([
                TextColumn::make('judul')->searchable()->sortable()->limit(45),
                TextColumn::make('status')->badge(),
                IconColumn::make('unggulan')->boolean(),
                TextColumn::make('kecamatan.nama')->label('Kecamatan')->placeholder('-'),
                TextColumn::make('opd.nama')->label('OPD')->placeholder('-'),
                TextColumn::make('tanggal_terbit')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ResourceOptions::statusData()),
                SelectFilter::make('kecamatan')->relationship('kecamatan', 'nama')->searchable()->preload(),
                SelectFilter::make('opd')->relationship('opd', 'nama')->searchable()->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => AdminFormMutation::sanitizeKontenPublik(
                        $data,
                        ! FilamentWorkspace::isKominfo(),
                    )),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageBerita::route('/')];
    }
}
