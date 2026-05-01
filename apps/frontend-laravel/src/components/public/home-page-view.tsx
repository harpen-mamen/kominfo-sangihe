"use client";

import Image from "next/image";
import Link from "next/link";
import {
  ArrowRight,
  Building2,
  Database,
  Download,
  FileText,
  Layers3,
  MapPinned,
  Newspaper,
  ShieldCheck,
  Waves,
  type LucideIcon,
} from "lucide-react";
import { localizeText } from "@/lib/i18n";
import type { HeroData, LocalizedText, MapLayerSummary, NewsItem, StatCard } from "@/lib/portal-data";
import { useUISettings } from "@/components/providers/ui-settings-provider";
import { AppButton } from "@/components/ui/app-button";

type LandingContent = {
  aboutRegion: {
    title: string;
    subtitle: string;
    content: string;
    imageUrl?: string | null;
    buttonText: string;
    buttonLink: string;
  };
  mapHighlight: {
    title: string;
    description: string;
    buttonText: string;
    buttonLink: string;
  };
  statisticsHighlight: {
    title: string;
    description: string;
    buttonText: string;
    buttonLink: string;
  };
  openData: {
    title: string;
    description: string;
    primaryButtonText: string;
    primaryButtonLink: string;
    secondaryButtonText: string;
  };
};

type HomePageViewProps = {
  hero: HeroData;
  kpis: StatCard[];
  statisticKpis?: StatCard[];
  news: NewsItem[];
  mapLayers: MapLayerSummary[];
  landingContent?: LandingContent;
  profileHighlights?: {
    title: LocalizedText;
    copy: LocalizedText;
    facts: Array<{
      label: LocalizedText;
      value: string;
      detail?: LocalizedText;
    }>;
  };
};

const OPEN_DATA_CSV_URL = `${
  process.env.NEXT_PUBLIC_API_BASE_URL ?? "http://localhost:8000/api"
}/public/open-data.csv`;

const fallbackLandingContent: LandingContent = {
  aboutRegion: {
    title: "Tentang Kabupaten Kepulauan Sangihe",
    subtitle: "Profil daerah kepulauan dengan kekuatan maritim, budaya, dan layanan publik berbasis data.",
    content:
      "Kabupaten Kepulauan Sangihe menghimpun informasi wilayah, statistik pembangunan, fasilitas publik, peta digital, dan berita daerah dalam satu portal publik yang mudah diakses.",
    imageUrl: null,
    buttonText: "Tentang Daerah",
    buttonLink: "/tentang-daerah",
  },
  mapHighlight: {
    title: "Peta Interaktif Daerah",
    description:
      "Klik kecamatan atau desa untuk melihat batas wilayah, fasilitas publik, kegiatan, dan statistik terkait.",
    buttonText: "Buka Peta Interaktif",
    buttonLink: "/peta",
  },
  statisticsHighlight: {
    title: "Statistik Pembangunan",
    description: "Pantau indikator prioritas daerah melalui ringkasan dan grafik publik yang mudah dipahami.",
    buttonText: "Lihat Semua Statistik",
    buttonLink: "/statistik",
  },
  openData: {
    title: "Data Terbuka untuk Masyarakat",
    description: "Akses data agregat daerah untuk mendukung transparansi, penelitian, dan pengambilan keputusan.",
    primaryButtonText: "Lihat Dataset",
    primaryButtonLink: "/data",
    secondaryButtonText: "Unduh Data",
  },
};

const kpiIcons = [MapPinned, Building2, Waves, Layers3, Newspaper];

