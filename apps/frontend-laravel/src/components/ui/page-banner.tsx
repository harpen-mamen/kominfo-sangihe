"use client";

import type { LocalizedText } from "@/lib/portal-data";
import { localizeText } from "@/lib/i18n";
import { useUISettings } from "@/components/providers/ui-settings-provider";

export function PageBanner({
  title,
  description,
}: {
  title: LocalizedText;
  description: LocalizedText;
}) {
  const { language } = useUISettings();

  return (
    <section className="page-banner">
      <div className="page-banner__content">
        <span className="eyebrow">Portal Publik Sangihe</span>
        <h1>{localizeText(title, language)}</h1>
        <p>{localizeText(description, language)}</p>
      </div>
      <div aria-hidden="true" className="page-banner__accent">
        <span />
        <span />
      </div>
    </section>
  );
}
