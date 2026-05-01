"use client";

import { Download } from "lucide-react";
import { statisticsTableColumns, type HeroData, type TableRow } from "@/lib/portal-data";
import { StatisticsCharts } from "@/components/public/statistics-charts";
import { AppButton } from "@/components/ui/app-button";
import { AppCard } from "@/components/ui/app-card";
import { AppSectionHeader } from "@/components/ui/app-section-header";
import { AppStatCard } from "@/components/ui/app-stat-card";
import { AppTable } from "@/components/ui/app-table";
import { PublicHeroHeading } from "@/components/public/public-hero-heading";

type StatisticsPageViewProps = {
  hero: HeroData;
  kpis: Array<{ label: { id: string; en: string }; value: string; note: { id: string; en: string } }>;
  chartData: Array<{
    year: string;
    stunting: number;
    imunisasi: number;
    siswa: number;
    umkm: number;
  }>;
  tableRows: TableRow[];
};

export function StatisticsPageView({
  hero,
  kpis,
  chartData,
  tableRows,
}: StatisticsPageViewProps) {
  const downloadUrl =
    `${process.env.NEXT_PUBLIC_API_BASE_URL ?? "http://localhost:8000/api"}/public/open-data.csv`;

  return (
    <div className="public-dashboard">
      <PublicHeroHeading
        actions={
          <AppButton href={downloadUrl} variant="secondary">
            <Download size={16} />
            Unduh Data (CSV)
          </AppButton>
        }
        hero={hero}
        subtitle={{
          id: "Ringkasan indikator pembangunan dan layanan daerah. Data bersumber dari publikasi dashboard admin.",
          en: "Summary of development and public service indicators. Data is published from the admin dashboard.",
        }}
        title={{ id: "Statistik Daerah", en: "Regional Statistics" }}
      />

      <section className="site-container public-dashboard__section">
        <div className="public-dashboard__cards">
          <div className="stats-grid">
            {kpis.map((item) => (
              <AppStatCard
                key={`${item.label.id}-${item.value}`}
                label={item.label}
                note={item.note}
                value={item.value}
              />
            ))}
          </div>

          <AppCard>
            <AppSectionHeader
              kicker={{ id: "Visualisasi", en: "Visualization" }}
              title={{
                id: "Grafik tren dan ringkasan",
                en: "Trend charts and summary",
              }}
            />
            <StatisticsCharts data={chartData} />
          </AppCard>

          <AppCard>
            <AppSectionHeader
              kicker={{ id: "Tabel Detail", en: "Detailed Table" }}
              title={{
                id: "Data rinci indikator",
                en: "Detailed indicator data",
              }}
            />
            <AppTable columns={statisticsTableColumns} rows={tableRows} />
          </AppCard>
        </div>
      </section>
    </div>
  );
}
