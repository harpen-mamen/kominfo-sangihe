const script = `
(() => {
  const storageKey = "kominfo-public-theme";
  const saved = localStorage.getItem(storageKey) || "system";
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  const resolved = saved === "system" ? (prefersDark ? "dark" : "light") : saved;
  document.documentElement.dataset.theme = resolved;
  document.documentElement.dataset.themeSource = saved;
})();
`;

export function ThemeScript() {
  return <script dangerouslySetInnerHTML={{ __html: script }} />;
}

