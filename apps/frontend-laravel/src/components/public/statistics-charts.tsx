"use client";

import { useSyncExternalStore } from "react";
import {
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  Legend,
  Line,
  LineChart,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";

type StatisticsChartsProps = {
  data: Array<{
    year: string;
    stunting: number;
    imunisasi: number;
    siswa: number;
    umkm: number;
  }>;
};

const donutColors = ["var(--chart-primary)", "var(--chart-secondary)", "var(--chart-accent)"];

export function StatisticsCharts({ data }: StatisticsChartsProps) {
  const mounted = useSyncExternalStore(
    () => () => {},
    () => true,
    () => false,
  );

  if (!mounted) {
    return <div style={{ height: 720 }} />;
  }

  const latest = data[data.length - 1];
  const donutData = [
    { name: "Kesehatan", value: latest?.stunting ?? 0 },
    { name: "Imunisasi", value: latest?.imunisasi ?? 0 },
    { name: "UMKM", value: latest?.umkm ?? 0 },
  ];

  return (
    <div className="charts-grid">
      <div className="chart-card">
        <h3>Tren layanan kesehatan</h3>
        <ResponsiveContainer height={280}>
          <LineChart data={data}>
            <CartesianGrid stroke="var(--color-border-soft)" vertical={false} />
            <XAxis axisLine={false} dataKey="year" tickLine={false} />
            <YAxis axisLine={false} tickLine={false} />
            <Tooltip />
            <Legend />
            <Line
              dataKey="stunting"
              dot={{ r: 4 }}
              stroke="var(--chart-primary)"
              strokeWidth={3}
              type="monotone"
            />
            <Line
              dataKey="imunisasi"
              dot={{ r: 4 }}
              stroke="var(--chart-secondary)"
              strokeWidth={3}
              type="monotone"
            />
          </LineChart>
        </ResponsiveContainer>
      </div>
      <div className="chart-card">
        <h3>Perbandingan pendidikan dan UMKM</h3>
        <ResponsiveContainer height={280}>
          <BarChart data={data}>
            <CartesianGrid stroke="var(--color-border-soft)" vertical={false} />
            <XAxis axisLine={false} dataKey="year" tickLine={false} />
            <YAxis axisLine={false} tickLine={false} />
            <Tooltip />
            <Legend />
            <Bar dataKey="siswa" fill="var(--chart-primary)" radius={[12, 12, 0, 0]} />
            <Bar dataKey="umkm" fill="var(--chart-secondary)" radius={[12, 12, 0, 0]} />
          </BarChart>
        </ResponsiveContainer>
      </div>
      <div className="chart-card chart-card--narrow">
        <h3>Komposisi indikator prioritas</h3>
        <ResponsiveContainer height={280}>
          <PieChart>
            <Pie
              cx="50%"
              cy="50%"
              data={donutData}
              dataKey="value"
              innerRadius={70}
              outerRadius={105}
              paddingAngle={4}
            >
              {donutData.map((entry, index) => (
                <Cell fill={donutColors[index % donutColors.length]} key={entry.name} />
              ))}
            </Pie>
            <Tooltip />
            <Legend />
          </PieChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}
