export type Language = "id" | "en";

export type ThemeMode = "light" | "dark" | "system";

export type LocalizedText = {
  id: string;
  en: string;
};

export type NavItem = {
  href: string;
  label: LocalizedText;
};

export type LinkCard = {
  label: LocalizedText;
  description: LocalizedText;
  url: string;
};

export type InfoItem = {
  label: LocalizedText;
};

export type HeroPerson = {
  name: string;
  title: LocalizedText;
  imageUrl: string;
};

export type HeroData = {
  badge: LocalizedText;
  headline: LocalizedText;
  subheadline: LocalizedText;
  ctaPrimary: {
    label: LocalizedText;
    url: string;
  };
  ctaSecondary: {
    label: LocalizedText;
    url: string;
  };
  backgroundImageUrls: string[];
  backgroundType?: "image" | "video";
  backgroundVideoUrl?: string | null;
  backgroundVideoPosterUrl?: string | null;
  regent: HeroPerson;
  viceRegent: HeroPerson;
  quickLinks: LinkCard[];
  infoItems: InfoItem[];
  visualOrder: string[];
};

export type PortalSettings = {
  title: string;
  logoUrl?: string | null;
  footerDescription?: string | null;
  contact?: {
    address?: string | null;
    email?: string | null;
    phone?: string | null;
  };
};

export type StatCard = {
  label: LocalizedText;
  value: string;
  note: LocalizedText;
};

export type NewsItem = {
  slug: string;
  title: string;
  excerpt: string;
  category: string;
  date: string;
  imageUrl?: string;
  department?: string;
  type?: string;
  location?: string;
  latitude?: number | null;
  longitude?: number | null;
};

export type MapPoint = {
  name: string;
  coordinates: [number, number];
  color: string;
  layer: string;
};

export type MapLayerSummary = {
  slug?: string;
  name: string;
  featureCount: number;
  department?: string;
  color?: string;
  kind?: string;
};

export type MapWorkbenchFeature = {
  id: string;
  name: string;
  coordinates: [number, number];
  layerName: string;
  layerSlug: string;
  kind: "news" | "event" | "gis";
  color: string;
  summary?: string;
  subtitle?: string;
  locationLabel?: string;
  startsAt?: string;
  detailUrl?: string;
  rawGeometry?: unknown;
  kecamatanId?: number | null;
  desaId?: number | null;
  opdId?: number | null;
  jenisFasilitas?: string | null;
  metrics?: StatisticsRecord[];
};

export type MapWorkbenchLayer = {
  id: string;
  slug: string;
  name: string;
  kind: "news" | "event" | "gis";
  layerType: string;
  featureCount: number;
  department?: string;
  color: string;
  features: MapWorkbenchFeature[];
};

export type TableColumn = {
  key: string;
  label: LocalizedText;
};

export type TableRow = Record<string, string>;

export type PortalSummary = {
  jumlah_kecamatan: number;
  jumlah_desa: number;
  jumlah_penduduk: number | null;
  jumlah_fasilitas_publik: number;
  jumlah_berita_kegiatan: number;
  jumlah_layer_peta: number;
  jumlah_indikator: number;
  periode_terbaru?: string | null;
};

export type PortalOption = {
  id: number;
  nama: string;
  kode?: string;
  kecamatan_id?: number | null;
  satuan?: string | null;
  kelompok?: string | null;
  opd_id?: number | null;
};

export type PortalFilters = {
  periode: Array<PortalOption & { label?: string; tahun?: number; bulan?: number }>;
  kecamatan: PortalOption[];
  desa: PortalOption[];
  opd: PortalOption[];
  indikator: PortalOption[];
};

export type StatisticsRecord = {
  id?: number;
  periode_id?: number | null;
  periode?: string | null;
  tahun?: number | null;
  bulan?: number | null;
  tingkat_rekap?: string | null;
  kecamatan_id?: number | null;
  kecamatan?: string | null;
  desa_id?: number | null;
  desa?: string | null;
  opd_id?: number | null;
  opd?: string | null;
  indikator_id?: number | null;
  indikator?: string | null;
  indikator_kode?: string | null;
  satuan?: string | null;
  nilai_total: number;
  nilai_persen?: number | null;
};

