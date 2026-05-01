# Frontend Strategy dan Design System

## 1. UI Strategy

- Arah visual: formal, modern, bersih, dan kredibel untuk konteks website pemerintah.
- Karakter visual: putih dominan, biru laut sebagai identitas utama, teal untuk statistik/GIS, emas lembut sebagai aksen identitas daerah.
- Prinsip layout:
  - hero resmi dan representatif untuk portal publik
  - content density tinggi namun tetap rapi untuk admin
  - desain modular berbasis reusable component
  - light, dark, dan auto theme dengan contrast tetap aman
  - bilingual shell UI untuk Indonesia dan English

## 2. Sitemap

### Public

- `/`
- `/statistik`
- `/peta-digital`
- `/berita`
- `/berita/[slug]`
- `/profil-daerah`
- `/profil-dinas`
- `/profil-pimpinan`
- `/informasi-umum`
- `/dokumen-publik`
- `/kontak`
- `/pencarian`

### Admin

- `/`
- `/login`
- `/data-statistik`
- `/peta`
- `/berita`
- `/profil-daerah`
- `/informasi-umum`
- `/hero-section`
- `/media-library`
- `/master/wilayah`
- `/master/dinas`
- `/master/unit`
- `/template-data`
- `/submissions`
- `/approval`
- `/pengguna`
- `/pengaturan`
- `/log-aktivitas`

## 3. Design Tokens

### Public Tokens

- `primary`: `#0f4c81`
- `primary-dark`: `#09294d`
- `secondary`: `#0f766e`
- `accent`: `#d4a53a`
- `background`: `#f6f9fc`
- `surface`: `rgba(255,255,255,0.92)`
- `text`: `#183247`
- `text-soft`: `#5a7185`

### Admin Tokens

- `primary`: `#0f4c81`
- `secondary`: `#12b5a5`
- `accent`: `#d7a336`
- `background`: `#0a1420`
- `surface`: `rgba(14,24,36,0.88)`
- `text`: `#edf5ff`
- `text-soft`: `#9db1c6`

### Shared Scale

- radius: `18 / 24 / 30 / 32`
- shadow: soft panel blur dengan depth sedang
- font body: `Aptos / Segoe UI Variable / Segoe UI`
- font heading public: `Cambria / Palatino Linotype / Book Antiqua`

## 4. Wireframe Deskriptif

### Homepage Public

1. Sticky header dengan logo, menu utama, language switcher, theme switcher, search, admin login.
2. Hero full-width dengan:
   - portrait Bupati kiri
   - konten hero di tengah
   - portrait Wakil Bupati kanan
   - background image configurable
   - quick info chips
3. Quick access cards.
4. KPI cards.
5. Preview peta digital.
6. Berita terbaru.
7. Profil singkat daerah.
8. Sorotan statistik sektoral.
9. CTA ke statistik penuh dan peta penuh.
10. Footer resmi.

### Admin

1. Sidebar kiri enterprise-style.
2. Topbar dengan search, notifications, quick actions, language, theme, profile.
3. Dashboard cards KPI.
4. Workflow board tiga lajur.
5. Chart monitoring kecamatan.
6. Viewer GIS operasional.
7. Submission table.
8. Halaman editor hero dengan form kiri dan live preview kanan.

## 5. Component Hierarchy

### Public

- `SiteShell`
- `SiteHeader`
- `SiteFooter`
- `AppButton`
- `AppCard`
- `AppStatCard`
- `AppSectionHeader`
- `AppFilterBar`
- `AppTable`
- `AppLanguageSwitcher`
- `AppThemeSwitcher`
- `AppMapContainer`
- `AppNewsCard`
- `AppProfileCard`
- `PageBanner`
- `HomePageView`
- `StatisticsPageView`
- `MapPageView`
- `NewsPageView`
- `NewsDetailView`
- `ProfilePageView`
- `InfoPageView`
- `DocumentsPageView`
- `ContactPageView`
- `SearchPageView`

### Admin

- `AdminShell`
- `AppButton`
- `AppCard`
- `AppStatCard`
- `AppTable`
- `AppFilterBar`
- `AppLanguageSwitcher`
- `AppThemeSwitcher`
- `AppHeroEditorPreview`
- `WorkflowBoard`
- `DistrictChart`
- `AdminMap`
- `SubmissionTable`
- `HeroEditorForm`
- `PlaceholderPage`

## 6. Accessibility Notes

- semantic layout untuk `header`, `nav`, `main`, `section`, `footer`
- skip link pada portal publik
- focus state kontras tinggi
- tombol switcher menggunakan `aria-pressed`
- menu mobile memakai `aria-expanded`
- input search dan form memiliki label atau label visual yang jelas
- light/dark theme tetap menjaga contrast ratio tinggi

## 7. Integrasi Portal Publik

- Statistik publik membaca `ringkasan_statistik`.
- Peta digital membaca `lapisan_peta`, `fitur_peta`, berita berlokasi, dan kegiatan berlokasi.
- Berita publik membaca tabel `berita` berstatus `terbit`.
- Kegiatan publik membaca tabel `kegiatan` berstatus `terbit`.
- Hero homepage memakai fallback terkurasi di API publik; rancangan final tidak membuat tabel `hero_sections`.
