"use client";

import { useState } from "react";
import { interfaceCopy, localizeText } from "@/lib/i18n";
import { useUISettings } from "@/components/providers/ui-settings-provider";
import { AppCard } from "@/components/ui/app-card";
import { AppButton } from "@/components/ui/app-button";
import { AppNewsCard } from "@/components/ui/app-news-card";
import { PageBanner } from "@/components/ui/page-banner";

type SearchPageViewProps = {
  keyword: string;
  news: Array<{
    slug: string;
    title: string;
    excerpt: string;
    category: string;
    date: string;
  }>;
  pages: string[];
  departments: string[];
};

export function SearchPageView({
  keyword,
  news,
  pages,
  departments,
}: SearchPageViewProps) {
  const { language } = useUISettings();
  const [value, setValue] = useState(keyword);

  return (
    <div className="site-container section-stack">
      <PageBanner
        description={{
          id: "Cari berita, halaman, dokumen, dan informasi sektoral dari seluruh portal publik.",
          en: "Search news, pages, documents, and sectoral information across the public portal.",
        }}
        title={{ id: "Pencarian", en: "Search" }}
      />

      <form action="/pencarian" className="search-bar">
        <input
          name="q"
          onChange={(event) => setValue(event.target.value)}
          placeholder={localizeText(interfaceCopy.footerSearchPlaceholder, language)}
          type="search"
          value={value}
        />
        <AppButton type="submit">Cari</AppButton>
      </form>

      <section className="content-section">
        <AppCard muted>
          <strong>Kata kunci</strong>
          <p>{keyword || "Belum ada kata kunci. Coba cari topik seperti statistik, blank spot, atau dokumen."}</p>
        </AppCard>
      </section>

      <section className="content-section">
        <div className="search-grid">
          <AppCard>
            <h3>Hasil berita</h3>
            <div className="stack-grid">
              {news.length ? (
                news.map((item) => <AppNewsCard item={item} key={item.slug} />)
              ) : (
                <p>{localizeText(interfaceCopy.noResults, language)}</p>
              )}
            </div>
          </AppCard>
          <AppCard>
            <h3>Halaman terkait</h3>
            <div className="stack-grid">
              {pages.map((page) => (
                <div className="detail-panel" key={page}>
                  <strong>{page}</strong>
                </div>
              ))}
            </div>
          </AppCard>
          <AppCard>
            <h3>Dinas terkait</h3>
            <div className="stack-grid">
              {departments.map((department) => (
                <div className="detail-panel" key={department}>
                  <strong>{department}</strong>
                </div>
              ))}
            </div>
          </AppCard>
        </div>
      </section>
    </div>
  );
}
