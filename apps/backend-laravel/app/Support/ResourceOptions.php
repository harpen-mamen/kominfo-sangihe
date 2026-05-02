<?php

namespace App\Support;

class ResourceOptions
{
    /**
     * @return array<string, string>
     */
    public static function statusData(): array
    {
        return [
            'draft' => 'Draft',
            'diajukan' => 'Menunggu Peninjauan',
            'revisi' => 'Revisi',
            'terverifikasi' => 'Terverifikasi',
            'ditolak' => 'Ditolak',
            'terbit' => 'Terbit',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function tipeSumber(): array
    {
        return [
            'desa' => 'Desa',
            'puskesmas' => 'Puskesmas',
            'opd' => 'OPD',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function levelInput(): array
    {
        return [
            'desa' => 'Desa',
            'kecamatan' => 'Kecamatan',
            'opd' => 'OPD',
            'fasilitas' => 'Fasilitas',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function tipeNilai(): array
    {
        return [
            'integer' => 'Integer',
            'decimal' => 'Decimal',
            'percent' => 'Persen',
            'currency' => 'Rupiah',
            'text' => 'Teks',
            'boolean' => 'Ya/Tidak',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function metodeAgregasi(): array
    {
        return [
            'sum' => 'Jumlah',
            'average' => 'Rata-rata',
            'weighted_average' => 'Rata-rata berbobot',
            'latest' => 'Nilai terbaru',
            'count' => 'Hitung record',
            'formula' => 'Formula',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function jenisSumberData(): array
    {
        return [
            'desa' => 'Desa',
            'kecamatan' => 'Kecamatan',
            'puskesmas' => 'Puskesmas',
            'sekolah' => 'Sekolah',
            'opd' => 'OPD',
            'fasilitas_publik' => 'Fasilitas Publik',
            'lainnya' => 'Lainnya',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function tingkatRekap(): array
    {
        return [
            'desa' => 'Desa',
            'kecamatan' => 'Kecamatan',
            'kabupaten' => 'Kabupaten',
            'opd' => 'OPD',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function tipeLapisan(): array
    {
        return [
            'geojson' => 'GeoJSON',
            'manual' => 'Manual',
            'statistik' => 'Statistik',
            'titik' => 'Titik',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function jenisGeometri(): array
    {
        return [
            'point' => 'Point',
            'line' => 'Line',
            'polygon' => 'Polygon',
            'multipolygon' => 'Multi Polygon',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sumberInput(): array
    {
        return [
            'manual' => 'Manual',
            'gps' => 'GPS',
            'file' => 'File',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function jenisKonten(): array
    {
        return [
            'berita' => 'Berita',
            'kegiatan' => 'Kegiatan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function kelompokIndikator(): array
    {
        return [
            'demografi' => 'Demografi',
            'kependudukan' => 'Kependudukan',
            'penduduk' => 'Penduduk',
            'pekerjaan' => 'Pekerjaan',
            'pendidikan' => 'Pendidikan',
            'kesehatan' => 'Kesehatan',
            'penyakit' => 'Penyakit',
            'fasilitas' => 'Fasilitas',
            'ekonomi' => 'Ekonomi',
            'infrastruktur' => 'Infrastruktur',
            'sosial' => 'Sosial',
            'lainnya' => 'Lainnya',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function jenisDokumenPublik(): array
    {
        return [
            'peraturan' => 'Peraturan',
            'rab_desa' => 'RAB Desa',
            'rab_kecamatan' => 'RAB Kecamatan',
            'rab_kabupaten' => 'RAB Kabupaten',
            'laporan_anggaran' => 'Laporan Anggaran',
            'dokumen_perencanaan' => 'Dokumen Perencanaan',
            'dokumen_kegiatan' => 'Dokumen Kegiatan',
            'pengumuman_resmi' => 'Pengumuman Resmi',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusDokumenPublik(): array
    {
        return [
            'draft' => 'Draft',
            'dikirim' => 'Dikirim',
            'ditinjau' => 'Ditinjau',
            'terbit' => 'Terbit',
            'ditolak' => 'Ditolak',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function tingkatWilayahDokumen(): array
    {
        return [
            'desa' => 'Desa',
            'kecamatan' => 'Kecamatan',
            'kabupaten' => 'Kabupaten',
            'opd' => 'OPD',
        ];
    }
}
