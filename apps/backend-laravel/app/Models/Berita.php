<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Berita extends KontenPublik
{
    protected static string $jenisKonten = 'berita';

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

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penulis_id');
    }

    public function ditinjauOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditinjau_oleh');
    }
}
