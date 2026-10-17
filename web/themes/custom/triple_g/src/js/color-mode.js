(function () {
  'use strict';

  const root = document.documentElement;
  const storageKey = 'triple-g-color-mode';
  const toggleSelector = '[data-color-mode-toggle]';

  function getColorMode() {
    return root.dataset.colorMode || 'dark';
  }

  function updateToggles(context = document) {
    context.querySelectorAll(toggleSelector).forEach((toggle) => {
      const targetMode = getColorMode() === 'dark' ? 'light' : 'dark';
      toggle.dataset.colorModeTarget = targetMode;
      const label = targetMode === 'dark' ? Drupal.t('Dark mode') : Drupal.t('Light mode');
      toggle.setAttribute('aria-label', label);
      toggle.dataset.tooltip = label;
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
          // Storage may be unavailable; the current page can still use its selected mode.
        }

        root.dataset.colorModeReady = 'true';
      }

      updateToggles(context);

      once('triple-g-color-mode-toggle', toggleSelector, context).forEach((toggle) => {
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
