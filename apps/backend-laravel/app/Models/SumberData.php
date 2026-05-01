<?php

namespace App\Models;

use App\Support\AdminScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class SumberData extends ModelIndonesia
{
    protected $table = 'sumber_data';

    protected static function booted(): void
    {
        static::saving(function (self $sumberData): void {
            $sumberData->loadMissing('desa');

            if ($sumberData->desa_id && ! $sumberData->kecamatan_id && $sumberData->desa) {
                $sumberData->kecamatan_id = $sumberData->desa->kecamatan_id;
            }

            if ($sumberData->desa && $sumberData->kecamatan_id && $sumberData->desa->kecamatan_id !== $sumberData->kecamatan_id) {
                throw ValidationException::withMessages([
                    'desa_id' => 'Desa sumber data harus berada pada kecamatan yang sama.',
                ]);
            }

            $user = auth()->user();

            if (! $user instanceof User) {
                return;
            }

            if (AdminScope::isSubdistrict($user)) {
                if (blank($user->kecamatan_id)) {
                    throw ValidationException::withMessages([
                        'kecamatan_id' => 'Admin kecamatan wajib terhubung dengan kecamatan aktif.',
                    ]);
                }

                $sumberData->kecamatan_id = $user->kecamatan_id;

                if ($sumberData->desa && $sumberData->desa->kecamatan_id !== $user->kecamatan_id) {
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

                $sumberData->opd_id = $user->opd_id;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
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

    public function nilaiDataMentah(): HasMany
    {
        return $this->hasMany(NilaiDataMentah::class);
    }

    public function fiturPeta(): HasMany
    {
        return $this->hasMany(FiturPeta::class);
    }
}
