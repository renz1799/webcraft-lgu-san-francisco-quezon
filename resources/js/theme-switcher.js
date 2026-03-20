(function () {
  const host = document.getElementById('hs-overlay-switcher');
  if (!host) {
    return;
  }

  const colorPanel = document.getElementById('switcher-2');
  const colorsUrl = host.dataset.updateColorsUrl || '';
  const canEditColors = host.dataset.canEditColors === '1';
  const colorKeys = new Set(['ynexMenu', 'ynexHeader', 'primaryRGB', 'primaryRGB1', 'bodyBgRGB', 'darkBgRGB', 'bgimg']);

  const disableColorEditing = () => {
    if (!colorPanel) {
      return;
    }

    colorPanel.querySelectorAll('input').forEach((input) => {
      if (input instanceof HTMLInputElement) {
        input.disabled = true;
      }
    });

    colorPanel.querySelectorAll('.pickr-container-primary, .pickr-container-background').forEach((element) => {
      if (element instanceof HTMLElement) {
        element.style.pointerEvents = 'none';
        element.style.opacity = '0.65';
      }
    });
  };

  const snapshotColors = () => {
    const colors = {};

    const menu = localStorage.getItem('ynexMenu');
    const header = localStorage.getItem('ynexHeader');
    const primaryRgb = localStorage.getItem('primaryRGB');
    const primaryRgb1 = localStorage.getItem('primaryRGB1');
    const bodyBgRgb = localStorage.getItem('bodyBgRGB');
    const darkBgRgb = localStorage.getItem('darkBgRGB');
    const bgImage = localStorage.getItem('bgimg');

    if (menu) colors.menu = menu;
    if (header) colors.header = header;
    if (primaryRgb) colors.primaryRgb = primaryRgb;
    if (primaryRgb1) colors.primaryRgb1 = primaryRgb1;
    if (bodyBgRgb) colors.bodyBgRgb = bodyBgRgb;
    if (darkBgRgb) colors.darkBgRgb = darkBgRgb;
    if (bgImage) colors.bgImage = bgImage;

    return colors;
  };

  const postColors = async (payload) => {
    if (!colorsUrl) {
      return;
    }

    const response = await fetch(colorsUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Theme colors request failed with status ${response.status}`);
    }

    return response.json().catch(() => null);
  };

  let colorInteractionUntil = 0;
  let syncTimer = null;

  const markColorInteraction = () => {
    colorInteractionUntil = Date.now() + 10000;
  };

  const queueSync = () => {
    if (!canEditColors || !colorsUrl) {
      return;
    }

    window.clearTimeout(syncTimer);
    syncTimer = window.setTimeout(async () => {
      const payload = snapshotColors();
      host.dataset.themeColors = JSON.stringify(payload);

      try {
        await postColors(payload);
      } catch (error) {
        console.error('[theme-colors] Failed to save module theme colors.', error);
      }
    }, 250);
  };

  const shouldSync = (key) => {
    return canEditColors
      && colorsUrl
      && colorPanel
      && !colorPanel.classList.contains('hidden')
      && colorKeys.has(String(key))
      && Date.now() <= colorInteractionUntil;
  };

  const originalSetItem = localStorage.setItem.bind(localStorage);
  const originalRemoveItem = localStorage.removeItem.bind(localStorage);
  const originalClear = localStorage.clear.bind(localStorage);

  localStorage.setItem = function (key, value) {
    const result = originalSetItem(key, value);

    if (shouldSync(key)) {
      queueSync();
    }

    return result;
  };

  localStorage.removeItem = function (key) {
    const result = originalRemoveItem(key);

    if (shouldSync(key)) {
      queueSync();
    }

    return result;
  };

  localStorage.clear = function () {
    const result = originalClear();

    if (canEditColors && Date.now() <= colorInteractionUntil) {
      queueSync();
    }

    return result;
  };

  if (!canEditColors) {
    disableColorEditing();
    return;
  }

  if (colorPanel) {
    ['pointerdown', 'click', 'change', 'input'].forEach((eventName) => {
      colorPanel.addEventListener(eventName, () => {
        markColorInteraction();
      }, true);
    });
  }

  document.getElementById('switcher-item-2')?.addEventListener('click', () => {
    markColorInteraction();
  }, true);

  document.getElementById('reset-all')?.addEventListener('click', () => {
    if (colorPanel && !colorPanel.classList.contains('hidden')) {
      markColorInteraction();
      window.setTimeout(queueSync, 50);
    }
  }, true);
})();


