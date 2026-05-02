<?php

namespace App\Filament\Resources\PengajuanData;

use App\Filament\Resources\PengajuanData\Pages\InputDataPengajuan;
use App\Filament\Resources\PengajuanData\Pages\ManagePengajuanData;
use App\Filament\Resources\PengajuanData\Pages\ViewPengajuanData;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema as DbSchema;

class PengajuanDataResource extends Resource
{
    protected static ?string $model = PengajuanData::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Input & Verifikasi';

    protected static ?string $modelLabel = 'Pengajuan Data';

    protected static ?string $pluralModelLabel = 'Pengajuan Data Mentah';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationBadge(): ?string
    {
        $count = static::navigationAttentionCount();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::navigationAttentionCount() > 0 ? 'warning' : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        if (FilamentWorkspace::isKominfo()) {
            return 'Pengajuan menunggu peninjauan';
        }

        if (FilamentWorkspace::isSubdistrict() || FilamentWorkspace::isDepartment()) {
            return 'Pengajuan perlu revisi';
        }

        return null;
    }

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::canAccessWorkflow();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::canAccessWorkflow();
    }

    public static function canEdit(Model $record): bool
    {
        return $record instanceof PengajuanData
            && $record->canInputValues()
            && static::canInputForRecord($record);
    }

    public static function canCreate(): bool
    {
        return FilamentWorkspace::isSubdistrict() || FilamentWorkspace::isDepartment();
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
                            ->where(function (Builder $query): void {
                                $query
                                    ->where('terkunci', false)
                                    ->orWhereNull('terkunci');
                            })
                            ->orderByDesc('tahun')
                            ->orderByDesc('bulan')
                            ->pluck('label', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->required(),

                    Hidden::make('dikirim_oleh')
                        ->default(fn () => auth()->id()),

                    Hidden::make('status')
                        ->default('draft')
                        ->dehydrateStateUsing(fn (?string $state): string => $state ?: 'draft'),

                    Select::make('kelompok_indikator')
                        ->label('Filter Kelompok Indikator')
                        ->options(ResourceOptions::kelompokIndikator())
                        ->searchable()
                        ->helperText('Kosongkan untuk memakai seluruh indikator aktif yang dibuka untuk kecamatan.'),

                    Textarea::make('catatan')
                        ->rows(3)
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
                    $scoped = AdminScope::pengajuanDataQuery($user);

                    if (! FilamentWorkspace::isKominfo()) {
                        $query->whereIn('id', $scoped->select('id'));
                    }
                }

                $query->with([
                    'kecamatan',
                    'opd',
                    'periodeData',
                    'dikirimOleh',
                    'diverifikasiOleh',
                ]);

                if (DbSchema::hasTable('nilai_data_mentah')) {
                    $query->withCount('nilaiDataMentah');
                }

                return $query;
            })
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('kecamatan.nama')
                    ->label('Kecamatan')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('opd.nama')
                    ->label('OPD')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('periodeData.label')
                    ->label('Periode')
                    ->sortable(),

                TextColumn::make('kelompok_indikator')
                    ->label('Kelompok')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ResourceOptions::statusData()[$state] ?? (string) $state)
                    ->color(fn (?string $state): string => match ($state) {
                        'draft' => 'gray',
                        'diajukan' => 'warning',
                        'revisi' => 'warning',
                        'terverifikasi' => 'success',
                        'ditolak' => 'danger',
                        'terbit' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('dikirimOleh.nama')
                    ->label('Dikirim oleh')
                    ->placeholder('-'),

                TextColumn::make('nilai_data_mentah_count')
                    ->label('Data Masuk')
                    ->numeric()
                    ->placeholder('0')
                    ->toggleable(),

                TextColumn::make('tanggal_kirim')
                    ->dateTime('d M Y H:i')
                    ->toggleable(),

                TextColumn::make('tanggal_terbit')
                    ->dateTime('d M Y H:i')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ResourceOptions::statusData()),

                SelectFilter::make('kecamatan')
                    ->relationship('kecamatan', 'nama')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('opd')
                    ->relationship('opd', 'nama')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('periodeData')
                    ->relationship('periodeData', 'label')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('input_data')
                    ->label('Isi Data')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->url(fn (PengajuanData $record): string => static::getUrl('input', ['record' => $record]))
                    ->visible(fn (PengajuanData $record): bool => $record->canInputValues() && static::canInputForRecord($record)),

                static::workflowAction('ajukan', 'Ajukan', 'success')
                    ->icon('heroicon-o-paper-airplane')
                    ->requiresConfirmation()
                    ->visible(fn (PengajuanData $record): bool => static::canInputForRecord($record)
                        && in_array($record->status, ['draft', 'revisi'], true)),

                static::workflowAction('minta_revisi', 'Minta Revisi', 'warning', true)
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->visible(fn (PengajuanData $record): bool => static::canKominfoVerify()
                        && in_array($record->status, ['diajukan', 'terverifikasi'], true)),

                static::workflowAction('verifikasi', 'Verifikasi', 'success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn (PengajuanData $record): bool => static::canKominfoVerify()
                        && in_array($record->status, ['diajukan', 'revisi'], true)),

                static::workflowAction('tolak', 'Tolak', 'danger', true)
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (PengajuanData $record): bool => static::canKominfoVerify()
                        && in_array($record->status, ['diajukan', 'revisi'], true)),

                static::workflowAction('terbitkan', 'Terbitkan', 'primary')
                    ->icon('heroicon-o-globe-alt')
                    ->requiresConfirmation()
                    ->visible(fn (PengajuanData $record): bool => static::canKominfoPublish()
                        && $record->status === 'terverifikasi'),

                static::workflowAction('tarik_publikasi', 'Tarik Publikasi', 'gray', true)
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->visible(fn (PengajuanData $record): bool => static::canKominfoPublish()
                        && $record->status === 'terbit'),

                ViewAction::make()
                    ->label('Lihat'),

                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => static::mutatePengajuanData($data))
                    ->visible(fn (PengajuanData $record): bool => static::canEdit($record)),

                DeleteAction::make()
                    ->visible(fn (PengajuanData $record): bool => ! FilamentWorkspace::isKominfo()
                        && in_array($record->status, ['draft', 'ditolak'], true)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => ! FilamentWorkspace::isKominfo()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePengajuanData::route('/'),
            'view' => ViewPengajuanData::route('/{record}'),
            'input' => InputDataPengajuan::route('/{record}/input'),
        ];
    }

    public static function workflowAction(string $name, string $label, string $color, bool $needsNote = false): Action
    {
        return Action::make($name)
            ->label($label)
            ->color($color)
            ->schema($needsNote ? [
                Textarea::make('catatan')
                    ->label('Catatan')
                    ->required()
                    ->rows(3),
            ] : [])
            ->action(function (PengajuanData $record, array $data) use ($name): void {
                try {
                    $service = app(WorkflowService::class);
                    $catatan = $data['catatan'] ?? null;

                    match ($name) {
                        'ajukan' => $service->ajukan((int) $record->id),
                        'minta_revisi' => $service->mintaRevisi((int) $record->id, (string) $catatan),
                        'verifikasi' => $service->verifikasi((int) $record->id, $catatan),
                        'tolak' => $service->tolak((int) $record->id, (string) $catatan),
                        'terbitkan' => $service->terbitkan((int) $record->id),
                        'tarik_publikasi' => $service->tarikPublikasi((int) $record->id, $catatan),
                        default => null,
                    };

                    Notification::make()
                        ->title('Status pengajuan diperbarui.')
                        ->success()
                        ->send();
                } catch (\Throwable $exception) {
                    Notification::make()
                        ->title('Gagal memperbarui status.')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function countForStatus(?string $status = null): int
    {
        $user = FilamentWorkspace::user();
        $query = $user ? AdminScope::pengajuanDataQuery($user) : PengajuanData::query();

        if (filled($status)) {
            $query->where('status', $status);
        }

        return (int) $query->count();
    }

    public static function navigationAttentionCount(): int
    {
        if (FilamentWorkspace::isKominfo()) {
            return static::countForStatus('diajukan');
        }

        if (FilamentWorkspace::isSubdistrict() || FilamentWorkspace::isDepartment()) {
            return static::countForStatus('revisi');
        }

        return 0;
    }

    public static function canKominfoVerifyForPage(): bool
    {
        return static::canKominfoVerify();
    }

    public static function canKominfoPublishForPage(): bool
    {
        return static::canKominfoPublish();
    }

    protected static function canKominfoVerify(): bool
    {
        $user = FilamentWorkspace::user();

        return $user
            ? AdminScope::hasRole($user, ['super_admin', 'admin_kominfo', 'verifikator_kominfo'])
            : false;
    }

    protected static function canKominfoPublish(): bool
    {
        $user = FilamentWorkspace::user();

        return $user
            ? AdminScope::hasRole($user, ['super_admin', 'admin_kominfo'])
            : false;
    }

    /**
     * @return array<int|string, string>
     */
    public static function desaOptions(): array
    {
        $query = Desa::query()
            ->where('aktif', true)
            ->orderBy('nama');

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

        return AdminScope::orderIndikatorQuery($query)
            ->pluck('nama', 'id')
            ->all();
    }

    public static function canInputForRecord(PengajuanData $record): bool
    {
        $user = FilamentWorkspace::user();

        if (! $user) {
            return false;
        }

        if (AdminScope::isSubdistrict($user)) {
            return filled($user->kecamatan_id)
                && (int) $record->kecamatan_id === (int) $user->kecamatan_id;
        }

        if (AdminScope::isDepartment($user)) {
            return filled($user->opd_id)
                && (int) $record->opd_id === (int) $user->opd_id;
        }

        return false;
    }

    /**
     * @return array<int|string, string>
     */
    public static function sumberDataOptions(): array
    {
        $query = SumberData::query()
            ->where('aktif', true)
            ->orderBy('nama');

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
