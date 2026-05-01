<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class AdminFormMutation
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function sanitizePengajuanData(array $data, bool $forceSubmission = false): array
    {
        if (! $forceSubmission) {
            return $data;
        }

        $data['status'] = 'diajukan';
        $data['tanggal_kirim'] = Carbon::now();
        $data['diverifikasi_oleh'] = null;
        $data['tanggal_verifikasi'] = null;
        $data['tanggal_terbit'] = null;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function sanitizeKontenPublik(array $data, bool $forceSubmission = false): array
    {
        if (! $forceSubmission) {
            return $data;
        }

        $data['status'] = 'diajukan';
        $data['ditinjau_oleh'] = null;
        $data['tanggal_terbit'] = null;

        if (array_key_exists('unggulan', $data)) {
            $data['unggulan'] = false;
        }

        return $data;
    }
}
