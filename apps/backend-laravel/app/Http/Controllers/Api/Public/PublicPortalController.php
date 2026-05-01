<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\Desa;
use App\Models\IndikatorData;
use App\Models\Kecamatan;
use App\Models\Konten;
use App\Models\LapisanPeta;
use App\Models\Opd;
use App\Models\PeriodeData;
use App\Models\RingkasanStatistik;
use App\Models\SumberData;
use App\Services\PortalSettingsService;
use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicPortalController extends Controller
{
    public function __construct(
        private StatisticsService $statisticsService,
        private PortalSettingsService $portalSettings,
    ) {
    }

    public function home(): JsonResponse
    {
        return response()->json([
            'portal' => $this->portalPayload(),
            'hero' => $this->resolveHero(),
            'landing_content' => $this->landingContentPayload(),
            'summary' => $this->summaryPayload(),
            'statistics' => $this->statisticsService->dashboardSummary(),
            'featured_news' => Berita::query()
                ->with(['opd', 'kecamatan'])
                ->where('status', 'terbit')
                ->latest('tanggal_terbit')
                ->limit(4)
                ->get()
                ->map(fn (Berita $post): array => $this->serializeBerita($post))
                ->values(),
            'featured_layers' => LapisanPeta::query()
                ->withCount('fiturPeta')
                ->where('aktif', true)
                ->orderBy('urutan')
                ->limit(4)
                ->get()
                ->map(fn (LapisanPeta $layer): array => $this->serializeLayerSummary($layer))
                ->values(),
            'departments' => Opd::query()->where('aktif', true)->limit(8)->get()->map(fn (Opd $opd): array => [
                'id' => $opd->id,
                'name' => $opd->nama,
                'code' => $opd->kode,
            ]),
        ]);
    }

    public function summary(): JsonResponse
    {
        return response()->json(['data' => $this->summaryPayload()]);
    }

    public function kecamatan(): JsonResponse
    {
        return response()->json([
            'data' => Kecamatan::query()
                ->withCount('desa')
                ->where('aktif', true)
                ->orderBy('nama')
                ->get()
                ->map(fn (Kecamatan $kecamatan): array => [
                    'id' => $kecamatan->id,
                    'kode' => $kecamatan->kode,
                    'nama' => $kecamatan->nama,
                    'desa_count' => (int) $kecamatan->desa_count,
                ])
                ->values(),
        ]);
    }

    public function desa(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Desa::query()
                ->with('kecamatan')
                ->where('aktif', true)
                ->when($request->integer('kecamatan_id'), fn ($query, int $id) => $query->where('kecamatan_id', $id))
                ->orderBy('nama')
                ->get()
                ->map(fn (Desa $desa): array => [
                    'id' => $desa->id,
                    'kode' => $desa->kode,
                    'nama' => $desa->nama,
                    'kecamatan_id' => $desa->kecamatan_id,
                    'kecamatan' => $desa->kecamatan?->nama,
                ])
                ->values(),
        ]);
    }

    public function hero(): JsonResponse
    {
        return response()->json(['data' => $this->resolveHero()]);
    }

    public function portalSettings(): JsonResponse
    {
        return response()->json(['data' => $this->portalPayload()]);
    }

    public function news(): JsonResponse
    {
        return response()->json([
            'data' => Berita::query()
                ->with(['penulis', 'opd', 'kecamatan'])
                ->where('status', 'terbit')
                ->latest('tanggal_terbit')
                ->paginate(9)
                ->through(fn (Berita $post): array => $this->serializeBerita($post)),
        ]);
    }

    public function konten(Request $request): JsonResponse
    {
        $query = Konten::query()
            ->with(['opd', 'kecamatan', 'desa'])
            ->where('status', 'terbit')
            ->when($request->filled('jenis'), fn ($builder) => $builder->where('jenis_konten', $request->string('jenis')->value()))
            ->when($request->integer('opd_id'), fn ($builder, int $id) => $builder->where('opd_id', $id))
            ->when($request->integer('kecamatan_id'), fn ($builder, int $id) => $builder->where('kecamatan_id', $id))
            ->when($request->integer('desa_id'), fn ($builder, int $id) => $builder->where('desa_id', $id))
            ->latest('tanggal_terbit');

        return response()->json([
            'data' => $query
                ->paginate((int) $request->integer('per_page') ?: 12)
                ->through(fn (Konten $konten): array => $this->serializeKonten($konten)),
        ]);
    }

    public function kontenShow(string $slug): JsonResponse
    {
        $konten = Konten::query()
            ->with(['opd', 'kecamatan', 'desa'])
            ->where('slug', $slug)
            ->where('status', 'terbit')
            ->firstOrFail();

        return response()->json(['data' => $this->serializeKonten($konten, detailed: true)]);
    }

    public function sumberData(Request $request): JsonResponse
    {
        return response()->json([
            'data' => SumberData::query()
                ->with(['opd', 'kecamatan', 'desa'])
                ->where('aktif', true)
                ->when($request->integer('opd_id'), fn ($query, int $id) => $query->where('opd_id', $id))
                ->when($request->integer('kecamatan_id'), fn ($query, int $id) => $query->where('kecamatan_id', $id))
                ->when($request->integer('desa_id'), fn ($query, int $id) => $query->where('desa_id', $id))
                ->when($request->filled('jenis'), fn ($query) => $query->where('jenis', $request->string('jenis')->value()))
                ->orderBy('nama')
                ->get()
                ->map(fn (SumberData $sumber): array => [
                    'id' => $sumber->id,
                    'nama' => $sumber->nama,
                    'jenis' => $sumber->jenis,
                    'opd' => $sumber->opd?->nama,
                    'opd_id' => $sumber->opd_id,
                    'kecamatan' => $sumber->kecamatan?->nama,
                    'kecamatan_id' => $sumber->kecamatan_id,
                    'desa' => $sumber->desa?->nama,
                    'desa_id' => $sumber->desa_id,
                    'alamat' => $sumber->alamat,
                    'latitude' => $sumber->latitude ? (float) $sumber->latitude : null,
                    'longitude' => $sumber->longitude ? (float) $sumber->longitude : null,
                ])
                ->values(),
        ]);
    }

    public function departments(): JsonResponse
    {
        return response()->json([
            'data' => Opd::query()
                ->where('aktif', true)
                ->orderBy('nama')
                ->get()
                ->map(fn (Opd $opd): array => ['id' => $opd->id, 'name' => $opd->nama, 'code' => $opd->kode]),
        ]);
    }

    public function documents(): JsonResponse
    {
        $snapshots = $this->statisticsService->publishedSnapshots()
            ->where('tingkat_rekap', 'kabupaten')
            ->sortByDesc(fn ($row) => $row->periodeData?->tahun ?? 0)
            ->groupBy(fn ($row) => $row->periodeData?->tahun ?? 'terbaru')
            ->take(6)
            ->map(fn ($items, $year): array => [
                'title' => 'Ringkasan Statistik Daerah ' . $year,
                'category' => 'Statistik Terbit',
                'published_at' => optional($items->first()?->updated_at ?? $items->first()?->created_at)?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'data' => [
                'data' => $snapshots->isNotEmpty() ? $snapshots : [[
                    'title' => 'Ringkasan Statistik Daerah',
                    'category' => 'Statistik Terbit',
                    'published_at' => null,
                ]],
            ],
        ]);
    }

    public function newsShow(Berita $berita): JsonResponse
    {
        abort_unless($berita->status === 'terbit', 404);

        return response()->json([
            'data' => $this->serializeBerita($berita->load(['penulis', 'opd', 'kecamatan'])),
        ]);
    }

    public function pageShow(string $slug): JsonResponse
    {
        if ($slug === 'profil-kabupaten-kepulauan-sangihe') {
            $defaults = $this->portalSettings->defaults();

            $title = $this->portalSettings->get('about_region_title', $defaults['about_region_title'] ?? null)
                ?? ($defaults['about_region_title'] ?? 'Tentang Kabupaten Kepulauan Sangihe');
            $subtitle = $this->portalSettings->get('about_region_subtitle', $defaults['about_region_subtitle'] ?? null);
            $content = $this->portalSettings->get('about_region_content', $defaults['about_region_content'] ?? null);
            $imageUrl = $this->portalSettings->publicUrl($this->portalSettings->get('about_region_image'));

            return response()->json([
                'data' => [
                    'title' => $title,
                    'content' => trim(implode("\n\n", array_filter([$subtitle, $content]))),
                    'image_url' => $imageUrl,
                    'seo_description' => $subtitle ?: 'Profil daerah Kabupaten Kepulauan Sangihe.',
                ],
            ]);
        }

        $pages = [
            'profil-kabupaten-kepulauan-sangihe' => [
                'title' => 'Profil Kabupaten Kepulauan Sangihe',
                'content' => 'Kabupaten Kepulauan Sangihe adalah wilayah kepulauan di Provinsi Sulawesi Utara. Portal ini menempatkan statistik daerah, berita resmi, layer peta, dan informasi publik dalam satu kanal resmi yang dikelola melalui dashboard admin.',
            ],
            'profil-bupati-dan-wakil-bupati' => [
                'title' => 'Profil Bupati dan Wakil Bupati',
                'content' => 'Pimpinan daerah mendorong tata kelola pemerintahan berbasis data, publikasi informasi yang tertib, dan layanan digital yang dapat dipantau masyarakat.',
            ],
            'profil-dinas-komunikasi-dan-informatika' => [
                'title' => 'Profil Dinas Komunikasi dan Informatika',
                'content' => 'Dinas Komunikasi dan Informatika mengelola layanan informasi, publikasi digital, statistik sektoral, infrastruktur komunikasi, dan dukungan integrasi data lintas perangkat daerah.',
            ],
            'informasi-umum-layanan-data' => [
                'title' => 'Informasi Umum Layanan Data',
                'content' => 'Data yang tampil pada portal publik berasal dari pengajuan, verifikasi, dan publikasi berjenjang. Konten berita, kegiatan, statistik, dan GIS dikurasi melalui panel admin Filament.',
            ],
            'kontak-kominfo-sangihe' => [
                'title' => 'Kontak Kominfo Sangihe',
                'content' => 'Hubungi Dinas Komunikasi dan Informatika Kabupaten Kepulauan Sangihe untuk koordinasi publikasi data, informasi publik, dan pengelolaan layanan digital pemerintah daerah.',
            ],
        ];

        $page = $pages[$slug] ?? [
            'title' => str($slug)->replace('-', ' ')->headline()->value(),
            'content' => 'Informasi halaman ini mengikuti rancangan portal publik dan dapat diperluas melalui modul konten backend.',
        ];

        return response()->json([
            'data' => [
                'title' => $page['title'],
                'content' => $page['content'],
                'seo_description' => 'Informasi publik Kabupaten Kepulauan Sangihe.',
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $keyword = $request->string('q')->trim()->value();

        return response()->json([
            'keyword' => $keyword,
            'news' => Berita::query()
                ->where('status', 'terbit')
                ->where(fn ($query) => $query
                    ->where('judul', 'like', "%{$keyword}%")
                    ->orWhere('ringkasan', 'like', "%{$keyword}%"))
                ->limit(5)
                ->get()
                ->map(fn (Berita $post): array => [
                    'slug' => $post->slug,
                    'title' => $post->judul,
                    'excerpt' => $post->ringkasan,
                ]),
            'pages' => [],
            'departments' => Opd::query()
                ->where('nama', 'like', "%{$keyword}%")
                ->limit(5)
                ->get()
                ->map(fn (Opd $opd): array => ['name' => $opd->nama]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveHero(): array
    {
        $heroAssetBaseUrl = rtrim(url('portal-assets/hero'), '/');
        $defaults = $this->portalSettings->defaults();

        $backgroundType = $this->portalSettings->get('hero_background_type', $defaults['hero_background_type'] ?? 'image') ?? 'image';
        $storedHeroImage = $this->portalSettings->publicUrl($this->portalSettings->get('hero_background_image'));
        $storedHeroVideo = $this->portalSettings->publicUrl($this->portalSettings->get('hero_background_video'));
        $storedVideoPoster = $this->portalSettings->publicUrl($this->portalSettings->get('hero_video_poster'));

        $fallbackImage = "{$heroAssetBaseUrl}/sangihe-coast.png";
        $fallbackImages = ["{$heroAssetBaseUrl}/sangihe-coast.png", "{$heroAssetBaseUrl}/sangihe-bay.png", "{$heroAssetBaseUrl}/sangihe-perahu.jpg"];

        $backgroundImageUrl = $storedHeroImage ?: $fallbackImage;
        $backgroundImageUrls = array_values(array_unique(array_filter(array_merge(
            [$backgroundImageUrl],
            $fallbackImages,
        ))));

        $heroTitle = $this->portalSettings->get('hero_title', $defaults['hero_title'] ?? null) ?? 'Portal Data Daerah Kabupaten Kepulauan Sangihe';
        $heroSubtitle = $this->portalSettings->get('hero_subtitle', $defaults['hero_subtitle'] ?? null)
            ?? 'Informasi statistik, peta wilayah, fasilitas publik, berita, dan data pembangunan daerah.';
        $heroBadge = $this->portalSettings->get('hero_badge_text', $defaults['hero_badge_text'] ?? null)
            ?? 'Pemerintah Kabupaten Kepulauan Sangihe';

        $primaryLabel = $this->portalSettings->get('hero_primary_button_text', $defaults['hero_primary_button_text'] ?? null) ?? 'Jelajahi Peta';
        $primaryUrl = $this->portalSettings->get('hero_primary_button_link', $defaults['hero_primary_button_link'] ?? null) ?? '/peta';
        $secondaryLabel = $this->portalSettings->get('hero_secondary_button_text', $defaults['hero_secondary_button_text'] ?? null) ?? 'Lihat Statistik';
        $secondaryUrl = $this->portalSettings->get('hero_secondary_button_link', $defaults['hero_secondary_button_link'] ?? null) ?? '/statistik';

        return [
            'background_type' => $backgroundType,
            'background_image_url' => $backgroundImageUrl,
            'background_image_urls' => $backgroundImageUrls,
            'background_video_url' => $storedHeroVideo,
            'background_video_poster_url' => $storedVideoPoster ?: $storedHeroImage,
            'badge' => ['id' => $heroBadge, 'en' => $heroBadge],
            'headline' => ['id' => $heroTitle, 'en' => $heroTitle],
            'subheadline' => ['id' => $heroSubtitle, 'en' => $heroSubtitle],
            'cta_primary' => ['label_id' => $primaryLabel, 'label_en' => $primaryLabel, 'url' => $primaryUrl],
            'cta_secondary' => ['label_id' => $secondaryLabel, 'label_en' => $secondaryLabel, 'url' => $secondaryUrl],
            'regent' => ['name' => 'Michael Thungari, S.E., M.AP', 'title_id' => 'Bupati Kabupaten Kepulauan Sangihe', 'title_en' => 'Regent of Sangihe Islands Regency', 'image_url' => "{$heroAssetBaseUrl}/bupati-sangihe.png"],
            'vice_regent' => ['name' => 'Tendris Bulahari', 'title_id' => 'Wakil Bupati Kabupaten Kepulauan Sangihe', 'title_en' => 'Vice Regent of Sangihe Islands Regency', 'image_url' => "{$heroAssetBaseUrl}/wakil-bupati-sangihe.png"],
            'quick_links' => [
                ['label_id' => 'Statistik Daerah', 'label_en' => 'Regional Statistics', 'description_id' => 'Indikator dan grafik kabupaten', 'description_en' => 'Indicators and regional charts', 'url' => '/statistik'],
                ['label_id' => 'Peta Digital', 'label_en' => 'Digital Maps', 'description_id' => 'Layer tematik dan administrasi', 'description_en' => 'Thematic and administrative layers', 'url' => '/peta'],
                ['label_id' => 'Berita', 'label_en' => 'News', 'description_id' => 'Berita kabupaten dan OPD', 'description_en' => 'Regency and agency news', 'url' => '/berita'],
            ],
            'info_items' => [
                ['label_id' => '15 Kecamatan', 'label_en' => '15 Districts'],
                ['label_id' => 'Data Mentah Terverifikasi', 'label_en' => 'Verified Raw Data'],
                ['label_id' => 'Agregasi Otomatis', 'label_en' => 'Automatic Aggregation'],
                ['label_id' => 'Peta dan Kegiatan', 'label_en' => 'Maps and Events'],
            ],
            'visual_order' => ['regent', 'content', 'vice_regent'],
            'is_active' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function portalPayload(): array
    {
        $defaults = $this->portalSettings->defaults();

        $title = $this->portalSettings->get('portal_title', $defaults['hero_title'] ?? null)
            ?? $this->portalSettings->get('hero_title', $defaults['hero_title'] ?? null)
            ?? 'Portal Data Daerah Kabupaten Kepulauan Sangihe';

        $logoPath = $this->portalSettings->get('portal_logo');

        return [
            'title' => $title,
            'logo_url' => $this->portalSettings->publicUrl($logoPath),
            'footer_description' => $this->portalSettings->get('footer_description', $defaults['footer_description'] ?? null),
            'contact' => [
                'address' => $this->portalSettings->get('contact_address', $defaults['contact_address'] ?? null),
                'email' => $this->portalSettings->get('contact_email', $defaults['contact_email'] ?? null),
                'phone' => $this->portalSettings->get('contact_phone', $defaults['contact_phone'] ?? null),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function landingContentPayload(): array
    {
        $defaults = $this->portalSettings->defaults();

        $aboutImageUrl = $this->portalSettings->publicUrl($this->portalSettings->get('about_region_image'));

        return [
            'about_region' => [
                'title' => $this->portalSettings->get('about_region_title', $defaults['about_region_title'] ?? null),
                'subtitle' => $this->portalSettings->get('about_region_subtitle', $defaults['about_region_subtitle'] ?? null),
                'content' => $this->portalSettings->get('about_region_content', $defaults['about_region_content'] ?? null),
                'image_url' => $aboutImageUrl,
                'button_text' => $this->portalSettings->get('about_region_button_text', $defaults['about_region_button_text'] ?? null),
                'button_link' => $this->portalSettings->get('about_region_button_link', $defaults['about_region_button_link'] ?? null),
            ],
            'map_highlight' => [
                'title' => $this->portalSettings->get('map_highlight_title', $defaults['map_highlight_title'] ?? null),
                'description' => $this->portalSettings->get('map_highlight_description', $defaults['map_highlight_description'] ?? null),
                'button_text' => $this->portalSettings->get('map_highlight_button_text', $defaults['map_highlight_button_text'] ?? null),
                'button_link' => $this->portalSettings->get('map_highlight_button_link', $defaults['map_highlight_button_link'] ?? null),
            ],
            'statistics_highlight' => [
                'title' => $this->portalSettings->get('statistics_highlight_title', $defaults['statistics_highlight_title'] ?? null),
                'description' => $this->portalSettings->get('statistics_highlight_description', $defaults['statistics_highlight_description'] ?? null),
                'button_text' => $this->portalSettings->get('statistics_highlight_button_text', $defaults['statistics_highlight_button_text'] ?? null),
                'button_link' => $this->portalSettings->get('statistics_highlight_button_link', $defaults['statistics_highlight_button_link'] ?? null),
            ],
            'open_data' => [
                'title' => $this->portalSettings->get('open_data_title', $defaults['open_data_title'] ?? null),
                'description' => $this->portalSettings->get('open_data_description', $defaults['open_data_description'] ?? null),
                'primary_button_text' => $this->portalSettings->get('open_data_primary_button_text', $defaults['open_data_primary_button_text'] ?? null),
                'primary_button_link' => $this->portalSettings->get('open_data_primary_button_link', $defaults['open_data_primary_button_link'] ?? null),
                'secondary_button_text' => $this->portalSettings->get('open_data_secondary_button_text', $defaults['open_data_secondary_button_text'] ?? null),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summaryPayload(): array
    {
        $latestPopulationPeriodId = RingkasanStatistik::query()
            ->whereHas('indikatorData', fn ($query) => $query->where('kode', 'jumlah_penduduk'))
            ->orderByDesc('periode_data_id')
            ->value('periode_data_id');

        $latestPopulation = $latestPopulationPeriodId
            ? RingkasanStatistik::query()
                ->where('periode_data_id', $latestPopulationPeriodId)
                ->whereHas('indikatorData', fn ($query) => $query->where('kode', 'jumlah_penduduk'))
                ->sum('nilai_total')
            : 0;

        return [
            'jumlah_kecamatan' => Kecamatan::query()->where('aktif', true)->count(),
            'jumlah_desa' => Desa::query()->where('aktif', true)->count(),
            'jumlah_penduduk' => $latestPopulation > 0 ? (float) $latestPopulation : null,
            'jumlah_fasilitas_publik' => SumberData::query()->where('aktif', true)->count(),
            'jumlah_berita_kegiatan' => Konten::query()->where('status', 'terbit')->count(),
            'jumlah_layer_peta' => LapisanPeta::query()->where('aktif', true)->count(),
            'jumlah_indikator' => IndikatorData::query()->where('aktif', true)->count(),
            'periode_terbaru' => PeriodeData::query()->orderByDesc('tahun')->orderByDesc('bulan')->value('label'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeBerita(Berita $post): array
    {
        return [
            'id' => $post->id,
            'slug' => $post->slug,
            'title' => $post->judul,
            'excerpt' => $post->ringkasan,
            'content' => $post->isi,
            'published_at' => optional($post->tanggal_terbit)?->toIso8601String(),
            'featured_image_url' => $post->gambar_sampul,
            'category' => ['name' => $post->kecamatan_id ? 'Berita Kecamatan' : 'Berita OPD'],
            'department' => $post->opd ? ['name' => $post->opd->nama] : null,
            'area' => $post->kecamatan ? ['name' => $post->kecamatan->nama] : null,
            'latitude' => $post->latitude ? (float) $post->latitude : null,
            'longitude' => $post->longitude ? (float) $post->longitude : null,
            'location' => $post->lokasi,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeKonten(Konten $konten, bool $detailed = false): array
    {
        return [
            'id' => $konten->id,
            'slug' => $konten->slug,
            'jenis' => $konten->jenis_konten,
            'title' => $konten->judul,
            'excerpt' => $konten->ringkasan,
            'content' => $detailed ? ($konten->isi ?? $konten->uraian) : null,
            'published_at' => optional($konten->tanggal_terbit)?->toIso8601String(),
            'featured_image_url' => $konten->gambar_sampul,
            'department' => $konten->opd ? ['id' => $konten->opd->id, 'name' => $konten->opd->nama] : null,
            'kecamatan' => $konten->kecamatan ? ['id' => $konten->kecamatan->id, 'name' => $konten->kecamatan->nama] : null,
            'desa' => $konten->desa ? ['id' => $konten->desa->id, 'name' => $konten->desa->nama] : null,
            'location' => $konten->lokasi,
            'latitude' => $konten->latitude ? (float) $konten->latitude : null,
            'longitude' => $konten->longitude ? (float) $konten->longitude : null,
            'starts_at' => optional($konten->mulai)?->toIso8601String(),
            'ends_at' => optional($konten->selesai)?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeLayerSummary(LapisanPeta $layer): array
    {
        $config = $layer->konfigurasi_json ?? [];

        return [
            'slug' => $layer->slug,
            'name' => $layer->nama,
            'features_count' => $layer->fitur_peta_count ?? $layer->fiturPeta()->count(),
            'department' => ['name' => 'Kominfo Sangihe'],
            'kind' => 'gis',
            'color' => $config['color'] ?? '#2563eb',
        ];
    }
}
