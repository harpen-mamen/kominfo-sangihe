<?php

namespace App\Services;

use App\Models\FiturPeta;
use App\Models\Kecamatan;
use App\Models\LapisanPeta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SangiheBoundaryImportService
{
    /**
     * @return array<string, int>
     */
    public function import(): array
    {
        $kecamatanLookup = Kecamatan::query()
            ->get()
            ->mapWithKeys(fn (Kecamatan $kecamatan): array => [$this->normalize($kecamatan->nama) => $kecamatan->id])
            ->all();

        return DB::transaction(function () use ($kecamatanLookup): array {
            $kecamatanCount = $this->importLayer(
                filePath: base_path('database/data/maps/sangihe-kecamatan.geojson'),
                name: 'Batas Kecamatan',
                slug: 'batas-kecamatan',
                color: '#0d6efd',
                kecamatanLookup: $kecamatanLookup,
                areaKey: 'name',
                districtKey: 'name',
            );

            $desaCount = $this->importLayer(
                filePath: base_path('database/data/maps/sangihe-desa.geojson'),
                name: 'Batas Desa',
                slug: 'batas-desa',
                color: '#20c997',
                kecamatanLookup: $kecamatanLookup,
                areaKey: 'name',
                districtKey: 'district',
            );

            return [
                'kecamatan' => $kecamatanCount,
                'desa' => $desaCount,
            ];
        });
    }

    /**
     * @param  array<string, int>  $kecamatanLookup
     */
    private function importLayer(
        string $filePath,
        string $name,
        string $slug,
        string $color,
        array $kecamatanLookup,
        string $areaKey,
        string $districtKey,
    ): int {
        if (! is_file($filePath)) {
            throw new RuntimeException("File boundary tidak ditemukan: {$filePath}");
        }

        $payload = json_decode((string) file_get_contents($filePath), true);

        if (! is_array($payload) || ! isset($payload['features']) || ! is_array($payload['features'])) {
            throw new RuntimeException("File boundary tidak valid: {$filePath}");
        }

        $layer = LapisanPeta::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'nama' => $name,
                'kategori' => 'batas_wilayah',
                'tipe_sumber' => 'geojson',
                'hanya_admin_kominfo' => true,
                'konfigurasi_json' => [
                    'color' => $color,
                    'fillOpacity' => 0.18,
                    'weight' => $slug === 'batas-kecamatan' ? 2 : 1,
                ],
                'aktif' => true,
                'urutan' => $slug === 'batas-kecamatan' ? 1 : 2,
            ],
        );

        FiturPeta::query()->where('lapisan_peta_id', $layer->id)->delete();

        $count = 0;

        foreach ($payload['features'] as $feature) {
            $properties = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
            $geometry = $feature['geometry'] ?? null;

            if (! is_array($geometry) || ! isset($geometry['type'])) {
                continue;
            }

            $districtName = (string) ($properties[$districtKey] ?? '');
            $kecamatanId = $kecamatanLookup[$this->normalize($districtName)] ?? null;
            $featureName = (string) ($properties[$areaKey] ?? 'Wilayah');

            FiturPeta::query()->create([
                'lapisan_peta_id' => $layer->id,
                'kecamatan_id' => $kecamatanId,
                'nama' => $featureName,
                'jenis_geometri' => $this->resolveGeometryType((string) $geometry['type']),
                'geojson' => json_encode($geometry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'properti_json' => [
                    'boundary_type' => $slug === 'batas-kecamatan' ? 'kecamatan' : 'desa',
                    'code' => $properties['code'] ?? null,
                    'district' => $districtName ?: null,
                    'district_code' => $properties['district_code'] ?? null,
                    'regency' => $properties['regency'] ?? null,
                    'regency_code' => $properties['regency_code'] ?? null,
                    'province' => $properties['province'] ?? null,
                    'province_code' => $properties['province_code'] ?? null,
                    'villages_count' => $properties['villages_count'] ?? null,
                    'source' => $properties['source'] ?? null,
                    'valid_on' => $properties['valid_on'] ?? null,
                ],
                'sumber_input' => 'file',
                'aktif' => true,
            ]);

            $count++;
        }

        return $count;
    }

    private function resolveGeometryType(string $type): string
    {
        return match (Str::lower($type)) {
            'point', 'multipoint' => 'point',
            'linestring', 'multilinestring' => 'line',
            default => 'polygon',
        };
    }

    private function normalize(?string $value): string
    {
        return Str::of((string) $value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->value();
    }
}
