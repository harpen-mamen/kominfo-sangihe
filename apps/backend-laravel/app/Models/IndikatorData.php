<?php

namespace App\Models;

use App\Support\AdminScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class IndikatorData extends ModelIndonesia
{
    protected $table = 'indikator_data';

    protected static function booted(): void
    {
        static::saving(function (self $indikator): void {
            $user = auth()->user();

            if (! $user instanceof User || ! AdminScope::isDepartment($user)) {
                return;
            }

            if (blank($user->opd_id)) {
                throw ValidationException::withMessages([
                    'opd_id' => 'Admin OPD wajib terhubung dengan OPD aktif.',
                ]);
            }

            $indikator->opd_id = $user->opd_id;
        });
    }

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'boleh_diinput_kecamatan' => 'boolean',
            'boleh_diinput_opd' => 'boolean',
        ];
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function nilaiDataMentah(): HasMany
    {
        return $this->hasMany(NilaiDataMentah::class);
    }

    public function ringkasanStatistik(): HasMany
    {
        return $this->hasMany(RingkasanStatistik::class);
    }
}
