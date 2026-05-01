<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\Desa;
use App\Models\FiturPeta;
use App\Models\Kecamatan;
use App\Models\Kegiatan;
use App\Models\LapisanPeta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function layers(): JsonResponse
    {
        return response()->json(['data' => $this->resolveLayerSummaries()]);
    }

    public function workbench(): JsonResponse
    {
        return response()->json([
            'data' => [
                'initial_view' => ['center' => [125.5302, 3.6118], 'zoom' => 12],
                'filters' => [
                    'kecamatan' => Kecamatan::query()->where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
                    'desa' => Desa::query()->where('aktif', true)->orderBy('nama')->get(['id', 'kecamatan_id', 'nama']),
                ],
                'layers' => $this->resolveWorkbenchLayers(),
            ],
        ]);
    }

    public function allFeatures(Request $request): JsonResponse
    {
        $layers = LapisanPeta::query()
            ->with(['fiturPeta' => fn ($query) => $query
                ->where('aktif', true)
                ->when($request->integer('kecamatan_id'), fn ($builder, int $id) => $builder->where('kecamatan_id', $id))
                ->when($request->integer('desa_id'), fn ($builder, int $id) => $builder->where('desa_id', $id))
                ->when($request->integer('opd_id'), fn ($builder, int $id) => $builder->where('opd_id', $id))
                ->when($request->integer('sumber_data_id'), fn ($builder, int $id) => $builder->where('sumber_data_id', $id))])
            ->where('aktif', true)
            ->when($request->filled('layer'), fn ($query) => $query->where('slug', $request->string('layer')->value()))
            ->orderBy('urutan')
            ->get();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $layers
                ->flatMap(fn (LapisanPeta $layer): Collection => $layer->fiturPeta
                    ->map(fn (FiturPeta $feature): array => $this->serializeMapFeature($feature, $layer)))
                ->values(),
        ]);
    }

    public function features(LapisanPeta $layer): JsonResponse
    {
        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $layer->fiturPeta()
                ->where('aktif', true)
                ->get()
                ->map(fn (FiturPeta $feature): array => $this->serializeMapFeature($feature, $layer))
                ->values(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveLayerSummaries(): array
    {
        $publishedLayers = LapisanPeta::query()
            ->withCount('fiturPeta')
            ->where('aktif', true)
            ->orderBy('urutan')
            ->get()
            ->map(fn (LapisanPeta $layer): array => [
                'slug' => $layer->slug,
                'name' => $layer->nama,
                'features_count' => (int) $layer->fitur_peta_count,
                'department' => ['name' => 'Kominfo Sangihe'],
                'kind' => 'gis',
                'color' => $this->resolveLayerColor($layer),
            ]);

        return $publishedLayers
            ->push([
                'slug' => 'berita-kecamatan',
                'name' => 'Berita Kecamatan',
                'features_count' => Berita::query()->where('status', 'terbit')->whereNotNull('latitude')->whereNotNull('longitude')->count(),
                'department' => ['name' => 'Publikasi Wilayah'],
                'kind' => 'news',
                'color' => '#dc2626',
            ])
            ->push([
                'slug' => 'agenda-kegiatan',
                'name' => 'Agenda Kegiatan',
                'features_count' => Kegiatan::query()->where('status', 'terbit')->whereNotNull('latitude')->whereNotNull('longitude')->count(),
                'department' => ['name' => 'Agenda Daerah'],
                'kind' => 'event',
                'color' => '#0f766e',
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveWorkbenchLayers(): array
    {
        return LapisanPeta::query()
            ->with(['fiturPeta' => fn ($query) => $query->where('aktif', true)])
            ->where('aktif', true)
            ->orderBy('urutan')
            ->get()
            ->map(fn (LapisanPeta $layer): array => [
                'id' => 'layer-' . $layer->id,
                'slug' => $layer->slug,
                'name' => $layer->nama,
                'kind' => 'gis',
                'layer_type' => $layer->tipe_sumber === 'titik' ? 'point' : $layer->tipe_sumber,
                'department' => 'Kominfo Sangihe',
                'color' => $this->resolveLayerColor($layer),
                'feature_count' => $layer->fiturPeta->count(),
                'features' => [
                    'type' => 'FeatureCollection',
                    'features' => $layer->fiturPeta
                        ->map(fn (FiturPeta $feature): array => $this->serializeMapFeature($feature, $layer))
                        ->values()
                        ->all(),
                ],
            ])
            ->push($this->buildNewsLayer())
            ->push($this->buildEventLayer())
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildNewsLayer(): array
    {
        $features = Berita::query()
            ->with(['opd', 'kecamatan'])
            ->where('status', 'terbit')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest('tanggal_terbit')
            ->get()
            ->map(fn (Berita $post): array => [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float) $post->longitude, (float) $post->latitude]],
                'properties' => [
                    'id' => $post->id,
                    'name' => $post->judul,
                    'summary' => $post->ringkasan,
                    'layer_name' => 'Berita Kecamatan',
                    'layer_slug' => 'berita-kecamatan',
                    'department' => $post->opd?->nama,
                    'area' => $post->kecamatan?->nama,
                    'location_label' => $post->lokasi,
                    'popup_title' => $post->judul,
                    'popup_subtitle' => trim(implode(' - ', array_filter(['Berita', $post->kecamatan?->nama, $post->opd?->nama]))),
                    'popup_description' => $post->ringkasan,
                    'popup_image_url' => $post->gambar_sampul,
                    'published_at' => optional($post->tanggal_terbit)?->toIso8601String(),
                    'detail_url' => '/berita/' . $post->slug,
                    'color' => '#dc2626',
                    'kind' => 'news',
                ],
            ])
            ->values()
            ->all();

        return [
            'id' => 'dynamic-news',
            'slug' => 'berita-kecamatan',
            'name' => 'Berita Kecamatan',
            'kind' => 'news',
            'layer_type' => 'point',
            'department' => 'Publikasi Wilayah',
            'color' => '#dc2626',
            'feature_count' => count($features),
            'features' => ['type' => 'FeatureCollection', 'features' => $features],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEventLayer(): array
    {
        $features = Kegiatan::query()
            ->with(['opd', 'kecamatan'])
            ->where('status', 'terbit')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('mulai')
            ->get()
            ->map(fn (Kegiatan $event): array => [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float) $event->longitude, (float) $event->latitude]],
                'properties' => [
                    'id' => $event->id,
                    'name' => $event->judul,
                    'summary' => $event->uraian,
                    'layer_name' => 'Agenda Kegiatan',
                    'layer_slug' => 'agenda-kegiatan',
                    'department' => $event->opd?->nama,
                    'area' => $event->kecamatan?->nama,
                    'location_label' => $event->lokasi,
                    'popup_title' => $event->judul,
                    'popup_subtitle' => trim(implode(' - ', array_filter(['Agenda', $event->kecamatan?->nama, $event->opd?->nama]))),
                    'popup_description' => $event->uraian,
                    'starts_at' => optional($event->mulai)?->toIso8601String(),
                    'ends_at' => optional($event->selesai)?->toIso8601String(),
                    'color' => '#0f766e',
                    'kind' => 'event',
                ],
            ])
            ->values()
            ->all();

        return [
            'id' => 'dynamic-events',
            'slug' => 'agenda-kegiatan',
            'name' => 'Agenda Kegiatan',
            'kind' => 'event',
            'layer_type' => 'point',
            'department' => 'Agenda Daerah',
            'color' => '#0f766e',
            'feature_count' => count($features),
            'features' => ['type' => 'FeatureCollection', 'features' => $features],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeMapFeature(FiturPeta $feature, LapisanPeta $layer): array
    {
        $feature->loadMissing(['kecamatan', 'desa', 'opd', 'sumberData']);

        return [
            'type' => 'Feature',
            'properties' => [
                'id' => $feature->id,
                'name' => $feature->nama,
                'summary' => $feature->properti_json['summary'] ?? null,
                'layer_name' => $layer->nama,
                'layer_slug' => $layer->slug,
                'department' => $feature->opd?->nama ?? $feature->sumberData?->opd?->nama ?? 'Kominfo Sangihe',
                'opd_id' => $feature->opd_id ?? $feature->sumberData?->opd_id,
                'kecamatan_id' => $feature->kecamatan_id,
                'kecamatan' => $feature->kecamatan?->nama,
                'desa_id' => $feature->desa_id,
                'desa' => $feature->desa?->nama,
                'sumber_data_id' => $feature->sumber_data_id,
                'sumber_data' => $feature->sumberData?->nama,
                'jenis_fasilitas' => $feature->sumberData?->jenis,
                'popup_title' => $feature->nama,
                'popup_subtitle' => $feature->kecamatan?->nama ?? 'Layer publik',
                'popup_description' => $feature->properti_json['description'] ?? $feature->properti_json['summary'] ?? null,
                'location_label' => $feature->properti_json['location_label'] ?? null,
                'color' => $this->resolveLayerColor($layer),
                'kind' => 'gis',
                ...($feature->properti_json ?? []),
            ],
            'geometry' => $this->resolveGeometry($feature),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveGeometry(FiturPeta $feature): ?array
    {
        if ($feature->geojson) {
            $geojson = json_decode($feature->geojson, true);

            if (is_array($geojson)) {
                return $geojson;
            }
        }

        if ($feature->latitude !== null && $feature->longitude !== null) {
            return ['type' => 'Point', 'coordinates' => [(float) $feature->longitude, (float) $feature->latitude]];
        }

        return null;
    }

    private function resolveLayerColor(LapisanPeta $layer): string
    {
        $config = $layer->konfigurasi_json ?? [];

        return $config['color'] ?? $config['fill'] ?? $config['stroke'] ?? '#2563eb';
    }
}
