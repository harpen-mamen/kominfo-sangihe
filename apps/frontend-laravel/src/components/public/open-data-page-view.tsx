"use client";

import { Download, FileText } from "lucide-react";
import { useMemo } from "react";
import { PageBanner } from "@/components/ui/page-banner";
import { AppCard } from "@/components/ui/app-card";
import { AppButton } from "@/components/ui/app-button";

type OpenDataPageViewProps = {
  meta: { title: string; license?: string; download_csv_url?: string };
  rows: Array<Record<string, unknown>>;
};

export function OpenDataPageView({ meta, rows }: OpenDataPageViewProps) {
  const previewRows = useMemo(() => rows.slice(0, 10), [rows]);

  return (
    <div className="site-container section-stack">
      <PageBanner
        description={{
          id: "Akses dataset agregat daerah untuk transparansi, penelitian, dan pengambilan keputusan.",
          en: "Access regional aggregated datasets for transparency, research, and decision making.",
        }}
        title={{ id: "Data Terbuka", en: "Open Data" }}
      />

      <AppCard>
        <div className="open-data-card">
          <div className="open-data-card__head">
            <FileText size={20} />
            <div>
              <strong>{meta.title}</strong>
              <small>{meta.license ?? "Data publik pemerintah daerah"}</small>
            </div>
          </div>

          <div className="open-data-card__actions">
            <AppButton href={meta.download_csv_url ?? "#"} variant="secondary">
              <Download size={16} />
              Unduh CSV
            </AppButton>
          </div>

          <div className="open-data-card__meta">
            <span>Total baris (preview): {rows.length.toString()}</span>
            <span>Preview ditampilkan 10 baris teratas.</span>
          </div>
        </div>
      </AppCard>

      <AppCard>
        <div className="open-data-table">
          <div className="open-data-table__head">
            <strong>Preview Dataset</strong>
            <small>Gunakan tombol unduh untuk data lengkap.</small>
          </div>
          <div className="open-data-table__wrap">
            <table>
              <thead>
                <tr>
                  {Object.keys(previewRows[0] ?? {}).slice(0, 8).map((key) => (
                    <th key={key}>{key}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {previewRows.map((row, index) => (
                  <tr key={String(index)}>
                    {Object.keys(previewRows[0] ?? {}).slice(0, 8).map((key) => (
                      <td key={key}>{String(row[key] ?? "")}</td>
                    ))}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </AppCard>
    </div>
  );
}

