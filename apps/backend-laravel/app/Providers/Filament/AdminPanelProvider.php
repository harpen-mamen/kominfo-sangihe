<?php

namespace App\Providers\Filament;

use App\Filament\Pages\InputData\InputCepat;
use App\Filament\Pages\InputData\InputPerIndikator;
use App\Filament\Pages\InputData\UploadExcelDataMentah;
use App\Filament\Pages\LaporanIndikatorDaerah;
use App\Filament\Pages\PengaturanPortal;
use App\Filament\Pages\PreviewPeta;
use App\Filament\Pages\StatistikDaerah\DashboardStatistik;
use App\Filament\Pages\StatistikDaerah\Infografis;
use App\Filament\Pages\StatistikDaerah\PerbandinganWilayah;
use App\Filament\Pages\StatistikDaerah\StatistikDaerah;
use App\Filament\Pages\StatistikDaerah\StatistikPerDesa;
use App\Filament\Pages\StatistikDaerah\StatistikPerIndikator;
use App\Filament\Pages\StatistikDaerah\StatistikPerKecamatan;
use App\Filament\Pages\StatistikDaerah\StatistikPerOpd;
use App\Filament\Pages\StatistikDaerah\TrenBulananTahunan;
use App\Filament\Pages\UploadBatasWilayah;
use App\Filament\Resources\Berita\BeritaResource;
use App\Filament\Resources\Desa\DesaResource;
use App\Filament\Resources\DokumenPublik\DokumenPublikResource;
use App\Filament\Resources\FiturPeta\FiturPetaResource;
use App\Filament\Resources\IndikatorData\IndikatorDataResource;
use App\Filament\Resources\Kecamatan\KecamatanResource;
use App\Filament\Resources\Kegiatan\KegiatanResource;
use App\Filament\Resources\Konten\KontenResource;
use App\Filament\Resources\LapisanPeta\LapisanPetaResource;
use App\Filament\Resources\LogAudit\LogAuditResource;
use App\Filament\Resources\NilaiDataMentah\NilaiDataMentahResource;
use App\Filament\Resources\Opd\OpdResource;
use App\Filament\Resources\PengajuanData\PengajuanDataResource;
use App\Filament\Resources\Pengguna\PenggunaResource;
use App\Filament\Resources\PeriodeData\PeriodeDataResource;
use App\Filament\Resources\RingkasanStatistik\RingkasanStatistikResource;
use App\Filament\Resources\RiwayatTinjau\RiwayatTinjauResource;
use App\Filament\Resources\SumberData\SumberDataResource;
use App\Filament\Widgets\AdminDashboardShowcase;
use App\Filament\Widgets\AdminOverview;
use App\Filament\Widgets\VerificationInbox;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Kominfo Sangihe')
            ->brandLogo(asset('adminlte-assets/img/logo-sangihe.png'))
            ->brandLogoHeight('2.35rem')
            ->login()
            ->defaultThemeMode(ThemeMode::Light)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18rem')
            ->collapsedSidebarWidth('4.75rem')
            ->maxContentWidth(Width::Full)
            ->colors([
                'primary' => Color::Sky,
                'info' => Color::Cyan,
                'warning' => Color::Amber,
                'success' => Color::Teal,
                'danger' => Color::Rose,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::SCRIPTS_AFTER,
                fn (): string => Blade::render("@vite(['resources/js/admin-map-picker.js', 'resources/js/admin-dashboard.js'])"),
            )
            ->resources([
                KecamatanResource::class,
                DesaResource::class,
                OpdResource::class,
                PeriodeDataResource::class,
                IndikatorDataResource::class,
                SumberDataResource::class,
                PengajuanDataResource::class,
                NilaiDataMentahResource::class,
                RingkasanStatistikResource::class,
                DokumenPublikResource::class,
                KontenResource::class,
                BeritaResource::class,
                KegiatanResource::class,
                LapisanPetaResource::class,
                FiturPetaResource::class,
                PenggunaResource::class,
                RiwayatTinjauResource::class,
                LogAuditResource::class,
            ])
            ->pages([
                Dashboard::class,
                InputCepat::class,
                UploadExcelDataMentah::class,
                InputPerIndikator::class,
                UploadBatasWilayah::class,
                PreviewPeta::class,
                LaporanIndikatorDaerah::class,
                StatistikDaerah::class,
                DashboardStatistik::class,
                StatistikPerIndikator::class,
                StatistikPerKecamatan::class,
                StatistikPerDesa::class,
                StatistikPerOpd::class,
                PerbandinganWilayah::class,
                TrenBulananTahunan::class,
                Infografis::class,
                PengaturanPortal::class,
            ])
            ->widgets([
                AdminDashboardShowcase::class,
                AdminOverview::class,
                VerificationInbox::class,
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        $shieldPlugin = FilamentShieldPlugin::class;

        if (class_exists($shieldPlugin)) {
            $panel->plugin($shieldPlugin::make());
        }

        return $panel;
    }
}
