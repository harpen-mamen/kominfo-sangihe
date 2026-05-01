<?php

namespace App\Services;

use App\Models\PortalSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PortalSettingsService
{
    public const CACHE_KEY = 'portal_settings.all';

    /**
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function (): array {
            if (! class_exists(PortalSetting::class)) {
                return [];
            }

            try {
                return PortalSetting::query()
                    ->pluck('value', 'key')
                    ->map(fn ($value) => $value === null ? null : (string) $value)
                    ->all();
            } catch (\Throwable) {
                return [];
            }
        });
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $all = $this->all();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public function set(string $key, ?string $value, ?string $type = null): void
    {
        PortalSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type],
        );

        Cache::forget(self::CACHE_KEY);
    }

    public function publicUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * @return array<string, string>
     */
    public function defaults(): array
    {
        return [
            'hero_background_type' => 'image',
            'hero_title' => 'Portal Data Daerah Kabupaten Kepulauan Sangihe',
            'hero_subtitle' => 'Jelajahi informasi wilayah, statistik pembangunan, peta interaktif, fasilitas publik, dan berita daerah secara terbuka dan terpadu.',
            'hero_badge_text' => 'Satu Data Daerah',
            'hero_primary_button_text' => 'Jelajahi Peta',
            'hero_primary_button_link' => '/peta',
            'hero_secondary_button_text' => 'Lihat Statistik',
            'hero_secondary_button_link' => '/statistik',
            'footer_description' => 'Portal publik Pemerintah Kabupaten Kepulauan Sangihe untuk menghadirkan informasi wilayah, statistik, peta, fasilitas publik, berita, dan data terbuka secara terpadu.',
            'contact_address' => 'Tahuna, Kabupaten Kepulauan Sangihe, Sulawesi Utara',
            'contact_email' => 'admin@kominfo-sangihe.go.id',
            'contact_phone' => '(0432) 21001',

            'about_region_title' => 'Tentang Kabupaten Kepulauan Sangihe',
            'about_region_subtitle' => 'Profil singkat daerah kepulauan, karakter wilayah maritim, serta ringkasan indikator utama.',
            'about_region_content' => 'Kabupaten Kepulauan Sangihe adalah wilayah kepulauan di Provinsi Sulawesi Utara. Portal ini menghimpun statistik, peta digital, fasilitas publik, berita, dan data pembangunan daerah secara terbuka dan terpadu.',
            'about_region_button_text' => 'Tentang Daerah',
            'about_region_button_link' => '/tentang-daerah',

            'map_highlight_title' => 'Peta Interaktif Daerah',
            'map_highlight_description' => 'Klik kecamatan atau desa untuk melihat batas wilayah, fasilitas publik, kegiatan, dan statistik terkait.',
            'map_highlight_button_text' => 'Buka Peta Interaktif',
            'map_highlight_button_link' => '/peta',

            'statistics_highlight_title' => 'Statistik Pembangunan',
            'statistics_highlight_description' => 'Pantau indikator prioritas daerah melalui ringkasan dan grafik publik yang mudah dipahami.',
            'statistics_highlight_button_text' => 'Lihat Semua Statistik',
            'statistics_highlight_button_link' => '/statistik',

            'open_data_title' => 'Data Terbuka untuk Masyarakat',
            'open_data_description' => 'Akses data agregat daerah untuk mendukung transparansi, penelitian, dan pengambilan keputusan.',
            'open_data_primary_button_text' => 'Lihat Dataset',
            'open_data_primary_button_link' => '/data',
            'open_data_secondary_button_text' => 'Unduh Data',
        ];
    }
}