export type ChartDatum = {
  label: string;
  value: number;
};

export type OpenDataResponse = {
  meta: {
    title: string;
    license?: string;
    download_csv_url?: string;
  };
  filters: PortalFilters;
  data: StatisticsRecord[];
};

export const navigationItems: NavItem[] = [
  { href: "/", label: { id: "Beranda", en: "Home" } },
  { href: "/tentang-daerah", label: { id: "Tentang Daerah", en: "About" } },
  { href: "/peta", label: { id: "Peta", en: "Map" } },
  { href: "/statistik", label: { id: "Statistik", en: "Statistics" } },
  { href: "/berita", label: { id: "Berita", en: "News" } },
  { href: "/data", label: { id: "Data", en: "Data" } },
  { href: "/kontak", label: { id: "Kontak", en: "Contact" } },
];

export const pageSlugs = {
  profileRegion: "profil-kabupaten-kepulauan-sangihe",
  profileDepartment: "profil-dinas-komunikasi-dan-informatika",
  publicInfo: "informasi-umum-layanan-data",
  contact: "kontak-kominfo-sangihe",
  leadership: "profil-bupati-dan-wakil-bupati",
} as const;

export const fallbackHero: HeroData = {
  badge: {
    id: "Pemerintah Kabupaten Kepulauan Sangihe",
    en: "Government of Sangihe Islands Regency",
  },
  headline: {
    id: "Sistem Informasi Statistik & Peta Digital Terpadu",
    en: "Integrated Statistics & Digital Map Information System",
  },
  subheadline: {
    id: "Portal resmi Kabupaten Kepulauan Sangihe untuk statistik daerah, peta digital, berita, dan informasi umum yang terverifikasi.",
    en: "Official Sangihe Islands Regency portal for verified regional statistics, digital maps, news, and public information.",
  },
  ctaPrimary: {
    label: { id: "Lihat Statistik", en: "View Statistics" },
    url: "/statistik",
  },
  ctaSecondary: {
    label: { id: "Jelajahi Peta", en: "Explore Maps" },
    url: "/peta",
  },
  backgroundImageUrls: [
    "/sangihe-bay.png",
    "/sangihe-coast.png",
    "/sangihe-perahu.jpg",
  ],
  regent: {
    name: "Michael Thungari, S.E., M.AP",
    title: {
      id: "Bupati Kabupaten Kepulauan Sangihe",
      en: "Regent of Sangihe Islands Regency",
    },
    imageUrl: "/bupati-sangihe.png",
  },
  viceRegent: {
    name: "Tendris Bulahari",
    title: {
      id: "Wakil Bupati Kabupaten Kepulauan Sangihe",
      en: "Vice Regent of Sangihe Islands Regency",
    },
    imageUrl: "/wakil-bupati-sangihe.png",
  },
  quickLinks: [
    {
      label: { id: "Statistik Daerah", en: "Regional Statistics" },
      description: {
        id: "KPI, grafik, dan tabel lintas sektor",
        en: "KPI, charts, and cross-sector tables",
      },
      url: "/statistik",
    },
    {
      label: { id: "Peta Digital", en: "Digital Maps" },
      description: {
        id: "Layer administrasi dan tematik sektoral",
        en: "Administrative and sectoral thematic layers",
      },
      url: "/peta",
    },
    {
      label: { id: "Berita", en: "News" },
      description: {
        id: "Berita kabupaten dan dinas terbaru",
        en: "Latest regency and department updates",
      },
      url: "/berita",
    },
    {
      label: { id: "Layanan Informasi", en: "Information Services" },
      description: {
        id: "Pengumuman, dokumen, dan layanan publik",
        en: "Announcements, documents, and public services",
      },
      url: "/informasi-umum",
    },
  ],
  infoItems: [
    { label: { id: "15 kecamatan", en: "15 districts" } },
    { label: { id: "29 OPD", en: "29 agencies" } },
    { label: { id: "Layer peta aktif", en: "Active map layers" } },
    { label: { id: "Publikasi terverifikasi", en: "Verified publication" } },
  ],
  visualOrder: ["regent", "content", "vice_regent"],
};

