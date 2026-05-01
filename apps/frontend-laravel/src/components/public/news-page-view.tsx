"use client";

import type { NewsItem } from "@/lib/portal-data";
import { AppButton } from "@/components/ui/app-button";
import { AppNewsCard } from "@/components/ui/app-news-card";
import { PageBanner } from "@/components/ui/page-banner";

export function NewsPageView({ news }: { news: NewsItem[] }) {
  return (
    <div className="site-container section-stack">
      <PageBanner
        description={{
          id: "Berita resmi kabupaten dan dinas dengan featured story, grid berita, kategori, serta artikel detail yang mudah dibaca.",
          en: "Official regency and agency news with a featured story, article grid, categories, and readable detail pages.",
        }}
        title={{ id: "Berita", en: "News" }}
      />

      <section className="news-page-grid">
        <AppNewsCard featured item={news[0]} />
        <div className="stack-grid">
          {news.slice(1, 4).map((item) => (
            <AppNewsCard item={item} key={item.slug} />
          ))}
        </div>
      </section>

      <div className="news-list-grid">
        {news.map((item) => (
          <AppNewsCard item={item} key={item.slug} />
        ))}
      </div>

      <div className="centered-row">
        <AppButton href="/pencarian?category=berita" variant="ghost">
          Cari berita lainnya
        </AppButton>
      </div>
    </div>
  );
}

