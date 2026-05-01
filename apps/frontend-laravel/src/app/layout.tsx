import type { Metadata } from "next";
import type { ReactNode } from "react";
import { SiteShell } from "@/components/layout/site-shell";
import { PortalSettingsProvider } from "@/components/providers/portal-settings-provider";
import { ThemeScript } from "@/components/providers/theme-script";
import { UISettingsProvider } from "@/components/providers/ui-settings-provider";
import { getPortalSettingsData } from "@/lib/api";
import "./globals.css";

export const metadata: Metadata = {
  title: "Sistem Informasi Statistik & Peta Digital Terpadu | Kabupaten Kepulauan Sangihe",
  description:
    "Portal resmi Pemerintah Kabupaten Kepulauan Sangihe untuk statistik daerah, peta digital, berita, dokumen publik, dan informasi umum.",
};

export default async function RootLayout({
  children,
}: Readonly<{
  children: ReactNode;
}>) {
  const portalSettings = await getPortalSettingsData();

  return (
    <html lang="id" suppressHydrationWarning>
      <body>
        <ThemeScript />
        <UISettingsProvider>
          <PortalSettingsProvider initialSettings={portalSettings}>
            <SiteShell>{children}</SiteShell>
          </PortalSettingsProvider>
        </UISettingsProvider>
      </body>
    </html>
  );
}
