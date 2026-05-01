import {
  defaultMapLayers,
  departmentProfiles,
  documentItems,
  fallbackHero,
  mapPoints,
  pageSlugs,
  profileHighlights,
  publicKpis,
  publicNews,
  sectorHighlights,
  statisticSeries,
  statisticsTableRows,
  type ChartDatum,
  type HeroData,
  type LinkCard,
  type MapLayerSummary,
  type MapWorkbenchLayer,
  type NewsItem,
  type OpenDataResponse,
  type PortalSettings,
  type PortalFilters,
  type PortalOption,
  type PortalSummary,
  type StatCard,
  type StatisticsRecord,
} from "@/lib/portal-data";

const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_BASE_URL ?? "http://localhost:8000/api";
const API_ORIGIN = API_BASE_URL.replace(/\/api(?:\/v\d+)?\/?$/, "");

type HeroResponse = {
  data?: {
    background_type?: "image" | "video";
    badge?: { id?: string; en?: string };
    headline?: { id?: string; en?: string };
    subheadline?: { id?: string; en?: string };
    cta_primary?: { label_id?: string; label_en?: string; url?: string };
    cta_secondary?: { label_id?: string; label_en?: string; url?: string };
    background_image_url?: string;
    background_image_urls?: string[];
    background_video_url?: string | null;
    background_video_poster_url?: string | null;
    regent?: { name?: string; title_id?: string; title_en?: string; image_url?: string };
    vice_regent?: { name?: string; title_id?: string; title_en?: string; image_url?: string };
    quick_links?: Array<{
      label_id?: string;
      label_en?: string;
      description_id?: string;
      description_en?: string;
      url?: string;
    }>;
    info_items?: Array<{ label_id?: string; label_en?: string }>;
    visual_order?: string[];
  };
};

type HomeResponse = {
  hero?: HeroResponse["data"];
  portal?: {
    title?: string | null;
    logo_url?: string | null;
    footer_description?: string | null;
    contact?: { address?: string | null; email?: string | null; phone?: string | null };
  };
  landing_content?: LandingContentResponse;
  summary?: PortalSummary;
  statistics?: { kpis?: Array<{ indicator?: string | null; value?: number; period_year?: number | null }> };
  featured_news?: Array<ContentPayload>;
  featured_layers?: Array<LayerPayload>;
};

type LandingContentResponse = {
  about_region?: {
    title?: string | null;
    subtitle?: string | null;
    content?: string | null;
    image_url?: string | null;
    button_text?: string | null;
    button_link?: string | null;
  };
  map_highlight?: {
    title?: string | null;
    description?: string | null;
    button_text?: string | null;
    button_link?: string | null;
  };
  statistics_highlight?: {
    title?: string | null;
    description?: string | null;
    button_text?: string | null;
    button_link?: string | null;
  };
  open_data?: {
    title?: string | null;
    description?: string | null;
    primary_button_text?: string | null;
    primary_button_link?: string | null;
    secondary_button_text?: string | null;
  };
};

type SummaryResponse = { data?: PortalSummary };
type PortalSettingsResponse = {
  data?: {
    title?: string | null;
    logo_url?: string | null;
    footer_description?: string | null;
    contact?: { address?: string | null; email?: string | null; phone?: string | null };
  };
};
type FiltersResponse = { data?: PortalOption[] };
type LayersResponse = { data?: LayerPayload[] };
type WorkbenchResponse = {
  data?: {
    initial_view?: { center?: [number, number]; zoom?: number };
    layers?: WorkbenchLayerPayload[];
  };
};
type StatisticsResponse = {
  summary?: { kpis?: Array<{ indicator?: string | null; value?: number; period_year?: number | null }> };
  filters?: PortalFilters;
  summary_cards?: Array<{ indicator?: string | null; unit?: string | null; value?: number; period?: string | null }>;
  trend?: ChartDatum[];
  comparison?: ChartDatum[];
  table?: StatisticsRecord[];
  data?: Record<string, Array<{ period_year?: number; value?: number }>>;
};
type ContentListResponse = { data?: { data?: ContentPayload[] } };
type ContentDetailResponse = { data?: ContentPayload };
type DocumentsResponse = { data?: { data?: Array<{ title: string; category?: string | null; published_at?: string | null }> } };
type DepartmentsResponse = { data?: Array<{ name: string }> };
type PageResponse = {
  data?: {
    title?: string | null;
    content?: string | null;
    seo_description?: string | null;
    image_url?: string | null;
  };
};
type SearchResponse = {
  keyword?: string;
  news?: Array<{ slug: string; title?: string | null; excerpt?: string | null }>;
  pages?: Array<{ slug: string; title?: string | null }>;
  departments?: Array<{ name?: string | null }>;
};

