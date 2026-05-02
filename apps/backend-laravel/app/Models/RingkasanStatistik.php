<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RingkasanStatistik extends ModelIndonesia
{
    protected $table = 'ringkasan_statistik';

    protected function casts(): array
    {
        return [
            'nilai_total' => 'decimal:2',
            'nilai_persen' => 'decimal:2',
            'persentase_kelengkapan' => 'decimal:2',
            'jumlah_sumber_masuk' => 'integer',
            'jumlah_sumber_wajib' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function periodeData(): BelongsTo
    {
        return $this->belongsTo(PeriodeData::class);
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

    public function indikatorData(): BelongsTo
    {
        return $this->belongsTo(IndikatorData::class);
    }
}
