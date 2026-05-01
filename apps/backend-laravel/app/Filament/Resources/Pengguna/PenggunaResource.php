<?php

namespace App\Filament\Resources\Pengguna;

use App\Filament\Resources\Pengguna\Pages\ManagePengguna;
use App\Models\User;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PenggunaResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengguna & Role';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::canAccessUsers();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::canAccessUsers();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Akun Pengguna')
                ->schema([
                    TextInput::make('nama')->required()->maxLength(150),
                    TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(190),
                    Select::make('shield_role')
                        ->label('Role Shield')
                        ->options([
                            'super_admin' => 'Super Admin',
                            'admin_kominfo' => 'Admin Kominfo',
                            'admin_kecamatan' => 'Admin Kecamatan',
                            'admin_opd' => 'Admin OPD',
                        ])
                        ->default('admin_kecamatan')
                        ->live()
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Select $component, ?User $record): void {
                            $component->state($record?->primaryRole() ?? 'admin_kecamatan');
                        })
                        ->helperText('Role utama disimpan melalui Spatie Permission / Filament Shield. Kolom role lama hanya dipertahankan sebagai mirror kompatibilitas.')
                        ->required(),
                    TextInput::make('kata_sandi')
                        ->password()
                        ->revealable()
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (?User $record): bool => ! $record),
                    Select::make('kecamatan_id')
                        ->relationship('kecamatan', 'nama')
                        ->searchable()
                        ->preload()
                        ->required(fn (Get $get): bool => $get('shield_role') === 'admin_kecamatan')
                        ->visible(fn (Get $get): bool => in_array($get('shield_role'), ['admin_kecamatan'], true)),
                    Select::make('opd_id')
                        ->relationship('opd', 'nama')
                        ->searchable()
                        ->preload()
                        ->required(fn (Get $get): bool => $get('shield_role') === 'admin_opd')
                        ->visible(fn (Get $get): bool => in_array($get('shield_role'), ['admin_opd', 'admin_kominfo'], true)),
                    Toggle::make('aktif')->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['kecamatan', 'opd']))
            ->columns([
                TextColumn::make('nama')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->label('Role')
                    ->formatStateUsing(fn (?string $state, User $record): string => AdminScope::primaryRole($record)),
                TextColumn::make('kecamatan.nama')->label('Kecamatan')->placeholder('-'),
                TextColumn::make('opd.nama')->label('OPD')->placeholder('-'),
                IconColumn::make('aktif')->boolean(),
                TextColumn::make('login_terakhir_pada')->dateTime('d M Y H:i')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('shield_role')
                    ->label('Role')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'admin_kominfo' => 'Admin Kominfo',
                        'admin_kecamatan' => 'Admin Kecamatan',
                        'admin_opd' => 'Admin OPD',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $role = $data['value'] ?? null;

                        return filled($role)
                            ? $query->whereHas('roles', fn (Builder $builder): Builder => $builder->where('name', $role))
                            : $query;
                    }),
                SelectFilter::make('kecamatan')->relationship('kecamatan', 'nama')->searchable()->preload(),
                SelectFilter::make('opd')->relationship('opd', 'nama')->searchable()->preload(),
                TernaryFilter::make('aktif'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => ManagePengguna::mutateUserData($data))
                    ->using(function (User $record, array $data): User {
                        $record->fill(\Illuminate\Support\Arr::except($data, ['kata_sandi']));

                        if (filled($data['kata_sandi'] ?? null)) {
                            $record->forceFill(['kata_sandi' => \Illuminate\Support\Facades\Hash::make((string) $data['kata_sandi'])]);
                        }

                        $record->save();
                        ManagePengguna::syncShieldRole($record, $data);

                        return $record;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePengguna::route('/')];
    }
}
