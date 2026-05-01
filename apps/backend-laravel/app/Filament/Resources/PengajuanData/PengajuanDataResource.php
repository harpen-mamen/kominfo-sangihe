<?php

namespace App\Filament\Resources\PengajuanData;

use App\Filament\Resources\PengajuanData\Pages\ManagePengajuanData;
use App\Models\Desa;
use App\Models\IndikatorData;
use App\Models\PengajuanData;
use App\Models\PeriodeData;
use App\Models\SumberData;
use App\Services\WorkflowService;
use App\Support\AdminFormMutation;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
use App\Support\ResourceOptions;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Throwable;

class PengajuanDataResource extends Resource
{
    protected static ?string $model = PengajuanData::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Input & Verifikasi';

    protected static ?string $modelLabel = 'Pengajuan Data';

    protected static ?string $pluralModelLabel = 'Pengajuan Data Mentah';

    protected static ?string $recordTitleAttribute = 'id';

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
            Section::make('Header Pengajuan')
                ->schema([
                    Select::make('kecamatan_id')
                        ->relationship('kecamatan', 'nama')
                        ->default(fn () => auth()->user()?->kecamatan_id)
                        ->disabled(fn (): bool => FilamentWorkspace::isSubdistrict() || FilamentWorkspace::isDepartment())
                        ->visible(fn (): bool => ! FilamentWorkspace::isDepartment())
                        ->dehydrated()
                        ->searchable()
                        ->preload()
                        ->required(fn (): bool => ! FilamentWorkspace::isDepartment()),
                    Select::make('opd_id')
                        ->relationship('opd', 'nama')
                        ->default(fn () => auth()->user()?->opd_id)
                        ->disabled(fn (): bool => FilamentWorkspace::isDepartment())
                        ->dehydrated()
                        ->searchable()
                        ->preload()
                        ->visible(fn (): bool => FilamentWorkspace::isDepartment() || FilamentWorkspace::isKominfo())
                        ->required(fn (): bool => FilamentWorkspace::isDepartment()),
                    Select::make('periode_data_id')
                        ->options(fn (): array => PeriodeData::query()
                            ->where('terkunci', false)
                            ->orderByDesc('tahun')
                            ->orderByDesc('bulan')
                            ->pluck('label', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->required(),
                    Hidden::make('dikirim_oleh')->default(fn () => auth()->id()),
                    Select::make('status')
                        ->options(fn (): array => FilamentWorkspace::isSubdistrict() || FilamentWorkspace::isDepartment()
                            ? [
                                'draft' => ResourceOptions::statusData()['draft'],
                                'diajukan' => ResourceOptions::statusData()['diajukan'],
                            ]
                            : ResourceOptions::statusData())
                        ->default('draft')
                        ->dehydrateStateUsing(fn (?string $state): string => $state ?: 'draft')
                        ->required(),
                    Textarea::make('catatan')->rows(3)->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Nilai Data Mentah')
                ->schema([
                    Repeater::make('nilaiDataMentah')
                        ->relationship('nilaiDataMentah')
                        ->schema([
                            Select::make('desa_id')
                                ->label('Desa')
                                ->options(fn (): array => static::desaOptions())
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('indikator_data_id')
                                ->label('Indikator')
                                ->options(fn (): array => static::indikatorOptions())
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('sumber_data_id')
                                ->label('Sumber Data')
                                ->options(fn (): array => static::sumberDataOptions())
                                ->searchable()
                                ->preload(),
                            TextInput::make('nilai')->numeric()->required()->minValue(0),
                            Textarea::make('catatan')->rows(2)->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $user = FilamentWorkspace::user();

                if ($user) {
                    $scoped = AdminScope::pengajuanDataQuery($user);

                    if (! FilamentWorkspace::isKominfo()) {
                        $query->whereIn('id', $scoped->select('id'));
                    }
                }

                return $query->with(['kecamatan', 'opd', 'periodeData', 'dikirimOleh', 'diverifikasiOleh']);
            })
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('kecamatan.nama')->label('Kecamatan')->searchable()->sortable(),
                TextColumn::make('opd.nama')->label('OPD')->placeholder('-')->searchable()->sortable(),
                TextColumn::make('periodeData.label')->label('Periode')->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('dikirimOleh.nama')->label('Dikirim oleh'),
                TextColumn::make('tanggal_kirim')->dateTime('d M Y H:i')->toggleable(),
                TextColumn::make('tanggal_terbit')->dateTime('d M Y H:i')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ResourceOptions::statusData()),
                SelectFilter::make('kecamatan')->relationship('kecamatan', 'nama')->searchable()->preload(),
                SelectFilter::make('opd')->relationship('opd', 'nama')->searchable()->preload(),
                SelectFilter::make('periodeData')->relationship('periodeData', 'label')->searchable()->preload(),
            ])
            ->recordActions([
                Action::make('workflow')
                    ->label('Ubah Status')
                    ->color('warning')
                    ->schema([
                        Select::make('next_status')
                            ->label('Status berikutnya')
                            ->options(fn (PengajuanData $record): array => static::transitionOptions($record))
                            ->required(),
                        Textarea::make('catatan')->rows(3),
                    ])
                    ->action(function (PengajuanData $record, array $data): void {
                        try {
                            app(WorkflowService::class)->transition($record, $data['next_status'], auth()->user(), $data['catatan'] ?? null);
                            Notification::make()->title('Status pengajuan diperbarui.')->success()->send();
                        } catch (Throwable $exception) {
                            Notification::make()->title('Gagal memperbarui status.')->body($exception->getMessage())->danger()->send();
                        }
                    })
                    ->visible(fn (PengajuanData $record): bool => filled(static::transitionOptions($record))),
                ViewAction::make(),
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => static::mutatePengajuanData($data)),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePengajuanData::route('/')];
    }

    /**
     * @return array<string, string>
     */
    protected static function transitionOptions(PengajuanData $record): array
    {
        $allowed = app(WorkflowService::class)->transitions()[$record->status] ?? [];

        if (FilamentWorkspace::isSubdistrict() || FilamentWorkspace::isDepartment()) {
            $allowed = in_array($record->status, ['draft', 'revisi'], true) ? ['diajukan'] : [];
        }

        if (FilamentWorkspace::isKominfo() && $record->status === 'draft') {
            $allowed = [];
        }

        return collect($allowed)
            ->mapWithKeys(fn (string $status): array => [$status => ResourceOptions::statusData()[$status] ?? $status])
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public static function desaOptions(): array
    {
        $query = Desa::query()->where('aktif', true)->orderBy('nama');

        if (FilamentWorkspace::isSubdistrict() && auth()->user()?->kecamatan_id) {
            $query->where('kecamatan_id', auth()->user()->kecamatan_id);
        }

        return $query->pluck('nama', 'id')->all();
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
    public static function sumberDataOptions(): array
    {
        $query = SumberData::query()->where('aktif', true)->orderBy('nama');

        if (FilamentWorkspace::isSubdistrict() && auth()->user()?->kecamatan_id) {
            $query->where(function (Builder $builder): void {
                $builder
                    ->where('kecamatan_id', auth()->user()->kecamatan_id)
                    ->orWhereNull('kecamatan_id');
            });
        }

        if (FilamentWorkspace::isDepartment() && auth()->user()?->opd_id) {
            $query->where('opd_id', auth()->user()->opd_id);
        }

        return $query->pluck('nama', 'id')->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mutatePengajuanData(array $data): array
    {
        $user = auth()->user();

        if (FilamentWorkspace::isSubdistrict() && $user?->kecamatan_id) {
            $data['kecamatan_id'] = $user->kecamatan_id;
            $data['opd_id'] = null;
        }

        if (FilamentWorkspace::isDepartment() && $user?->opd_id) {
            $data['opd_id'] = $user->opd_id;
            $data['kecamatan_id'] = null;
        }

        $data['dikirim_oleh'] = $data['dikirim_oleh'] ?? $user?->id;
        $data['status'] = $data['status'] ?? 'draft';

        if ($data['status'] === 'diajukan' && empty($data['tanggal_kirim'])) {
            $data['tanggal_kirim'] = Carbon::now();
        }

        return AdminFormMutation::sanitizePengajuanData($data);
    }
}
