<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAudit extends AktivitasSistem
{
    protected static string $kategoriAktivitas = 'audit';

    protected function casts(): array
    {
        return [
            'nilai_lama_json' => 'array',
            'nilai_baru_json' => 'array',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}
