"use client";

import {
  createContext,
  useContext,
  useEffect,
  useMemo,
  useSyncExternalStore,
  type ReactNode,
} from "react";
import type { Language, ThemeMode } from "@/lib/portal-data";

type UISettingsContextValue = {
  language: Language;
  setLanguage: (language: Language) => void;
  theme: ThemeMode;
  resolvedTheme: "light" | "dark";
  setTheme: (theme: ThemeMode) => void;
};

const UISettingsContext = createContext<UISettingsContextValue | null>(null);

const THEME_STORAGE_KEY = "kominfo-public-theme";
const LANGUAGE_STORAGE_KEY = "kominfo-public-language";
const SETTINGS_CHANGE_EVENT = "kominfo-public-settings-change";

type UISettingsSnapshot = Pick<
  UISettingsContextValue,
  "language" | "theme" | "resolvedTheme"
>;

const DEFAULT_SETTINGS: UISettingsSnapshot = {
  language: "id",
  theme: "system",
  resolvedTheme: "light",
};

let cachedSnapshot = DEFAULT_SETTINGS;

function normalizeTheme(value: string | null): ThemeMode {
  return value === "light" || value === "dark" || value === "system"
    ? value
    : "system";
}

function resolveTheme(
  theme: ThemeMode,
  mediaQuery: MediaQueryList,
): "light" | "dark" {
  return theme === "system" ? (mediaQuery.matches ? "dark" : "light") : theme;
}

function readLanguage(): Language {
  if (typeof window === "undefined") {
    return DEFAULT_SETTINGS.language;
  }

  return window.localStorage.getItem(LANGUAGE_STORAGE_KEY) === "en" ? "en" : "id";
}

function readTheme(): ThemeMode {
  if (typeof window === "undefined") {
    return DEFAULT_SETTINGS.theme;
  }

  return normalizeTheme(window.localStorage.getItem(THEME_STORAGE_KEY));
}

function getSnapshot(): UISettingsSnapshot {
  if (typeof window === "undefined") {
    return DEFAULT_SETTINGS;
  }

  const theme = readTheme();
  const nextSnapshot: UISettingsSnapshot = {
    language: readLanguage(),
    theme,
    resolvedTheme: resolveTheme(
      theme,
      window.matchMedia("(prefers-color-scheme: dark)"),
    ),
  };

  if (
    cachedSnapshot.language === nextSnapshot.language &&
    cachedSnapshot.theme === nextSnapshot.theme &&
    cachedSnapshot.resolvedTheme === nextSnapshot.resolvedTheme
  ) {
    return cachedSnapshot;
  }

  cachedSnapshot = nextSnapshot;
  return cachedSnapshot;
}

function subscribe(onStoreChange: () => void) {
  if (typeof window === "undefined") {
    return () => {};
  }

  const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");

  const handleStorage = (event: StorageEvent) => {
    if (
      event.key === null ||
      event.key === THEME_STORAGE_KEY ||
      event.key === LANGUAGE_STORAGE_KEY
    ) {
      onStoreChange();
    }
  };

  mediaQuery.addEventListener("change", onStoreChange);
  window.addEventListener("storage", handleStorage);
  window.addEventListener(SETTINGS_CHANGE_EVENT, onStoreChange);

  return () => {
    mediaQuery.removeEventListener("change", onStoreChange);
    window.removeEventListener("storage", handleStorage);
    window.removeEventListener(SETTINGS_CHANGE_EVENT, onStoreChange);
  };
}

function persistLanguage(language: Language) {
  if (typeof window === "undefined") {
    return;
  }

  window.localStorage.setItem(LANGUAGE_STORAGE_KEY, language);
  window.dispatchEvent(new Event(SETTINGS_CHANGE_EVENT));
}

function persistTheme(theme: ThemeMode) {
  if (typeof window === "undefined") {
    return;
  }

  window.localStorage.setItem(THEME_STORAGE_KEY, theme);
  window.dispatchEvent(new Event(SETTINGS_CHANGE_EVENT));
}

export function UISettingsProvider({ children }: { children: ReactNode }) {
  const { language, theme, resolvedTheme } = useSyncExternalStore(
    subscribe,
    getSnapshot,
    () => DEFAULT_SETTINGS,
  );

  useEffect(() => {
    document.documentElement.lang = language;
    document.documentElement.dataset.theme = resolvedTheme;
    document.documentElement.dataset.themeSource = theme;
  }, [language, resolvedTheme, theme]);

  const value = useMemo(
    () => ({
      language,
      setLanguage: persistLanguage,
      theme,
      resolvedTheme,
      setTheme: persistTheme,
    }),
    [language, resolvedTheme, theme],
  );

  return (
    <UISettingsContext.Provider value={value}>
      {children}
    </UISettingsContext.Provider>
  );
}

export function useUISettings() {
  const context = useContext(UISettingsContext);

  if (!context) {
    throw new Error("useUISettings must be used within UISettingsProvider");
  }

  return context;
}
