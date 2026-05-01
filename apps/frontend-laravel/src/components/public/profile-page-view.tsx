"use client";

import Image from "next/image";
import { localizeText } from "@/lib/i18n";
import type { HeroData } from "@/lib/portal-data";
import { useUISettings } from "@/components/providers/ui-settings-provider";
import { AppCard } from "@/components/ui/app-card";
import { AppProfileCard } from "@/components/ui/app-profile-card";
import { AppSectionHeader } from "@/components/ui/app-section-header";
import { PageBanner } from "@/components/ui/page-banner";
import { PublicHeroHeading } from "@/components/public/public-hero-heading";

type ProfilePageViewProps = {
  mode: "region" | "leadership" | "department";
  title: { id: string; en: string };
  description: { id: string; en: string };
  content: string;
  hero?: HeroData;
  imageUrl?: string | null;
  departments?: string[];
};

export function ProfilePageView({
  mode,
  title,
  description,
  content,
  hero,
  imageUrl,
  departments,
}: ProfilePageViewProps) {
  const { language } = useUISettings();

  return (
    <div className="site-container section-stack">
      {hero ? (
        <PublicHeroHeading hero={hero} subtitle={description} title={title} />
      ) : (
        <PageBanner description={description} title={title} />
      )}

      <section className="content-section content-section--split">
        <AppCard className="article-card">
          <AppSectionHeader
            kicker={{ id: "Ringkasan", en: "Overview" }}
            title={title}
          />
          <div className="rich-text">
            {imageUrl ? (
              <div className="profile-hero-image" aria-hidden="true">
                <Image alt="" fill sizes="(max-width: 1080px) 100vw, 640px" src={imageUrl} />
              </div>
            ) : null}
            <p>{content}</p>
            <p>
              Portal ini dirancang untuk mendukung tata kelola data, publikasi
              informasi, dan visualisasi spasial yang konsisten dari tingkat desa
              sampai kabupaten.
            </p>
          </div>
        </AppCard>

        {mode === "department" ? (
          <AppCard muted>
            <AppSectionHeader
              kicker={{ id: "Dinas", en: "Agencies" }}
              title={{ id: "Dinas terkait", en: "Related agencies" }}
            />
            <div className="stack-grid">
              {departments?.map((department) => (
                <div className="detail-panel" key={department}>
                  <strong>{department}</strong>
                  <p>Template, statistik, berita, dan layer peta dapat dikelola dari backoffice terintegrasi.</p>
                </div>
              ))}
            </div>
          </AppCard>
        ) : null}

        {mode === "leadership" && hero ? (
          <div className="stack-grid">
            <AppProfileCard
              copy="Memimpin penguatan tata kelola data, layanan publik, dan transparansi pembangunan daerah."
              person={hero.regent}
            />
            <AppProfileCard
              copy="Mengawal sinkronisasi lintas dinas, penguatan digitalisasi, dan publikasi data terverifikasi."
              person={hero.viceRegent}
            />
          </div>
        ) : null}
      </section>

      {mode === "region" ? (
        <AppCard>
          <AppSectionHeader
            kicker={{ id: "Karakter Wilayah", en: "Regional Character" }}
            title={{
              id: "Kabupaten kepulauan yang membutuhkan layanan berbasis data spasial",
              en: "An archipelagic regency that needs spatial-data-driven services",
            }}
            description={{
              id: "Informasi geografis, infrastruktur, dan konektivitas menjadi komponen penting dalam kebijakan daerah.",
              en: "Geographic information, infrastructure, and connectivity are essential parts of regional policy.",
            }}
          />
          <div className="facts-grid">
            {[
              { label: { id: "Tema visual", en: "Visual theme" }, value: "Maritim modern" },
              { label: { id: "Prioritas portal", en: "Portal priority" }, value: "Statistik & peta digital" },
              { label: { id: "Pendekatan data", en: "Data approach" }, value: "Terverifikasi berjenjang" },
            ].map((fact) => (
              <div className="fact-pill" key={fact.label.id}>
                <span>{localizeText(fact.label, language)}</span>
                <strong>{fact.value}</strong>
              </div>
            ))}
          </div>
        </AppCard>
      ) : null}
    </div>
  );
}
