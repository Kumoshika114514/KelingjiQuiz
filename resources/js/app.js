import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

(async function () {
  function applyPreferences(theme = 'light', font = 'md') {
    // Toggle dark class on <html>
    if (theme === 'dark') document.documentElement.classList.add('dark');
    else document.documentElement.classList.remove('dark');

    // Apply text-size classes on <body>
    document.body.classList.remove('text-sm', 'text-base', 'text-lg');
    if (font === 'sm') document.body.classList.add('text-sm');
    else if (font === 'lg') document.body.classList.add('text-lg');
    else document.body.classList.add('text-base'); // md => text-base
  }

  // Helper to read cookie 
  function getCookie(name) {
    const m = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return m ? decodeURIComponent(m[2]) : null;
  }

  // Try to get preferences from session endpoint
  try {
    const res = await fetch('/user-preferences', { credentials: 'same-origin' });
    if (res.ok) {
      const json = await res.json();
      const theme = (json && json.theme) ? json.theme : null;
      const font  = (json && json.font_size) ? json.font_size : null;

      if (theme || font) {
        applyPreferences(theme ?? 'light', font ?? 'md');
        return; // done
      }
    }
  } catch (err) {
    // fail silently and try cookie fallback
  }

  // Fallback to cookies 
  const cookieTheme = getCookie('theme');
  const cookieFont  = getCookie('font_size');

  if (cookieTheme || cookieFont) {
    applyPreferences(cookieTheme ?? 'light', cookieFont ?? 'md');
  }
})();

document.addEventListener('DOMContentLoaded', function() {
  // only wire toggle and sync icons; do not override server-initialized class
  function syncIcons(){ /* ... */ }
  document.getElementById('theme-toggle')?.addEventListener('click', ()=> {
     const newTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
     window.setAppTheme?.(newTheme);
  });
  syncIcons();
});