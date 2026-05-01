<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Berita\BeritaResource;
use App\Filament\Resources\Kegiatan\KegiatanResource;
use App\Filament\Resources\LapisanPeta\LapisanPetaResource;
use App\Filament\Resources\PengajuanData\PengajuanDataResource;
use App\Filament\Resources\Pengguna\PenggunaResource;
use App\Filament\Resources\RingkasanStatistik\RingkasanStatistikResource;
use App\Support\AdminScope;
use App\Support\FilamentWorkspace;
use Filament\Widgets\Widget;

class RoleWorkspaceHero extends Widget
{
    protected string $view = 'filament.widgets.role-workspace-hero';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = FilamentWorkspace::user();

        if (! $user) {
            return [
                'eyebrow' => 'Dashboard Admin',
                'title' => 'Panel kendali Kominfo',
                'description' => 'Masuk kembali untuk melihat dashboard sesuai role.',
                'highlights' => [],
                'links' => [],
            ];
        }

        $scopeLabel = AdminScope::scopeLabel($user);

        return match (FilamentWorkspace::key()) {
            'kecamatan' => [
                'eyebrow' => 'Ruang Kerja Kecamatan',
                'title' => 'Fokus harian admin kecamatan',
                'description' => "Kelola data mentah, berita, dan kegiatan untuk {$scopeLabel}. Pastikan setiap pengajuan lengkap sebelum dikirim ke Kominfo.",
                'highlights' => [
                    "Cakupan: {$scopeLabel}",
                    'Prioritas: kelengkapan data',
                    'Workflow: draft -> diajukan',
                ],
                'links' => [
                    [
                        'label' => 'Pengajuan Data',
                        'description' => 'Masuk ke antrean input dan kirim data.',
                        'url' => PengajuanDataResource::getUrl(),
                    ],
                    [
                        'label' => 'Berita Kecamatan',
                        'description' => 'Kelola berita lokal dan rilis cepat.',
                        'url' => BeritaResource::getUrl(),
                    ],
                    [
                        'label' => 'Agenda Kegiatan',
                        'description' => 'Atur agenda layanan dan kegiatan wilayah.',
                        'url' => KegiatanResource::getUrl(),
                    ],
                ],
            ],
            'opd' => [
                'eyebrow' => 'Ruang Kerja OPD',
                'title' => 'Dashboard validasi sektoral OPD',
                'description' => "Pantau kualitas konten dan kesiapan data sektoral untuk {$scopeLabel}. Fokus utama role ini adalah konsistensi, verifikasi, dan kesiapan publikasi.",
                'highlights' => [
                    "Cakupan: {$scopeLabel}",
                    'Prioritas: validasi sektoral',
                    'Workflow: review internal -> diajukan',
                ],
                'links' => [
                    [
                        'label' => 'Ringkasan Statistik',
                        'description' => 'Baca hasil agregasi dan tren sektoral.',
                        'url' => RingkasanStatistikResource::getUrl(),
                    ],
                    [
                        'label' => 'Berita OPD',
                        'description' => 'Kelola berita resmi unit kerja.',
                        'url' => BeritaResource::getUrl(),
                    ],
                    [
                        'label' => 'Agenda OPD',
                        'description' => 'Jadwalkan dan siapkan agenda publik.',
                        'url' => KegiatanResource::getUrl(),
                    ],
                ],
            ],
            default => [
                'eyebrow' => 'Pusat Governance Kominfo',
                'title' => 'Dashboard utama admin Kominfo',
                'description' => 'Kendalikan master kecamatan, desa, indikator, sumber data, akun admin kecamatan, dan publikasi statistik dari satu panel kerja terpusat.',
                'highlights' => [
                    "Cakupan: {$scopeLabel}",
                    'Prioritas: approval dan publikasi',
                    'Panel utama: lintas kecamatan dan OPD',
                ],
                'links' => [
                    [
                        'label' => 'Monitoring Pengajuan',
                        'description' => 'Lihat antrean pengajuan dan status verifikasi.',
                        'url' => PengajuanDataResource::getUrl(),
                    ],
                    [
                        'label' => 'Kelola Pengguna',
                        'description' => 'Atur akun admin, role, dan status aktif.',
                        'url' => PenggunaResource::getUrl(),
                    ],
                    [
                        'label' => 'Peta Digital',
                        'description' => 'Kelola layer publik dan titik layanan.',
                        'url' => LapisanPetaResource::getUrl(),
                    ],
                ],
            ],
        };
    }
}