export const publicKpis: StatCard[] = [
  {
    label: { id: "Jumlah Kecamatan", en: "Districts" },
    value: "15",
    note: {
      id: "Wilayah administratif kabupaten",
      en: "Administrative districts across the regency",
    },
  },
  {
    label: { id: "Jumlah Desa/Kelurahan", en: "Villages/Wards" },
    value: "167",
    note: {
      id: "Data master wilayah terintegrasi",
      en: "Integrated area master data",
    },
  },
  {
    label: { id: "Jumlah OPD", en: "Agencies" },
    value: "29",
    note: {
      id: "Perangkat daerah dan unit pelaksana",
      en: "Regional agencies and implementing units",
    },
  },
  {
    label: { id: "Data Sektoral Aktif", en: "Active Sectoral Datasets" },
    value: "42",
    note: {
      id: "Statistik, peta, dan publikasi digital",
      en: "Statistics, maps, and digital publications",
    },
  },
  {
    label: { id: "Berita Terbaru", en: "Published News" },
    value: "136",
    note: {
      id: "Berita kabupaten dan dinas terbit",
      en: "Published regency and agency stories",
    },
  },
  {
    label: { id: "Layer Peta", en: "Map Layers" },
    value: "18",
    note: {
      id: "Layer publik administrasi dan sektoral",
      en: "Public administrative and sectoral layers",
    },
  },
];

export const executiveSpotlightStats: StatCard[] = [
  {
    label: { id: "Data Administratif", en: "Administrative Data" },
    value: "15 kecamatan",
    note: {
      id: "167 desa/kelurahan",
      en: "167 villages/wards",
    },
  },
  {
    label: { id: "Data Kependudukan", en: "Population Data" },
    value: "150.000 jiwa",
    note: {
      id: "Kepadatan xx jiwa/km2 | Pertumbuhan xx%",
      en: "Density xx people/km2 | Growth xx%",
    },
  },
  {
    label: { id: "Data Wilayah & Geografi", en: "Area & Geography" },
    value: "105 pulau",
    note: {
      id: "xx berpenghuni | xx tidak berpenghuni",
      en: "xx inhabited | xx uninhabited",
    },
  },
  {
    label: { id: "Data Ekonomi / Unggulan", en: "Economy / Regional Strengths" },
    value: "Perikanan dominan",
    note: {
      id: "Kelapa, pala, dan PDRB daerah xx",
      en: "Coconut, nutmeg, and regional GDP xx",
    },
  },
  {
    label: { id: "Infrastruktur / Pemerintahan", en: "Infrastructure / Government" },
    value: "29 OPD",
    note: {
      id: "Sekolah, puskesmas, dan pelabuhan xx",
      en: "Schools, health centers, and ports xx",
    },
  },
];

export const statisticSeries = [
  { year: "2023", stunting: 152, imunisasi: 78, siswa: 1280, umkm: 436 },
  { year: "2024", stunting: 144, imunisasi: 82, siswa: 1335, umkm: 462 },
  { year: "2025", stunting: 126, imunisasi: 87, siswa: 1340, umkm: 489 },
];

export const statisticsTableColumns: TableColumn[] = [
  { key: "indicator", label: { id: "Indikator", en: "Indicator" } },
  { key: "district", label: { id: "Wilayah", en: "Area" } },
  { key: "period", label: { id: "Periode", en: "Period" } },
  { key: "value", label: { id: "Nilai", en: "Value" } },
  { key: "source", label: { id: "Sumber", en: "Source" } },
];

export const statisticsTableRows: TableRow[] = [
  {
    indicator: "Kasus Stunting",
    district: "Tahuna",
    period: "2025",
    value: "126",
    source: "Dinas Kesehatan",
  },
  {
    indicator: "Cakupan Imunisasi",
    district: "Kabupaten",
    period: "2025",
    value: "87%",
    source: "Dinas Kesehatan",
  },
  {
    indicator: "Jumlah Siswa",
    district: "Kabupaten",
    period: "2025",
    value: "1.340",
    source: "Dinas Pendidikan",
  },
  {
    indicator: "Jumlah UMKM",
    district: "Kabupaten",
    period: "2025",
    value: "489",
    source: "Dinas Perdagangan",
  },
];

