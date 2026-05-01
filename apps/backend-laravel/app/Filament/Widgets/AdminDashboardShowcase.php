<?php

namespace App\Filament\Widgets;

use App\Services\AdminDashboardService;
use App\Support\FilamentWorkspace;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class AdminDashboardShowcase extends Widget
{
    use HasWidgetShield {
        canView as shieldCanView;
    }

    protected string $view = 'filament.widgets.admin-dashboard-showcase';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return static::shieldCanView() && FilamentWorkspace::canAccessWorkflow();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return app(AdminDashboardService::class)->build(FilamentWorkspace::user());
    }
}
