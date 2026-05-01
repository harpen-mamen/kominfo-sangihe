<?php

namespace App\Models;

use App\Support\AdminScope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class LapisanPeta extends ModelIndonesia
{
    protected $table = 'lapisan_peta';

    protected static function booted(): void
    {
        static::saving(function (self $lapisan): void {
            $user = auth()->user();

            if ($user instanceof User && ! AdminScope::isKominfo($user)) {
                throw ValidationException::withMessages([
                    'nama' => 'Hanya admin Kominfo yang dapat mengubah layer peta.',
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'konfigurasi_json' => 'array',
            'hanya_admin_kominfo' => 'boolean',
            'aktif' => 'boolean',
        ];
    }

    public function fiturPeta(): HasMany
    {
        return $this->hasMany(FiturPeta::class);
    }
}
