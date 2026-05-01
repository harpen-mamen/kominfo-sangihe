"use client";

import { Mail, MapPin, Phone } from "lucide-react";
import { AppCard } from "@/components/ui/app-card";
import { AppSectionHeader } from "@/components/ui/app-section-header";
import { PageBanner } from "@/components/ui/page-banner";

export function ContactPageView({ title, content }: { title: string; content: string }) {
  return (
    <div className="site-container section-stack">
      <PageBanner
        description={{
          id: "Saluran kontak resmi, alamat kantor, dan informasi pelayanan Kominfo Kabupaten Kepulauan Sangihe.",
          en: "Official contact channels, office address, and service information of the Communications Office.",
        }}
        title={{ id: "Kontak", en: "Contact" }}
      />

      <section className="content-section content-section--split">
        <AppCard className="article-card">
          <AppSectionHeader
            kicker={{ id: "Kontak Resmi", en: "Official Contact" }}
            title={{ id: title, en: title }}
          />
          <div className="rich-text">
            <p>{content}</p>
          </div>
        </AppCard>

        <div className="stack-grid">
          <AppCard>
            <div className="feature-row">
              <div className="feature-row__icon">
                <MapPin size={18} />
              </div>
              <div>
                <strong>Alamat</strong>
                <p>Jl. A. Yani Tahuna, Kabupaten Kepulauan Sangihe</p>
              </div>
            </div>
          </AppCard>
          <AppCard>
            <div className="feature-row">
              <div className="feature-row__icon">
                <Mail size={18} />
              </div>
              <div>
                <strong>Email</strong>
                <p>admin@kominfo-sangihe.go.id</p>
              </div>
            </div>
          </AppCard>
          <AppCard>
            <div className="feature-row">
              <div className="feature-row__icon">
                <Phone size={18} />
              </div>
              <div>
                <strong>Telepon</strong>
                <p>(0432) 21001</p>
              </div>
            </div>
          </AppCard>
        </div>
      </section>
    </div>
  );
}

