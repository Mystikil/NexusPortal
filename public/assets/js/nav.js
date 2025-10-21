(function () {
    const doc = document;
    const root = doc.documentElement;
    const header = doc.querySelector('[data-site-header]');
    const nav = doc.querySelector('[data-site-nav]');
    const navToggle = doc.querySelector('[data-nav-toggle]');
    const navPanels = Array.from(doc.querySelectorAll('[data-nav-panel]'));
    const navDrawer = doc.querySelector('[data-nav-drawer]');
    const themeToggles = Array.from(doc.querySelectorAll('[data-theme-toggle]'));
    const accountMenu = doc.querySelector('[data-account-menu]');
    const drawerOverlay = navDrawer ? navDrawer.querySelector('[data-drawer-overlay]') : null;
    const drawerClose = navDrawer ? navDrawer.querySelector('[data-drawer-close]') : null;
    const searchForms = Array.from(doc.querySelectorAll('[data-nav-search], [data-nav-search-mobile]'));

    const focusableSelectors = 'a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

    let activePanel = null;
    let activeTrigger = null;
    let focusTrapListener = null;
    let lastFocusedBeforeDrawer = null;

    const closePanel = (panel, trigger) => {
        if (!panel) {
            return;
        }
        panel.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
        panel.setAttribute('hidden', '');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
        if (activePanel === panel) {
            activePanel = null;
        }
        if (activeTrigger === trigger) {
            activeTrigger = null;
        }
    };

    const openPanel = (panel, trigger) => {
        if (!panel || !trigger) {
            return;
        }
        if (activePanel && activePanel !== panel) {
            closePanel(activePanel, activeTrigger);
        }
        panel.classList.add('is-open');
        panel.removeAttribute('hidden');
        panel.setAttribute('aria-hidden', 'false');
        trigger.setAttribute('aria-expanded', 'true');
        activePanel = panel;
        activeTrigger = trigger;
    };

    const getPanelFromTrigger = (trigger) => {
        if (!trigger) {
            return null;
        }
        const panelId = trigger.getAttribute('aria-controls');
        if (!panelId) {
            return null;
        }
        return doc.getElementById(panelId);
    };

    const handleTriggerClick = (event) => {
        const trigger = event.currentTarget;
        const panel = getPanelFromTrigger(trigger);
        if (!panel) {
            return;
        }
        const isExpanded = trigger.getAttribute('aria-expanded') === 'true';
        if (isExpanded) {
            closePanel(panel, trigger);
        } else {
            openPanel(panel, trigger);
        }
    };

    const handleTriggerMouseEnter = (event) => {
        const trigger = event.currentTarget;
        const panel = getPanelFromTrigger(trigger);
        if (!panel) {
            return;
        }
        openPanel(panel, trigger);
    };

    const handleTriggerMouseLeave = (event) => {
        const trigger = event.currentTarget;
        const panel = getPanelFromTrigger(trigger);
        if (!panel) {
            return;
        }
        const related = event.relatedTarget;
        if (related && panel.contains(related)) {
            return;
        }
        closePanel(panel, trigger);
    };

    const handlePanelMouseLeave = (event) => {
        const related = event.relatedTarget;
        if (related && (event.currentTarget.contains(related) || (activeTrigger && activeTrigger.contains(related)))) {
            return;
        }
        closePanel(event.currentTarget, activeTrigger);
    };

    const handleDocumentClick = (event) => {
        if (!activePanel) {
            return;
        }
        if (event.target.closest('[data-nav-panel]') || event.target.closest('[data-nav-trigger]')) {
            return;
        }
        closePanel(activePanel, activeTrigger);
    };

    const handleTriggerKeyDown = (event) => {
        const trigger = event.currentTarget;
        const triggers = Array.from(nav.querySelectorAll('[data-nav-trigger], [data-nav-link]'));
        const currentIndex = triggers.indexOf(trigger);
        switch (event.key) {
            case 'ArrowRight':
                event.preventDefault();
                if (currentIndex > -1) {
                    const next = triggers[(currentIndex + 1) % triggers.length];
                    next.focus();
                }
                break;
            case 'ArrowLeft':
                event.preventDefault();
                if (currentIndex > -1) {
                    const prev = triggers[(currentIndex - 1 + triggers.length) % triggers.length];
                    prev.focus();
                }
                break;
            case 'ArrowDown': {
                const panel = getPanelFromTrigger(trigger);
                if (panel) {
                    event.preventDefault();
                    openPanel(panel, trigger);
                    const firstLink = panel.querySelector('[data-panel-link]');
                    if (firstLink) {
                        firstLink.focus();
                    }
                }
                break;
            }
            case 'Escape': {
                const panel = getPanelFromTrigger(trigger);
                if (panel) {
                    closePanel(panel, trigger);
                }
                break;
            }
            case 'Home':
                event.preventDefault();
                triggers[0]?.focus();
                break;
            case 'End':
                event.preventDefault();
                triggers[triggers.length - 1]?.focus();
                break;
            default:
                break;
        }
    };

    const handlePanelKeyDown = (event) => {
        const panel = event.currentTarget;
        const links = Array.from(panel.querySelectorAll('[data-panel-link]'));
        if (!links.length) {
            return;
        }
        const currentIndex = links.indexOf(doc.activeElement);
        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                links[(currentIndex + 1) % links.length].focus();
                break;
            case 'ArrowUp':
                event.preventDefault();
                links[(currentIndex - 1 + links.length) % links.length].focus();
                break;
            case 'Home':
                event.preventDefault();
                links[0].focus();
                break;
            case 'End':
                event.preventDefault();
                links[links.length - 1].focus();
                break;
            case 'Escape':
                event.preventDefault();
                if (activeTrigger) {
                    closePanel(panel, activeTrigger);
                    activeTrigger.focus();
                }
                break;
            case 'ArrowLeft':
            case 'ArrowRight':
                event.preventDefault();
                if (activeTrigger) {
                    closePanel(panel, activeTrigger);
                    const triggers = Array.from(nav.querySelectorAll('[data-nav-trigger], [data-nav-link]'));
                    const currentTriggerIndex = triggers.indexOf(activeTrigger);
                    if (currentTriggerIndex > -1) {
                        const delta = event.key === 'ArrowRight' ? 1 : -1;
                        const nextTrigger = triggers[(currentTriggerIndex + delta + triggers.length) % triggers.length];
                        nextTrigger.focus();
                    }
                }
                break;
            default:
                break;
        }
    };

    const initialiseDesktopNav = () => {
        if (!nav) {
            return;
        }
        const triggers = Array.from(nav.querySelectorAll('[data-nav-trigger]'));
        triggers.forEach((trigger) => {
            trigger.addEventListener('click', handleTriggerClick);
            trigger.addEventListener('mouseenter', handleTriggerMouseEnter);
            trigger.addEventListener('mouseleave', handleTriggerMouseLeave);
            trigger.addEventListener('focus', handleTriggerMouseEnter);
            trigger.addEventListener('blur', (event) => {
                const related = event.relatedTarget;
                const panel = getPanelFromTrigger(trigger);
                if (panel && related && panel.contains(related)) {
                    return;
                }
                closePanel(panel, trigger);
            });
            trigger.addEventListener('keydown', handleTriggerKeyDown);
        });
        const links = Array.from(nav.querySelectorAll('[data-nav-link]'));
        links.forEach((link) => {
            link.addEventListener('keydown', handleTriggerKeyDown);
        });
        navPanels.forEach((panel) => {
            panel.addEventListener('mouseleave', handlePanelMouseLeave);
            panel.addEventListener('keydown', handlePanelKeyDown);
            panel.addEventListener('focusout', (event) => {
                const related = event.relatedTarget;
                if (!panel.contains(related) && related !== activeTrigger) {
                    closePanel(panel, activeTrigger);
                }
            });
        });
        doc.addEventListener('click', handleDocumentClick);
    };

    const getFocusableChildren = (container) => {
        return Array.from(container.querySelectorAll(focusableSelectors)).filter((element) => !element.hasAttribute('disabled') && element.offsetParent !== null);
    };

    const trapFocus = (event) => {
        if (event.key !== 'Tab' || !navDrawer?.classList.contains('is-open')) {
            return;
        }
        const focusable = getFocusableChildren(navDrawer);
        if (!focusable.length) {
            return;
        }
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && doc.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && doc.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    };

    const openDrawer = () => {
        if (!navDrawer) {
            return;
        }
        lastFocusedBeforeDrawer = doc.activeElement;
        navDrawer.classList.add('is-open');
        navDrawer.removeAttribute('hidden');
        navToggle?.setAttribute('aria-expanded', 'true');
        doc.body.classList.add('body--locked');
        focusTrapListener = trapFocus;
        doc.addEventListener('keydown', focusTrapListener);
        const focusable = getFocusableChildren(navDrawer);
        if (focusable.length) {
            focusable[0].focus();
        }
    };

    const closeDrawer = () => {
        if (!navDrawer) {
            return;
        }
        navDrawer.classList.remove('is-open');
        navDrawer.setAttribute('hidden', '');
        navToggle?.setAttribute('aria-expanded', 'false');
        doc.body.classList.remove('body--locked');
        if (focusTrapListener) {
            doc.removeEventListener('keydown', focusTrapListener);
            focusTrapListener = null;
        }
        if (lastFocusedBeforeDrawer instanceof HTMLElement) {
            lastFocusedBeforeDrawer.focus();
        }
    };

    const initialiseDrawer = () => {
        if (!navDrawer || !navToggle) {
            return;
        }
        navToggle.addEventListener('click', () => {
            if (navDrawer.classList.contains('is-open')) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });
        drawerOverlay?.addEventListener('click', closeDrawer);
        drawerClose?.addEventListener('click', closeDrawer);
        doc.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && navDrawer.classList.contains('is-open')) {
                event.preventDefault();
                closeDrawer();
            }
        });
        const accordions = Array.from(navDrawer.querySelectorAll('[data-drawer-accordion]'));
        accordions.forEach((button) => {
            const controls = button.getAttribute('aria-controls');
            const panel = controls ? doc.getElementById(controls) : null;
            button.addEventListener('click', () => {
                const expanded = button.getAttribute('aria-expanded') === 'true';
                button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                if (panel) {
                    if (expanded) {
                        panel.classList.remove('is-open');
                        panel.setAttribute('hidden', '');
                    } else {
                        panel.classList.add('is-open');
                        panel.removeAttribute('hidden');
                    }
                }
            });
        });
    };

    const initialiseAccountMenu = () => {
        if (!accountMenu) {
            return;
        }
        const trigger = accountMenu.querySelector('.site-nav__account-trigger');
        const panel = accountMenu.querySelector('.site-nav__account-panel');
        if (!trigger || !panel) {
            return;
        }
        trigger.addEventListener('click', () => {
            const isOpen = panel.classList.contains('is-open');
            if (isOpen) {
                panel.classList.remove('is-open');
                panel.setAttribute('hidden', '');
                trigger.setAttribute('aria-expanded', 'false');
            } else {
                panel.classList.add('is-open');
                panel.removeAttribute('hidden');
                trigger.setAttribute('aria-expanded', 'true');
                const firstLink = panel.querySelector('a');
                if (firstLink) {
                    firstLink.focus();
                }
            }
        });
        trigger.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                panel.classList.remove('is-open');
                panel.setAttribute('hidden', '');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
        panel.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                panel.classList.remove('is-open');
                panel.setAttribute('hidden', '');
                trigger.setAttribute('aria-expanded', 'false');
                trigger.focus();
            }
        });
        doc.addEventListener('click', (event) => {
            if (!accountMenu.contains(event.target)) {
                panel.classList.remove('is-open');
                panel.setAttribute('hidden', '');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    };

    const applyTheme = (preference) => {
        let next = preference;
        if (!next) {
            next = 'system';
        }
        let resolved = next;
        if (next === 'system') {
            resolved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        root.dataset.theme = resolved;
        root.dataset.themePreference = next;
        themeToggles.forEach((button) => {
            const isDark = resolved === 'dark';
            button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            button.setAttribute('data-theme-state', next);
        });
    };

    const cycleTheme = () => {
        const current = root.dataset.themePreference || localStorage.getItem('theme') || 'system';
        const sequence = ['light', 'dark', 'system'];
        const index = sequence.indexOf(current);
        const nextPreference = sequence[(index + 1) % sequence.length];
        if (nextPreference === 'system') {
            localStorage.removeItem('theme');
        } else {
            localStorage.setItem('theme', nextPreference);
        }
        applyTheme(nextPreference);
    };

    const initialiseTheme = () => {
        if (!themeToggles.length) {
            return;
        }
        const stored = localStorage.getItem('theme');
        const initialPreference = stored || 'system';
        applyTheme(initialPreference);
        themeToggles.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                cycleTheme();
            });
        });
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            const preference = root.dataset.themePreference || 'system';
            if (preference === 'system') {
                applyTheme('system');
            }
        });
    };

    const initialiseSearch = () => {
        searchForms.forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const input = form.querySelector('input[name="q"]');
                if (input) {
                    console.info('Search submitted:', input.value);
                }
            });
        });
    };

    const initialiseStickyHeader = () => {
        if (!header) {
            return;
        }
        const updateState = () => {
            if (window.scrollY > 8) {
                header.classList.add('is-sticky');
            } else {
                header.classList.remove('is-sticky');
            }
        };
        updateState();
        window.addEventListener('scroll', updateState, { passive: true });
    };

    const initialiseKeyboardClose = () => {
        doc.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                if (activePanel && activeTrigger) {
                    closePanel(activePanel, activeTrigger);
                }
            }
        });
    };

    const init = () => {
        initialiseDesktopNav();
        initialiseDrawer();
        initialiseAccountMenu();
        initialiseTheme();
        initialiseSearch();
        initialiseStickyHeader();
        initialiseKeyboardClose();
    };

    if (doc.readyState === 'loading') {
        doc.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
