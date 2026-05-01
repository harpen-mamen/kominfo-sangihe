"use client";

import Image from "next/image";
import type { ReactNode } from "react";
import type { HeroData, LocalizedText } from "@/lib/portal-data";
import { localizeText } from "@/lib/i18n";
import { useUISettings } from "@/components/providers/ui-settings-provider";

export function PublicHeroHeading({
  hero,
  title,
  subtitle,
  actions,
}: {
  hero: HeroData;
  title: LocalizedText;
  subtitle?: LocalizedText;
  actions?: ReactNode;
}) {
  const { language } = useUISettings();
  const heroImage = hero.backgroundImageUrls[0] ?? "/sangihe-coast.png";

  return (
    <section className="public-hero-heading">
      <div className="public-hero-heading__media" aria-hidden="true">
        {hero.backgroundType === "video" && hero.backgroundVideoUrl ? (
          <video
            aria-hidden="true"
            autoPlay
            loop
            muted
            playsInline
            poster={hero.backgroundVideoPosterUrl ?? undefined}
            preload="metadata"
          >
            <source src={hero.backgroundVideoUrl} type="video/mp4" />
          </video>
        ) : (
          <Image alt="" fill priority sizes="100vw" src={heroImage} />
        )}
      </div>
      <div className="public-hero-heading__overlay" aria-hidden="true" />
      <div className="site-container public-hero-heading__inner">
        <h1>{localizeText(title, language)}</h1>
        {subtitle ? <p>{localizeText(subtitle, language)}</p> : null}
        {actions ? <div className="public-hero-heading__actions">{actions}</div> : null}
      </div>
    </section>
  );
}

