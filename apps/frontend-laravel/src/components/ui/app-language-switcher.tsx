"use client";

import { interfaceCopy, localizeText } from "@/lib/i18n";
import { useUISettings } from "@/components/providers/ui-settings-provider";

export function AppLanguageSwitcher() {
  const { language, setLanguage } = useUISettings();

  return (
    <div
      aria-label={localizeText(interfaceCopy.languages.id, language)}
      className="switcher"
      role="group"
    >
      {(["id", "en"] as const).map((item) => (
        <button
          aria-pressed={language === item}
          className="switcher__button"
          data-active={language === item}
          key={item}
          onClick={() => setLanguage(item)}
          type="button"
        >
          {item.toUpperCase()}
        </button>
      ))}
    </div>
  );
}