type ContentPayload = {
  slug: string;
  jenis?: string | null;
  title: string;
  excerpt?: string | null;
  content?: string | null;
  published_at?: string | null;
  featured_image_url?: string | null;
  category?: { name?: string | null };
  department?: { name?: string | null };
  kecamatan?: { name?: string | null };
  desa?: { name?: string | null };
  location?: string | null;
  latitude?: number | null;
  longitude?: number | null;
};

type LayerPayload = {
  slug?: string;
  name?: string | null;
  features_count?: number;
  department?: { name?: string | null };
  color?: string | null;
  kind?: string | null;
};

type WorkbenchFeaturePayload = {
  geometry?: { type?: string; coordinates?: unknown };
  properties?: {
    id?: string | number;
    name?: string | null;
    summary?: string | null;
    layer_name?: string | null;
    layer_slug?: string | null;
    popup_subtitle?: string | null;
    location_label?: string | null;
    starts_at?: string | null;
    detail_url?: string | null;
    color?: string | null;
    kind?: string | null;
    kecamatan_id?: number | null;
    desa_id?: number | null;
    opd_id?: number | null;
    jenis_fasilitas?: string | null;
  };
};

type WorkbenchLayerPayload = {
  id?: string;
  slug?: string;
  name?: string | null;
  kind?: string | null;
  layer_type?: string | null;
  department?: string | null;
  color?: string | null;
  feature_count?: number;
  features?: { type?: string; features?: WorkbenchFeaturePayload[] };
};

async function getJson<T>(path: string): Promise<T | null> {
  try {
    const response = await fetch(`${API_BASE_URL}${path}`, {
      next: { revalidate: 60 },
      headers: { Accept: "application/json" },
      signal: AbortSignal.timeout(4000),
    });

    if (!response.ok) {
      return null;
    }

    return (await response.json()) as T;
  } catch {
    return null;
  }
}

function resolveApiAssetUrl(value?: string | null): string {
  if (!value) {
    return "";
  }

  if (/^https?:\/\//i.test(value)) {
    return value;
  }

  if (value.startsWith("//")) {
    return `https:${value}`;
  }

  if (value.startsWith("/")) {
    return `${API_ORIGIN}${value}`;
  }

  return `${API_ORIGIN}/storage/${value.replace(/^storage\//, "")}`;
}

function formatDate(value?: string | null): string {
  if (!value) {
    return "Belum terbit";
  }

  return new Intl.DateTimeFormat("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric",
  }).format(new Date(value));
}

function formatNumber(value?: number | null): string {
  if (value == null) {
    return "-";
  }

  return new Intl.NumberFormat("id-ID", {
    maximumFractionDigits: Number.isInteger(value) ? 0 : 2,
  }).format(value);
}

