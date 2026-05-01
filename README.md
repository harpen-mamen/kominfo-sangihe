# Platform Data Terpadu Kominfo Kabupaten Kepulauan Sangihe

Fondasi ini membangun dua sisi sistem sekaligus:

- portal publik kabupaten untuk statistik, peta digital, berita, dokumen, dan informasi umum
- backoffice/admin system terintegrasi untuk input data, validasi, workflow approval, CMS, statistik, dan GIS

## Ringkasan solusi

- `apps/backend-laravel`: backend Laravel 12 untuk auth, RBAC, workflow, template form, statistik, GIS, CMS, API publik, dan dashboard admin Filament
- `apps/frontend-laravel`: website publik utama
- `docs`: dokumen kebutuhan, stack, arsitektur, database, dan rencana implementasi
- `infra/docker/docker-compose.yml`: opsi service lokal database dan Redis bila nanti dibutuhkan

## Dokumen inti

- `docs/01-kebutuhan-dan-stack.md`
- `docs/02-arsitektur-sistem.md`
- `docs/03-desain-database.md`
- `docs/04-rencana-implementasi.md`

## Stack yang digunakan

- Backend: Laravel 12, PHP 8.3+
- Database lokal saat ini: MySQL / MariaDB (XAMPP)
- Peta digital: GeoJSON di MySQL/MariaDB
- Cache/queue: Redis
- Frontend publik: Next.js 16, React 19, TypeScript
- Dashboard admin: Filament 5 di Laravel
- Charts: Recharts
- Map: MapLibre GL JS
- Auth API: Laravel Sanctum
- RBAC: Spatie Laravel Permission
- Audit log: tabel `aktivitas_sistem`

## Modul yang sudah difondasikan

- Auth API
- Role dan permission
- Master kecamatan, desa, dan OPD
- Pengajuan data mentah bulanan
- Verifikasi Kominfo dan aktivitas sistem
- Agregasi statistik otomatis
- Ringkasan statistik publik
- Lapisan dan fitur peta digital
- Konten publik terpadu untuk berita dan kegiatan
- Portal publik multi-halaman
- Dashboard admin multi-role berbasis Filament

## Struktur folder

```text
kominfo/
  apps/
    backend-laravel/
    frontend-laravel/
  docs/
  infra/
    docker/
      docker-compose.yml
```

## Role utama

- `super_admin`
- `admin_kominfo`
- `verifikator_kominfo`
- `admin_kecamatan`
- `admin_opd`

## Workflow data

`draft` -> `diajukan` -> `revisi` / `terverifikasi` / `ditolak` -> `terbit`

## Dummy data yang disiapkan

- 15 kecamatan
- beberapa desa
- dinas utama sektoral
- unit puskesmas, sekolah, kelompok tani, kelompok nelayan
- akun admin dan operator
- periode dan indikator statistik contoh
- pengajuan data mentah contoh
- ringkasan statistik hasil agregasi
- berita, halaman umum, dokumen publik
- layer peta fasilitas kesehatan, sekolah, dan blank spot internet

## Kredensial seed

Semua akun seed menggunakan password:

```text
Password123!
```

Contoh email login:

- `superadmin@kominfo-sangihe.go.id`
- `admin@kominfo-sangihe.go.id`
- `tahuna@kecamatan-sangihe.go.id`
- `admin@dinkes-sangihe.go.id`
- `admin@disdik-sangihe.go.id`

## Cara install

### 1. Siapkan dependency

Backend:

```powershell
cd apps/backend-laravel
composer install
Copy-Item .env.example .env
```

Frontend publik:

```powershell
cd apps/frontend-laravel
npm install
Copy-Item .env.example .env.local
```

### 2. Siapkan database MySQL XAMPP

Buat database baru di MySQL XAMPP:

```sql
CREATE DATABASE kominfo_sangihe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Default `.env.example` backend sekarang memakai:

- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=kominfo_sangihe`
- `DB_USERNAME=root`
- `DB_PASSWORD=` kosong

### 3. Konfigurasi backend

Pastikan MySQL XAMPP aktif, lalu jalankan:

```powershell
cd apps/backend-laravel
php artisan key:generate
php artisan migrate --seed
```

### 4. Jalankan aplikasi

Backend Laravel + admin Filament:

```powershell
cd apps/backend-laravel
php artisan serve
```

Portal publik:

```powershell
cd apps/frontend-laravel
npm run dev
```

Admin Filament:

```text
http://localhost:8000/admin/login
```

## Build production

Backend:

```powershell
cd apps/backend-laravel
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Frontend publik:

```powershell
cd apps/frontend-laravel
npm run build
```

Frontend admin utama sekarang mengikuti build Laravel backend karena memakai Filament.

## Catatan environment lokal saat implementasi

Verifikasi yang berhasil dijalankan:

- `php artisan route:list`
- `npm run lint` pada frontend publik
- `npm run build` pada frontend publik

Verifikasi backend database lokal ditargetkan memakai `pdo_mysql` dengan MySQL/MariaDB.
