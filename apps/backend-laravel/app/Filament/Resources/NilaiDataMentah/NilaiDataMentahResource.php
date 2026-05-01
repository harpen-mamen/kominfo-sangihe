<?php

namespace App\Filament\Resources\NilaiDataMentah;

use App\Filament\Resources\NilaiDataMentah\Pages\ManageNilaiDataMentah;
use App\Models\Desa;
use App\Models\IndikatorData;
use App\Models\NilaiDataMentah;
use App\Models\PengajuanData;
use App\Services\SumberDataFilterService;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NilaiDataMentahResource extends Resource
{
    protected static ?string $model = NilaiDataMentah::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|\UnitEnum|null $navigationGroup = 'Input & Verifikasi';

    protected static ?string $modelLabel = 'Nilai Data Mentah';

    protected static ?string $pluralModelLabel = 'Nilai Data Mentah';

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::canAccessWorkflow();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::canAccessWorkflow();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Entri Nilai Mentah')
                ->schema([
                    Select::make('pengajuan_data_id')
                        ->options(fn (): array => ($user = FilamentWorkspace::user())
                            ? AdminScope::pengajuanDataQuery($user)->orderByDesc('id')->pluck('id', 'id')->all()
                            : PengajuanData::query()->orderByDesc('id')->pluck('id', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('desa_id')
                        ->options(fn (): array => ($user = FilamentWorkspace::user())
                            ? AdminScope::desaQuery($user)->where('aktif', true)->orderBy('nama')->pluck('nama', 'id')->all()
                            : Desa::query()->where('aktif', true)->orderBy('nama')->pluck('nama', 'id')->all())
                        ->live()
                        ->afterStateUpdated(function (Set $set): void {
                            $set('sumber_data_id', null);
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('indikator_data_id')
                        ->options(fn (): array => static::indikatorOptions())
                        ->live()
                        ->afterStateUpdated(function (Set $set): void {
                            $set('sumber_data_id', null);
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('sumber_data_id')
                        ->options(fn (Get $get): array => static::sumberDataOptions(
                            $get('indikator_data_id'),
                            $get('desa_id'),
                        ))
                        ->disabled(fn (Get $get): bool => blank($get('indikator_data_id')))
                        ->placeholder(fn (Get $get): string => blank($get('indikator_data_id'))
                            ? 'Pilih indikator terlebih dahulu'
                            : 'Pilih sumber data')
                        ->helperText(fn (Get $get): ?string => static::sumberDataHelperText($get('indikator_data_id')))
                        ->noOptionsMessage('Belum ada sumber data yang sesuai untuk indikator dan kecamatan ini.')
                        ->searchable()
                        ->preload(),
                    TextInput::make('nilai')->numeric()->required()->minValue(0),
                    Textarea::make('catatan')->rows(3)->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $user = FilamentWorkspace::user();

                if ($user && ! FilamentWorkspace::isKominfo()) {
                    $query->whereIn('id', AdminScope::nilaiDataMentahQuery($user)->select('id'));
                }

                return $query->with(['pengajuanData.kecamatan', 'pengajuanData.opd', 'desa', 'indikatorData', 'sumberData']);
            })
            ->columns([
                TextColumn::make('pengajuanData.kecamatan.nama')->label('Kecamatan')->placeholder('-'),
                TextColumn::make('pengajuanData.opd.nama')->label('OPD')->placeholder('-'),
                TextColumn::make('desa.nama')->label('Desa')->searchable(),
                TextColumn::make('indikatorData.nama')->label('Indikator')->searchable(),
                TextColumn::make('sumberData.nama')->label('Sumber Data')->placeholder('-'),
                TextColumn::make('nilai')->numeric(decimalPlaces: 2)->sortable(),
                TextColumn::make('updated_at')->dateTime('d M Y H:i')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('desa_id')->label('Desa')->options(fn (): array => ($user = FilamentWorkspace::user())
                    ? AdminScope::desaQuery($user)->where('aktif', true)->orderBy('nama')->pluck('nama', 'id')->all()
                    : Desa::query()->where('aktif', true)->orderBy('nama')->pluck('nama', 'id')->all())->searchable()->preload(),
                SelectFilter::make('indikator_data_id')->label('Indikator')->options(fn (): array => static::indikatorOptions())->searchable()->preload(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNilaiDataMentah::route('/')];
    }

    /**
     * @return array<int|string, string>
     */
    public static function indikatorOptions(): array
    {
        $user = FilamentWorkspace::user();

        $query = $user
            ? AdminScope::indikatorDataQuery($user, forInput: true)
            : IndikatorData::query()->where('aktif', true);

        return $query
            ->orderBy('urutan')
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public static function sumberDataOptions(mixed $indikatorId = null, mixed $desaId = null): array
    {
        return app(SumberDataFilterService::class)
            ->optionsForIndikatorId($indikatorId, FilamentWorkspace::user(), $desaId);
    }

    public static function sumberDataHelperText(mixed $indikatorId = null): ?string
    {
        return app(SumberDataFilterService::class)->helperTextForIndikatorId($indikatorId);
    }
}