function normalizeHero(payload?: HeroResponse["data"]): HeroData {
  if (!payload) {
    return fallbackHero;
  }

  const quickLinks: LinkCard[] =
    payload.quick_links?.map((item) => ({
      label: { id: item.label_id ?? "Tautan", en: item.label_en ?? item.label_id ?? "Link" },
      description: {
        id: item.description_id ?? "Akses cepat",
        en: item.description_en ?? item.description_id ?? "Quick access",
      },
      url: item.url ?? "/",
    })) ?? fallbackHero.quickLinks;

  const backgroundImageUrls =
    payload.background_image_urls?.map(resolveApiAssetUrl).filter(Boolean) ??
    (payload.background_image_url ? [resolveApiAssetUrl(payload.background_image_url)] : []);

  return {
    badge: { id: payload.badge?.id ?? fallbackHero.badge.id, en: payload.badge?.en ?? fallbackHero.badge.en },
    headline: {
      id: payload.headline?.id ?? fallbackHero.headline.id,
      en: payload.headline?.en ?? fallbackHero.headline.en,
    },
    subheadline: {
      id: payload.subheadline?.id ?? fallbackHero.subheadline.id,
      en: payload.subheadline?.en ?? fallbackHero.subheadline.en,
    },
    ctaPrimary: {
      label: {
        id: payload.cta_primary?.label_id ?? fallbackHero.ctaPrimary.label.id,
        en: payload.cta_primary?.label_en ?? fallbackHero.ctaPrimary.label.en,
      },
      url: payload.cta_primary?.url ?? fallbackHero.ctaPrimary.url,
    },
    ctaSecondary: {
      label: {
        id: payload.cta_secondary?.label_id ?? fallbackHero.ctaSecondary.label.id,
        en: payload.cta_secondary?.label_en ?? fallbackHero.ctaSecondary.label.en,
      },
      url: payload.cta_secondary?.url ?? fallbackHero.ctaSecondary.url,
    },
    backgroundImageUrls: backgroundImageUrls.length ? backgroundImageUrls : fallbackHero.backgroundImageUrls,
    backgroundType: payload.background_type ?? "image",
    backgroundVideoUrl: resolveApiAssetUrl(payload.background_video_url) || null,
    backgroundVideoPosterUrl: resolveApiAssetUrl(payload.background_video_poster_url) || null,
    regent: {
      name: payload.regent?.name ?? fallbackHero.regent.name,
      title: {
        id: payload.regent?.title_id ?? fallbackHero.regent.title.id,
        en: payload.regent?.title_en ?? fallbackHero.regent.title.en,
      },
      imageUrl: resolveApiAssetUrl(payload.regent?.image_url) || fallbackHero.regent.imageUrl,
    },
    viceRegent: {
      name: payload.vice_regent?.name ?? fallbackHero.viceRegent.name,
      title: {
        id: payload.vice_regent?.title_id ?? fallbackHero.viceRegent.title.id,
        en: payload.vice_regent?.title_en ?? fallbackHero.viceRegent.title.en,
      },
      imageUrl: resolveApiAssetUrl(payload.vice_regent?.image_url) || fallbackHero.viceRegent.imageUrl,
    },
    quickLinks,
    infoItems:
      payload.info_items?.map((item) => ({
        label: { id: item.label_id ?? "Info", en: item.label_en ?? item.label_id ?? "Info" },
      })) ?? fallbackHero.infoItems,
    visualOrder: payload.visual_order ?? fallbackHero.visualOrder,
  };
}

function normalizePortalSettings(
  payload?: PortalSettingsResponse["data"] | HomeResponse["portal"],
): PortalSettings {
  return {
    title: payload?.title ?? "Portal Data Daerah Kabupaten Kepulauan Sangihe",
    logoUrl: resolveApiAssetUrl(payload?.logo_url) || null,
    footerDescription: payload?.footer_description ?? null,
    contact: {
      address: payload?.contact?.address ?? null,
      email: payload?.contact?.email ?? null,
      phone: payload?.contact?.phone ?? null,
    },
  };
}

function normalizeLandingContent(payload?: LandingContentResponse | null) {
  return {
    aboutRegion: {
      title: payload?.about_region?.title ?? "Tentang Kabupaten Kepulauan Sangihe",
      subtitle:
        payload?.about_region?.subtitle ??
        "Profil singkat daerah kepulauan, karakter wilayah maritim, serta ringkasan indikator utama.",
      content:
        payload?.about_region?.content ??
        "Kabupaten Kepulauan Sangihe adalah wilayah kepulauan di Provinsi Sulawesi Utara. Portal ini menghimpun statistik, peta digital, fasilitas publik, berita, dan data pembangunan daerah secara terbuka dan terpadu.",
      imageUrl: resolveApiAssetUrl(payload?.about_region?.image_url) || null,
      buttonText: payload?.about_region?.button_text ?? "Tentang Daerah",
      buttonLink: payload?.about_region?.button_link ?? "/tentang-daerah",
    },
    mapHighlight: {
      title: payload?.map_highlight?.title ?? "Peta Interaktif Daerah",
      description:
        payload?.map_highlight?.description ??
        "Klik kecamatan atau desa untuk melihat batas wilayah, fasilitas publik, kegiatan, dan statistik terkait.",
      buttonText: payload?.map_highlight?.button_text ?? "Buka Peta Interaktif",
      buttonLink: payload?.map_highlight?.button_link ?? "/peta",
    },
    statisticsHighlight: {
      title: payload?.statistics_highlight?.title ?? "Statistik Pembangunan",
      description:
        payload?.statistics_highlight?.description ??
        "Pantau indikator prioritas daerah melalui ringkasan dan grafik publik yang mudah dipahami.",
      buttonText: payload?.statistics_highlight?.button_text ?? "Lihat Semua Statistik",
      buttonLink: payload?.statistics_highlight?.button_link ?? "/statistik",
    },
    openData: {
      title: payload?.open_data?.title ?? "Data Terbuka untuk Masyarakat",
      description:
        payload?.open_data?.description ??
        "Akses data agregat daerah untuk mendukung transparansi, penelitian, dan pengambilan keputusan.",
      primaryButtonText: payload?.open_data?.primary_button_text ?? "Lihat Dataset",
      primaryButtonLink: payload?.open_data?.primary_button_link ?? "/data",
      secondaryButtonText: payload?.open_data?.secondary_button_text ?? "Unduh Data",
    },
  };
}

