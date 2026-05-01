"use client";

import { AppCard } from "@/components/ui/app-card";
import { AppButton } from "@/components/ui/app-button";
import { AppSectionHeader } from "@/components/ui/app-section-header";
import { PageBanner } from "@/components/ui/page-banner";

export function DocumentsPageView({
  documents,
}: {
  documents: Array<{ title: string; category: string; updatedAt: string }>;
}) {
  return (
    <div className="site-container section-stack">
      <PageBanner
        description={{
          id: "Dokumen resmi, publikasi, laporan, dan ringkasan statistik yang dapat diakses publik.",
          en: "Official documents, publications, reports, and statistical summaries accessible to the public.",
        }}
        title={{ id: "Dokumen & Publikasi", en: "Documents & Publications" }}
      />

      <AppSectionHeader
        kicker={{ id: "Pusat Unduh", en: "Download Center" }}
        title={{
          id: "Dokumen prioritas dan publikasi resmi",
          en: "Priority documents and official publications",
        }}
      />

      <div className="documents-grid">
        {documents.map((item) => (
          <AppCard key={item.title}>
            <span className="eyebrow">{item.category}</span>
            <h3>{item.title}</h3>
            <p>Diperbarui {item.updatedAt}</p>
            <AppButton variant="ghost">Lihat dokumen</AppButton>
          </AppCard>
        ))}
      </div>
    </div>
  );
}

