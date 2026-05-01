"use client";

import { faqItems, infoSections } from "@/lib/portal-data";
import { localizeText } from "@/lib/i18n";
import { useUISettings } from "@/components/providers/ui-settings-provider";
import { AppCard } from "@/components/ui/app-card";
import { AppSectionHeader } from "@/components/ui/app-section-header";
import { PageBanner } from "@/components/ui/page-banner";

export function InfoPageView({
  title,
  content,
}: {
  title: string;
  content: string;
}) {
  const { language } = useUISettings();

  return (
    <div className="site-container section-stack">
      <PageBanner
        description={{
          id: "Pengumuman, agenda, FAQ, layanan informasi, dan pusat dokumen publik dalam satu halaman resmi.",
          en: "Announcements, agenda, FAQ, information services, and public document center in one official page.",
        }}
        title={{ id: "Informasi Umum", en: "Public Information" }}
      />

      <AppCard className="article-card">
        <AppSectionHeader
          kicker={{ id: "Informasi Umum", en: "Public Information" }}
          title={{ id: title, en: title }}
        />
        <div className="rich-text">
          <p>{content}</p>
        </div>
      </AppCard>

      <div className="info-grid">
        {infoSections.map((section) => (
          <AppCard key={section.title.id}>
            <strong>{localizeText(section.title, language)}</strong>
            <p>{localizeText(section.description, language)}</p>
          </AppCard>
        ))}
      </div>

      <AppCard>
        <AppSectionHeader
          kicker={{ id: "FAQ", en: "FAQ" }}
          title={{ id: "Pertanyaan yang sering muncul", en: "Frequently asked questions" }}
        />
        <div className="stack-grid">
          {faqItems.map((item) => (
            <div className="detail-panel" key={item.question.id}>
              <strong>{localizeText(item.question, language)}</strong>
              <p>{localizeText(item.answer, language)}</p>
            </div>
          ))}
        </div>
      </AppCard>
    </div>
  );
}

