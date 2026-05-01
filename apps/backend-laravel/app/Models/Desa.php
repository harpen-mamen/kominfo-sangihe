<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Desa extends ModelIndonesia
{
    protected $table = 'desa';

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function nilaiDataMentah(): HasMany
    {
        return $this->hasMany(NilaiDataMentah::class);
    }

    public function ringkasanStatistik(): HasMany
    {
        return $this->hasMany(RingkasanStatistik::class);
    }

    public function sumberData(): HasMany
    {
        return $this->hasMany(SumberData::class);
    }

    public function fiturPeta(): HasMany
    {
        return $this->hasMany(FiturPeta::class);
    }

    public function konten(): HasMany
    {
        return $this->hasMany(Konten::class);
    }
}
