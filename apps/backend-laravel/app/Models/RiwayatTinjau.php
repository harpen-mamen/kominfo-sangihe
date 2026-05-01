<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatTinjau extends AktivitasSistem
{
    protected static string $kategoriAktivitas = 'tinjau';

    public function peninjau(): BelongsTo
    {
        return $this->belongsTo(User::class, 'peninjau_id');
    }
}
