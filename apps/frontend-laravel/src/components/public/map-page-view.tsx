"use client";

import { CalendarDays, Layers3, MapPinned, Newspaper } from "lucide-react";
import { AppMapContainer } from "@/components/ui/app-map-container";
import type { HeroData, MapLayerSummary, MapWorkbenchLayer } from "@/lib/portal-data";
import { PublicHeroHeading } from "@/components/public/public-hero-heading";
import { AppButton } from "@/components/ui/app-button";
import { ArrowRight } from "lucide-react";

export function MapPageView({
  hero,
  layers,
  workbenchLayers,
  initialView,
}: {
  hero: HeroData;
  layers: MapLayerSummary[];
  workbenchLayers: MapWorkbenchLayer[];
  initialView: {
    center: [number, number];
    zoom: number;
  };
}) {
  const totalFeatures = layers.reduce((sum, layer) => sum + layer.featureCount, 0);
  const newsLayer = layers.find((layer) => layer.kind === "news");
  const eventLayer = layers.find((layer) => layer.kind === "event");

  return (
    <div className="public-dashboard">
      <PublicHeroHeading
        actions={
          <>
            <AppButton href="/statistik" variant="secondary">
              Lihat Statistik
              <ArrowRight size={16} />
            </AppButton>
          </>
        }
        hero={hero}
        subtitle={{
          id: "Jelajahi batas wilayah, fasilitas publik, agenda kegiatan, dan titik informasi melalui peta interaktif.",
          en: "Explore boundaries, public facilities, events, and information points via an interactive map.",
        }}
        title={{ id: "Peta Digital Terpadu", en: "Integrated Digital Map" }}
      />

      <section className="site-container public-dashboard__section">
        <div className="public-dashboard__kpis">
          <div className="public-dashboard__kpi">
            <Layers3 size={16} />
            <span>Layer aktif</span>
            <strong>{layers.length}</strong>
          </div>
          <div className="public-dashboard__kpi">
            <MapPinned size={16} />
            <span>Fitur peta</span>
            <strong>{totalFeatures}</strong>
          </div>
          <div className="public-dashboard__kpi">
            <Newspaper size={16} />
            <span>Titik berita</span>
            <strong>{newsLayer?.featureCount ?? 0}</strong>
          </div>
          <div className="public-dashboard__kpi">
            <CalendarDays size={16} />
            <span>Agenda kegiatan</span>
            <strong>{eventLayer?.featureCount ?? 0}</strong>
          </div>
        </div>

        <div className="public-dashboard__map">
          <AppMapContainer
            className="public-dashboard__map-canvas"
            height={820}
            initialView={initialView}
            mode="workbench"
            showSearch
            workbenchLayers={workbenchLayers}
          />
        </div>
      </section>
    </div>
  );
}
