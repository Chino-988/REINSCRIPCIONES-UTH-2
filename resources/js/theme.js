export function getPreferredTheme() {
  try {
    const stored = localStorage.getItem('theme');
    if (stored === 'dark' || stored === 'light') return stored;
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
      ? 'dark' : 'light';
  } catch { return 'light'; }
}

export function applyTheme(theme) {
  const e = document.documentElement;
  if (theme === 'dark') e.classList.add('dark'); else e.classList.remove('dark');
}

export function setTheme(theme) {
  try { localStorage.setItem('theme', theme); } catch {}
  applyTheme(theme);
}

// Exponer helpers globales (usados por el toggle)
window.applyTheme = applyTheme;
window.setTheme = setTheme;
window.getPreferredTheme = getPreferredTheme;

// Aplicar al cargar JS (el preload ya lo hizo en <head>, esto asegura coherencia)
applyTheme(getPreferredTheme());
