import type { Language, LocalizedText } from "@/lib/portal-data";

export const interfaceCopy = {
  brandKicker: {
    id: "DINAS KOMUNIKASI DAN INFORMATIKA",
    en: "DEPARTMENT OF COMMUNICATIONS AND INFORMATICS",
  },
  brandTitle: {
    id: "PEMERINTAH KABUPATEN KEPULAUAN SANGIHE",
    en: "GOVERNMENT OF SANGIHE ISLANDS REGENCY",
  },
  portalName: {
    id: "Portal Informasi Publik dan Peta Digital",
    en: "Public Information and Digital Maps Portal",
  },
  search: {
    id: "Pencarian",
    en: "Search",
  },
  login: {
    id: "Masuk Admin",
    en: "Admin Login",
  },
  readMore: {
    id: "Baca selengkapnya",
    en: "Read more",
  },
  learnMore: {
    id: "Pelajari lebih lanjut",
    en: "Learn more",
  },
  viewAll: {
    id: "Lihat semua",
    en: "View all",
  },
  lastUpdated: {
    id: "Pembaruan terakhir",
    en: "Last updated",
  },
  source: {
    id: "Sumber data",
    en: "Data source",
  },
  export: {
    id: "Ekspor data",
    en: "Export data",
  },
  filters: {
    id: "Filter data",
    en: "Data filters",
  },
  noResults: {
    id: "Belum ada hasil yang cocok.",
    en: "No matching results yet.",
  },
  quickAccess: {
    id: "Akses Cepat",
    en: "Quick Access",
  },
  footerLead: {
    id: "Portal resmi untuk statistik, peta digital, berita, dokumen, dan layanan informasi publik Kabupaten Kepulauan Sangihe.",
    en: "Official portal for statistics, digital maps, news, documents, and public information services of Sangihe Islands Regency.",
  },
  footerAddress: {
    id: "Jl. A. Yani Tahuna, Kabupaten Kepulauan Sangihe",
    en: "Jl. A. Yani Tahuna, Sangihe Islands Regency",
  },
  footerContact: {
    id: "Kontak Kominfo",
    en: "Communications Office",
  },
  footerSearchPlaceholder: {
    id: "Cari berita, dokumen, atau halaman...",
    en: "Search news, documents, or pages...",
  },
  themes: {
    light: { id: "Terang", en: "Light" },
    dark: { id: "Gelap", en: "Dark" },
    system: { id: "Otomatis", en: "System" },
  },
  languages: {
    id: { id: "Indonesia", en: "Bahasa Indonesia" },
    en: { id: "English", en: "English" },
  },
};

export function localizeText(value: LocalizedText, language: Language) {
  return language === "en" ? value.en : value.id;
}
