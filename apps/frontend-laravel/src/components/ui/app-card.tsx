import type { ReactNode } from "react";
import { cn } from "@/lib/utils";

type AppCardProps = {
  children: ReactNode;
  className?: string;
  muted?: boolean;
};

export function AppCard({ children, className, muted = false }: AppCardProps) {
  return (
    <article
      className={cn("app-card", muted && "app-card--muted", className)}
    >
      {children}
    </article>
  );
}
