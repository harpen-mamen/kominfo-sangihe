<?php

namespace App\Models;

use App\Support\AdminScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class PengajuanData extends ModelIndonesia
{
    protected $table = 'pengajuan_data';

    protected static function booted(): void
    {
        static::saving(function (self $pengajuan): void {
            $user = auth()->user();

            if (! $user instanceof User) {
                return;
            }

            if (AdminScope::isSubdistrict($user)) {
                if (blank($user->kecamatan_id)) {
                    throw ValidationException::withMessages([
                        'kecamatan_id' => 'Admin kecamatan wajib terhubung dengan kecamatan aktif.',
                    ]);
                }

                $pengajuan->kecamatan_id = $user->kecamatan_id;
                $pengajuan->opd_id = null;
            }

            if (AdminScope::isDepartment($user)) {
                if (blank($user->opd_id)) {
                    throw ValidationException::withMessages([
                        'opd_id' => 'Admin OPD wajib terhubung dengan OPD aktif.',
                    ]);
                }

                $pengajuan->opd_id = $user->opd_id;
                $pengajuan->kecamatan_id = null;
            }

            if (blank($pengajuan->kecamatan_id) && blank($pengajuan->opd_id)) {
                throw ValidationException::withMessages([
                    'kecamatan_id' => 'Pengajuan data harus terkait kecamatan atau OPD.',
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'tanggal_kirim' => 'datetime',
            'tanggal_verifikasi' => 'datetime',
            'tanggal_terbit' => 'datetime',
        ];
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function periodeData(): BelongsTo
    {
        return $this->belongsTo(PeriodeData::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function dikirimOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }

    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function nilaiDataMentah(): HasMany
    {
        return $this->hasMany(NilaiDataMentah::class);
    }
}
