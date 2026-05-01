# 4. Struktur Database Final

Database aplikasi memakai nama tabel dan kolom Bahasa Indonesia sesuai spesifikasi final. Prinsip utamanya: admin kecamatan menginput data mentah, Kominfo memverifikasi, lalu sistem membuat agregasi otomatis ke tabel ringkasan.

## Tabel Aplikasi

- `kecamatan`: master kecamatan.
- `desa`: master desa/kelurahan, terhubung ke `kecamatan`.
- `opd`: master organisasi perangkat daerah.
- `pengguna`: akun sistem, terhubung opsional ke `kecamatan` atau `opd`.
- `periode_data`: periode pelaporan bulanan.
- `indikator_data`: master indikator statistik generik.
- `pengajuan_data`: header pengajuan data mentah dari kecamatan.
- `nilai_data_mentah`: angka mentah per sumber dan indikator.
- `ringkasan_statistik`: hasil agregasi desa, kecamatan, dan kabupaten.
- `konten`: tabel terpadu untuk berita dan kegiatan, dibedakan dengan `jenis_konten`.
- `lapisan_peta`: master layer peta digital.
- `fitur_peta`: objek peta titik/garis/polygon berbasis GeoJSON.
- `aktivitas_sistem`: jejak review dan audit dalam satu tabel dengan `kategori_aktivitas`.

Tabel infrastruktur Laravel yang tetap dipakai:

- `password_reset_tokens`
- `sessions`
- `cache`, `cache_locks`
- `jobs`, `job_batches`, `failed_jobs`
- `personal_access_tokens`
- tabel Spatie Permission: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`

## Status Data

Status standar untuk pengajuan dan konten:

```text
draft -> diajukan -> revisi / terverifikasi / ditolak -> terbit
```

Pada `pengajuan_data`, status `terverifikasi` atau `terbit` memicu regenerasi `ringkasan_statistik`.

## Relasi Utama

```text
kecamatan 1---* desa
kecamatan 1---* pengguna
opd 1---* pengguna

kecamatan 1---* pengajuan_data
periode_data 1---* pengajuan_data
pengguna 1---* pengajuan_data
pengajuan_data 1---* nilai_data_mentah
indikator_data 1---* nilai_data_mentah

periode_data 1---* ringkasan_statistik
indikator_data 1---* ringkasan_statistik
kecamatan 1---* ringkasan_statistik
desa 1---* ringkasan_statistik

pengguna 1---* konten
kecamatan 1---* konten
opd 1---* konten

lapisan_peta 1---* fitur_peta
kecamatan 1---* fitur_peta
pengguna 1---* aktivitas_sistem
```

## Indeks dan Constraint

- Unique: `kecamatan.kode`, `desa.kode`, `opd.kode`, `indikator_data.kode`, `konten.slug`, `lapisan_peta.slug`.
- Unique: `periode_data(tahun, bulan)`.
- Unique: `pengajuan_data(kecamatan_id, periode_data_id)`.
- Unique: `nilai_data_mentah(pengajuan_data_id, indikator_data_id, tipe_sumber, sumber_id)`.
- Index: `pengajuan_data(kecamatan_id, periode_data_id, status)`.
- Index: `ringkasan_statistik(periode_data_id, tingkat_rekap, kecamatan_id, indikator_data_id)`.
- Index: `konten(jenis_konten, status, tanggal_terbit)`.
- Index: `konten(jenis_konten, mulai)`.
- Index: `fitur_peta(lapisan_peta_id, kecamatan_id, aktif)`.

## Agregasi Statistik

Service agregasi membaca `nilai_data_mentah` dari pengajuan yang sudah diverifikasi, lalu menulis ulang:

- rekap `desa` untuk sumber bertipe `desa`
- rekap `kecamatan` per indikator
- rekap `kabupaten` dari seluruh rekap kecamatan pada periode yang sama

Dashboard publik membaca `ringkasan_statistik`, bukan menghitung ulang dari data mentah setiap request.
