<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('map:import-sangihe-boundaries', function () {
    $result = app(\App\Services\SangiheBoundaryImportService::class)->import();

    $this->info('Boundary Sangihe berhasil diimpor.');
    $this->line('Kecamatan: ' . $result['kecamatan']);
    $this->line('Desa: ' . $result['desa']);
})->purpose('Import batas kecamatan dan desa Kabupaten Kepulauan Sangihe');

Artisan::command('statistik:rekap-terverifikasi', function () {
    $total = app(\App\Services\ServiceAgregasiStatistik::class)->regenerasiSemuaTerverifikasi();

    $this->info("Rekap statistik selesai. {$total} pengajuan terverifikasi/terbit diproses.");
})->purpose('Rekap ulang pengajuan data mentah yang sudah terverifikasi atau terbit');
