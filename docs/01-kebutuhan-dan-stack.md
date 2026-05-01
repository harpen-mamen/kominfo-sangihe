# 1. Ringkasan Pemahaman Kebutuhan

## Sasaran Sistem

Membangun satu platform pemerintahan daerah yang mengintegrasikan:

- pengumpulan data mentah dari admin kecamatan
- verifikasi pusat oleh Kominfo
- publikasi otomatis ke portal publik
- visualisasi statistik, berita, kegiatan, dan peta digital

## Entitas Operasional Utama

- admin kecamatan: 15 kecamatan
- admin dinas/OPD: sekitar 29 dinas
- admin Kominfo kabupaten
- publik/masyarakat

## Karakteristik Solusi

- input data mentah terstruktur dan tervalidasi
- indikator sektoral dapat ditambah tanpa mengubah inti sistem
- workflow review Kominfo dengan komentar revisi dan audit trail
- statistik teragregasi desa -> kecamatan -> kabupaten
- dukungan GIS: titik, garis, poligon, heatmap, thematic map, popup, filter
- pemisahan portal publik dan backoffice
- aman, responsif, SEO-friendly, maintainable, dan scalable

# 2. Rekomendasi Stack dan Alasan

## Stack yang Dipilih

- Backend API: Laravel 12, PHP 8.3
- Database: MySQL atau MariaDB
- Cache/queue/session: Redis
- Frontend publik: Next.js 16 + React 19 + TypeScript
- Admin utama: Laravel Filament
- Frontend admin lama: Next.js 16 + React 19 + TypeScript
- UI: Tailwind CSS 4 + shadcn/ui-style component patterns
- Charts: Recharts
- GIS frontend: MapLibre GL JS
- Auth backend: Laravel Sanctum
- Authorization: Spatie Laravel Permission + Filament Shield
- Audit log: tabel `log_audit`
- File storage: local/S3-compatible object storage
- Search awal: query database sederhana, dapat ditingkatkan ke Meilisearch/Elastic nanti
- Deployment: Docker Compose untuk dev, reverse proxy Nginx untuk production

## Alasan Pemilihan

### Laravel 12

- matang untuk sistem pemerintahan yang butuh RBAC, validasi, policy, queue, job, notification, dan workflow
- ekosistem PHP/Laravel relatif lebih mudah dirawat oleh tim pengembang daerah di Indonesia
- produktif untuk CRUD kompleks, approval workflow, dan API modular
- kuat untuk implementasi authorization, audit log, dan pengelolaan konten

### Next.js 16

- cocok untuk portal publik SEO-friendly dengan App Router dan SSR/ISR
- memudahkan pemisahan portal publik dan admin tanpa mengorbankan konsistensi UI
- nyaman untuk dashboard interaktif statistik dan peta

### MySQL / MariaDB

- mudah dijalankan pada XAMPP dan hosting umum
- cukup untuk rancangan final yang menyimpan peta sebagai GeoJSON ringan
- satu sumber data untuk statistik, konten, dan peta digital

### MapLibre GL JS

- open-source, cocok untuk pemerintahan
- mendukung vector style, layer filtering, thematic maps, clustering, dan performa lebih baik untuk data spasial menengah-besar

## Alternatif yang Dipertimbangkan

- NestJS dipertimbangkan kuat untuk arsitektur enterprise, namun Laravel lebih pragmatis untuk konteks maintainability tim daerah.
- Django/GeoDjango kuat di GIS, namun Laravel + MySQL/MariaDB + MapLibre lebih pragmatis untuk rancangan final yang memakai GeoJSON ringan.
