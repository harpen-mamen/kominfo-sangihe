<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

abstract class KontenPublik extends Konten
{
    protected static string $jenisKonten = 'konten';

    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('jenis_konten', function (Builder $query): void {
            $query->where('jenis_konten', static::$jenisKonten);
        });

        static::creating(function (self $model): void {
            $model->jenis_konten ??= static::$jenisKonten;
        });
    }
}
