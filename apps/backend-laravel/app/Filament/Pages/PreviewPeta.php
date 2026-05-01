<?php

namespace App\Filament\Pages;

use App\Services\AdminDashboardService;
use App\Support\FilamentWorkspace;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PreviewPeta extends Page
{
    use HasPageShield {
        canAccess as shieldCanAccess;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Preview Peta';

    protected static string|\UnitEnum|null $navigationGroup = 'Peta';

    protected static ?int $navigationSort = 40;

    protected string $view = 'filament.pages.preview-peta';

    /**
     * @var array<string, mixed>
     */
    public array $map = [];

    public function mount(AdminDashboardService $dashboardService): void
    {
        $this->map = $dashboardService->mapPreview(FilamentWorkspace::user());
    }

    public static function canAccess(): bool
    {
        return static::shieldCanAccess() && FilamentWorkspace::canAccessMapFeatures();
    }
}
