<?php

namespace App\Models;

use App\Support\AdminScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class NilaiDataMentah extends ModelIndonesia
{
    protected $table = 'nilai_data_mentah';

    protected static function booted(): void
    {
        static::saving(function (self $nilai): void {
            $nilai->loadMissing(['desa', 'indikatorData', 'pengajuanData', 'sumberData']);

            $user = auth()->user();

            if (! $user instanceof User) {
                return;
            }

            if (! $nilai->pengajuanData) {
                throw ValidationException::withMessages([
                    'pengajuan_data_id' => 'Pengajuan data tidak ditemukan.',
                ]);
            }

            if (! $nilai->indikatorData) {
                throw ValidationException::withMessages([
                    'indikator_data_id' => 'Indikator data tidak ditemukan.',
                ]);
            }

            if (! $nilai->pengajuanData->canInputValues()) {
                throw ValidationException::withMessages([
                    'pengajuan_data_id' => 'Nilai hanya dapat diedit saat pengajuan berstatus draft atau revisi.',
                ]);
            }

            $nilai->tipe_sumber ??= $nilai->desa_id ? 'desa' : 'kecamatan';
            $nilai->sumber_id ??= $nilai->desa_id ?: $nilai->pengajuanData->kecamatan_id;
            $nilai->nilai_decimal = $nilai->normalisasiNilaiDecimal();
            $nilai->nilai = $nilai->nilai_decimal ?? 0;

            if ($nilai->desa && $nilai->pengajuanData->kecamatan_id && $nilai->desa->kecamatan_id !== $nilai->pengajuanData->kecamatan_id) {
                throw ValidationException::withMessages([
                    'desa_id' => 'Desa harus berada pada kecamatan pengajuan yang sama.',
                ]);
            }

            if ($nilai->sumberData?->opd_id && $nilai->pengajuanData->opd_id && $nilai->sumberData->opd_id !== $nilai->pengajuanData->opd_id) {
                throw ValidationException::withMessages([
                    'sumber_data_id' => 'Sumber data tidak berada pada OPD pengajuan yang sama.',
                ]);
            }

            if (AdminScope::isSubdistrict($user)) {
                if ($nilai->pengajuanData->kecamatan_id !== $user->kecamatan_id) {
                    throw ValidationException::withMessages([
                        'pengajuan_data_id' => 'Admin kecamatan tidak boleh memakai pengajuan di luar kecamatannya.',
                    ]);
                }

                if ($nilai->desa && $nilai->desa->kecamatan_id !== $user->kecamatan_id) {
                    throw ValidationException::withMessages([
                        'desa_id' => 'Admin kecamatan tidak boleh menyimpan desa di luar kecamatannya.',
                    ]);
                }

                if (! AdminScope::canInputIndicator($user, $nilai->indikatorData)) {
                    throw ValidationException::withMessages([
                        'indikator_data_id' => 'Admin kecamatan hanya boleh menginput indikator yang dibuka untuk kecamatan.',
                    ]);
                }
            }

            if (AdminScope::isDepartment($user)) {
                if ($nilai->pengajuanData->opd_id !== $user->opd_id) {
                    throw ValidationException::withMessages([
                        'pengajuan_data_id' => 'Admin OPD tidak boleh memakai pengajuan milik OPD lain.',
                    ]);
                }

                if ($nilai->sumberData?->opd_id && $nilai->sumberData->opd_id !== $user->opd_id) {
                    throw ValidationException::withMessages([
                        'sumber_data_id' => 'Admin OPD tidak boleh memakai sumber data milik OPD lain.',
                    ]);
                }

                if (! AdminScope::canInputIndicator($user, $nilai->indikatorData)) {
                    throw ValidationException::withMessages([
                        'indikator_data_id' => 'Admin OPD hanya boleh menginput indikator milik OPD sendiri.',
                    ]);
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
            'nilai_decimal' => 'decimal:4',
            'is_tidak_tersedia' => 'boolean',
        ];
    }

    public function normalisasiNilaiDecimal(): ?string
    {
        if ($this->is_tidak_tersedia) {
            return null;
        }

        if ($this->nilai_decimal !== null && $this->nilai_decimal !== '') {
            return (string) $this->nilai_decimal;
        }

        if ($this->nilai !== null && $this->nilai !== '') {
            return (string) $this->nilai;
        }

        return null;
    }

    public function hasUsableValue(): bool
    {
        return (bool) $this->is_tidak_tersedia
            || $this->nilai_decimal !== null
            || filled($this->nilai_text);
    }

    public function pengajuanData(): BelongsTo
    {
        return $this->belongsTo(PengajuanData::class);
    }

    public function indikatorData(): BelongsTo
    {
        return $this->belongsTo(IndikatorData::class);
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function sumberData(): BelongsTo
    {
        return $this->belongsTo(SumberData::class);
    }
}
