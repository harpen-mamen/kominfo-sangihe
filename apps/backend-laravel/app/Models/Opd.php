<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends ModelIndonesia
{
    protected $table = 'opd';

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function berita(): HasMany
    {
        return $this->hasMany(Berita::class);
    }

    public function kegiatan(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }

    public function sumberData(): HasMany
    {
        return $this->hasMany(SumberData::class);
    }

    public function indikatorData(): HasMany
    {
        return $this->hasMany(IndikatorData::class);
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
