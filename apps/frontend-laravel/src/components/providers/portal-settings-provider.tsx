"use client";

import type { ReactNode } from "react";
import { createContext, useContext, useEffect, useMemo, useState } from "react";
import { getPortalSettingsData } from "@/lib/api";
import type { PortalSettings } from "@/lib/portal-data";

type PortalSettingsContextValue = {
  settings: PortalSettings;
  isLoading: boolean;
};

const fallbackSettings: PortalSettings = {
  title: "Portal Data Daerah Kabupaten Kepulauan Sangihe",
  logoUrl: null,
  footerDescription: null,
  contact: { address: null, email: null, phone: null },
};

const PortalSettingsContext = createContext<PortalSettingsContextValue>({
  settings: fallbackSettings,
  isLoading: true,
});

export function PortalSettingsProvider({
  children,
  initialSettings,
}: {
  children: ReactNode;
  initialSettings?: PortalSettings | null;
}) {
  const [settings, setSettings] = useState<PortalSettings>(initialSettings ?? fallbackSettings);
  const [isLoading, setIsLoading] = useState(!initialSettings);

  useEffect(() => {
    let mounted = true;

    async function load() {
      try {
        const data = await getPortalSettingsData();
        if (!mounted) return;
        setSettings(data ?? fallbackSettings);
      } finally {
        if (!mounted) return;
        setIsLoading(false);
      }
    }

    load();

    return () => {
      mounted = false;
    };
  }, []);

  const value = useMemo(() => ({ settings, isLoading }), [settings, isLoading]);

  return <PortalSettingsContext.Provider value={value}>{children}</PortalSettingsContext.Provider>;
}

export function usePortalSettings() {
  return useContext(PortalSettingsContext);
}

