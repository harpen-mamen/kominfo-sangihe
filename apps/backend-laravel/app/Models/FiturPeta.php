<?php

namespace App\Models;

use App\Support\AdminScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class FiturPeta extends ModelIndonesia
{
    protected $table = 'fitur_peta';

    protected static function booted(): void
    {
        static::saving(function (self $fitur): void {
            $fitur->loadMissing(['lapisanPeta', 'desa', 'sumberData']);

            if ($fitur->desa_id && ! $fitur->kecamatan_id && $fitur->desa) {
                $fitur->kecamatan_id = $fitur->desa->kecamatan_id;
            }

            if ($fitur->desa && $fitur->kecamatan_id && $fitur->desa->kecamatan_id !== $fitur->kecamatan_id) {
                throw ValidationException::withMessages([
                    'desa_id' => 'Desa fitur peta harus berada pada kecamatan yang sama.',
                ]);
            }

            if ($fitur->sumberData?->kecamatan_id && $fitur->kecamatan_id && $fitur->sumberData->kecamatan_id !== $fitur->kecamatan_id) {
                throw ValidationException::withMessages([
                    'sumber_data_id' => 'Sumber data tidak berada pada kecamatan yang sama.',
                ]);
            }

            if ($fitur->sumberData?->opd_id && $fitur->opd_id && $fitur->sumberData->opd_id !== $fitur->opd_id) {
                throw ValidationException::withMessages([
                    'sumber_data_id' => 'Sumber data tidak berada pada OPD yang sama.',
                ]);
            }

            $user = auth()->user();

            if ($user instanceof User) {
                if (AdminScope::isSubdistrict($user)) {
                    if (blank($user->kecamatan_id)) {
                        throw ValidationException::withMessages([
                            'kecamatan_id' => 'Admin kecamatan wajib terhubung dengan kecamatan aktif.',
                        ]);
                    }

                    if ($fitur->lapisanPeta?->hanya_admin_kominfo || in_array($fitur->lapisanPeta?->slug, ['batas-kecamatan', 'batas-desa', 'batas-kecamatan-sangihe', 'batas-desa-sangihe'], true)) {
                        throw ValidationException::withMessages([
                            'lapisan_peta_id' => 'Admin kecamatan tidak boleh mengubah batas wilayah resmi.',
                        ]);
                    }

                    $fitur->kecamatan_id = $user->kecamatan_id;

                    if ($fitur->desa && $fitur->desa->kecamatan_id !== $user->kecamatan_id) {
                        throw ValidationException::withMessages([
                            'desa_id' => 'Admin kecamatan tidak boleh menyimpan desa di luar kecamatannya.',
                        ]);
                    }
                }

                if (AdminScope::isDepartment($user)) {
                    if (blank($user->opd_id)) {
                        throw ValidationException::withMessages([
                            'opd_id' => 'Admin OPD wajib terhubung dengan OPD aktif.',
                        ]);
                    }

                    if ($fitur->lapisanPeta?->hanya_admin_kominfo) {
                        throw ValidationException::withMessages([
                            'lapisan_peta_id' => 'Admin OPD tidak boleh mengubah layer khusus Kominfo.',
                        ]);
                    }

                    $fitur->opd_id = $user->opd_id;
                }

                $fitur->dibuat_oleh ??= $user->id;
            }

            $fitur->sumber_input ??= 'manual';

            if ($fitur->jenis_geometri !== 'point') {
                return;
            }

            if ($fitur->latitude === null || $fitur->longitude === null) {
                return;
            }

            $fitur->geojson = json_encode([
                'type' => 'Point',
                'coordinates' => [
                    (float) $fitur->longitude,
                    (float) $fitur->latitude,
                ],
            ], JSON_UNESCAPED_SLASHES);
        });
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'properti_json' => 'array',
            'sumber_input' => 'string',
            'aktif' => 'boolean',
        ];
    }

    public function lapisanPeta(): BelongsTo
    {
        return $this->belongsTo(LapisanPeta::class);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function sumberData(): BelongsTo
    {
        return $this->belongsTo(SumberData::class);
    }

    public function konten(): BelongsTo
    {
        return $this->belongsTo(Konten::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