function mapKpis(items?: Array<{ indicator?: string | null; value?: number; period_year?: number | null }>): StatCard[] {
  if (!items?.length) {
    return publicKpis;
  }

  return items.map((item) => ({
    label: { id: item.indicator ?? "Indikator", en: item.indicator ?? "Indicator" },
    value: formatNumber(Number(item.value ?? 0)),
    note: { id: `Periode ${item.period_year ?? "-"}`, en: `Period ${item.period_year ?? "-"}` },
  }));
}

function summaryToKpis(summary?: PortalSummary | null): StatCard[] {
  if (!summary) {
    return publicKpis.slice(0, 5);
  }

  return [
    {
      label: { id: "Jumlah Kecamatan", en: "Districts" },
      value: formatNumber(summary.jumlah_kecamatan),
      note: { id: "Wilayah administratif aktif", en: "Active administrative districts" },
    },
    {
      label: { id: "Jumlah Desa", en: "Villages" },
      value: formatNumber(summary.jumlah_desa),
      note: { id: "Desa dan kelurahan aktif", en: "Active villages and wards" },
    },
    {
      label: { id: "Jumlah Penduduk", en: "Population" },
      value: formatNumber(summary.jumlah_penduduk),
      note: { id: summary.periode_terbaru ?? "Data terbaru", en: summary.periode_terbaru ?? "Latest data" },
    },
    {
      label: { id: "Fasilitas Publik", en: "Public Facilities" },
      value: formatNumber(summary.jumlah_fasilitas_publik),
      note: { id: "Sumber data aktif", en: "Active public data sources" },
    },
    {
      label: { id: "Berita/Kegiatan", en: "News/Events" },
      value: formatNumber(summary.jumlah_berita_kegiatan),
      note: { id: "Konten berstatus terbit", en: "Published public content" },
    },
  ];
}

function mapNews(items?: ContentPayload[]): NewsItem[] {
  if (!items?.length) {
    return publicNews;
  }

  return items.map((item, index) => ({
    slug: item.slug,
    title: item.title,
    excerpt: item.excerpt ?? "Konten publik Kabupaten Kepulauan Sangihe.",
    category: item.jenis === "kegiatan" ? "Kegiatan" : item.category?.name ?? "Berita",
    date: formatDate(item.published_at),
    imageUrl: resolveApiAssetUrl(item.featured_image_url) || publicNews[index % publicNews.length]?.imageUrl,
    department: item.department?.name ?? undefined,
    type: item.jenis ?? "berita",
    location: item.location ?? item.kecamatan?.name ?? undefined,
    latitude: item.latitude ?? null,
    longitude: item.longitude ?? null,
  }));
}

function mapLayers(items?: LayerPayload[]): MapLayerSummary[] {
  if (!items?.length) {
    return defaultMapLayers;
  }

  return items.map((item) => ({
    slug: item.slug ?? undefined,
    name: item.name ?? "Layer Peta",
    featureCount: Number(item.features_count ?? 0),
    department: item.department?.name ?? undefined,
    color: item.color ?? undefined,
    kind: item.kind ?? undefined,
  }));
}

function normalizeLayerKind(value?: string | null): "news" | "event" | "gis" {
  return value === "news" || value === "event" ? value : "gis";
}

