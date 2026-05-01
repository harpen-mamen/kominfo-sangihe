"use client";

import type { TableColumn, TableRow } from "@/lib/portal-data";
import { localizeText } from "@/lib/i18n";
import { useUISettings } from "@/components/providers/ui-settings-provider";

type AppTableProps = {
  columns: TableColumn[];
  rows: TableRow[];
};

export function AppTable({ columns, rows }: AppTableProps) {
  const { language } = useUISettings();

  return (
    <div className="table-shell">
      <table className="app-table">
        <thead>
          <tr>
            {columns.map((column) => (
              <th key={column.key}>{localizeText(column.label, language)}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((row, rowIndex) => (
            <tr key={`${rowIndex}-${row[columns[0]?.key] ?? "row"}`}>
              {columns.map((column) => (
                <td key={column.key}>{row[column.key]}</td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
