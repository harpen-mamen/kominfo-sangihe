"use client";

import { MonitorCog, MoonStar, SunMedium } from "lucide-react";
import { interfaceCopy, localizeText } from "@/lib/i18n";
import { useUISettings } from "@/components/providers/ui-settings-provider";

const items = [
  { value: "light", Icon: SunMedium },
  { value: "dark", Icon: MoonStar },
  { value: "system", Icon: MonitorCog },
] as const;

export function AppThemeSwitcher() {
  const { language, theme, setTheme } = useUISettings();

  return (
    <div
      aria-label={localizeText(interfaceCopy.themes.system, language)}
      className="switcher"
      role="group"
    >
      {items.map(({ value, Icon }) => (
        <button
          aria-label={localizeText(interfaceCopy.themes[value], language)}
          aria-pressed={theme === value}
          className="switcher__button"
          data-active={theme === value}
          key={value}
          onClick={() => setTheme(value)}
          type="button"
        >
          <Icon size={15} />
        </button>
      ))}
    </div>
  );
}

