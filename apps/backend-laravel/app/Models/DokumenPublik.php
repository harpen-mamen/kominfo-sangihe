<?php

namespace App\Models;

use App\Support\AdminScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DokumenPublik extends ModelIndonesia
{
    protected $table = 'dokumen_publik';

    protected static function booted(): void
    {
        static::saving(function (self $dokumen): void {
            $user = auth()->user();

            if (blank($dokumen->slug) && filled($dokumen->judul)) {
                $dokumen->slug = static::uniqueSlug($dokumen->judul, $dokumen->id);
            }

            if ($dokumen->desa_id && blank($dokumen->kecamatan_id)) {
                $dokumen->loadMissing('desa');
                $dokumen->kecamatan_id = $dokumen->desa?->kecamatan_id;
            }

            if ($dokumen->status === 'terbit' && blank($dokumen->tanggal_terbit)) {
                $dokumen->tanggal_terbit = Carbon::now();
            }

            if (! $user instanceof User) {
                return;
            }

            if (! $dokumen->exists && blank($dokumen->dikirim_oleh)) {
                $dokumen->dikirim_oleh = $user->id;
            }

            if (AdminScope::isSubdistrict($user)) {
                if (blank($user->kecamatan_id)) {
                    throw ValidationException::withMessages(['kecamatan_id' => 'Admin kecamatan wajib memiliki kecamatan.']);
                }

                $dokumen->kecamatan_id = $user->kecamatan_id;
                $dokumen->opd_id = null;

                if ($dokumen->desa_id) {
                    $dokumen->loadMissing('desa');

                    if ((int) $dokumen->desa?->kecamatan_id !== (int) $user->kecamatan_id) {
                        throw ValidationException::withMessages(['desa_id' => 'Desa harus berada dalam kecamatan Anda.']);
                    }
                }

                if (in_array($dokumen->status, ['ditinjau', 'terbit', 'ditolak'], true)) {
                    throw ValidationException::withMessages(['status' => 'Admin kecamatan tidak boleh publish atau menolak dokumen sendiri.']);
                }
            }

            if (AdminScope::isDepartment($user)) {
                if (blank($user->opd_id)) {
                    throw ValidationException::withMessages(['opd_id' => 'Admin OPD wajib memiliki OPD.']);
                }

                $dokumen->opd_id = $user->opd_id;

                if (in_array($dokumen->status, ['ditinjau', 'terbit', 'ditolak'], true)) {
                    throw ValidationException::withMessages(['status' => 'Admin OPD tidak boleh publish atau menolak dokumen sendiri.']);
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'tanggal_dokumen' => 'date',
            'tanggal_terbit' => 'datetime',
        ];
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'dokumen';
        $slug = $base;
        $counter = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
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

    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }

    public function peninjau(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditinjau_oleh');
    }
}
