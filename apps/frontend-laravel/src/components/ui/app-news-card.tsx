"use client";

import { CalendarDays } from "lucide-react";
import type { NewsItem } from "@/lib/portal-data";
import { interfaceCopy, localizeText } from "@/lib/i18n";
import { useUISettings } from "@/components/providers/ui-settings-provider";
import { AppButton } from "@/components/ui/app-button";

export function AppNewsCard({
  item,
  featured = false,
}: {
  item: NewsItem;
  featured?: boolean;
}) {
  const { language } = useUISettings();

  return (
    <article className={`news-card ${featured ? "news-card--featured" : ""}`}>
      <div
        aria-hidden="true"
        className="news-card__media"
        style={{
          backgroundImage: `linear-gradient(180deg, rgba(9, 32, 61, 0.12), rgba(9, 32, 61, 0.48)), url(${item.imageUrl})`,
        }}
      />
      <div className="news-card__body">
        <div className="news-card__meta">
          <span className="category-chip">{item.category}</span>
          <span>
            <CalendarDays size={14} />
            {item.date}
          </span>
        </div>
        <h3>{item.title}</h3>
        <p>{item.excerpt}</p>
        <AppButton href={`/berita/${item.slug}`} variant="ghost">
          {localizeText(interfaceCopy.readMore, language)}
        </AppButton>
      </div>
    </article>
  );
}
