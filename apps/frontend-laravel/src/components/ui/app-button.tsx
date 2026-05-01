import type { ReactNode } from "react";
import Link from "next/link";
import { cn } from "@/lib/utils";

type AppButtonProps = {
  children: ReactNode;
  href?: string;
  variant?: "primary" | "secondary" | "ghost" | "soft";
  className?: string;
  type?: "button" | "submit" | "reset";
  onClick?: () => void;
};

export function AppButton({
  children,
  href,
  variant = "primary",
  className,
  type = "button",
  onClick,
}: AppButtonProps) {
  const classes = cn("app-button", `app-button--${variant}`, className);

  if (href) {
    return (
      <Link className={classes} href={href}>
        {children}
      </Link>
    );
  }

  return (
    <button className={classes} onClick={onClick} type={type}>
      {children}
    </button>
  );
}
