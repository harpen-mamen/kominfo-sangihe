<?php

namespace App\Models;

use App\Support\AdminScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Konten extends ModelIndonesia
{
    protected $table = 'konten';

    protected static function booted(): void
    {
        static::saving(function (self $konten): void {
            $konten->loadMissing('desa');

            if ($konten->desa_id && ! $konten->kecamatan_id && $konten->desa) {
                $konten->kecamatan_id = $konten->desa->kecamatan_id;
            }

            if ($konten->desa && $konten->kecamatan_id && $konten->desa->kecamatan_id !== $konten->kecamatan_id) {
                throw ValidationException::withMessages([
                    'desa_id' => 'Desa konten harus berada pada kecamatan yang sama.',
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

                $konten->kecamatan_id = $user->kecamatan_id;
            }

            if (AdminScope::isDepartment($user)) {
                if (blank($user->opd_id)) {
                    throw ValidationException::withMessages([
                        'opd_id' => 'Admin OPD wajib terhubung dengan OPD aktif.',
                    ]);
                }

                $konten->opd_id = $user->opd_id;
            }

            $konten->pembuat_id ??= $user->id;
            $konten->penulis_id ??= $user->id;
        });
    }

    protected function casts(): array
    {
        return [
            'tanggal_terbit' => 'datetime',
            'unggulan' => 'boolean',
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

    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penulis_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }

    public function fiturPeta(): HasMany
    {
        return $this->hasMany(FiturPeta::class);
    }
}
