<?php

namespace App\Services;

use App\Models\PengajuanData;
use App\Models\RiwayatTinjau;
use App\Models\User;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class WorkflowService
{
    /**
     * @return array<string, array<int, string>>
     */
    public function transitions(): array
    {
        return [
            'draft' => ['diajukan'],
            'diajukan' => ['revisi', 'terverifikasi', 'ditolak'],
            'revisi' => ['diajukan'],
            'terverifikasi' => ['terbit'],
            'terbit' => [],
            'ditolak' => [],
        ];
    }

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, $this->transitions()[$from] ?? [], true);
    }

    public function transition(PengajuanData $pengajuan, string $nextStatus, User $actor, ?string $catatan = null): PengajuanData
    {
        $currentStatus = $pengajuan->status;

        if (! $this->canTransition($currentStatus, $nextStatus)) {
            throw new InvalidArgumentException("Transisi {$currentStatus} ke {$nextStatus} tidak diizinkan.");
        }

        $now = Carbon::now();
        $pengajuan->status = $nextStatus;
        $pengajuan->catatan = $catatan;

        if ($nextStatus === 'diajukan') {
            $pengajuan->tanggal_kirim = $now;
        }

        if (in_array($nextStatus, ['terverifikasi', 'ditolak'], true)) {
            $pengajuan->diverifikasi_oleh = $actor->id;
            $pengajuan->tanggal_verifikasi = $now;
        }

        if ($nextStatus === 'terbit') {
            $pengajuan->tanggal_terbit = $now;
        }

        $pengajuan->save();

        RiwayatTinjau::create([
            'jenis_objek' => PengajuanData::class,
            'objek_id' => $pengajuan->id,
            'peninjau_id' => $actor->id,
            'aksi' => $nextStatus,
            'catatan' => $catatan,
        ]);

        return $pengajuan->refresh();
    }
}
