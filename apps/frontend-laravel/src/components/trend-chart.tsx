"use client";

import { useSyncExternalStore } from "react";
import {
  Bar,
  BarChart,
  CartesianGrid,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";

type TrendChartProps = {
  mode: "bar" | "line";
  data: Array<Record<string, number | string>>;
  firstKey: string;
  secondKey?: string;
};

export function TrendChart({
  mode,
  data,
  firstKey,
  secondKey,
}: TrendChartProps) {
  const mounted = useSyncExternalStore(
    () => () => {},
    () => true,
    () => false,
  );

  if (!mounted) {
    return <div style={{ width: "100%", height: 280 }} />;
  }

  const shared = {
    data,
    margin: { top: 12, right: 12, left: -18, bottom: 0 },
  };

  return (
    <div style={{ width: "100%", height: 280 }}>
      <ResponsiveContainer>
        {mode === "line" ? (
          <LineChart {...shared}>
            <CartesianGrid stroke="rgba(17,51,54,0.08)" vertical={false} />
            <XAxis dataKey="year" stroke="#537475" tickLine={false} axisLine={false} />
            <YAxis stroke="#537475" tickLine={false} axisLine={false} />
            <Tooltip />
            <Line type="monotone" dataKey={firstKey} stroke="#0f766e" strokeWidth={3} dot={{ r: 4 }} />
            {secondKey ? (
              <Line type="monotone" dataKey={secondKey} stroke="#e9782f" strokeWidth={3} dot={{ r: 4 }} />
            ) : null}
          </LineChart>
        ) : (
          <BarChart {...shared}>
            <CartesianGrid stroke="rgba(17,51,54,0.08)" vertical={false} />
            <XAxis dataKey="year" stroke="#537475" tickLine={false} axisLine={false} />
            <YAxis stroke="#537475" tickLine={false} axisLine={false} />
            <Tooltip />
            <Bar dataKey={firstKey} fill="#0f766e" radius={[8, 8, 0, 0]} />
            {secondKey ? <Bar dataKey={secondKey} fill="#e9782f" radius={[8, 8, 0, 0]} /> : null}
          </BarChart>
        )}
      </ResponsiveContainer>
    </div>
  );
}
