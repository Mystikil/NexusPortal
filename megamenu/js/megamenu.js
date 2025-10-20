(function () {
  const MENU_PATH = 'data/menu.json';
  const PREF_KEY = 'mega-nav-theme';

  document.addEventListener('DOMContentLoaded', () => {
    fetch(MENU_PATH)
      .then((response) => {
        if (!response.ok) {
          throw new Error('Unable to load navigation menu.');
        }
        return response.json();
      })
      .then((data) => initMegaMenu(data))
      .catch((err) => {
        console.error(err);
      });
  });

  function initMegaMenu(config) {
    const host = document.getElementById('mega-nav-root');
    if (!host) {
      return;
    }

    const nav = document.createElement('nav');
    nav.className = 'mega-nav is-sticky';
    nav.setAttribute('role', 'navigation');
    nav.setAttribute('aria-label', 'Primary');

    const inner = document.createElement('div');
    inner.className = 'mega-nav__inner';

    const brandLink = document.createElement('a');
    brandLink.className = 'mega-nav__brand';
    brandLink.href = config.brand?.href ?? '#';
    brandLink.setAttribute('aria-label', config.brand?.logoText ?? 'Home');
    brandLink.appendChild(createLogoGlyph());
    const brandText = document.createElement('span');
    brandText.textContent = config.brand?.logoText ?? '';
    brandLink.appendChild(brandText);

    const toggleButton = document.createElement('button');
    toggleButton.className = 'mega-nav__toggle';
    toggleButton.type = 'button';
    toggleButton.setAttribute('aria-label', 'Open primary navigation');
    toggleButton.setAttribute('aria-expanded', 'false');
    toggleButton.setAttribute('aria-controls', 'mega-nav-drawer');
    toggleButton.appendChild(createIcon('menu'));

    const primaryWrapper = document.createElement('div');
    primaryWrapper.className = 'mega-nav__primary';
    primaryWrapper.setAttribute('role', 'menubar');

    const utilityWrapper = document.createElement('div');
    utilityWrapper.className = 'mega-nav__utility';

    const panelContainer = document.createElement('div');
    panelContainer.className = 'mega-nav__panel-container';

    inner.append(brandLink, primaryWrapper, utilityWrapper, toggleButton);
    nav.append(inner, panelContainer);

    host.appendChild(nav);

    const overlay = document.createElement('div');
    overlay.className = 'mega-nav__overlay';
    host.appendChild(overlay);

    const drawer = buildDrawer(config, toggleButton, overlay);
    host.appendChild(drawer);

    if (Array.isArray(config.primary)) {
      config.primary.forEach((item, index) => {
        const { trigger, panel } = buildPrimaryItem(item, index);
        primaryWrapper.appendChild(trigger);
        panelContainer.appendChild(panel);
      });
    }

    if (Array.isArray(config.utility)) {
      config.utility.forEach((item) => {
        const utilElement = buildUtilityItem(item);
        if (utilElement) {
          utilityWrapper.appendChild(utilElement);
        }
      });
    }

    setupPrimaryBehaviors(nav, panelContainer);
    initialiseTheme();
  }

  function buildPrimaryItem(item, index) {
    const triggerWrapper = document.createElement('div');
    triggerWrapper.className = 'mega-nav__primary-item';

    const trigger = document.createElement('button');
    trigger.className = 'mega-nav__trigger';
    trigger.type = 'button';
    trigger.textContent = item.label;
    const triggerId = `mega-nav-trigger-${index}`;
    const panelId = `mega-nav-panel-${index}`;
    trigger.id = triggerId;
    trigger.setAttribute('aria-expanded', 'false');
    trigger.setAttribute('aria-controls', panelId);
    trigger.setAttribute('role', 'menuitem');

    triggerWrapper.appendChild(trigger);

    const panel = buildPanel(item, panelId, triggerId);

    return { trigger: triggerWrapper, panel };
  }

  function buildPanel(item, panelId, triggerId) {
    const panel = document.createElement('div');
    panel.className = `mega-nav__panel mega-nav__panel--${item.panelWidth === 'full' ? 'full' : 'container'}`;
    panel.id = panelId;
    panel.setAttribute('role', 'menu');
    panel.setAttribute('aria-labelledby', triggerId);
    panel.setAttribute('aria-hidden', 'true');

    const surface = document.createElement('div');
    surface.className = 'mega-nav__panel-surface';

    const groupsWrapper = document.createElement('div');
    groupsWrapper.className = 'mega-nav__panel-groups';

    (item.groups ?? []).forEach((group) => {
      const groupContainer = document.createElement('div');
      const title = document.createElement('p');
      title.className = 'mega-nav__group-title';
      title.textContent = group.title ?? '';
      groupContainer.appendChild(title);

      const list = document.createElement('ul');
      list.className = 'mega-nav__group-list';

      (group.items ?? []).forEach((entry) => {
        const listItem = document.createElement('li');
        const link = document.createElement('a');
        link.className = 'mega-nav__item-link';
        link.href = entry.href ?? '#';
        link.setAttribute('role', 'menuitem');

        const iconWrapper = document.createElement('span');
        iconWrapper.className = 'mega-nav__item-icon';
        iconWrapper.appendChild(createIcon(entry.icon));

        const textWrapper = document.createElement('span');
        textWrapper.className = 'mega-nav__item-text';

        const label = document.createElement('span');
        label.textContent = entry.label ?? '';
        label.className = 'mega-nav__item-label';
        textWrapper.appendChild(label);

        if (entry.desc) {
          const desc = document.createElement('span');
          desc.className = 'mega-nav__item-desc';
          desc.textContent = entry.desc;
          textWrapper.appendChild(desc);
        }

        link.append(iconWrapper, textWrapper);

        if (entry.badge) {
          const badge = document.createElement('span');
          badge.className = 'mega-nav__badge';
          badge.textContent = entry.badge;
          link.appendChild(badge);
        }

        listItem.appendChild(link);
        list.appendChild(listItem);
      });

      groupContainer.appendChild(list);
      groupsWrapper.appendChild(groupContainer);
    });

    surface.appendChild(groupsWrapper);
    panel.appendChild(surface);
    return panel;
  }

  function buildUtilityItem(item) {
    switch (item.type) {
      case 'search': {
        const form = document.createElement('form');
        form.className = 'mega-nav__search';
        form.setAttribute('role', 'search');
        form.addEventListener('submit', (event) => event.preventDefault());

        const searchInput = document.createElement('input');
        searchInput.type = 'search';
        searchInput.className = 'mega-nav__search-input';
        searchInput.placeholder = item.placeholder ?? 'Search';
        searchInput.setAttribute('aria-label', item.placeholder ?? 'Search');

        const button = document.createElement('button');
        button.type = 'submit';
        button.className = 'mega-nav__search-button';
        button.setAttribute('aria-label', 'Submit search');
        button.appendChild(createIcon('search'));

        form.append(searchInput, button);
        return form;
      }
      case 'link': {
        const link = document.createElement('a');
        link.className = 'mega-nav__utility-link';
        link.href = item.href ?? '#';
        link.appendChild(createIcon(item.icon));
        const span = document.createElement('span');
        span.textContent = item.label ?? '';
        link.appendChild(span);
        return link;
      }
      case 'theme-toggle': {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'mega-nav__theme-toggle';
        button.setAttribute('aria-label', 'Toggle color theme');
        button.appendChild(createIcon('sun'));
        button.dataset.themeToggle = 'true';
        button.addEventListener('click', toggleTheme);
        updateThemeToggleIcon(button);
        return button;
      }
      default:
        return null;
    }
  }

  function buildDrawer(config, toggleButton, overlay) {
    const drawer = document.createElement('aside');
    drawer.className = 'mega-nav__drawer';
    drawer.id = 'mega-nav-drawer';
    drawer.setAttribute('aria-hidden', 'true');

    const drawerHeader = document.createElement('div');
    drawerHeader.className = 'mega-nav__drawer-header';

    const brand = document.createElement('span');
    brand.className = 'mega-nav__brand';
    brand.textContent = config.brand?.logoText ?? 'Menu';

    const closeBtn = document.createElement('button');
    closeBtn.className = 'mega-nav__drawer-close';
    closeBtn.type = 'button';
    closeBtn.setAttribute('aria-label', 'Close navigation');
    closeBtn.innerHTML = '&times;';

    drawerHeader.append(brand, closeBtn);

    const drawerBody = document.createElement('div');
    drawerBody.className = 'mega-nav__drawer-body';

    const searchInput = document.createElement('input');
    searchInput.className = 'mega-nav__drawer-search';
    searchInput.type = 'search';
    searchInput.placeholder = config.utility?.find((item) => item.type === 'search')?.placeholder ?? 'Search…';
    searchInput.setAttribute('aria-label', 'Search site');
    drawerBody.appendChild(searchInput);

    const accordion = document.createElement('div');
    accordion.className = 'mega-nav__accordion';

    (config.primary ?? []).forEach((section, index) => {
      const item = document.createElement('div');
      item.className = 'mega-nav__accordion-item';

      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'mega-nav__accordion-button';
      button.setAttribute('aria-expanded', 'false');
      button.setAttribute('aria-controls', `mega-nav-accordion-panel-${index}`);
      button.innerHTML = `<span>${section.label ?? ''}</span>`;
      const chevron = createIcon('chevron-down');
      chevron.classList.add('mega-nav__chevron');
      button.appendChild(chevron);

      const panel = document.createElement('div');
      panel.className = 'mega-nav__accordion-panel';
      panel.id = `mega-nav-accordion-panel-${index}`;

      const links = document.createElement('ul');
      links.className = 'mega-nav__accordion-links';

      (section.groups ?? []).forEach((group) => {
        (group.items ?? []).forEach((entry) => {
          const li = document.createElement('li');
          const anchor = document.createElement('a');
          anchor.href = entry.href ?? '#';
          anchor.innerHTML = `<span>${entry.label ?? ''}</span>`;
          if (entry.desc) {
            const desc = document.createElement('span');
            desc.className = 'mega-nav__accordion-desc';
            desc.textContent = entry.desc;
            anchor.appendChild(desc);
          }
          if (entry.badge) {
            const badge = document.createElement('span');
            badge.className = 'mega-nav__badge';
            badge.textContent = entry.badge;
            anchor.appendChild(badge);
          }
          li.appendChild(anchor);
          links.appendChild(li);
        });
      });

      panel.appendChild(links);
      item.append(button, panel);
      accordion.appendChild(item);

      button.addEventListener('click', () => {
        const isOpen = button.getAttribute('aria-expanded') === 'true';
        if (isOpen) {
          collapsePanel(button, panel);
        } else {
          expandExclusive(button, panel, accordion);
        }
      });
    });

    drawerBody.appendChild(accordion);

    const utilities = document.createElement('div');
    utilities.className = 'mega-nav__drawer-utility';
    (config.utility ?? []).forEach((item) => {
      if (item.type === 'link') {
        const link = document.createElement('a');
        link.className = 'mega-nav__utility-link';
        link.href = item.href ?? '#';
        link.textContent = item.label ?? '';
        utilities.appendChild(link);
      }
      if (item.type === 'theme-toggle') {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'mega-nav__theme-toggle';
        button.setAttribute('aria-label', 'Toggle color theme');
        button.dataset.themeToggle = 'true';
        button.appendChild(createIcon('sun'));
        button.addEventListener('click', toggleTheme);
        updateThemeToggleIcon(button);
        utilities.appendChild(button);
      }
    });
    if (utilities.children.length) {
      drawerBody.appendChild(utilities);
    }

    drawer.append(drawerHeader, drawerBody);

    let lastFocusedElement = null;

    function openDrawer() {
      lastFocusedElement = document.activeElement;
      drawer.classList.add('is-open');
      drawer.setAttribute('aria-hidden', 'false');
      overlay.classList.add('is-visible');
      toggleButton.setAttribute('aria-expanded', 'true');
      document.body.classList.add('has-drawer-open');
      requestAnimationFrame(() => {
        const focusables = getFocusableElements(drawer);
        (focusables[0] ?? closeBtn).focus();
      });
    }

    function closeDrawer() {
      drawer.classList.remove('is-open');
      drawer.setAttribute('aria-hidden', 'true');
      overlay.classList.remove('is-visible');
      toggleButton.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('has-drawer-open');
      if (lastFocusedElement) {
        lastFocusedElement.focus({ preventScroll: true });
      } else {
        toggleButton.focus({ preventScroll: true });
      }
    }

    toggleButton.addEventListener('click', () => {
      const isOpen = drawer.classList.contains('is-open');
      if (isOpen) {
        closeDrawer();
      } else {
        openDrawer();
      }
    });

    closeBtn.addEventListener('click', closeDrawer);
    overlay.addEventListener('click', closeDrawer);

    drawer.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        event.preventDefault();
        closeDrawer();
        return;
      }
      if (event.key === 'Tab') {
        const focusables = getFocusableElements(drawer);
        if (!focusables.length) {
          event.preventDefault();
          return;
        }
        const first = focusables[0];
        const last = focusables[focusables.length - 1];
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      }
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth >= 1024 && drawer.classList.contains('is-open')) {
        closeDrawer();
      }
    });

    return drawer;
  }

  function expandExclusive(button, panel, accordion) {
    Array.from(accordion.querySelectorAll('.mega-nav__accordion-button')).forEach((btn) => {
      const targetId = btn.getAttribute('aria-controls');
      const target = accordion.querySelector(`#${targetId}`);
      if (btn === button) {
        btn.setAttribute('aria-expanded', 'true');
        target.classList.add('is-open');
        target.style.maxHeight = `${target.scrollHeight}px`;
      } else {
        btn.setAttribute('aria-expanded', 'false');
        target.classList.remove('is-open');
        target.style.maxHeight = '0px';
      }
    });
  }

  function collapsePanel(button, panel) {
    button.setAttribute('aria-expanded', 'false');
    panel.classList.remove('is-open');
    panel.style.maxHeight = '0px';
  }

  function setupPrimaryBehaviors(nav, panelContainer) {
    const triggers = Array.from(nav.querySelectorAll('.mega-nav__trigger'));
    let openPanel = null;
    let pointerIntent = null;
    let closeIntent = null;

    function open(panel) {
      if (openPanel === panel) {
        return;
      }
      close();
      const trigger = nav.querySelector(`[aria-controls="${panel.id}"]`);
      if (trigger) {
        trigger.setAttribute('aria-expanded', 'true');
      }
      panel.classList.add('is-open');
      panel.setAttribute('aria-hidden', 'false');
      openPanel = panel;
    }

    function close() {
      if (!openPanel) {
        return;
      }
      const trigger = nav.querySelector(`[aria-controls="${openPanel.id}"]`);
      if (trigger) {
        trigger.setAttribute('aria-expanded', 'false');
      }
      openPanel.classList.remove('is-open');
      openPanel.setAttribute('aria-hidden', 'true');
      openPanel = null;
    }

    function scheduleOpen(panel) {
      clearTimeout(pointerIntent);
      pointerIntent = setTimeout(() => open(panel), 200);
    }

    function scheduleClose() {
      clearTimeout(closeIntent);
      closeIntent = setTimeout(() => close(), 150);
    }

    triggers.forEach((trigger, index) => {
      const panelId = trigger.getAttribute('aria-controls');
      const panel = panelContainer.querySelector(`#${panelId}`);
      if (!panel) {
        return;
      }

      trigger.addEventListener('click', () => {
        const expanded = trigger.getAttribute('aria-expanded') === 'true';
        if (expanded) {
          close();
        } else {
          open(panel);
        }
      });

      trigger.addEventListener('pointerenter', (event) => {
        if (window.matchMedia('(pointer: coarse)').matches) {
          return;
        }
        if (event.pointerType === 'mouse') {
          scheduleOpen(panel);
        }
      });

      trigger.addEventListener('pointerleave', () => {
        if (window.matchMedia('(pointer: coarse)').matches) {
          return;
        }
        scheduleClose();
      });

      trigger.addEventListener('focus', () => {
        clearTimeout(pointerIntent);
        clearTimeout(closeIntent);
        open(panel);
      });

      trigger.addEventListener('keydown', (event) => {
        const key = event.key;
        if (key === 'ArrowRight' || key === 'ArrowLeft') {
          event.preventDefault();
          const dir = key === 'ArrowRight' ? 1 : -1;
          const nextIndex = (index + dir + triggers.length) % triggers.length;
          const nextTrigger = triggers[nextIndex];
          nextTrigger.focus();
        }
        if (key === 'ArrowDown') {
          event.preventDefault();
          open(panel);
          focusFirstItem(panel);
        }
        if (key === 'ArrowUp') {
          event.preventDefault();
          open(panel);
          focusLastItem(panel);
        }
        if (key === 'Escape') {
          event.preventDefault();
          close();
          trigger.focus();
        }
        if (key === 'Enter' || key === ' ') {
          event.preventDefault();
          const expanded = trigger.getAttribute('aria-expanded') === 'true';
          if (expanded) {
            close();
          } else {
            open(panel);
            focusFirstItem(panel);
          }
        }
      });

      panel.addEventListener('pointerenter', () => {
        clearTimeout(closeIntent);
      });

      panel.addEventListener('pointerleave', () => {
        if (window.matchMedia('(pointer: coarse)').matches) {
          return;
        }
        scheduleClose();
      });

      panel.addEventListener('keydown', (event) => {
        const key = event.key;
        if (key === 'Escape') {
          event.preventDefault();
          close();
          trigger.focus();
          return;
        }
        if (key === 'ArrowDown' || key === 'ArrowUp' || key === 'Home' || key === 'End') {
          event.preventDefault();
          moveWithinPanel(panel, key);
        }
      });
    });

    nav.addEventListener('focusout', () => {
      setTimeout(() => {
        if (!nav.contains(document.activeElement)) {
          close();
        }
      }, 100);
    });

    document.addEventListener('click', (event) => {
      if (!nav.contains(event.target)) {
        close();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        close();
      }
    });

    function focusFirstItem(panel) {
      const focusables = getFocusableElements(panel);
      const firstLink = focusables.find((el) => el.tagName === 'A');
      (firstLink ?? focusables[0])?.focus();
    }

    function focusLastItem(panel) {
      const focusables = getFocusableElements(panel).filter((el) => el.tagName === 'A');
      (focusables[focusables.length - 1] ?? null)?.focus();
    }
  }

  function moveWithinPanel(panel, key) {
    const focusables = getFocusableElements(panel).filter((el) => el.tagName === 'A');
    if (!focusables.length) {
      return;
    }
    const currentIndex = focusables.indexOf(document.activeElement);
    let nextIndex = 0;
    if (key === 'ArrowDown') {
      nextIndex = currentIndex < 0 ? 0 : (currentIndex + 1) % focusables.length;
    } else if (key === 'ArrowUp') {
      nextIndex = currentIndex <= 0 ? focusables.length - 1 : currentIndex - 1;
    } else if (key === 'Home') {
      nextIndex = 0;
    } else if (key === 'End') {
      nextIndex = focusables.length - 1;
    }
    focusables[nextIndex].focus();
  }

  function getFocusableElements(container) {
    return Array.from(
      container.querySelectorAll(
        'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])'
      )
    );
  }

  function createIcon(name) {
    const iconName = name ? `icon-${name}` : 'icon-placeholder';
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('aria-hidden', 'true');
    svg.setAttribute('focusable', 'false');
    const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
    use.setAttributeNS('http://www.w3.org/1999/xlink', 'href', `#${iconName}`);
    svg.appendChild(use);
    return svg;
  }

  function createLogoGlyph() {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 32 32');
    svg.setAttribute('aria-hidden', 'true');
    const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    circle.setAttribute('cx', '16');
    circle.setAttribute('cy', '16');
    circle.setAttribute('r', '15');
    circle.setAttribute('fill', 'url(#brandGradient)');
    svg.appendChild(circle);
    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    path.setAttribute('d', 'M10 20.5L13.5 9h5L22 20.5h-3l-.8-2.7h-5.2l-.8 2.7z');
    path.setAttribute('fill', '#fff');
    svg.appendChild(path);
    return svg;
  }

  function initialiseTheme() {
    const stored = localStorage.getItem(PREF_KEY);
    if (stored) {
      document.documentElement.dataset.theme = stored;
    }
    syncToggleButtons();
  }

  function toggleTheme() {
    const current = document.documentElement.dataset.theme;
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    localStorage.setItem(PREF_KEY, next);
    syncToggleButtons();
  }

  function syncToggleButtons() {
    const toggles = document.querySelectorAll('[data-theme-toggle="true"]');
    toggles.forEach(updateThemeToggleIcon);
  }

  function updateThemeToggleIcon(button) {
    const current = document.documentElement.dataset.theme;
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = current ? current === 'dark' : prefersDark;
    button.innerHTML = '';
    button.appendChild(createIcon(isDark ? 'moon' : 'sun'));
    button.setAttribute('aria-pressed', String(isDark));
    button.setAttribute('aria-label', isDark ? 'Switch to light theme' : 'Switch to dark theme');
  }
})();
