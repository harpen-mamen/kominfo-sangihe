"use client";

import Link from "next/link";
import { interfaceCopy, localizeText } from "@/lib/i18n";
import { navigationItems } from "@/lib/portal-data";
import { useUISettings } from "@/components/providers/ui-settings-provider";
import { usePortalSettings } from "@/components/providers/portal-settings-provider";
import { AppButton } from "@/components/ui/app-button";

const ADMIN_LOGIN_URL =
  process.env.NEXT_PUBLIC_ADMIN_BASE_URL ?? "http://localhost:8000/admin/login";

export function SiteFooter() {
  const { language } = useUISettings();
  const { settings } = usePortalSettings();
  const footerTags =
    language === "en"
      ? ["Statistics", "Digital Maps", "Official News", "Public Documents"]
      : ["Statistik", "Peta Digital", "Berita Resmi", "Dokumen Publik"];

  return (
    <footer className="site-footer">
      <div className="site-container site-footer__grid">
        <div className="footer-block footer-block--brand">
          <span className="eyebrow">{localizeText(interfaceCopy.footerContact, language)}</span>
          <h2>{settings.title || localizeText(interfaceCopy.portalName, language)}</h2>
          <p>
            {settings.footerDescription?.trim()
              ? settings.footerDescription
              : localizeText(interfaceCopy.footerLead, language)}
          </p>
          <div className="footer-tags">
            {footerTags.map((item) => (
              <span key={item}>{item}</span>
            ))}
          </div>
        </div>
        <div className="footer-block">
          <h3>Navigasi</h3>
          <div className="footer-links">
            {navigationItems.map((item) => (
              <Link href={item.href} key={item.href}>
                {localizeText(item.label, language)}
              </Link>
            ))}
          </div>
        </div>
        <div className="footer-block">
          <h3>{localizeText(interfaceCopy.footerContact, language)}</h3>
          <p>{settings.contact?.address || localizeText(interfaceCopy.footerAddress, language)}</p>
          <p>{settings.contact?.email || "admin@kominfo-sangihe.go.id"}</p>
          <p>{settings.contact?.phone || "(0432) 21001"}</p>
        </div>
      </div>

      <div className="site-container site-footer__bottom">
        <span>
          {language === "en"
            ? "Official public portal of the Government of Sangihe Islands Regency."
            : "Portal publik resmi Pemerintah Kabupaten Kepulauan Sangihe."}
        </span>
        <AppButton className="footer-admin-link" href={ADMIN_LOGIN_URL} variant="secondary">
          {localizeText(interfaceCopy.login, language)}
        </AppButton>
      </div>
    </footer>
  );
}
