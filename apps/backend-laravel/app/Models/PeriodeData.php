<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeData extends ModelIndonesia
{
    protected $table = 'periode_data';

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'terkunci' => 'boolean',
        ];
    }

    public function pengajuanData(): HasMany
    {
        return $this->hasMany(PengajuanData::class);
    }

    public function ringkasanStatistik(): HasMany
    {
        return $this->hasMany(RingkasanStatistik::class);
    }
}
