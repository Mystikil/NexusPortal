# Megamenu Navigation Demo

This package contains a responsive, accessible megamenu that can be dropped into any vanilla HTML project. The demo
(`index.html`) loads data from `data/menu.json`, renders the navigation, and showcases both desktop and mobile
interactions.

## Getting started

1. Copy the `megamenu/` folder (or the individual files) into your web root.
2. Open `index.html` in your browser, or include the navigation assets in your existing layout:
   ```html
   <link rel="stylesheet" href="/css/megamenu.css" />
   <div id="mega-nav-root"></div>
   <script defer src="/js/megamenu.js"></script>
   ```
3. Ensure `data/menu.json` is served from the same origin so the script can fetch it.

To make the navbar sticky on scroll, add the `.is-sticky` class to the `<nav class="mega-nav">` element. The script
applies it by default in the demo, but you can remove the class if you prefer a static header.

## Customising menu content

Menu structure, descriptions, icons, and badges are defined in `data/menu.json`. Update the JSON to match your
information architecture:

- `brand.logoText` and `brand.href` control the logo link.
- `primary` holds each top-level section. Set `panelWidth` to `container` or `full` for panel sizing.
- Within each `group`, add `items` with `label`, `href`, optional `desc`, `icon`, and `badge` fields.
- `utility` supports a search field, utility links, and a theme toggle button.

The navigation expects icon names to match the SVG symbols declared in `index.html`. Add new `<symbol>` definitions as
needed or use the `icon-placeholder` fallback.

## Accessibility notes

- Semantic roles and ARIA attributes expose the navigation structure to assistive technology.
- Buttons that open panels/drawers update `aria-expanded` and manage focus when opened or closed.
- Keyboard interactions:
  - **Tab / Shift+Tab** move between triggers, panels, and controls.
  - **Arrow Left / Right** move between top-level items.
  - **Arrow Down / Up** move within an open panel.
  - **Esc** closes the active panel or mobile drawer and restores focus to the trigger.
- The mobile drawer traps focus while open and restores focus to the opener when closed.
- `prefers-reduced-motion` is respected to minimise motion-heavy transitions.
- The theme toggle stores the user’s choice in `localStorage` and falls back to the system preference.

## Testing checklist

- [ ] Tab through the navbar: focus order is logical; Esc closes panels/drawer.
- [ ] Arrow keys navigate between triggers and items as expected.
- [ ] Screen reader announces open/close states and panel labels.
- [ ] Mobile drawer traps focus and restores it when closed.
- [ ] Works in the latest Chrome, Firefox, Edge, and Safari.
