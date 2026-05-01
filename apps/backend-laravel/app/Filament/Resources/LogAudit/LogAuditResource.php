<?php

namespace App\Filament\Resources\LogAudit;

use App\Filament\Resources\LogAudit\Pages\ManageLogAudit;
use App\Models\LogAudit;
use App\Support\FilamentWorkspace;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LogAuditResource extends Resource
{
    protected static ?string $model = LogAudit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengguna & Role';

    protected static ?string $modelLabel = 'Log Audit';

    protected static ?string $pluralModelLabel = 'Log Audit';

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
            ->modifyQueryUsing(fn ($query) => $query->with('pengguna')->latest('created_at'))
            ->columns([
                TextColumn::make('created_at')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('pengguna.nama')->label('Pengguna')->placeholder('Sistem'),
                TextColumn::make('modul')->badge(),
                TextColumn::make('aksi')->badge(),
                TextColumn::make('jenis_target')->limit(40),
                TextColumn::make('target_id')->label('ID Target'),
            ])
            ->filters([SelectFilter::make('modul')->options([
                'master' => 'Master',
                'statistik' => 'Statistik',
                'konten' => 'Konten',
                'peta' => 'Peta',
            ])])
            ->recordActions([ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageLogAudit::route('/')];
    }
}
