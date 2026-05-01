"use client";

import type { LocalizedText } from "@/lib/portal-data";
import { localizeText } from "@/lib/i18n";
import { useUISettings } from "@/components/providers/ui-settings-provider";

type AppSectionHeaderProps = {
  kicker?: LocalizedText;
  title: LocalizedText;
  description?: LocalizedText;
  action?: React.ReactNode;
};

export function AppSectionHeader({
  kicker,
  title,
  description,
  action,
}: AppSectionHeaderProps) {
  const { language } = useUISettings();

  return (
    <div className="section-header">
      <div className="section-header__copy">
        {kicker ? (
          <span className="eyebrow">{localizeText(kicker, language)}</span>
        ) : null}
        <h2>{localizeText(title, language)}</h2>
        {description ? (
          <p>{localizeText(description, language)}</p>
        ) : null}
      </div>
      {action ? <div className="section-header__action">{action}</div> : null}
    </div>
  );
}
