<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Support\Str;
use RuntimeException;

class SangiheRegionSyncService
{
    public function sync(): void
    {
        $districtFeatures = $this->readFeatures(base_path('database/data/maps/sangihe-kecamatan.geojson'));
        $villageFeatures = $this->readFeatures(base_path('database/data/maps/sangihe-desa.geojson'));

        $districts = collect($districtFeatures)
            ->map(fn (array $feature): array => [
                'kode' => (string) data_get($feature, 'properties.code'),
                'nama' => (string) data_get($feature, 'properties.name'),
            ])
            ->filter(fn (array $district): bool => filled($district['kode']) && filled($district['nama']))
            ->unique('kode')
            ->sortBy('nama')
            ->values();

        foreach ($districts as $district) {
            Kecamatan::query()->updateOrCreate(
                ['kode' => $district['kode']],
                [
                    'nama' => $district['nama'],
                    'aktif' => true,
                ],
            );
        }

        $districtLookup = Kecamatan::query()
            ->get()
            ->mapWithKeys(fn (Kecamatan $kecamatan): array => [$this->normalize($kecamatan->nama) => $kecamatan->id]);

        $villages = collect($villageFeatures)
            ->map(function (array $feature) use ($districtLookup): ?array {
                $districtName = (string) data_get($feature, 'properties.district');
                $districtId = $districtLookup->get($this->normalize($districtName));

                if (! $districtId) {
                    return null;
                }

                return [
                    'kode' => (string) data_get($feature, 'properties.code'),
                    'nama' => (string) data_get($feature, 'properties.name'),
                    'kecamatan_id' => $districtId,
                ];
            })
            ->filter(fn (?array $village): bool => filled($village['kode'] ?? null) && filled($village['nama'] ?? null))
            ->unique('kode')
            ->sortBy(['kecamatan_id', 'nama'])
            ->values();

        foreach ($villages as $village) {
            Desa::query()->updateOrCreate(
                ['kode' => $village['kode']],
                [
                    'kecamatan_id' => $village['kecamatan_id'],
                    'nama' => $village['nama'],
                    'aktif' => true,
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readFeatures(string $filePath): array
    {
        if (! is_file($filePath)) {
            throw new RuntimeException("File wilayah tidak ditemukan: {$filePath}");
        }

        $payload = json_decode((string) file_get_contents($filePath), true);
        $features = $payload['features'] ?? null;

        if (! is_array($features)) {
            throw new RuntimeException("File wilayah tidak valid: {$filePath}");
        }

        return array_values(array_filter($features, 'is_array'));
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
