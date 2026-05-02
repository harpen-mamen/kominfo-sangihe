<?php

namespace App\Observers;

use App\Models\NilaiDataMentah;
use App\Models\User;
use App\Services\ServiceAgregasiStatistik;
use App\Services\SumberDataFilterService;
use App\Support\AdminScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class NilaiDataMentahObserver
{
    public function saving(NilaiDataMentah $nilai): void
    {
        $nilai->load(['desa', 'indikatorData', 'pengajuanData.periodeData', 'sumberData']);

        if (! $nilai->indikatorData?->aktif) {
            throw ValidationException::withMessages([
                'indikator_data_id' => 'Indikator data tidak aktif.',
            ]);
        }

        if ($nilai->pengajuanData?->periodeData?->terkunci) {
            throw ValidationException::withMessages([
                'periode_data_id' => 'Periode data sudah terkunci.',
            ]);
        }

        if ($nilai->pengajuanData && ! $nilai->pengajuanData->canInputValues()) {
            throw ValidationException::withMessages([
                'pengajuan_data_id' => 'Nilai hanya dapat diedit saat pengajuan berstatus draft atau revisi.',
            ]);
        }

        $nilai->tipe_sumber ??= $nilai->desa_id ? 'desa' : 'kecamatan';
        $nilai->sumber_id ??= $nilai->desa_id ?: $nilai->pengajuanData?->kecamatan_id;
        $nilai->nilai_decimal = $nilai->normalisasiNilaiDecimal();
        $nilai->nilai = $nilai->nilai_decimal ?? 0;

        if ($nilai->is_tidak_tersedia && blank($nilai->catatan)) {
            throw ValidationException::withMessages([
                'catatan' => 'Catatan wajib diisi jika nilai ditandai tidak tersedia.',
            ]);
        }

        if (! $nilai->is_tidak_tersedia && $nilai->nilai_decimal !== null && $nilai->indikatorData) {
            if ($nilai->indikatorData->batas_min !== null && (float) $nilai->nilai_decimal < (float) $nilai->indikatorData->batas_min) {
                throw ValidationException::withMessages([
                    'nilai_decimal' => 'Nilai berada di bawah batas minimum indikator.',
                ]);
            }

            if ($nilai->indikatorData->batas_max !== null && (float) $nilai->nilai_decimal > (float) $nilai->indikatorData->batas_max) {
                throw ValidationException::withMessages([
                    'nilai_decimal' => 'Nilai berada di atas batas maksimum indikator.',
                ]);
            }
        }

        if ($nilai->desa && $nilai->pengajuanData?->kecamatan_id && $nilai->desa->kecamatan_id !== $nilai->pengajuanData->kecamatan_id) {
            throw ValidationException::withMessages([
                'desa_id' => 'Desa harus berada dalam kecamatan pengajuan.',
            ]);
        }

        if ($nilai->sumberData && $nilai->sumberData->desa_id && $nilai->sumberData->desa_id !== $nilai->desa_id) {
            throw ValidationException::withMessages([
                'sumber_data_id' => 'Sumber data desa tidak sesuai.',
            ]);
        }

        if ($nilai->sumberData && $nilai->sumberData->kecamatan_id && $nilai->pengajuanData && $nilai->sumberData->kecamatan_id !== $nilai->pengajuanData->kecamatan_id) {
            throw ValidationException::withMessages([
                'sumber_data_id' => 'Sumber data kecamatan tidak sesuai.',
            ]);
        }

        $duplicate = NilaiDataMentah::query()
            ->where('pengajuan_data_id', $nilai->pengajuan_data_id)
            ->where('indikator_data_id', $nilai->indikator_data_id)
            ->where('tipe_sumber', $nilai->tipe_sumber)
            ->where('sumber_id', $nilai->sumber_id)
            ->when($nilai->exists, fn ($query) => $query->whereKeyNot($nilai->getKey()))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'indikator_data_id' => 'Nilai untuk pengajuan, indikator, dan sumber tersebut sudah ada.',
            ]);
        }

        $user = Auth::user();

        if ($user instanceof User && AdminScope::isSubdistrict($user) && $nilai->desa?->kecamatan_id !== $user->kecamatan_id) {
            throw ValidationException::withMessages([
                'desa_id' => 'Admin kecamatan hanya boleh menginput desa pada kecamatannya sendiri.',
            ]);
        }

        if ($user instanceof User && AdminScope::isSubdistrict($user) && ! AdminScope::canInputIndicator($user, $nilai->indikatorData)) {
            throw ValidationException::withMessages([
                'indikator_data_id' => 'Admin kecamatan hanya boleh menginput indikator yang dibuka untuk kecamatan.',
            ]);
        }

        if ($user instanceof User && AdminScope::isDepartment($user) && ! AdminScope::canInputIndicator($user, $nilai->indikatorData)) {
            throw ValidationException::withMessages([
                'indikator_data_id' => 'Admin OPD hanya boleh menginput indikator milik OPD sendiri.',
            ]);
        }

        if ($user instanceof User) {
            app(SumberDataFilterService::class)->validateNilaiDataMentah($nilai, $user);
        }
    }

    public function saved(NilaiDataMentah $nilai): void
    {
        $this->sinkronkanPengajuan($nilai);
    }

    public function deleted(NilaiDataMentah $nilai): void
    {
        $this->sinkronkanPengajuan($nilai);
    }

    private function sinkronkanPengajuan(NilaiDataMentah $nilai): void
    {
        $pengajuan = $nilai->pengajuanData;

        if (! $pengajuan) {
            return;
        }

        app(ServiceAgregasiStatistik::class)->sinkronkan($pengajuan);
    }
}