function extractCoordinates(geometry?: WorkbenchFeaturePayload["geometry"]): [number, number] | null {
  if (!geometry?.type || geometry.coordinates == null) {
    return null;
  }

  if (geometry.type === "Point" && Array.isArray(geometry.coordinates)) {
    const [longitude, latitude] = geometry.coordinates as [number, number];

    return typeof longitude === "number" && typeof latitude === "number" ? [longitude, latitude] : null;
  }

  if (
    geometry.type === "Polygon" &&
    Array.isArray(geometry.coordinates) &&
    Array.isArray(geometry.coordinates[0]) &&
    Array.isArray(geometry.coordinates[0][0])
  ) {
    const [longitude, latitude] = geometry.coordinates[0][0] as [number, number];

    return typeof longitude === "number" && typeof latitude === "number" ? [longitude, latitude] : null;
  }

  if (
    geometry.type === "MultiPolygon" &&
    Array.isArray(geometry.coordinates) &&
    Array.isArray(geometry.coordinates[0]) &&
    Array.isArray(geometry.coordinates[0][0]) &&
    Array.isArray(geometry.coordinates[0][0][0])
  ) {
    const [longitude, latitude] = geometry.coordinates[0][0][0] as [number, number];

    return typeof longitude === "number" && typeof latitude === "number" ? [longitude, latitude] : null;
  }

  return null;
}

function mapWorkbenchLayers(payload?: WorkbenchResponse["data"]): MapWorkbenchLayer[] {
  if (!payload?.layers?.length) {
    return [
      {
        id: "fallback-points",
        slug: "fallback-points",
        name: "Fasilitas Publik",
        kind: "gis",
        layerType: "point",
        featureCount: mapPoints.length,
        department: "Publik",
        color: "#0f766e",
        features: mapPoints.map((point, index) => ({
          id: `fallback-${index}`,
          name: point.name,
          coordinates: point.coordinates,
          layerName: point.layer,
          layerSlug: "fallback-points",
          kind: "gis",
          color: point.color,
          summary: point.layer,
        })),
      },
    ];
  }

  return payload.layers.map((layer) => {
    const features =
      layer.features?.features
        ?.map((feature, index) => {
          const coordinates = extractCoordinates(feature.geometry);

          if (!coordinates) {
            return null;
          }

          return {
            id: String(feature.properties?.id ?? `${layer.slug ?? "layer"}-${index}`),
            name: feature.properties?.name ?? "Fitur peta",
            coordinates,
            layerName: feature.properties?.layer_name ?? layer.name ?? "Layer",
            layerSlug: feature.properties?.layer_slug ?? layer.slug ?? "layer",
            kind: normalizeLayerKind(feature.properties?.kind),
            color: feature.properties?.color ?? layer.color ?? "#0b6b7a",
            summary: feature.properties?.summary ?? undefined,
            subtitle: feature.properties?.popup_subtitle ?? undefined,
            locationLabel: feature.properties?.location_label ?? undefined,
            startsAt: feature.properties?.starts_at ?? undefined,
            detailUrl: feature.properties?.detail_url ?? undefined,
            rawGeometry: feature.geometry,
            kecamatanId: feature.properties?.kecamatan_id ?? null,
            desaId: feature.properties?.desa_id ?? null,
            opdId: feature.properties?.opd_id ?? null,
            jenisFasilitas: feature.properties?.jenis_fasilitas ?? null,
          };
        })
        .filter((feature): feature is NonNullable<typeof feature> => feature !== null) ?? [];

    return {
      id: layer.id ?? layer.slug ?? `layer-${layer.name ?? "map"}`,
      slug: layer.slug ?? "layer",
      name: layer.name ?? "Layer Peta",
      kind: normalizeLayerKind(layer.kind),
      layerType: layer.layer_type ?? "point",
      featureCount: Number(layer.feature_count ?? features.length),
      department: layer.department ?? undefined,
      color: layer.color ?? "#0b6b7a",
      features,
    };
  });
}

function recordsToTableRows(records?: StatisticsRecord[]) {
  if (!records?.length) {
    return statisticsTableRows;
  }

  return records.slice(0, 80).map((row) => ({
    indicator: row.indikator ?? "-",
    district: row.desa ?? row.kecamatan ?? row.opd ?? "Kabupaten",
    period: row.periode ?? "-",
    value: `${formatNumber(row.nilai_total)} ${row.satuan ?? ""}`.trim(),
    source: row.opd ?? "-",
  }));
}

