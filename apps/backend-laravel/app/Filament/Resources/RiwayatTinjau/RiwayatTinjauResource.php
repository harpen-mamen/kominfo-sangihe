<?php

namespace App\Filament\Resources\RiwayatTinjau;

use App\Filament\Resources\RiwayatTinjau\Pages\ManageRiwayatTinjau;
use App\Models\RiwayatTinjau;
use App\Support\FilamentWorkspace;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RiwayatTinjauResource extends Resource
{
    protected static ?string $model = RiwayatTinjau::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengguna & Role';

    protected static ?string $modelLabel = 'Riwayat Tinjau';

    protected static ?string $pluralModelLabel = 'Riwayat Tinjau';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return FilamentWorkspace::isKominfo();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentWorkspace::isKominfo();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('peninjau')->latest('created_at'))
            ->columns([
                TextColumn::make('created_at')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('peninjau.nama')->label('Peninjau')->searchable(),
                TextColumn::make('aksi')->badge(),
                TextColumn::make('jenis_objek')->limit(40)->toggleable(),
                TextColumn::make('objek_id')->label('ID Objek'),
                TextColumn::make('catatan')->limit(60)->toggleable(),
            ])
            ->filters([SelectFilter::make('aksi')->options([
                'diajukan' => 'Diajukan',
                'revisi' => 'Revisi',
                'terverifikasi' => 'Terverifikasi',
                'terbit' => 'Terbit',
                'ditolak' => 'Ditolak',
            ])])
            ->recordActions([ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageRiwayatTinjau::route('/')];
    }
}
