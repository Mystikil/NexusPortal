(function () {
  const NAV_SELECTOR = '[data-nav]';
  const HOVER_INTENT_DELAY = 200;

  const nav = document.querySelector(NAV_SELECTOR);
  if (!nav) {
    return;
  }

  const state = {
    openTrigger: null,
    openPanel: null,
    hoverTimer: null,
    restoreFocusTo: null,
    drawerPreviouslyFocused: null,
    searchEscapeHandler: null,
    prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
  };

  const triggers = Array.from(nav.querySelectorAll('.nav-trigger'));
  const panels = new Map(
    Array.from(nav.querySelectorAll('[data-panel]')).map((panel) => [panel.id, panel])
  );

  const pointerFine = window.matchMedia('(pointer: fine)');

  function setExpanded(trigger, expand) {
    if (!trigger) return;
    trigger.setAttribute('aria-expanded', String(expand));
    trigger.classList.toggle('is-active', expand);
  }

  function setPanelVisibility(panel, show) {
    if (!panel) return;
    if (show) {
      panel.hidden = false;
      panel.setAttribute('aria-hidden', 'false');
      panel.classList.add('is-active');
    } else {
      panel.classList.remove('is-active');
      panel.setAttribute('aria-hidden', 'true');
      panel.hidden = true;
    }
    const links = panel.querySelectorAll('[role="menuitem"]');
    links.forEach((link, index) => {
      link.tabIndex = show && index === 0 ? 0 : -1;
    });
  }

  function closeOpenPanel() {
    if (state.openTrigger) {
      setExpanded(state.openTrigger, false);
    }
    if (state.openPanel) {
      setPanelVisibility(state.openPanel, false);
    }
    state.openTrigger = null;
    state.openPanel = null;
  }

  function openPanel(trigger) {
    if (!trigger) return;
    const panelId = trigger.getAttribute('aria-controls');
    const targetPanel = panels.get(panelId);
    if (!targetPanel) return;

    if (state.openTrigger && state.openTrigger !== trigger) {
      closeOpenPanel();
    }

    setExpanded(trigger, true);
    setPanelVisibility(targetPanel, true);

    state.openTrigger = trigger;
    state.openPanel = targetPanel;
  }

  function handleTriggerClick(event) {
    const trigger = event.currentTarget;
    if (!trigger) return;
    if (state.openTrigger === trigger) {
      closeOpenPanel();
    } else {
      openPanel(trigger);
    }
  }

  function scheduleClose() {
    if (state.prefersReducedMotion) {
      closeOpenPanel();
      return;
    }
    clearTimeout(state.hoverTimer);
    state.hoverTimer = window.setTimeout(() => {
      closeOpenPanel();
    }, HOVER_INTENT_DELAY);
  }

  function cancelScheduledClose() {
    clearTimeout(state.hoverTimer);
    state.hoverTimer = null;
  }

  function focusNextTrigger(current, direction) {
    const idx = triggers.indexOf(current);
    if (idx === -1) return;
    const nextIndex = (idx + direction + triggers.length) % triggers.length;
    triggers[nextIndex].focus();
  }

  function handleTriggerKeydown(event) {
    const { key } = event;
    if (key === 'ArrowRight') {
      event.preventDefault();
      focusNextTrigger(event.currentTarget, 1);
    } else if (key === 'ArrowLeft') {
      event.preventDefault();
      focusNextTrigger(event.currentTarget, -1);
    } else if (key === 'ArrowDown') {
      event.preventDefault();
      const trigger = event.currentTarget;
      openPanel(trigger);
      const panelId = trigger.getAttribute('aria-controls');
      const panel = panels.get(panelId);
      const firstLink = panel ? panel.querySelector('[role="menuitem"]') : null;
      if (firstLink) {
        firstLink.tabIndex = 0;
        firstLink.focus();
      }
    } else if (key === 'Tab' && !event.shiftKey) {
      if (state.openTrigger !== event.currentTarget) {
        return;
      }
      const panelId = event.currentTarget.getAttribute('aria-controls');
      const panel = panels.get(panelId);
      const firstLink = panel ? panel.querySelector('[role="menuitem"]') : null;
      if (firstLink) {
        event.preventDefault();
        firstLink.tabIndex = 0;
        firstLink.focus();
      }
    } else if (key === 'Escape') {
      event.preventDefault();
      closeOpenPanel();
      event.currentTarget.focus();
    }
  }

  function handlePanelKeydown(event) {
    const { key } = event;
    const items = Array.from(event.currentTarget.querySelectorAll('[role="menuitem"]'));
    if (!items.length) return;
    const activeIndex = items.findIndex((item) => item === document.activeElement);

    if (key === 'Tab') {
      const first = items[0];
      const last = items[items.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        const controls = event.currentTarget.getAttribute('aria-labelledby');
        const trigger = controls ? document.getElementById(controls) : null;
        if (trigger) {
          trigger.focus();
        }
        return;
      }
      if (!event.shiftKey && document.activeElement === last) {
        items.forEach((link) => (link.tabIndex = -1));
      }
      return;
    }

    if (key === 'ArrowDown') {
      event.preventDefault();
      const nextIndex = activeIndex === -1 ? 0 : (activeIndex + 1) % items.length;
      items.forEach((link) => (link.tabIndex = -1));
      items[nextIndex].tabIndex = 0;
      items[nextIndex].focus();
    } else if (key === 'ArrowUp') {
      event.preventDefault();
      const prevIndex = activeIndex <= 0 ? items.length - 1 : activeIndex - 1;
      items.forEach((link) => (link.tabIndex = -1));
      items[prevIndex].tabIndex = 0;
      items[prevIndex].focus();
    } else if (key === 'Escape') {
      event.preventDefault();
      const controls = event.currentTarget.getAttribute('aria-labelledby');
      const trigger = controls ? document.getElementById(controls) : null;
      closeOpenPanel();
      if (trigger) {
        trigger.focus();
      }
    }
  }

  function handleNavFocusOut(event) {
    const nextFocused = event.relatedTarget;
    if (!nav.contains(nextFocused)) {
      window.setTimeout(() => {
        if (!nav.contains(document.activeElement)) {
          closeOpenPanel();
        }
      }, 0);
    }
  }

  function handleDocumentPointerDown(event) {
    if (!nav.contains(event.target)) {
      closeOpenPanel();
    }
  }

  triggers.forEach((trigger) => {
    trigger.addEventListener('click', handleTriggerClick);
    trigger.addEventListener('keydown', handleTriggerKeydown);
    trigger.addEventListener('focus', () => {
      cancelScheduledClose();
      openPanel(trigger);
    });
    trigger.addEventListener('pointerenter', (event) => {
      if (!pointerFine.matches) return;
      cancelScheduledClose();
      openPanel(event.currentTarget);
    });
    trigger.addEventListener('pointerleave', () => {
      if (!pointerFine.matches) return;
      scheduleClose();
    });
  });

  panels.forEach((panel) => {
    panel.setAttribute('aria-hidden', 'true');
    panel.addEventListener('pointerenter', cancelScheduledClose);
    panel.addEventListener('pointerleave', () => {
      if (!pointerFine.matches) return;
      scheduleClose();
    });
    panel.addEventListener('keydown', handlePanelKeydown);
  });

  nav.addEventListener('focusout', handleNavFocusOut);
  document.addEventListener('pointerdown', handleDocumentPointerDown);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && state.openTrigger) {
      closeOpenPanel();
      state.openTrigger.focus();
    }
  });

  // Drawer logic
  const drawer = nav.querySelector('.nav-drawer');
  const drawerToggle = nav.querySelector('.nav-toggle');
  const drawerCloseButtons = nav.querySelectorAll('[data-drawer-close]');
  const drawerBody = nav.querySelector('[data-drawer-body]');

  function getFocusableElements(container) {
    return Array.from(
      container.querySelectorAll(
        'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
      )
    ).filter((el) => el.offsetParent !== null || getComputedStyle(el).position === 'fixed');
  }

  function handleDrawerKeydown(event) {
    if (event.key === 'Tab') {
      const focusable = getFocusableElements(drawer);
      if (!focusable.length) {
        event.preventDefault();
        return;
      }
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    } else if (event.key === 'Escape') {
      event.preventDefault();
      closeDrawer();
    }
  }

  function openDrawer() {
    if (!drawer) return;
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    document.body.classList.add('is-drawer-open');
    state.drawerPreviouslyFocused = document.activeElement;
    const focusables = getFocusableElements(drawer);
    const target = focusables.find((el) => el.matches('.drawer-accordion__trigger')) || focusables[0];
    window.setTimeout(() => {
      if (target) target.focus();
    }, 0);
    drawer.addEventListener('keydown', handleDrawerKeydown);
  }

  function closeDrawer() {
    if (!drawer) return;
    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('is-drawer-open');
    drawer.removeEventListener('keydown', handleDrawerKeydown);
    if (state.drawerPreviouslyFocused && typeof state.drawerPreviouslyFocused.focus === 'function') {
      state.drawerPreviouslyFocused.focus();
    }
    if (drawerToggle) {
      drawerToggle.setAttribute('aria-expanded', 'false');
    }
  }

  if (drawerToggle) {
    drawerToggle.addEventListener('click', () => {
      const expanded = drawerToggle.getAttribute('aria-expanded') === 'true';
      if (expanded) {
        closeDrawer();
      } else {
        drawerToggle.setAttribute('aria-expanded', 'true');
        openDrawer();
      }
    });
  }

  drawerCloseButtons.forEach((btn) => {
    btn.addEventListener('click', closeDrawer);
  });

  if (drawerBody) {
    drawerBody.addEventListener('click', (event) => {
      const trigger = event.target.closest('.drawer-accordion__trigger');
      if (!trigger) return;
      const panelId = trigger.getAttribute('aria-controls');
      const panel = panelId ? document.getElementById(panelId) : null;
      const expanded = trigger.getAttribute('aria-expanded') === 'true';
      trigger.setAttribute('aria-expanded', String(!expanded));
      if (panel) {
        panel.hidden = expanded;
        panel.setAttribute('aria-hidden', expanded ? 'true' : 'false');
      }
    });
  }

  // Search overlay logic
  const searchToggle = nav.querySelector('.nav-search__toggle');
  const searchOverlay = nav.querySelector('#nav-search-overlay');
  const searchOverlayClose = searchOverlay ? searchOverlay.querySelector('.nav-search-overlay__close') : null;
  const searchOverlayInput = searchOverlay ? searchOverlay.querySelector('input[type="search"]') : null;

  function openSearchOverlay() {
    if (!searchOverlay) return;
    searchOverlay.hidden = false;
    searchOverlay.classList.add('is-open');
    searchOverlay.setAttribute('aria-hidden', 'false');
    state.restoreFocusTo = document.activeElement;
    if (searchOverlayInput) {
      window.setTimeout(() => searchOverlayInput.focus(), 0);
    }
    searchOverlay.addEventListener('keydown', trapSearchTab);
    document.addEventListener('keydown', state.searchEscapeHandler);
    if (searchToggle) {
      searchToggle.setAttribute('aria-expanded', 'true');
    }
  }

  function closeSearchOverlay() {
    if (!searchOverlay) return;
    searchOverlay.classList.remove('is-open');
    searchOverlay.hidden = true;
    searchOverlay.setAttribute('aria-hidden', 'true');
    searchOverlay.removeEventListener('keydown', trapSearchTab);
    document.removeEventListener('keydown', state.searchEscapeHandler);
    if (state.restoreFocusTo && typeof state.restoreFocusTo.focus === 'function') {
      state.restoreFocusTo.focus();
    }
    if (searchToggle) {
      searchToggle.setAttribute('aria-expanded', 'false');
    }
  }

  function trapSearchTab(event) {
    if (event.key !== 'Tab') return;
    const focusable = getFocusableElements(searchOverlay);
    if (!focusable.length) {
      event.preventDefault();
      return;
    }
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }

  state.searchEscapeHandler = (event) => {
    if (!searchOverlay) return;
    if (event.key === 'Escape' && !searchOverlay.hidden) {
      event.preventDefault();
      closeSearchOverlay();
    }
  };

  if (searchToggle && searchOverlay) {
    searchToggle.addEventListener('click', () => {
      if (searchOverlay.hidden) {
        openSearchOverlay();
      } else {
        closeSearchOverlay();
      }
    });
  }

  if (searchOverlayClose) {
    searchOverlayClose.addEventListener('click', closeSearchOverlay);
  }

  searchOverlay?.addEventListener('click', (event) => {
    if (event.target === searchOverlay) {
      closeSearchOverlay();
    }
  });

  // Theme toggle logic
  const themeButtons = Array.from(nav.querySelectorAll('.nav-theme-toggle, .drawer-theme-toggle'));
  const storageKey = 'nexus-nav-theme';
  const root = document.documentElement;

  function applyTheme(theme) {
    root.setAttribute('data-theme', theme);
    themeButtons.forEach((btn) => btn.setAttribute('aria-pressed', String(theme === 'dark')));
    try {
      localStorage.setItem(storageKey, theme);
    } catch (error) {
      /* localStorage not available */
    }
  }

  function getPreferredTheme() {
    try {
      const stored = localStorage.getItem(storageKey);
      if (stored) return stored;
    } catch (error) {
      /* ignore */
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  if (themeButtons.length) {
    const currentTheme = root.getAttribute('data-theme') || getPreferredTheme();
    applyTheme(currentTheme);

    themeButtons.forEach((button) => {
      button.addEventListener('click', () => {
        const nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        applyTheme(nextTheme);
      });
    });
  }

  // Keep placeholders synced with JSON
  const menuSource = nav.getAttribute('data-menu-source');
  if (menuSource) {
    fetch(menuSource)
      .then((response) => response.json())
      .then((data) => {
        const searchPlaceholder = data?.utility?.find((item) => item.type === 'search')?.placeholder;
        if (searchPlaceholder) {
          const inlineSearch = nav.querySelector('.nav-search input');
          const drawerSearch = nav.querySelector('#drawer-search-input');
          const overlayInput = nav.querySelector('#nav-search-overlay-input');
          if (inlineSearch) inlineSearch.placeholder = searchPlaceholder;
          if (drawerSearch) drawerSearch.placeholder = searchPlaceholder;
          if (overlayInput) overlayInput.placeholder = searchPlaceholder;
        }
      })
      .catch(() => {
        /* ignore fetch errors */
      });
  }
})();
