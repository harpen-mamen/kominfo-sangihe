<?php

use Illuminate\Support\Facades\Route;
use App\Models\Desa;
use App\Models\DokumenPublik;
use App\Models\Kecamatan;
use App\Support\ResourceOptions;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dokumen', function () {
    $documents = DokumenPublik::query()
        ->with(['kecamatan', 'desa', 'opd'])
        ->where('status', 'terbit')
        ->when(request('tahun'), fn ($query, $tahun) => $query->where('tahun', $tahun))
        ->when(request('jenis_dokumen'), fn ($query, $jenis) => $query->where('jenis_dokumen', $jenis))
        ->when(request('kecamatan_id'), fn ($query, $id) => $query->where('kecamatan_id', $id))
        ->when(request('desa_id'), fn ($query, $id) => $query->where('desa_id', $id))
        ->orderByDesc('tanggal_terbit')
        ->orderByDesc('created_at')
        ->paginate(12)
        ->withQueryString();

    return view('public.dokumen', [
        'title' => 'Dokumen Publik',
        'documents' => $documents,
        'jenisOptions' => ResourceOptions::jenisDokumenPublik(),
        'tahunOptions' => DokumenPublik::query()->where('status', 'terbit')->whereNotNull('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun', 'tahun'),
        'kecamatanOptions' => Kecamatan::query()->where('aktif', true)->orderBy('nama')->pluck('nama', 'id'),
        'desaOptions' => Desa::query()->where('aktif', true)->when(request('kecamatan_id'), fn ($query, $id) => $query->where('kecamatan_id', $id))->orderBy('nama')->pluck('nama', 'id'),
    ]);
})->name('dokumen.index');

Route::get('/transparansi-anggaran', function () {
    request()->merge([
        'jenis_dokumen' => request('jenis_dokumen') ?: null,
    ]);

    $documents = DokumenPublik::query()
        ->with(['kecamatan', 'desa', 'opd'])
        ->where('status', 'terbit')
        ->whereIn('jenis_dokumen', ['rab_desa', 'rab_kecamatan', 'rab_kabupaten', 'laporan_anggaran', 'dokumen_perencanaan'])
        ->when(request('tahun'), fn ($query, $tahun) => $query->where('tahun', $tahun))
        ->when(request('jenis_dokumen'), fn ($query, $jenis) => $query->where('jenis_dokumen', $jenis))
        ->when(request('kecamatan_id'), fn ($query, $id) => $query->where('kecamatan_id', $id))
        ->when(request('desa_id'), fn ($query, $id) => $query->where('desa_id', $id))
        ->orderByDesc('tanggal_terbit')
        ->orderByDesc('created_at')
        ->paginate(12)
        ->withQueryString();

    return view('public.dokumen', [
        'title' => 'Transparansi Anggaran',
        'documents' => $documents,
        'jenisOptions' => collect(ResourceOptions::jenisDokumenPublik())->only(['rab_desa', 'rab_kecamatan', 'rab_kabupaten', 'laporan_anggaran', 'dokumen_perencanaan']),
        'tahunOptions' => DokumenPublik::query()->where('status', 'terbit')->whereNotNull('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun', 'tahun'),
        'kecamatanOptions' => Kecamatan::query()->where('aktif', true)->orderBy('nama')->pluck('nama', 'id'),
        'desaOptions' => Desa::query()->where('aktif', true)->when(request('kecamatan_id'), fn ($query, $id) => $query->where('kecamatan_id', $id))->orderBy('nama')->pluck('nama', 'id'),
    ]);
})->name('transparansi-anggaran');
