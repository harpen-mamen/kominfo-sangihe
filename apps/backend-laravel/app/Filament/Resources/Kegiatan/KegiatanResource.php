<?php

namespace App\Filament\Resources\Kegiatan;

use App\Filament\Resources\Kegiatan\Pages\ManageKegiatan;
use App\Models\Kegiatan;
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
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KegiatanResource extends Resource
{
    protected static ?string $model = Kegiatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten Publik';

    protected static ?string $modelLabel = 'Kegiatan';

    protected static ?string $pluralModelLabel = 'Kegiatan';

    protected static ?string $recordTitleAttribute = 'judul';

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::canManageSubdistrictAgenda();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::canManageSubdistrictAgenda();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Agenda Kegiatan')
                ->schema([
                    TextInput::make('judul')->required()->maxLength(200),
                    TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(220),
                    Textarea::make('uraian')->required()->rows(6)->columnSpanFull(),
                    DateTimePicker::make('mulai')->required(),
                    DateTimePicker::make('selesai'),
                    TextInput::make('lokasi')->maxLength(255),
                ])
                ->columns(2),
            Section::make('Publikasi')
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
                    Hidden::make('pembuat_id')->default(fn () => auth()->id()),
                    Select::make('status')
                        ->options(fn (): array => ! FilamentWorkspace::isKominfo()
                            ? ['diajukan' => ResourceOptions::statusData()['diajukan']]
                            : ResourceOptions::statusData())
                        ->default(fn (): string => ! FilamentWorkspace::isKominfo() ? 'diajukan' : 'draft')
                        ->formatStateUsing(fn (?string $state): string => ! FilamentWorkspace::isKominfo() ? 'diajukan' : ($state ?: 'draft'))
                        ->dehydrateStateUsing(fn (?string $state): string => ! FilamentWorkspace::isKominfo() ? 'diajukan' : ($state ?: 'draft'))
                        ->disabled(fn (): bool => ! FilamentWorkspace::isKominfo())
                        ->helperText(fn (): ?string => ! FilamentWorkspace::isKominfo()
                            ? 'Kegiatan dari kecamatan dan OPD dikirim untuk ditinjau Kominfo.'
                            : null)
                        ->required(),
                    Select::make('ditinjau_oleh')
                        ->relationship('ditinjauOleh', 'nama')
                        ->searchable()
                        ->preload()
                        ->visible(fn (): bool => FilamentWorkspace::isKominfo()),
                    DateTimePicker::make('tanggal_terbit')
                        ->visible(fn (): bool => FilamentWorkspace::isKominfo()),
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
                    $query->whereIn('id', AdminScope::kegiatanQuery($user)->select('id'));
                }

                return $query->with(['kecamatan', 'opd', 'pembuat']);
            })
            ->columns([
                TextColumn::make('judul')->searchable()->sortable()->limit(45),
                TextColumn::make('status')->badge(),
                TextColumn::make('mulai')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('kecamatan.nama')->label('Kecamatan')->placeholder('-'),
                TextColumn::make('opd.nama')->label('OPD')->placeholder('-'),
                TextColumn::make('tanggal_terbit')->dateTime('d M Y H:i')->toggleable(),
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
        return ['index' => ManageKegiatan::route('/')];
    }
}
