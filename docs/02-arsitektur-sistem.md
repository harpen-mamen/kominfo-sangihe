# 3. Arsitektur Sistem Lengkap

## Arsitektur Aplikasi

Arsitektur yang dipakai adalah modular monolith backend + public frontend + panel admin Filament.

- `apps/backend-laravel`: backend inti untuk auth, workflow, master data, statistik, GIS, CMS, API publik, dan panel admin Filament
- `apps/frontend-laravel`: website publik kabupaten

Pendekatan ini dipilih karena:

- domain bisnis tetap terpusat di satu backend
- publik tetap dapat dikembangkan terpisah, sementara admin dipusatkan di Laravel
- lebih sederhana dibanding microservices pada fase awal
- tetap siap diekstrak menjadi layanan terpisah jika traffic/kompleksitas meningkat

## Pembagian Modul Backend

- `Auth`: login, token/session, profil, reset password
- `IAM`: role, permission, user scope, policy
- `Master Data`: kecamatan, desa, OPD, periode, dan indikator
- `Data Collection`: input data mentah, draft, pengajuan, revisi, lampiran, validasi
- `Workflow`: review Kominfo, komentar, riwayat tinjau, status publikasi
- `Statistics`: agregasi otomatis, KPI, tren waktu, dan ringkasan statistik
- `GIS`: lapisan peta, fitur peta, GeoJSON, legenda, dan popup
- `Konten`: berita dan kegiatan
- `Public API`: endpoint portal publik
- `Notification`: notifikasi in-app/email/queue
- `Audit`: log audit dan jejak perubahan

## Alur Data

1. Admin kecamatan memilih periode data dan mengisi data mentah per desa.
2. Sistem melakukan validasi dasar dan mencegah duplikasi sumber-indikator.
3. Data berstatus `draft` lalu dikirim menjadi `diajukan`.
4. Kominfo meninjau pengajuan dan memberi catatan bila perlu revisi.
5. Jika disetujui, status menjadi `terverifikasi` dan sistem menjalankan agregasi otomatis.
6. Saat status `terbit`, ringkasan statistik tampil pada portal publik.
7. Berita dan kegiatan mengikuti pola `draft -> diajukan -> revisi/terbit/ditolak`.

## Role dan Permission Level Tinggi

- `super_admin`: kontrol penuh sistem
- `admin_kominfo`: approval akhir, publikasi, master data, dan peta
- `verifikator_kominfo`: pemeriksa khusus tanpa semua hak admin
- `admin_kecamatan`: input data mentah, berita, dan kegiatan kecamatan
- `admin_opd`: input konten dan data sektoral OPD
- `public_user`: hanya konsumsi data publik

## Workflow Approval

Urutan status:

`draft` -> `diajukan` -> `revisi` / `terverifikasi` / `ditolak` -> `terbit`

Aturan inti:

- perubahan status hanya boleh oleh role yang berwenang
- setiap revisi dan penolakan harus memiliki komentar
- seluruh transisi disimpan pada riwayat approval
- publikasi hanya dapat dilakukan untuk data `terverifikasi`

# 6. Struktur Folder Project

```text
kominfo/
  apps/
    backend-laravel/
    frontend-laravel/
  docs/
  infra/
    docker/
  scripts/
```

# 7. Daftar Modul dan Fitur

## Portal Publik

- beranda
- statistik daerah
- peta digital
- berita
- informasi umum
- profil kabupaten
- profil dinas
- dokumen publik
- kontak
- pencarian

## Backoffice

- authentication
- role dan permission
- dashboard per level
- master wilayah
- master dinas
- master periode dan indikator
- input data
- approval workflow
- monitoring status
- audit log
- upload layer peta
- manajemen berita
- manajemen kegiatan
- notifikasi

## Analitik dan GIS

- agregasi statistik otomatis
- KPI card, bar, line, pie/donut
- peta umum dan sektoral
- marker, polygon, heatmap, choropleth
- legenda dan popup informasi
- filter wilayah, periode, dan kategori

# 8. Daftar Role dan Permission

## Super Admin

- kelola semua user
- kelola semua role dan permission
- kelola semua master data
- override workflow
- akses semua data dan audit log

## Admin Kominfo

- approval akhir data
- publikasi ke portal publik
- kelola berita, kegiatan, dan publikasi statistik
- lihat monitoring lintas dinas
- kelola layer peta publik

## Admin OPD

- kelola konten OPD
- input data sektoral bila modul OPD diaktifkan
- lihat statistik terkait OPD

## Admin Kecamatan

- input/edit draft data mentah
- ajukan data ke Kominfo
- kelola berita dan kegiatan kecamatan
- lihat riwayat revisi

# 9. Alur Workflow Approval

1. Admin kecamatan membuat `draft`.
2. Admin kecamatan mengirim menjadi `diajukan`.
3. Kominfo memilih `revisi`, `terverifikasi`, atau `ditolak`.
4. Data `terverifikasi` dapat diterbitkan menjadi `terbit`.

Setiap tindakan menyimpan:

- aktor
- peran
- waktu
- komentar
- aksi/status tujuan
