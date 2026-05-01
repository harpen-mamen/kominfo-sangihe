<?php

namespace App\Filament\Widgets;

use App\Models\Berita;
use App\Models\Kecamatan;
use App\Models\Kegiatan;
use App\Models\LapisanPeta;
use App\Models\Opd;
use App\Models\PengajuanData;
use App\Models\RingkasanStatistik;
use App\Models\User;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverview extends StatsOverviewWidget
{
    use HasWidgetShield {
        canView as shieldCanView;
    }

    public static function canView(): bool
    {
        return static::shieldCanView();
    }

    protected function getStats(): array
    {
        $user = FilamentWorkspace::user();

        if ($user && FilamentWorkspace::isSubdistrict()) {
            return [
                Stat::make('Draft Pengajuan', PengajuanData::query()->where('kecamatan_id', $user->kecamatan_id)->where('status', 'draft')->count())
                    ->description('Siap dirapikan sebelum dikirim')
                    ->descriptionIcon('heroicon-m-inbox-stack', IconPosition::Before)
                    ->color('primary'),
                Stat::make('Sudah Diajukan', PengajuanData::query()->where('kecamatan_id', $user->kecamatan_id)->where('status', 'diajukan')->count())
                    ->description('Menunggu verifikasi Kominfo')
                    ->descriptionIcon('heroicon-m-paper-airplane', IconPosition::Before)
                    ->color('info'),
                Stat::make('Perlu Revisi', PengajuanData::query()->where('kecamatan_id', $user->kecamatan_id)->where('status', 'revisi')->count())
                    ->description('Perlu tindak lanjut wilayah')
                    ->descriptionIcon('heroicon-m-arrow-uturn-left', IconPosition::Before)
                    ->color('danger'),
                Stat::make('Konten Terbit', AdminScope::beritaQuery($user)->where('status', 'terbit')->count() + AdminScope::kegiatanQuery($user)->where('status', 'terbit')->count())
                    ->description('Berita dan agenda yang sudah tayang')
                    ->descriptionIcon('heroicon-m-check-badge', IconPosition::Before)
                    ->color('success'),
            ];
        }

        if ($user && FilamentWorkspace::isDepartment()) {
            return [
                Stat::make('Draft Konten', AdminScope::beritaQuery($user)->where('status', 'draft')->count() + AdminScope::kegiatanQuery($user)->where('status', 'draft')->count())
                    ->description('Materi OPD yang belum diajukan')
                    ->descriptionIcon('heroicon-m-pencil-square', IconPosition::Before)
                    ->color('primary'),
                Stat::make('Menunggu Review', AdminScope::beritaQuery($user)->where('status', 'diajukan')->count() + AdminScope::kegiatanQuery($user)->where('status', 'diajukan')->count())
                    ->description('Menunggu pemeriksaan Kominfo')
                    ->descriptionIcon('heroicon-m-clipboard-document-check', IconPosition::Before)
                    ->color('warning'),
                Stat::make('Perlu Perbaikan', AdminScope::beritaQuery($user)->where('status', 'revisi')->count() + AdminScope::kegiatanQuery($user)->where('status', 'revisi')->count())
                    ->description('Konten yang perlu dibenahi')
                    ->descriptionIcon('heroicon-m-arrow-path-rounded-square', IconPosition::Before)
                    ->color('danger'),
                Stat::make('Sudah Terbit', AdminScope::beritaQuery($user)->where('status', 'terbit')->count() + AdminScope::kegiatanQuery($user)->where('status', 'terbit')->count())
                    ->description('Konten OPD yang sudah publik')
                    ->descriptionIcon('heroicon-m-megaphone', IconPosition::Before)
                    ->color('success'),
            ];
        }

        return [
            Stat::make('Menunggu Review', PengajuanData::query()->where('status', 'diajukan')->count())
                ->description('Pengajuan data lintas wilayah')
                ->descriptionIcon('heroicon-m-inbox-stack', IconPosition::Before)
                ->color('warning'),
            Stat::make('Konten Perlu Kurasi', Berita::query()->where('status', 'diajukan')->count() + Kegiatan::query()->where('status', 'diajukan')->count())
                ->description('Berita dan agenda yang menunggu kurasi')
                ->descriptionIcon('heroicon-m-newspaper', IconPosition::Before)
                ->color('info'),
            Stat::make('Ringkasan Statistik', RingkasanStatistik::query()->count())
                ->description('Data agregat siap dibaca')
                ->descriptionIcon('heroicon-m-chart-bar-square', IconPosition::Before)
                ->color('success'),
            Stat::make('Layer Peta Aktif', LapisanPeta::query()->where('aktif', true)->count())
                ->description('Layer publik yang sedang tayang')
                ->descriptionIcon('heroicon-m-map', IconPosition::Before)
                ->color('primary'),
            Stat::make('Pengguna Aktif', User::query()->where('aktif', true)->count())
                ->description('Akun admin dan operator aktif')
                ->descriptionIcon('heroicon-m-users', IconPosition::Before)
                ->color('success'),
            Stat::make('Master Wilayah', Kecamatan::query()->where('aktif', true)->count() + Opd::query()->where('aktif', true)->count())
                ->description('Kecamatan dan OPD aktif')
                ->descriptionIcon('heroicon-m-building-office-2', IconPosition::Before)
                ->color('success'),
        ];
    }
}
