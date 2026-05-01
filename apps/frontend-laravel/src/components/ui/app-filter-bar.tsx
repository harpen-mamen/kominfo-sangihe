import type { ReactNode } from "react";

export function AppFilterBar({ children }: { children: ReactNode }) {
  return <div className="filter-bar">{children}</div>;
}

