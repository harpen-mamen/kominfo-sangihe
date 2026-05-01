<?php

namespace Database\Seeders;

use App\Models\Berita;
use App\Models\FiturPeta;
use App\Models\Kecamatan;
use App\Models\Kegiatan;
use App\Models\LapisanPeta;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'admin@kominfo-sangihe.go.id')->firstOrFail();
        $kominfo = Opd::where('kode', 'kominfo')->firstOrFail();
        $dinkes = Opd::where('kode', 'dinkes')->firstOrFail();
        $disdik = Opd::where('kode', 'disdik')->firstOrFail();
        $tahuna = Kecamatan::where('nama', 'Tahuna')->firstOrFail();
        $manganitu = Kecamatan::where('nama', 'Manganitu')->first();

        $posts = [
            [
                'opd_id' => $kominfo->id,
                'judul' => 'Portal Statistik dan Peta Digital Sangihe Mulai Diuji',
                'ringkasan' => 'Kominfo menyiapkan alur data mentah, verifikasi, agregasi, dan publikasi dalam satu sistem.',
                'isi' => 'Pemerintah Kabupaten Kepulauan Sangihe melalui Dinas Kominfo mulai menguji sistem web statistik, peta digital, berita, dan kegiatan berbasis data terverifikasi.',
                'lokasi' => 'Pusat Data Kominfo Tahuna',
                'latitude' => 3.6128,
                'longitude' => 125.4951,
                'unggulan' => true,
            ],
            [
                'opd_id' => $dinkes->id,
                'judul' => 'Pemutakhiran Data Kesehatan Dilakukan Bulanan',
                'ringkasan' => 'Data stunting, imunisasi, dan indikator kesehatan lain dikonsolidasikan lewat pengajuan data mentah.',
                'isi' => 'Admin kecamatan menginput data mentah per desa, lalu Kominfo meninjau sebelum sistem melakukan agregasi otomatis.',
                'lokasi' => 'Puskesmas Tahuna',
                'latitude' => 3.6125,
                'longitude' => 125.4931,
                'unggulan' => true,
            ],
            [
                'opd_id' => $disdik->id,
                'judul' => 'Dashboard Pendidikan Menampilkan Sebaran Siswa',
                'ringkasan' => 'Indikator pendidikan dapat direkap per desa, kecamatan, dan kabupaten.',
                'isi' => 'Data pendidikan yang sudah terverifikasi akan muncul pada dashboard publik dan dapat dipakai untuk infografis.',
                'lokasi' => 'SMA Negeri Tahuna',
                'latitude' => 3.6111,
                'longitude' => 125.4912,
                'unggulan' => false,
            ],
        ];

        foreach ($posts as $index => $post) {
            Berita::updateOrCreate(
                ['slug' => Str::slug($post['judul'])],
                [
                    ...$post,
                    'kecamatan_id' => $index === 1 ? $tahuna->id : null,
                    'penulis_id' => $author->id,
                    'status' => 'terbit',
                    'ditinjau_oleh' => $author->id,
                    'tanggal_terbit' => now()->subDays(6 - $index),
                    'gambar_sampul' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80',
                ],
            );
        }

        foreach ([
            [
                'judul' => 'Musrenbang Kecamatan Tahuna',
                'uraian' => 'Forum sinkronisasi program prioritas wilayah bersama perangkat kelurahan dan OPD terkait.',
                'mulai' => now()->addDays(4)->setTime(9, 0),
                'selesai' => now()->addDays(4)->setTime(13, 0),
                'lokasi' => 'Aula Kantor Kecamatan Tahuna',
                'latitude' => 3.6109,
                'longitude' => 125.4944,
                'kecamatan_id' => $tahuna->id,
                'opd_id' => $kominfo->id,
            ],
            [
                'judul' => 'Gerakan Bersih Pantai Manganitu',
                'uraian' => 'Agenda kolaborasi kecamatan, sekolah, dan komunitas pesisir.',
                'mulai' => now()->addDays(6)->setTime(7, 30),
                'selesai' => now()->addDays(6)->setTime(11, 30),
                'lokasi' => 'Pantai Manganitu',
                'latitude' => 3.5668,
                'longitude' => 125.5403,
                'kecamatan_id' => $manganitu?->id,
                'opd_id' => $kominfo->id,
            ],
        ] as $event) {
            Kegiatan::updateOrCreate(
                ['slug' => Str::slug($event['judul'])],
                [
                    ...$event,
                    'pembuat_id' => $author->id,
                    'status' => 'terbit',
                    'ditinjau_oleh' => $author->id,
                    'tanggal_terbit' => now()->subDay(),
                ],
            );
        }

        $layer = LapisanPeta::updateOrCreate(
            ['slug' => 'fasilitas-publik'],
            [
                'nama' => 'Fasilitas Publik',
                'tipe_sumber' => 'titik',
                'konfigurasi_json' => ['color' => '#0f766e'],
                'aktif' => true,
                'urutan' => 1,
            ],
        );

        foreach ([
            ['nama' => 'Puskesmas Tahuna', 'latitude' => 3.6125, 'longitude' => 125.4931, 'kecamatan_id' => $tahuna->id],
            ['nama' => 'Kantor Kecamatan Tahuna', 'latitude' => 3.6109, 'longitude' => 125.4944, 'kecamatan_id' => $tahuna->id],
        ] as $feature) {
            FiturPeta::updateOrCreate(
                ['lapisan_peta_id' => $layer->id, 'nama' => $feature['nama']],
                [
                    ...$feature,
                    'jenis_geometri' => 'point',
                    'geojson' => json_encode([
                        'type' => 'Point',
                        'coordinates' => [(float) $feature['longitude'], (float) $feature['latitude']],
                    ]),
                    'properti_json' => ['summary' => 'Fitur contoh peta digital'],
                    'aktif' => true,
                ],
            );
        }
    }
}
