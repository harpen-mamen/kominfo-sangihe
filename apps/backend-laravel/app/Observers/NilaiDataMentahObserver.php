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
            ->where('desa_id', $nilai->desa_id)
            ->where('indikator_data_id', $nilai->indikator_data_id)
            ->when(
                $nilai->sumber_data_id,
                fn ($query) => $query->where('sumber_data_id', $nilai->sumber_data_id),
                fn ($query) => $query->whereNull('sumber_data_id'),
            )
            ->when($nilai->exists, fn ($query) => $query->whereKeyNot($nilai->getKey()))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'indikator_data_id' => 'Nilai untuk desa, indikator, dan sumber data tersebut sudah ada.',
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
