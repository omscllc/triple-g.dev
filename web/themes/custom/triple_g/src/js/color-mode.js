(function () {
  'use strict';

  const root = document.documentElement;
  const colorScheme = window.matchMedia('(prefers-color-scheme: dark)');
  const storageKey = 'triple-g-color-mode';
  const toggleSelector = '[data-color-mode-toggle]';

  function getColorMode() {
    return root.dataset.colorMode || (colorScheme.matches ? 'dark' : 'light');
  }

  function updateToggles() {
    document.querySelectorAll(toggleSelector).forEach((toggle) => {
      toggle.setAttribute('aria-pressed', String(getColorMode() === 'dark'));
    });
  }

  try {
    const savedMode = window.localStorage.getItem(storageKey);
    if (savedMode === 'dark' || savedMode === 'light') {
      root.dataset.colorMode = savedMode;
    }
  }
  catch {
    // Storage may be unavailable; the current page can still follow OS settings.
  }

  document.addEventListener('click', (event) => {
    const toggle = event.target instanceof Element
      ? event.target.closest(toggleSelector)
      : null;

    if (!toggle) {
      return;
    }

    const nextMode = getColorMode() === 'dark' ? 'light' : 'dark';
    root.dataset.colorMode = nextMode;

    try {
      window.localStorage.setItem(storageKey, nextMode);
    }
    catch {
      // Keep the selection for the current page when storage is unavailable.
    }

    updateToggles();
  });

  document.addEventListener('DOMContentLoaded', updateToggles, { once: true });

  const updateFromSystemPreference = () => {
    if (!root.hasAttribute('data-color-mode')) {
      updateToggles();
    }
  };

  if (typeof colorScheme.addEventListener === 'function') {
    colorScheme.addEventListener('change', updateFromSystemPreference);
  }
  else {
    colorScheme.addListener(updateFromSystemPreference);
  }

  root.dataset.colorModeReady = 'true';
})();