export async function getHomePageData() {
  const [heroResponse, home, summaryResponse, statistics, layers] = await Promise.all([
    getJson<HeroResponse>("/public/hero"),
    getJson<HomeResponse>("/public/home"),
    getJson<SummaryResponse>("/public/summary"),
    getJson<StatisticsResponse>("/public/statistik"),
    getJson<LayersResponse>("/public/peta/layers"),
  ]);

  const summary = summaryResponse?.data ?? home?.summary ?? null;

  return {
    portal: normalizePortalSettings(home?.portal),
    hero: normalizeHero(heroResponse?.data ?? home?.hero),
    summary,
    kpis: summaryToKpis(summary),
    statisticKpis: mapKpis(statistics?.summary?.kpis ?? home?.statistics?.kpis),
    statisticTrend: statistics?.trend ?? statisticSeries.map((item) => ({ label: item.year, value: item.siswa })),
    news: mapNews(home?.featured_news),
    mapPoints,
    mapLayers: mapLayers(home?.featured_layers ?? layers?.data),
    landingContent: normalizeLandingContent(home?.landing_content),
    sectorHighlights,
    profileHighlights,
  };
}

export async function getPortalSettingsData() {
  const settings = await getJson<PortalSettingsResponse>("/public/portal-settings");

  return normalizePortalSettings(settings?.data);
}

export async function getHeroData() {
  const hero = await getJson<HeroResponse>("/public/hero");

  return normalizeHero(hero?.data);
}

export async function getStatisticsPageData() {
  const statistics = await getJson<StatisticsResponse>("/public/statistik");

  return {
    filters: statistics?.filters,
    kpis:
      statistics?.summary_cards?.map((item) => ({
        label: { id: item.indicator ?? "Indikator", en: item.indicator ?? "Indicator" },
        value: formatNumber(Number(item.value ?? 0)),
        note: { id: item.period ?? item.unit ?? "Data terbaru", en: item.period ?? item.unit ?? "Latest data" },
      })) ?? mapKpis(statistics?.summary?.kpis),
    trend: statistics?.trend ?? statisticSeries.map((item) => ({ label: item.year, value: item.stunting })),
    comparison: statistics?.comparison ?? statisticSeries.map((item) => ({ label: item.year, value: item.siswa })),
    tableRecords: statistics?.table ?? [],
    chartData:
      statistics?.data?.["Kasus Stunting"]?.map((item, index) => ({
        year: String(item.period_year ?? statisticSeries[index]?.year ?? ""),
        stunting: Number(item.value ?? 0),
        imunisasi: Number(statistics?.data?.["Cakupan Imunisasi"]?.[index]?.value ?? statisticSeries[index]?.imunisasi ?? 0),
        siswa: Number(statistics?.data?.["Jumlah Siswa"]?.[index]?.value ?? statisticSeries[index]?.siswa ?? 0),
        umkm: statisticSeries[index]?.umkm ?? 0,
      })) ?? statisticSeries,
    tableRows: recordsToTableRows(statistics?.table),
  };
}

export async function getMapPageData() {
  const [layers, workbench, statistics] = await Promise.all([
    getJson<LayersResponse>("/public/peta/layers"),
    getJson<WorkbenchResponse>("/public/peta/workbench"),
    getJson<StatisticsResponse>("/public/statistik"),
  ]);

  return {
    layers: mapLayers(layers?.data),
    workbenchLayers: mapWorkbenchLayers(workbench?.data),
    initialView: {
      center: workbench?.data?.initial_view?.center ?? ([125.5302, 3.6118] as [number, number]),
      zoom: workbench?.data?.initial_view?.zoom ?? 11,
    },
    filters: statistics?.filters,
    statisticsRecords: statistics?.table ?? [],
  };
}

export async function getNewsPageData() {
  const news = await getJson<ContentListResponse>("/public/konten");

  return mapNews(news?.data?.data);
}

