<?php

namespace App\Observers;

use App\Models\PengajuanData;
use App\Models\User;
use App\Services\ServiceAgregasiStatistik;
use App\Support\AdminScope;
use Illuminate\Validation\ValidationException;

class PengajuanDataObserver
{
    public function saving(PengajuanData $pengajuan): void
    {
        $pengajuan->load('periodeData');

        if ($pengajuan->periodeData?->terkunci) {
            throw ValidationException::withMessages([
                'periode_data_id' => 'Periode data sudah terkunci.',
            ]);
        }

        $user = auth()->user();

        if ($user instanceof User && AdminScope::isSubdistrict($user) && $pengajuan->kecamatan_id !== $user->kecamatan_id) {
            throw ValidationException::withMessages([
                'kecamatan_id' => 'Admin kecamatan hanya boleh membuat pengajuan untuk kecamatannya sendiri.',
            ]);
        }

        if ($user instanceof User && AdminScope::isDepartment($user) && $pengajuan->opd_id !== $user->opd_id) {
            throw ValidationException::withMessages([
                'opd_id' => 'Admin OPD hanya boleh membuat pengajuan untuk OPD sendiri.',
            ]);
        }
    }

    public function saved(PengajuanData $pengajuan): void
    {
        $service = app(ServiceAgregasiStatistik::class);

        if ($pengajuan->wasChanged(['periode_data_id', 'kecamatan_id', 'opd_id'])) {
            $periodeLama = $pengajuan->getOriginal('periode_data_id');
            $kecamatanLama = $pengajuan->getOriginal('kecamatan_id');
            $opdLama = $pengajuan->getOriginal('opd_id');

            if ($periodeLama && ($kecamatanLama || $opdLama)) {
                $service->hapusRekapUntuk(
                    (int) $periodeLama,
                    $kecamatanLama ? (int) $kecamatanLama : null,
                    $opdLama ? (int) $opdLama : null,
                );
            }
        }

        if ($pengajuan->wasRecentlyCreated || $pengajuan->wasChanged(['status', 'periode_data_id', 'kecamatan_id', 'opd_id'])) {
            $service->sinkronkan($pengajuan->refresh());
        }
    }

    public function deleted(PengajuanData $pengajuan): void
    {
        app(ServiceAgregasiStatistik::class)->hapusRekap($pengajuan);
    }
}
