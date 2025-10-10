// resources/js/theme-switcher.js
(function () {
  const host = document.getElementById('hs-overlay-switcher');
  if (!host) {
    console.warn('[theme] switcher host not found');
    return;
  }

  // ---------------- helpers ----------------
  const parseJson = (s, fallback = {}) => { try { return JSON.parse(s); } catch { return fallback; } };
  const now = () => new Date().toISOString();
  const log = (msg, extra) => console.log(`[theme] ${now()} ${msg}`, extra ?? '');

  const STYLE_URL  = host.dataset.updateStyleUrl || '';
  const COLORS_URL = host.dataset.updateColorsUrl || '';

  if (!STYLE_URL)  console.warn('[theme] missing data-update-style-url on switcher host');
  if (!COLORS_URL) console.warn('[theme] missing data-update-colors-url on switcher host');

  const DEFAULTS = { mode: 'light', dir: 'ltr', nav: 'vertical', menuStyle: 'menu-click' };

  const RAW = parseJson(host.dataset.themeStyle || '{}', {});
  // compat: legacy menuHover -> menuStyle
  if (!RAW.menuStyle && Object.prototype.hasOwnProperty.call(RAW, 'menuHover')) {
    RAW.menuStyle = RAW.menuHover ? 'menu-hover' : 'menu-click';
  }

  let state = { ...DEFAULTS, ...RAW };

  // ---------------- apply to <html> ----------------
  const applyStyle = (patch = {}) => {
    const html = document.documentElement;

    if (Object.prototype.hasOwnProperty.call(patch, 'mode')) {
      const dark = patch.mode === 'dark';
      html.classList.toggle('dark', dark);
      html.classList.toggle('light', !dark);
      // keep optional vendor attributes in sync if your CSS reads them
      html.setAttribute('data-header-styles', dark ? 'dark' : 'light');
      log(`apply mode -> ${dark ? 'dark' : 'light'}`);
    }

    if (Object.prototype.hasOwnProperty.call(patch, 'dir')) {
      const dir = patch.dir === 'rtl' ? 'rtl' : 'ltr';
      html.setAttribute('dir', dir);
      log(`apply dir -> ${dir}`);
    }

    if (Object.prototype.hasOwnProperty.call(patch, 'nav')) {
      const nav = (patch.nav === 'horizontal') ? 'horizontal' : 'vertical';
      html.setAttribute('data-nav-layout', nav);
      // legacy fallback if something reads dataset.nav
      html.dataset.nav = nav;
      log(`apply nav -> ${nav}`);
    }

    if (Object.prototype.hasOwnProperty.call(patch, 'menuStyle')) {
      const v = String(patch.menuStyle); // 'menu-click' | 'menu-hover' | 'icon-click' | 'icon-hover'
      html.setAttribute('data-menu-style', v);
      // derive flags for any legacy CSS/JS
      html.dataset.menuHover = v.endsWith('hover') ? '1' : '0';
      html.dataset.menuKind  = v.startsWith('icon') ? 'icon' : 'menu';
      log(`apply menuStyle -> ${v} (hover=${html.dataset.menuHover}, kind=${html.dataset.menuKind})`);
    }

    if (Object.prototype.hasOwnProperty.call(patch, 'sideMenuLayout')) {
  const v = String(patch.sideMenuLayout || 'default'); // default|closed|icontext|icon-overlay|detached|doublemenu
  if (v === 'default') {
    // important: remove the attribute so vendor won't wipe data-nav-style
    document.documentElement.removeAttribute('data-vertical-style');
    // localStorage for vendor
    localStorage.removeItem('ynexverticalstyles');
  } else {
    document.documentElement.setAttribute('data-vertical-style', v);
    // per vendor behavior, nav styles don't apply here; they’ll clear it in L()
    // keeping localStorage in sync prevents vendor switcher from fighting us
    localStorage.setItem('ynexverticalstyles', v);
  }
  log(`apply sideMenuLayout -> ${v}`);
}
  };

  const post = async (url, payload) => {
    if (!url) return {};
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload),
      });
      const json = await res.json().catch(() => ({}));
      log(`POST ${url} -> ${res.status}`, json);
      if (!res.ok) console.error('[theme] server rejected update', json);
      return json;
    } catch (e) {
      console.error('[theme] network error', e);
      return {};
    }
  };

  const same = (k, v) => state[k] === v;

  const applyAndSave = async (patch) => {
    // skip no-op updates
    const changed = Object.keys(patch).some(k => !same(k, patch[k]));
    if (!changed) return;

    applyStyle(patch);
    state = { ...state, ...patch };
    await post(STYLE_URL, patch);
  };

  // ---------------- initial paint ----------------
  log('Initial style from server:', state);
  applyStyle(state);

  // sync radios (useful if markup wasn't server-checked)
  const check = (name, value) => {
    host.querySelectorAll(`input[name="${name}"]`).forEach(i => { i.checked = (i.value === value); });
  };
  check('theme_mode',  state.mode);
  check('direction',   state.dir);
  check('nav_style',   state.nav);
  check('menu_style',  state.menuStyle);

  // ---------------- listeners ----------------
document.addEventListener('change', async (e) => {
  const t = e.target;
  if (!(t instanceof HTMLInputElement)) return;
  if (!host.contains(t)) return;

  if (t.name === 'theme_mode')   return applyAndSave({ mode: t.value });
  if (t.name === 'direction')    return applyAndSave({ dir: t.value });
  if (t.name === 'nav_style')    return applyAndSave({ nav: t.value });
  if (t.name === 'menu_style')   return applyAndSave({ menuStyle: t.value });

  // ✅ NEW: vertical-only Sidemenu Layout Styles
  if (t.name === 'sidemenu_layout') {
    // only act when layout is vertical (per design)
    const html = document.documentElement;
    if ((html.getAttribute('data-nav-layout') || 'vertical') !== 'vertical') return;
    return applyAndSave({ sideMenuLayout: t.value }); // default|closed|icontext|icon-overlay|detached|doublemenu
  }
}, true);

  // admin color pickers (use inputs with data-theme-color="primary|success|warning|danger")
  document.addEventListener('input', async (e) => {
    const t = e.target;
    if (!(t instanceof HTMLInputElement)) return;
    if (!host.contains(t)) return;
    if (!t.matches('[data-theme-color]')) return;

    const key = t.getAttribute('data-theme-color'); // e.g., 'primary'
    const val = t.value;
    document.documentElement.style.setProperty(`--color-${key}`, val);
    await post(COLORS_URL, { [key]: val });
  }, true);

  // optional: expose for debugging
  window.__themeDebug = { get state(){ return { ...state }; }, applyStyle, applyAndSave };
})();