export async function getNewsDetailPageData(slug: string) {
  const detail = await getJson<ContentDetailResponse>(`/public/konten/${slug}`);
  const fallback = publicNews.find((item) => item.slug === slug) ?? publicNews[0];
  const mapped = mapNews(detail?.data ? [detail.data] : [])[0];

  return {
    slug,
    title: detail?.data?.title ?? fallback.title,
    excerpt: detail?.data?.excerpt ?? fallback.excerpt,
    content:
      detail?.data?.content ??
      "Konten lengkap berita tersedia setelah backend publikasi terhubung penuh.",
    category: mapped?.category ?? fallback.category,
    department: mapped?.department ?? detail?.data?.department?.name ?? "Diskominfo",
    date: mapped?.date ?? fallback.date,
    imageUrl: mapped?.imageUrl ?? fallback.imageUrl,
    latitude: detail?.data?.latitude ?? null,
    longitude: detail?.data?.longitude ?? null,
    location: detail?.data?.location ?? null,
    related: publicNews.filter((item) => item.slug !== slug).slice(0, 3),
  };
}

export async function getOpenDataPageData() {
  const response = await getJson<OpenDataResponse>("/public/open-data");

  return {
    meta: response?.meta ?? {
      title: "Dataset Statistik Agregat Kabupaten Kepulauan Sangihe",
      download_csv_url: `${API_BASE_URL}/public/open-data.csv`,
    },
    filters: response?.filters,
    rows: response?.data ?? [],
  };
}

export async function getAboutPageData() {
  const [summary, regions, villages, sources, news, map] = await Promise.all([
    getJson<SummaryResponse>("/public/summary"),
    getJson<FiltersResponse>("/public/kecamatan"),
    getJson<FiltersResponse>("/public/desa"),
    getJson<{ data?: Array<{ nama: string; jenis?: string | null; kecamatan?: string | null; desa?: string | null }> }>("/public/sumber-data"),
    getJson<ContentListResponse>("/public/konten?per_page=4"),
    getMapPageData(),
  ]);

  return {
    summary: summary?.data ?? null,
    regions: regions?.data ?? [],
    villages: villages?.data ?? [],
    sources: sources?.data ?? [],
    news: mapNews(news?.data?.data),
    map,
  };
}

export async function getDocumentsPageData() {
  const documents = await getJson<DocumentsResponse>("/public/documents");

  return (
    documents?.data?.data?.map((item) => ({
      title: item.title,
      category: item.category ?? "Dokumen",
      updatedAt: formatDate(item.published_at),
    })) ?? documentItems
  );
}

export async function getDepartmentsPageData() {
  const departments = await getJson<DepartmentsResponse>("/public/departments");

  return departments?.data?.map((item) => item.name) ?? departmentProfiles;
}

export async function getPageContent(slug: string, fallbackTitle: string) {
  const page = await getJson<PageResponse>(`/public/pages/${slug}`);

  return {
    title: page?.data?.title ?? fallbackTitle,
    content:
      page?.data?.content ??
      "Konten halaman statis akan tampil dari CMS ketika backend publikasi terhubung penuh.",
    description: page?.data?.seo_description ?? "",
    imageUrl: resolveApiAssetUrl(page?.data?.image_url) || null,
  };
}

export async function getContactPageData() {
  return getPageContent(pageSlugs.contact, "Kontak Kominfo Sangihe");
}

export async function getLeadershipPageData() {
  const [heroResponse, page] = await Promise.all([
    getJson<HeroResponse>("/public/hero"),
    getPageContent(pageSlugs.leadership, "Profil Bupati dan Wakil Bupati"),
  ]);

  return { hero: normalizeHero(heroResponse?.data), page };
}

export async function getSearchPageData(query: string) {
  if (!query.trim()) {
    return {
      keyword: "",
      news: publicNews.slice(0, 3),
      pages: ["Tentang Daerah", "Statistik", "Peta"],
      departments: departmentProfiles.slice(0, 4),
    };
  }

  const response = await getJson<SearchResponse>(`/public/search?q=${encodeURIComponent(query)}`);

  return {
    keyword: response?.keyword ?? query,
    news:
      response?.news?.map((item) => ({
        slug: item.slug,
        title: item.title ?? "Berita",
        excerpt: item.excerpt ?? "Ringkasan berita publik.",
        category: "Pencarian",
        date: "Hasil pencarian",
      })) ?? [],
    pages: response?.pages?.map((item) => item.title ?? item.slug) ?? ["Tentang Daerah"],
    departments: response?.departments?.map((item) => item.name ?? "Dinas") ?? departmentProfiles.slice(0, 4),
  };
}
