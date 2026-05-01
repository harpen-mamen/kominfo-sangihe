<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends ModelIndonesia
{
    protected $table = 'kecamatan';

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function desa(): HasMany
    {
        return $this->hasMany(Desa::class);
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function pengajuanData(): HasMany
    {
        return $this->hasMany(PengajuanData::class);
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

    public function ringkasanStatistik(): HasMany
    {
        return $this->hasMany(RingkasanStatistik::class);
    }
}