export const publicNews: NewsItem[] = [
  {
    slug: "portal-data-terpadu-kabupaten-sangihe-mulai-diuji-internal",
    title: "Portal Data Terpadu Kabupaten Sangihe Mulai Diuji Internal",
    excerpt:
      "Diskominfo menyiapkan integrasi data sektoral, statistik, dan peta digital dalam satu portal publik.",
    category: "Berita Daerah",
    date: "5 Maret 2026",
    imageUrl:
      "https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80",
  },
  {
    slug: "pemutakhiran-data-stunting-puskesmas-dilakukan-secara-bulanan",
    title: "Pemutakhiran Data Stunting Puskesmas Dilakukan Secara Bulanan",
    excerpt:
      "Pelaporan terstruktur membantu Dinas Kesehatan mempercepat validasi dan publikasi indikator prioritas.",
    category: "Kesehatan",
    date: "7 Maret 2026",
    imageUrl:
      "https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1200&q=80",
  },
  {
    slug: "dashboard-pendidikan-menampilkan-rasio-guru-dan-sebaran-sekolah",
    title: "Dashboard Pendidikan Menampilkan Rasio Guru dan Sebaran Sekolah",
    excerpt:
      "Pemerintah daerah menyiapkan pembacaan tren pendidikan per kecamatan secara lebih cepat.",
    category: "Pendidikan",
    date: "9 Maret 2026",
    imageUrl:
      "https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80",
  },
  {
    slug: "peta-blank-spot-internet-menjadi-prioritas-monitoring-kominfo",
    title: "Peta Blank Spot Internet Menjadi Prioritas Monitoring Kominfo",
    excerpt:
      "Layer peta blank spot dan titik layanan internet kini tersedia dalam dashboard GIS lintas dinas.",
    category: "Kominfo",
    date: "11 Maret 2026",
    imageUrl:
      "https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80",
  },
];

export const mapPoints: MapPoint[] = [
  {
    name: "Puskesmas Tahuna",
    coordinates: [125.4931, 3.6125],
    color: "#0f766e",
    layer: "Kesehatan",
  },
  {
    name: "SMA Negeri Tahuna",
    coordinates: [125.4912, 3.6111],
    color: "#0f4c81",
    layer: "Pendidikan",
  },
  {
    name: "Blank Spot Nusa Tabukan",
    coordinates: [125.412, 3.76],
    color: "#c4931c",
    layer: "Kominfo",
  },
  {
    name: "Sentra Wisata Pantai Pananaru",
    coordinates: [125.533, 3.561],
    color: "#0b7c8e",
    layer: "Pariwisata",
  },
];

export const defaultMapLayers: MapLayerSummary[] = [
  { name: "Peta Fasilitas Kesehatan", featureCount: 12, department: "Dinas Kesehatan" },
  { name: "Peta Sekolah", featureCount: 28, department: "Dinas Pendidikan" },
  { name: "Peta Kelompok Tani", featureCount: 18, department: "Dinas Pertanian" },
  { name: "Peta Blank Spot Internet", featureCount: 9, department: "Dinas Kominfo" },
];

export const sectorHighlights = [
  {
    title: { id: "Kesehatan", en: "Health" },
    description: {
      id: "Stunting, imunisasi, kunjungan puskesmas, dan sebaran layanan kesehatan.",
      en: "Stunting, immunization, primary care visits, and health service distribution.",
    },
  },
  {
    title: { id: "Pendidikan", en: "Education" },
    description: {
      id: "Sekolah, siswa, guru, kondisi sarana, dan akses internet pendidikan.",
      en: "Schools, students, teachers, facilities, and school internet access.",
    },
  },
  {
    title: { id: "Kelautan & Perikanan", en: "Marine & Fisheries" },
    description: {
      id: "Kelompok nelayan, sentra budidaya, dan produksi tangkap.",
      en: "Fisher groups, aquaculture hubs, and capture production.",
    },
  },
];

