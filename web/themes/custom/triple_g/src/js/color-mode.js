(function () {
  'use strict';

  const root = document.documentElement;
  const colorScheme = window.matchMedia('(prefers-color-scheme: dark)');
  const storageKey = 'triple-g-color-mode';
  const toggleSelector = '[data-color-mode-toggle]';

  function getColorMode() {
    return root.dataset.colorMode || (colorScheme.matches ? 'dark' : 'light');
  }

  function updateToggles(context = document) {
    context.querySelectorAll(toggleSelector).forEach((toggle) => {
      toggle.setAttribute('aria-pressed', String(getColorMode() === 'dark'));
    });
  }

  function saveColorMode(mode) {
    try {
      window.localStorage.setItem(storageKey, mode);
    }
    catch {
      // Storage may be unavailable; the current page still uses the selected mode.
    }
  }

  Drupal.behaviors.tripleGColorMode = {
    attach(context, settings) {
      if (once('triple-g-color-mode-init', root).length) {
        try {
          const savedMode = window.localStorage.getItem(storageKey);
          if (savedMode === 'dark' || savedMode === 'light') {
            root.dataset.colorMode = savedMode;
          }
        }
        catch {
          // Storage may be unavailable; the current page can still follow OS settings.
        }

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
      }

      once('triple-g-color-mode-toggle', toggleSelector, context).forEach((toggle) => {
        toggle.setAttribute('aria-pressed', String(getColorMode() === 'dark'));
        toggle.addEventListener('click', () => {
          const nextMode = getColorMode() === 'dark' ? 'light' : 'dark';
          root.dataset.colorMode = nextMode;
          saveColorMode(nextMode);
          updateToggles();
        });
      });
    },
  };
})();
