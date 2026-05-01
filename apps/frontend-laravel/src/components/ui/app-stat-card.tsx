"use client";

import type { LocalizedText } from "@/lib/portal-data";
import { localizeText } from "@/lib/i18n";
import { useUISettings } from "@/components/providers/ui-settings-provider";

type AppStatCardProps = {
  label: LocalizedText;
  value: string;
  note: LocalizedText;
};

export function AppStatCard({ label, value, note }: AppStatCardProps) {
  const { language } = useUISettings();

  return (
    <article className="stat-card">
      <span className="stat-card__label">{localizeText(label, language)}</span>
      <strong className="stat-card__value">{value}</strong>
      <p className="stat-card__note">{localizeText(note, language)}</p>
    </article>
  );
}
