<?php

namespace App\Filament\Resources\DokumenPublik;

use App\Filament\Resources\DokumenPublik\Pages\ManageDokumenPublik;
use App\Models\Desa;
use App\Models\DokumenPublik;
use App\Models\Kecamatan;
use App\Models\Opd;
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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DokumenPublikResource extends Resource
{
    protected static ?string $model = DokumenPublik::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Dokumen Publik';

    protected static ?string $modelLabel = 'Dokumen Publik';

    protected static ?string $pluralModelLabel = 'Dokumen Publik';

    protected static ?string $recordTitleAttribute = 'judul';

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::canAccessPublicDocuments();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::canAccessPublicDocuments();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas Dokumen')
                ->schema([
                    TextInput::make('judul')->required()->maxLength(220)->live(onBlur: true),
                    TextInput::make('slug')->maxLength(240)->helperText('Kosongkan untuk dibuat otomatis dari judul.'),
                    Select::make('jenis_dokumen')
                        ->options(fn (): array => static::jenisDokumenOptions())
                        ->required()
                        ->searchable(),
                    Select::make('tingkat_wilayah')
                        ->options(ResourceOptions::tingkatWilayahDokumen())
                        ->default(fn (): string => FilamentWorkspace::isDepartment() ? 'opd' : 'kecamatan')
                        ->required(),
                    Select::make('kecamatan_id')
                        ->label('Kecamatan')
                        ->options(fn (): array => static::kecamatanOptions())
                        ->default(fn () => auth()->user()?->kecamatan_id)
                        ->disabled(fn (): bool => FilamentWorkspace::isSubdistrict())
                        ->dehydrated()
                        ->live()
                        ->searchable()
                        ->preload()
                        ->visible(fn (): bool => ! FilamentWorkspace::isDepartment()),
                    Select::make('desa_id')
                        ->label('Desa')
                        ->options(fn (Get $get): array => static::desaOptions($get('kecamatan_id')))
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get): bool => $get('tingkat_wilayah') === 'desa' || ! FilamentWorkspace::isDepartment()),
                    Select::make('opd_id')
                        ->label('OPD')
                        ->options(fn (): array => static::opdOptions())
                        ->default(fn () => auth()->user()?->opd_id)
                        ->disabled(fn (): bool => FilamentWorkspace::isDepartment())
                        ->dehydrated()
                        ->searchable()
                        ->preload()
                        ->visible(fn (): bool => FilamentWorkspace::isDepartment() || FilamentWorkspace::isKominfo()),
                    TextInput::make('tahun')->numeric()->minValue(2000)->maxValue(2100),
                    TextInput::make('nomor_dokumen')->maxLength(120),
                    DatePicker::make('tanggal_dokumen'),
                    FileUpload::make('file_path')
                        ->label('File')
                        ->disk('public')
                        ->directory('dokumen-publik')
                        ->acceptedFileTypes(['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->downloadable()
                        ->openable()
                        ->required(),
                    Textarea::make('ringkasan')->rows(4)->columnSpanFull(),
                    Hidden::make('dikirim_oleh')->default(fn () => auth()->id()),
                ])
                ->columns(2),
            Section::make('Verifikasi')
                ->schema([
                    Select::make('status')
                        ->options(fn (): array => FilamentWorkspace::isKominfo()
                            ? ResourceOptions::statusDokumenPublik()
                            : array_intersect_key(ResourceOptions::statusDokumenPublik(), array_flip(['draft', 'dikirim'])))
                        ->default('draft')
                        ->required(),
                    Textarea::make('catatan_verifikasi')->rows(3),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $user = FilamentWorkspace::user();

                if ($user && ! FilamentWorkspace::isKominfo()) {
                    $query->whereIn('id', AdminScope::dokumenPublikQuery($user)->select('id'));
                }

                return $query->with(['kecamatan', 'desa', 'opd', 'pengirim', 'peninjau']);
            })
            ->columns([
                TextColumn::make('judul')->searchable()->wrap(),
                TextColumn::make('jenis_dokumen')->label('Jenis')->formatStateUsing(fn (?string $state): string => ResourceOptions::jenisDokumenPublik()[$state] ?? (string) $state)->badge(),
                TextColumn::make('tingkat_wilayah')->label('Wilayah')->badge(),
                TextColumn::make('kecamatan.nama')->label('Kecamatan')->placeholder('-'),
                TextColumn::make('desa.nama')->label('Desa')->placeholder('-'),
                TextColumn::make('opd.nama')->label('OPD')->placeholder('-'),
                TextColumn::make('tahun')->sortable()->placeholder('-'),
                TextColumn::make('status')->badge(),
                TextColumn::make('tanggal_terbit')->dateTime('d M Y H:i')->placeholder('-')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ResourceOptions::statusDokumenPublik()),
                SelectFilter::make('jenis_dokumen')->label('Jenis')->options(ResourceOptions::jenisDokumenPublik()),
                SelectFilter::make('kecamatan')->relationship('kecamatan', 'nama')->searchable()->preload(),
                SelectFilter::make('desa')->relationship('desa', 'nama')->searchable()->preload(),
                SelectFilter::make('opd')->relationship('opd', 'nama')->searchable()->preload(),
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('Publish')
                    ->color('success')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->requiresConfirmation()
                    ->visible(fn (DokumenPublik $record): bool => FilamentWorkspace::isKominfo() && $record->status !== 'terbit')
                    ->action(function (DokumenPublik $record): void {
                        $record->update([
                            'status' => 'terbit',
                            'ditinjau_oleh' => auth()->id(),
                            'tanggal_terbit' => Carbon::now(),
                        ]);

                        Notification::make()->title('Dokumen dipublish.')->success()->send();
                    }),
                Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->schema([Textarea::make('catatan_verifikasi')->label('Catatan')->required()->rows(3)])
                    ->visible(fn (DokumenPublik $record): bool => FilamentWorkspace::isKominfo() && $record->status !== 'ditolak')
                    ->action(function (DokumenPublik $record, array $data): void {
                        $record->update([
                            'status' => 'ditolak',
                            'ditinjau_oleh' => auth()->id(),
                            'catatan_verifikasi' => $data['catatan_verifikasi'],
                        ]);

                        Notification::make()->title('Dokumen ditolak.')->success()->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->visible(fn (): bool => FilamentWorkspace::isKominfo()),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->visible(fn (): bool => FilamentWorkspace::isKominfo())])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageDokumenPublik::route('/')];
    }

    public static function jenisDokumenOptions(): array
    {
        $options = ResourceOptions::jenisDokumenPublik();

        if (FilamentWorkspace::isSubdistrict()) {
            return array_intersect_key($options, array_flip(['rab_desa', 'rab_kecamatan', 'dokumen_kegiatan', 'pengumuman_resmi']));
        }

        return $options;
    }

    public static function kecamatanOptions(): array
    {
        $query = Kecamatan::query()->where('aktif', true)->orderBy('nama');

        if (FilamentWorkspace::isSubdistrict() && auth()->user()?->kecamatan_id) {
            $query->whereKey(auth()->user()->kecamatan_id);
        }

        return $query->pluck('nama', 'id')->all();
    }

    public static function desaOptions(mixed $kecamatanId = null): array
    {
        $query = Desa::query()->where('aktif', true)->orderBy('nama');

        if (FilamentWorkspace::isSubdistrict() && auth()->user()?->kecamatan_id) {
            $query->where('kecamatan_id', auth()->user()->kecamatan_id);
        } elseif ($kecamatanId) {
            $query->where('kecamatan_id', $kecamatanId);
        }

        return $query->pluck('nama', 'id')->all();
    }

    public static function opdOptions(): array
    {
        $query = Opd::query()->where('aktif', true)->orderBy('nama');

        if (FilamentWorkspace::isDepartment() && auth()->user()?->opd_id) {
            $query->whereKey(auth()->user()->opd_id);
        }

        return $query->pluck('nama', 'id')->all();
    }
}
