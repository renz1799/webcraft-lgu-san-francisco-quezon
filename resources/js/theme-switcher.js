// resources/js/theme-switcher.js
(function () {
  const host = document.getElementById('hs-overlay-switcher');
  if (!host) {
    console.warn('[theme] switcher host not found');
    return;
  }

  // Helpers ----------------------------------------------------
  const parseJson = (s, fallback = {}) => {
    try { return JSON.parse(s); } catch { return fallback; }
  };
  const now = () => new Date().toISOString();

  const STYLE_URL  = host.dataset.updateStyleUrl;
  const COLORS_URL = host.dataset.updateColorsUrl;
  const CURRENT    = parseJson(host.dataset.themeStyle || '{}', {});

  const log = (msg, extra) => console.log(`[theme] ${now()} ${msg}`, extra ?? '');

  const applyStyle = (patch = {}) => {
    const html = document.documentElement;

    if (Object.prototype.hasOwnProperty.call(patch, 'mode')) {
      const dark = patch.mode === 'dark';
      html.classList.toggle('dark', dark);
      log(`apply mode -> ${dark ? 'dark' : 'light'}`);
    }
    if (Object.prototype.hasOwnProperty.call(patch, 'dir')) {
      const dir = patch.dir === 'rtl' ? 'rtl' : 'ltr';
      html.setAttribute('dir', dir);
      log(`apply dir -> ${dir}`);
    }
    if (Object.prototype.hasOwnProperty.call(patch, 'nav')) {
      const nav = (patch.nav === 'horizontal') ? 'horizontal' : 'vertical';
      html.dataset.nav = nav;
      log(`apply nav -> ${nav}`);
    }
    if (Object.prototype.hasOwnProperty.call(patch, 'menuHover')) {
      html.dataset.menuHover = patch.menuHover ? '1' : '0';
      log(`apply menuHover -> ${patch.menuHover ? 'on' : 'off'}`);
    }
  };

  const post = async (url, payload) => {
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
      });
      const json = await res.json().catch(() => ({}));
      log(`POST ${url} -> ${res.status}`, json);
      if (!res.ok) {
        console.error('[theme] server rejected update', json);
      }
      return json;
    } catch (e) {
      console.error('[theme] network error', e);
      return {};
    }
  };

  // Initial state from server ---------------------------------
  log('Initial style from server:', CURRENT);
  applyStyle(CURRENT);

  // Event handling (capture so wrappers don’t swallow it) ------
  document.addEventListener('change', async (e) => {
    const t = e.target;
    if (!(t instanceof HTMLInputElement)) return;
    if (!host.contains(t)) return; // only react to inputs inside the switcher

    // Theme mode
    if (t.name === 'theme_mode') {
      const payload = { mode: t.value }; // 'light' | 'dark'
      applyStyle(payload);
      await post(STYLE_URL, payload);
      return;
    }

    // Direction
    if (t.name === 'direction') {
      const payload = { dir: t.value }; // 'ltr' | 'rtl'
      applyStyle(payload);
      await post(STYLE_URL, payload);
      return;
    }

    // Navigation style
    if (t.name === 'nav_style') {
      const payload = { nav: t.value }; // 'vertical' | 'horizontal'
      applyStyle(payload);
      await post(STYLE_URL, payload);
      return;
    }

    // Optional: if you add a real toggle for hover someday
    if (t.name === 'menu_hover') {
      const payload = { menuHover: t.checked };
      applyStyle(payload);
      await post(STYLE_URL, payload);
      return;
    }
  }, true); // <— capture phase!

  // Admin color pickers (if you wire them)
  document.addEventListener('input', async (e) => {
    const t = e.target;
    if (!(t instanceof HTMLInputElement)) return;
    if (!host.contains(t)) return;
    if (!t.matches('[data-theme-color]')) return;

    const key = t.getAttribute('data-theme-color'); // e.g. 'primary'
    const val = t.value;
    // reflect immediately
    document.documentElement.style.setProperty(`--color-${key}`, val);
    await post(COLORS_URL, { [key]: val });
  }, true);
})();
