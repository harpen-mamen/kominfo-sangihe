"use client";

import Image from "next/image";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { LogIn, Menu, X } from "lucide-react";
import { useEffect, useState } from "react";
import { interfaceCopy, localizeText } from "@/lib/i18n";
import { navigationItems } from "@/lib/portal-data";
import { useUISettings } from "@/components/providers/ui-settings-provider";
import { usePortalSettings } from "@/components/providers/portal-settings-provider";
import { AppButton } from "@/components/ui/app-button";
import { AppLanguageSwitcher } from "@/components/ui/app-language-switcher";

const ADMIN_LOGIN_URL =
  process.env.NEXT_PUBLIC_ADMIN_BASE_URL ?? "http://localhost:8000/admin/login";

export function SiteHeader() {
  const { language } = useUISettings();
  const { settings } = usePortalSettings();
  const pathname = usePathname();
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const isHomePage = pathname === "/";

  useEffect(() => {
    const onScroll = () => setIsScrolled(window.scrollY > 72);

    onScroll();
    window.addEventListener("scroll", onScroll);

    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  return (
    <header
      className="site-header"
      data-home={isHomePage ? "true" : "false"}
      data-scrolled={isScrolled ? "true" : "false"}
    >
      <div className="site-container site-header__inner">
        <Link aria-label="Beranda portal publik" className="brand-mark" href="/">
          <div className="brand-mark__logos" aria-hidden="true">
            <span className="brand-mark__logo-badge brand-mark__logo-badge--seal">
              <Image
                alt=""
                className="brand-mark__logo-image"
                height={44}
                priority
                src={settings.logoUrl || "/logo-sangihe.png"}
                width={44}
              />
            </span>
            <span className="brand-mark__logo-badge brand-mark__logo-badge--komdigi">
              <Image
                alt=""
                className="brand-mark__logo-image brand-mark__logo-image--komdigi"
                height={30}
                priority
                src="/logo-komdigi-full.png"
                width={118}
              />
            </span>
          </div>
          <div className="brand-mark__copy">
            <span>{localizeText(interfaceCopy.brandKicker, language)}</span>
            <strong>{localizeText(interfaceCopy.brandTitle, language)}</strong>
            <small>{settings.title}</small>
          </div>
        </Link>

        <button
          aria-controls="site-navigation"
          aria-expanded={isMenuOpen}
          aria-label={isMenuOpen ? "Tutup menu navigasi" : "Buka menu navigasi"}
          className="mobile-nav-toggle"
          onClick={() => setIsMenuOpen((current) => !current)}
          type="button"
        >
          {isMenuOpen ? <X size={18} /> : <Menu size={18} />}
          <span>Menu</span>
        </button>

        <nav className="site-nav" data-open={isMenuOpen} id="site-navigation" aria-label="Navigasi publik">
          {navigationItems.map((item) => {
            const isActive = item.href === "/" ? pathname === "/" : pathname.startsWith(item.href);

            return (
              <Link
                className="site-nav__link"
                data-active={isActive}
                href={item.href}
                key={item.href}
                onClick={() => setIsMenuOpen(false)}
              >
                {localizeText(item.label, language)}
              </Link>
            );
          })}
        </nav>

        <div className="site-toolbar">
          <AppLanguageSwitcher />
          <AppButton className="header-action header-action--login" href={ADMIN_LOGIN_URL} variant="soft">
            <LogIn size={16} />
            {localizeText(interfaceCopy.login, language)}
          </AppButton>
        </div>
      </div>
    </header>
  );
}
