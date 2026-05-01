<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

abstract class AktivitasSistem extends ModelIndonesia
{
    protected $table = 'aktivitas_sistem';

    protected static string $kategoriAktivitas = 'umum';

    protected static function booted(): void
    {
        static::addGlobalScope('kategori_aktivitas', function (Builder $query): void {
            $query->where('kategori_aktivitas', static::$kategoriAktivitas);
        });

        static::creating(function (self $model): void {
            $model->kategori_aktivitas ??= static::$kategoriAktivitas;
        });
    }
}
