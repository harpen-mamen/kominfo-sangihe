"use client";

import type { NewsItem } from "@/lib/portal-data";
import { AppNewsCard } from "@/components/ui/app-news-card";
import { AppSectionHeader } from "@/components/ui/app-section-header";

type NewsDetailViewProps = {
  title: string;
  excerpt: string;
  content: string;
  category: string;
  department: string;
  date: string;
  imageUrl?: string;
  related: NewsItem[];
};

export function NewsDetailView({
  title,
  excerpt,
  content,
  category,
  department,
  date,
  imageUrl,
  related,
}: NewsDetailViewProps) {
  return (
    <div className="site-container section-stack">
      <article className="article-shell">
        <div
          aria-hidden="true"
          className="article-shell__hero"
          style={{
            backgroundImage: `linear-gradient(180deg, rgba(7, 27, 46, 0.14), rgba(7, 27, 46, 0.52)), url(${imageUrl})`,
          }}
        />
        <div className="article-shell__meta">
          <span className="category-chip">{category}</span>
          <span>{department}</span>
          <span>{date}</span>
        </div>
        <h1>{title}</h1>
        <p className="article-shell__excerpt">{excerpt}</p>
        <div className="article-shell__content">
          <p>{content}</p>
          <p>
            Halaman detail ini sudah disiapkan untuk artikel panjang, galeri,
            metadata sumber, dan artikel terkait dari CMS publik.
          </p>
        </div>
      </article>

      <section className="content-section">
        <AppSectionHeader
          kicker={{ id: "Berita Terkait", en: "Related News" }}
          title={{ id: "Artikel lain yang relevan", en: "More relevant stories" }}
        />
        <div className="news-list-grid">
          {related.map((item) => (
            <AppNewsCard item={item} key={item.slug} />
          ))}
        </div>
      </section>
    </div>
  );
}