export function HomePageView({
  hero,
  kpis,
  statisticKpis,
  news,
  mapLayers,
  landingContent,
}: HomePageViewProps) {
  const { language } = useUISettings();
  const content = landingContent ?? fallbackLandingContent;
  const heroImage = hero.backgroundImageUrls[0] ?? "/sangihe-coast.png";
  const aboutImage = content.aboutRegion.imageUrl || hero.backgroundImageUrls[1] || heroImage;
  const featuredNews = news.slice(0, 6);
  const featuredLayers = mapLayers.slice(0, 4);
  const featuredStats = (statisticKpis?.length ? statisticKpis : kpis).slice(0, 3);
  const totalFeatures = mapLayers.reduce((total, layer) => total + layer.featureCount, 0);
  const videoType = hero.backgroundVideoUrl?.toLowerCase().endsWith(".webm") ? "video/webm" : "video/mp4";

  return (
    <div className="public-page public-landing">
      <section className="landing-hero" aria-label="Portal Data Daerah Kabupaten Kepulauan Sangihe">
        <div className="landing-hero__media" aria-hidden="true">
          {hero.backgroundType === "video" && hero.backgroundVideoUrl ? (
            <>
              <Image
                alt=""
                className="landing-hero__fallback-image"
                fill
                priority
                sizes="100vw"
                src={hero.backgroundVideoPosterUrl || heroImage}
              />
              <video
                aria-hidden="true"
                autoPlay
                loop
                muted
                playsInline
                poster={hero.backgroundVideoPosterUrl ?? heroImage}
                preload="metadata"
              >
                <source src={hero.backgroundVideoUrl} type={videoType} />
              </video>
            </>
          ) : (
            <Image alt="" fill priority sizes="100vw" src={heroImage} />
          )}
        </div>
        <div className="landing-hero__overlay" aria-hidden="true" />

        <div className="site-container landing-hero__inner">
          <div className="landing-hero__copy">
            <span className="landing-badge">
              <ShieldCheck size={16} />
              {localizeText(hero.badge, language)}
            </span>
            <h1>{localizeText(hero.headline, language)}</h1>
            <p>{localizeText(hero.subheadline, language)}</p>
            <div className="landing-hero__actions">
              <AppButton href={hero.ctaPrimary.url}>
                {localizeText(hero.ctaPrimary.label, language)}
                <ArrowRight size={16} />
              </AppButton>
              <AppButton href={hero.ctaSecondary.url} variant="secondary">
                {localizeText(hero.ctaSecondary.label, language)}
              </AppButton>
            </div>
          </div>

          <div className="landing-hero__panel" aria-label="Ringkasan portal publik">
            <div>
              <span>Portal Data Daerah</span>
              <strong>Sangihe dalam angka</strong>
            </div>
            <div className="landing-hero__metrics">
              <Metric icon={MapPinned} label="Kecamatan" value={kpis[0]?.value ?? "-"} />
              <Metric icon={Building2} label="Desa" value={kpis[1]?.value ?? "-"} />
              <Metric icon={Layers3} label="Layer peta" value={mapLayers.length.toString()} />
            </div>
          </div>
        </div>
      </section>

      <section className="site-container landing-section landing-section--lifted" aria-label="Statistik ringkas">
        <div className="landing-kpi-grid">
          {kpis.slice(0, 5).map((item, index) => {
            const Icon = kpiIcons[index] ?? Database;

            return (
              <article className="landing-kpi" key={`${item.label.id}-${item.value}`}>
                <span className="landing-kpi__icon">
                  <Icon size={20} />
                </span>
                <small>{localizeText(item.label, language)}</small>
                <strong>{item.value}</strong>
                <em>{localizeText(item.note, language)}</em>
              </article>
            );
          })}
        </div>
      </section>

      <section className="site-container landing-section landing-map-highlight" aria-label="Peta interaktif highlight">
        <div className="landing-section__head">
          <span className="landing-eyebrow">Peta Interaktif</span>
          <h2>{content.mapHighlight.title}</h2>
          <p>{content.mapHighlight.description}</p>
          <div className="landing-actions-row">
            <AppButton href={content.mapHighlight.buttonLink}>
              {content.mapHighlight.buttonText}
              <ArrowRight size={16} />
            </AppButton>
          </div>
        </div>

        <div className="landing-map-card" aria-label="Preview peta dan layer aktif">
          <div className="landing-map-card__grid" aria-hidden="true" />
          <div className="landing-map-card__islands" aria-hidden="true">
            <span />
            <span />
            <span />
          </div>
          <div className="landing-map-card__content">
            <span className="landing-map-card__badge">Layer Publik</span>
            <strong>{featuredLayers.length || mapLayers.length} layer aktif</strong>
            <small>{totalFeatures} fitur wilayah, fasilitas, berita, dan kegiatan.</small>
            <div className="landing-layer-list">
              {(featuredLayers.length ? featuredLayers : mapLayers.slice(0, 4)).map((layer) => (
                <Link className="landing-layer-pill" href="/peta" key={layer.slug ?? layer.name}>
                  <span style={{ backgroundColor: layer.color ?? "#19B5C8" }} />
                  <div>
                    <b>{layer.name}</b>
                    <em>{layer.featureCount} fitur</em>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </div>
      </section>

      <section className="landing-band" aria-label="Statistik pembangunan highlight">
        <div className="site-container landing-section landing-section--band">
          <div className="landing-section__head">
            <span className="landing-eyebrow">Statistik Publik</span>
            <h2>{content.statisticsHighlight.title}</h2>
            <p>{content.statisticsHighlight.description}</p>
          </div>

          <div className="landing-stat-layout">
            <div className="landing-stat-cards">
              {featuredStats.map((item, index) => (
                <article className="landing-highlight" key={`${item.label.id}-${item.value}`}>
                  <span>{String(index + 1).padStart(2, "0")}</span>
                  <small>{localizeText(item.label, language)}</small>
                  <strong>{item.value}</strong>
                  <em>{localizeText(item.note, language)}</em>
                </article>
              ))}
            </div>

            <div className="landing-chart-card" aria-label="Grafik ringkas statistik">
              <div className="landing-chart-card__bars" aria-hidden="true">
                <span style={{ height: "52%" }} />
                <span style={{ height: "68%" }} />
                <span style={{ height: "46%" }} />
                <span style={{ height: "78%" }} />
                <span style={{ height: "62%" }} />
              </div>
              <div>
                <strong>Indikator Terbit</strong>
                <small>Ringkasan mengikuti data yang sudah dipublikasikan dari dashboard admin.</small>
              </div>
            </div>
          </div>

          <div className="landing-actions-row">
            <AppButton href={content.statisticsHighlight.buttonLink} variant="secondary">
              {content.statisticsHighlight.buttonText}
              <ArrowRight size={16} />
            </AppButton>
          </div>
        </div>
      </section>

      <section className="site-container landing-section landing-about" aria-label="Tentang daerah">
        <div className="landing-about__media">
          <Image alt="Foto daerah Kabupaten Kepulauan Sangihe" fill sizes="(max-width: 1080px) 100vw, 540px" src={aboutImage} />
        </div>
        <div className="landing-about__copy">
          <span className="landing-eyebrow">Tentang Daerah</span>
          <h2>{content.aboutRegion.title}</h2>
          <p className="landing-about__lead">{content.aboutRegion.subtitle}</p>
          <p>{content.aboutRegion.content}</p>
          <div className="landing-about__facts">
            <Fact label="Kecamatan" value={kpis[0]?.value ?? "-"} />
            <Fact label="Desa" value={kpis[1]?.value ?? "-"} />
            <Fact label="Fasilitas" value={kpis[3]?.value ?? "-"} />
          </div>
          <div className="landing-actions-row">
            <AppButton href={content.aboutRegion.buttonLink} variant="secondary">
              {content.aboutRegion.buttonText}
              <ArrowRight size={16} />
            </AppButton>
          </div>
        </div>
      </section>

      <section className="site-container landing-section" aria-label="Berita dan kegiatan terbaru">
        <div className="landing-section__head">
          <span className="landing-eyebrow">Berita / Kegiatan</span>
          <h2>Berita & kegiatan terbaru</h2>
          <p>Publikasi resmi yang sudah berstatus terbit dari dashboard admin.</p>
        </div>

        <div className="landing-news-grid">
          {featuredNews.map((item) => (
            <Link className="landing-news-card" href={`/berita/${item.slug}`} key={item.slug}>
              <div className="landing-news-card__media">
                {item.imageUrl ? (
                  <Image alt="" fill sizes="(max-width: 760px) 100vw, 420px" src={item.imageUrl} />
                ) : (
                  <div className="landing-news-card__placeholder">
                    <Newspaper size={32} />
                  </div>
                )}
              </div>
              <div className="landing-news-card__body">
                <span>{item.category || "Berita"}</span>
                <strong>{item.title}</strong>
                <small>{item.excerpt || item.date}</small>
              </div>
            </Link>
          ))}
        </div>

        <div className="landing-actions-row">
          <AppButton href="/berita" variant="ghost">
            Lihat Semua Berita
            <ArrowRight size={16} />
          </AppButton>
        </div>
      </section>

      <section className="site-container landing-open-data" aria-label="Data terbuka">
        <div className="landing-open-data__card">
          <div className="landing-open-data__icon" aria-hidden="true">
            <Database size={30} />
          </div>
          <div>
            <span className="landing-open-data__eyebrow">Open Data</span>
            <h2>{content.openData.title}</h2>
            <p>{content.openData.description}</p>
          </div>
          <div className="landing-open-data__actions">
            <AppButton href={content.openData.primaryButtonLink}>
              <FileText size={16} />
              {content.openData.primaryButtonText}
            </AppButton>
            <AppButton href={OPEN_DATA_CSV_URL} variant="ghost">
              <Download size={16} />
              {content.openData.secondaryButtonText}
            </AppButton>
          </div>
        </div>
      </section>
    </div>
  );
}

function Metric({ icon: Icon, label, value }: { icon: LucideIcon; label: string; value: string }) {
  return (
    <div className="landing-metric">
      <Icon size={18} />
      <span>{label}</span>
      <strong>{value}</strong>
    </div>
  );
}

function Fact({ label, value }: { label: string; value: string }) {
  return (
    <div className="landing-about__fact">
      <strong>{value}</strong>
      <span>{label}</span>
    </div>
  );
}
