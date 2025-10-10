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

  const DEFAULTS = {
    mode: 'light',
    dir: 'ltr',
    nav: 'vertical',
    menuStyle: 'menu-click',
    sideMenuLayout: 'default',
    pageStyle: 'regular',
    width: 'fullwidth',           // fullwidth | boxed
    menuPosition: 'fixed',        // fixed | scrollable
    headerPosition: 'fixed',      // fixed | scrollable
    loader: 'enable',             // enable | disable
  };

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
      log(`apply nav -> ${nav}`);
    }

    if (Object.prototype.hasOwnProperty.call(patch, 'menuStyle')) {
      const v = String(patch.menuStyle); // 'menu-click' | 'menu-hover' | 'icon-click' | 'icon-hover'
      // ✅ vendor expects this exact attribute name:
      html.setAttribute('data-nav-style', v);
      // handy hints for any custom CSS/JS you might have:
      html.dataset.menuHover = v.endsWith('hover') ? '1' : '0';
      html.dataset.menuKind  = v.startsWith('icon') ? 'icon' : 'menu';
      log(`apply menuStyle -> ${v}`);
    }

    if (Object.prototype.hasOwnProperty.call(patch, 'sideMenuLayout')) {
      const v = String(patch.sideMenuLayout || 'default'); // default|closed|icontext|icon-overlay|detached|doublemenu
      if (v === 'default') {
        html.removeAttribute('data-vertical-style');
        localStorage.removeItem('ynexverticalstyles');
      } else {
        html.setAttribute('data-vertical-style', v);
        localStorage.setItem('ynexverticalstyles', v);
      }
      log(`apply sideMenuLayout -> ${v}`);
    }

    if (Object.prototype.hasOwnProperty.call(patch, 'pageStyle')) {
      const v = String(patch.pageStyle); // regular|classic|modern
      html.setAttribute('data-page-style', v);
      log(`apply pageStyle -> ${v}`);
    }

    if (Object.prototype.hasOwnProperty.call(patch, 'width')) {
      const v = (patch.width === 'boxed') ? 'boxed' : 'fullwidth';
      html.setAttribute('data-width', v);
      if (v === 'boxed') {
        localStorage.setItem('ynexboxed', 'true');
        localStorage.removeItem('ynexfullwidth');
      } else {
        localStorage.setItem('ynexfullwidth', 'true');
        localStorage.removeItem('ynexboxed');
      }
      log(`apply width -> ${v}`);
    }

    if (Object.prototype.hasOwnProperty.call(patch, 'menuPosition')) {
      const v = (patch.menuPosition === 'scrollable') ? 'scrollable' : 'fixed';
      html.setAttribute('data-menu-position', v);
      if (v === 'scrollable') {
        localStorage.setItem('ynexmenuscrollable', 'true');
        localStorage.removeItem('ynexmenufixed');
      } else {
        localStorage.setItem('ynexmenufixed', 'true');
        localStorage.removeItem('ynexmenuscrollable');
      }
      log(`apply menuPosition -> ${v}`);
    }

    if (Object.prototype.hasOwnProperty.call(patch, 'headerPosition')) {
      const v = (patch.headerPosition === 'scrollable') ? 'scrollable' : 'fixed';
      html.setAttribute('data-header-position', v);
      if (v === 'scrollable') {
        localStorage.setItem('ynexheaderscrollable', 'true');
        localStorage.removeItem('ynexheaderfixed');
      } else {
        localStorage.setItem('ynexheaderfixed', 'true');
        localStorage.removeItem('ynexheaderscrollable');
      }
      log(`apply headerPosition -> ${v}`);
    }

    if (Object.prototype.hasOwnProperty.call(patch, 'loader')) {
      const v = (patch.loader === 'disable') ? 'disable' : 'enable';
      html.setAttribute('loader', v);
      localStorage.setItem('loaderEnable', v === 'disable' ? 'false' : 'true');
      const loaderEl = document.getElementById('loader');
      if (loaderEl) loaderEl.style.display = (v === 'disable') ? 'none' : '';
      log(`apply loader -> ${v}`);
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

  // sync radios on load (in case the server didn't mark them checked)
  const check = (name, value) => {
    host.querySelectorAll(`input[name="${name}"]`).forEach(i => { i.checked = (i.value === String(value)); });
  };
  check('theme_mode',  state.mode);
  check('direction',   state.dir);
  check('nav_style',   state.nav);
  check('menu_style',  state.menuStyle);
  check('sidemenu-layout-styles', state.sideMenuLayout);
  check('data-page-styles', state.pageStyle);
  check('layout-width', state.width);
  check('data-menu-positions', state.menuPosition);
  check('data-header-positions', state.headerPosition);
  check('page-loader', state.loader);

  // ---------------- listeners ----------------
  document.addEventListener('change', async (e) => {
    const t = e.target;
    if (!(t instanceof HTMLInputElement)) return;
    if (!host.contains(t)) return;

    if (t.name === 'theme_mode')               return applyAndSave({ mode: t.value });
    if (t.name === 'direction')                return applyAndSave({ dir: t.value });
    if (t.name === 'nav_style')                return applyAndSave({ nav: t.value });
    if (t.name === 'menu_style')               return applyAndSave({ menuStyle: t.value });

    // vertical-only: sidemenu layout styles
    if (t.name === 'sidemenu-layout-styles') {
      const html = document.documentElement;
      if ((html.getAttribute('data-nav-layout') || 'vertical') !== 'vertical') return;
      return applyAndSave({ sideMenuLayout: t.value }); // default|closed|icontext|icon-overlay|detached|doublemenu
    }

    if (t.name === 'data-page-styles')         return applyAndSave({ pageStyle: t.value });            // regular|classic|modern
    if (t.name === 'layout-width')             return applyAndSave({ width: t.value });               // fullwidth|boxed
    if (t.name === 'data-menu-positions')      return applyAndSave({ menuPosition: t.value });        // fixed|scrollable
    if (t.name === 'data-header-positions')    return applyAndSave({ headerPosition: t.value });      // fixed|scrollable
    if (t.name === 'page-loader')              return applyAndSave({ loader: t.value });              // enable|disable
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
