<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\FiturPeta;
use App\Models\Kecamatan;
use App\Models\LapisanPeta;
use App\Models\User;
use App\Support\AdminScope;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class BoundaryUploadService
{
    /**
     * @return array<string, mixed>
     */
    public function uploadGeoJson(
        UploadedFile $uploadedFile,
        int $lapisanPetaId,
        ?int $kecamatanId,
        ?int $desaId,
        User $actor,
    ): array {
        if (! AdminScope::isKominfo($actor)) {
            throw ValidationException::withMessages([
                'lapisanPetaId' => 'Hanya admin Kominfo yang boleh mengunggah batas wilayah resmi.',
            ]);
        }

        $layer = LapisanPeta::query()->findOrFail($lapisanPetaId);
        $kecamatan = $kecamatanId ? Kecamatan::query()->findOrFail($kecamatanId) : null;
        $desa = $desaId ? Desa::query()->findOrFail($desaId) : null;
        $context = $this->resolveBoundaryContext($layer);

        if (! $context) {
            throw ValidationException::withMessages([
                'lapisanPetaId' => 'Layer yang dipilih bukan layer batas wilayah resmi.',
            ]);
        }

        if ($context === 'kecamatan' && ! $kecamatan) {
            throw ValidationException::withMessages([
                'kecamatanId' => 'Pilih kecamatan untuk layer batas kecamatan.',
            ]);
        }

        if ($context === 'desa' && ! $desa) {
            throw ValidationException::withMessages([
                'desaId' => 'Pilih desa untuk layer batas desa.',
            ]);
        }

        if ($desa && $kecamatan && $desa->kecamatan_id !== $kecamatan->id) {
            throw ValidationException::withMessages([
                'desaId' => 'Desa yang dipilih tidak berada pada kecamatan yang sama.',
            ]);
        }

        if ($desa && ! $kecamatan) {
            $kecamatan = $desa->kecamatan;
        }

        $payload = json_decode((string) file_get_contents($uploadedFile->getRealPath()), true);
        $features = $this->normalizeGeoJsonPayload($payload);

        if (! count($features)) {
            throw ValidationException::withMessages([
                'geojsonFile' => 'File GeoJSON tidak memiliki feature yang bisa diproses.',
            ]);
        }

        $storedPath = $uploadedFile->storeAs(
            'boundaries',
            now()->format('YmdHis') . '-' . Str::slug(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME)) . '.geojson',
            'public',
        );

        return DB::transaction(function () use ($actor, $context, $desa, $features, $kecamatan, $layer, $storedPath): array {
            $replaceQuery = FiturPeta::query()
                ->where('lapisan_peta_id', $layer->id)
                ->where('sumber_input', 'file');

            if ($context === 'kecamatan' && $kecamatan) {
                $replaceQuery->where('kecamatan_id', $kecamatan->id)->whereNull('desa_id');
            }

            if ($context === 'desa' && $desa) {
                $replaceQuery->where('desa_id', $desa->id);
            }

            $replaceQuery->delete();

            $count = 0;

            foreach ($features as $feature) {
                $geometry = $feature['geometry'] ?? null;
                $properties = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];

                if (! is_array($geometry)) {
                    continue;
                }

                $geometryType = $this->resolveGeometryType((string) ($geometry['type'] ?? ''));

                if (! in_array($geometryType, ['polygon', 'multipolygon'], true)) {
                    throw ValidationException::withMessages([
                        'geojsonFile' => 'GeoJSON batas wilayah hanya menerima geometri polygon atau multipolygon.',
                    ]);
                }

                FiturPeta::query()->create([
                    'lapisan_peta_id' => $layer->id,
                    'kecamatan_id' => $kecamatan?->id,
                    'desa_id' => $desa?->id,
                    'dibuat_oleh' => $actor->id,
                    'nama' => $properties['name']
                        ?? $properties['nama']
                        ?? $desa?->nama
                        ?? $kecamatan?->nama
                        ?? $layer->nama,
                    'jenis_geometri' => $geometryType,
                    'geojson' => json_encode($geometry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'properti_json' => array_filter([
                        'boundary_type' => $context,
                        'district' => $kecamatan?->nama,
                        'village' => $desa?->nama,
                        'source_name' => basename($storedPath),
                        ...$properties,
                    ], fn ($value) => $value !== null && $value !== ''),
                    'sumber_input' => 'file',
                    'file_path' => $storedPath,
                    'aktif' => true,
                ]);

                $count++;
            }

            return [
                'count' => $count,
                'layer' => $layer->nama,
                'file_path' => Storage::disk('public')->url($storedPath),
            ];
        });
    }

    public function importKml(): never
    {
        throw new RuntimeException('Parser KML belum diaktifkan pada project ini.');
    }

    public function importShapefile(): never
    {
        throw new RuntimeException('Parser SHP belum diaktifkan pada project ini.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeGeoJsonPayload(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $type = Str::lower((string) ($payload['type'] ?? ''));

        return match ($type) {
            'featurecollection' => array_values(array_filter($payload['features'] ?? [], 'is_array')),
            'feature' => [$payload],
            'polygon', 'multipolygon' => [[
                'type' => 'Feature',
                'geometry' => $payload,
                'properties' => [],
            ]],
            default => [],
        };
    }

    private function resolveBoundaryContext(LapisanPeta $layer): ?string
    {
        return match (true) {
            str_contains($layer->slug, 'kecamatan') => 'kecamatan',
            str_contains($layer->slug, 'desa') => 'desa',
            $layer->kategori === 'batas_wilayah' && $layer->hanya_admin_kominfo => 'kecamatan',
            default => null,
        };
    }

    private function resolveGeometryType(string $type): string
    {
        return match (Str::lower($type)) {
            'multipolygon' => 'multipolygon',
            default => 'polygon',
        };
    }
}
