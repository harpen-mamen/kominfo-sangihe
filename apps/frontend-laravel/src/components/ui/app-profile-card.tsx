"use client";

import type { HeroPerson } from "@/lib/portal-data";
import { localizeText } from "@/lib/i18n";
import { useUISettings } from "@/components/providers/ui-settings-provider";

export function AppProfileCard({
  person,
  copy,
}: {
  person: HeroPerson;
  copy: string;
}) {
  const { language } = useUISettings();

  return (
    <article className="profile-card">
      <div
        aria-hidden="true"
        className="profile-card__photo"
        style={{ backgroundImage: `url(${person.imageUrl})` }}
      />
      <div className="profile-card__body">
        <span className="eyebrow">{localizeText(person.title, language)}</span>
        <h3>{person.name}</h3>
        <p>{copy}</p>
      </div>
    </article>
  );
}