export const profileHighlights = {
  title: {
    id: "Profil singkat daerah dan indikator utama Kabupaten Kepulauan Sangihe",
    en: "Regional profile and key indicators of Sangihe Islands Regency",
  },
  copy: {
    id: "Ringkasan ini mempertahankan informasi administratif sebagai fondasi, lalu menambahkan konteks kependudukan, geografi, ekonomi unggulan, dan infrastruktur pemerintahan agar profil daerah lebih informatif.",
    en: "This summary keeps administrative information as the foundation, then adds population, geography, economic strengths, and government infrastructure to make the regional profile more informative.",
  },
  facts: [
    {
      label: { id: "Data Administratif", en: "Administrative Data" },
      value: "15 kecamatan / 167 desa-kelurahan",
      detail: {
        id: "Fondasi data wilayah administratif tetap dipakai sebagai informasi utama.",
        en: "Administrative territorial data remains the core foundational information.",
      },
    },
    {
      label: { id: "Data Kependudukan", en: "Population Data" },
      value: "150.000 jiwa",
      detail: {
        id: "Kepadatan: xx jiwa/km2 | Pertumbuhan: xx%",
        en: "Density: xx people/km2 | Growth: xx%",
      },
    },
    {
      label: { id: "Data Wilayah & Geografi", en: "Area & Geography" },
      value: "105 pulau",
      detail: {
        id: "xx berpenghuni | xx tidak berpenghuni",
        en: "xx inhabited | xx uninhabited",
      },
    },
    {
      label: { id: "Data Ekonomi / Unggulan Daerah", en: "Economy / Regional Strengths" },
      value: "Perikanan, kelautan, kelapa & pala",
      detail: {
        id: "PDRB daerah: xx | Komoditas utama tetap ditonjolkan.",
        en: "Regional GDP: xx | Key commodities remain highlighted.",
      },
    },
    {
      label: { id: "Data Infrastruktur / Pemerintahan", en: "Infrastructure / Government" },
      value: "29 OPD",
      detail: {
        id: "Sekolah, puskesmas, dan pelabuhan: xx",
        en: "Schools, community health centers, and ports: xx",
      },
    },
  ],
};

export const infoSections = [
  {
    title: { id: "Pengumuman", en: "Announcements" },
    description: {
      id: "Informasi layanan, pengumuman resmi, dan pembaruan portal.",
      en: "Service notices, official announcements, and portal updates.",
    },
  },
  {
    title: { id: "Agenda", en: "Agenda" },
    description: {
      id: "Kegiatan kabupaten, rapat koordinasi, dan agenda dinas.",
      en: "Regency events, coordination meetings, and agency schedules.",
    },
  },
  {
    title: { id: "Layanan Informasi", en: "Information Services" },
    description: {
      id: "Akses dokumen, pusat unduh, FAQ, dan kanal kontak.",
      en: "Document access, download center, FAQ, and contact channels.",
    },
  },
];

export const documentItems = [
  {
    title: "Ringkasan Statistik Daerah 2025",
    category: "Statistik",
    updatedAt: "10 Maret 2026",
  },
  {
    title: "Profil Wilayah dan Potensi Kecamatan",
    category: "Profil Daerah",
    updatedAt: "2 Maret 2026",
  },
  {
    title: "Rencana Pengembangan Layanan Digital",
    category: "Perencanaan",
    updatedAt: "25 Februari 2026",
  },
];

export const departmentProfiles = [
  "Dinas Kesehatan",
  "Dinas Pendidikan",
  "Dinas Pertanian",
  "Dinas Kelautan dan Perikanan",
  "Dinas Sosial",
  "Dinas PUPR dan Perkim",
  "Dinas Pariwisata",
  "Dinas Kominfo",
];

export const faqItems = [
  {
    question: {
      id: "Bagaimana data ditampilkan di portal publik?",
      en: "How is data published on the public portal?",
    },
    answer: {
      id: "Data dipublikasi setelah melalui verifikasi unit, kecamatan, dinas, dan approval akhir Kominfo.",
      en: "Data is published after unit, district, agency, and final communications office verification.",
    },
  },
  {
    question: {
      id: "Apakah portal mendukung peta sektoral?",
      en: "Does the portal support sectoral maps?",
    },
    answer: {
      id: "Ya, portal mendukung peta administrasi, fasilitas, infrastruktur, bantuan sosial, blank spot internet, dan layer sektoral lainnya.",
      en: "Yes. The portal supports administrative, facility, infrastructure, social aid, connectivity blind spot, and other sectoral layers.",
    },
  },
];

export const searchSuggestions = [
  "stunting",
  "blank spot",
  "pendidikan",
  "dokumen",
];
