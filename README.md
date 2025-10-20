# Nexus One Web Portal

Nexus One is a neon-soaked control center for Devnexus worlds. It provides a
modern experience for account creation, player management, and community news
while integrating with the official game schema.

## Features

- **Stylized landing page** with live statistics for accounts, characters, and
  online players.
- **Account registration and login** wired to the existing `accounts` table
  using the classic SHA-1 hashing format (40 character hex digest).
- **Player dashboard** that surfaces account metadata and associated characters
  from the `players` table.
- **Administrator console** (accounts with `type >= 3`) with real-time world
  metrics plus a news broadcaster with deletion controls.
- **Community news feed** backed by the `site_news` table (auto-provisioned if
  missing).
- **In-browser character management** to create, rename, or delete players
  without leaving the portal.
- **Public highscores, guild directory, and death feed** sourced directly from
  the game schema for community insights.
- **Character lookup profiles** with live status indicators and recent death
  history for public viewing.

## Getting Started

1. Configure the database credentials in [`config.php`](./config.php) or via the
   `DEVNEXUS_DB_*` environment variables. Optional server heartbeat settings are
   available through `DEVNEXUS_GAME_HOST`, `DEVNEXUS_GAME_PORT`, and
   `DEVNEXUS_GAME_TIMEOUT` for the status indicator in the site header.
2. Deploy the `N1` directory to your PHP-enabled web server.
3. Ensure the existing Devnexus schema is installed (see the SQL bundle provided
   with your server). The portal will automatically create the `site_news`
   table when needed.
4. Visit `/N1/register.php` to create a new account or `/N1/login.php` to sign
   in.

## Administrator Access

- Promote an account by setting the `type` column in the `accounts` table to
  `3` (or higher). Admin accounts unlock the `/N1/admin/index.php` control
  center.
- Admins can push updates to the community feed, review statistics, and purge
  news posts.

## Styling & Assets

All custom styling lives inside [`assets/css/style.css`](./assets/css/style.css)
and JavaScript behaviour in [`assets/js/app.js`](./assets/js/app.js). Fonts are
sourced from Google Fonts (`Orbitron` and `Rubik`).

## Responsive Navigation Component

The repository now ships with a reusable navigation system that can be dropped
into any PHP/XAMPP deployment.

### Installation

1. Copy the new assets into your project root (paths are relative to this
   repository):
   - [`includes/nav.php`](./includes/nav.php)
   - [`public/css/nav.css`](./public/css/nav.css)
   - [`public/js/nav.js`](./public/js/nav.js)
   - [`public/data/menu.json`](./public/data/menu.json)
2. Ensure the CSS and JavaScript are referenced once on pages that render the
   navigation include:

   ```php
   <link rel="stylesheet" href="/public/css/nav.css">
   <script src="/public/js/nav.js" defer></script>
   ```

3. Drop the include where you want the navigation to appear (usually inside
   your global header template):

   ```php
   <?php include __DIR__ . '/includes/nav.php'; ?>
   ```

### Menu data

All menu content is sourced from [`public/data/menu.json`](./public/data/menu.json).
Update this file to change labels, URLs, badges, or icons. Every primary item
supports `panelWidth` (`"container"` or `"full"`), multiple `groups`, and
optional `desc` or `badge` fields per link. Utility items support the `search`
widget, external `link` buttons, and the `theme-toggle` control.

Icon names map directly to the inline SVG sprite in `includes/nav.php`.

### Accessibility & keyboard support

- `<nav role="navigation" aria-label="Primary">` wraps the experience.
- Trigger buttons set `aria-expanded` and `aria-controls` and announce their
  panels.
- Megamenu panels expose `role="menu"` with `role="menuitem"` links.
- Desktop keyboard shortcuts:
  - `Tab`/`Shift+Tab` moves through triggers and, when open, panel links.
  - `←`/`→` moves between primary triggers.
  - `↑`/`↓` cycles through links inside an open panel.
  - `Esc` closes the current panel and returns focus.
- The mobile drawer traps focus, closes with `Esc`, and restores focus to the
  hamburger toggle.
- Search overlay and drawer buttons mirror the same escape-and-restore
  behaviour.

### Testing checklist

- Desktop hover/focus opens a single panel at a time.
- `Esc` closes the panel and restores focus to its trigger.
- Keyboard navigation obeys the arrow key map above.
- Mobile drawer slides in, traps focus, supports accordion expansion, and
  closes with `Esc` while restoring focus.
- Screen readers announce `expanded`/`collapsed` states on buttons.
- Works in current Chrome, Firefox, Edge, and Safari.

### Applying HTML templates automatically

Drop your HTML theme inside the [`layout/`](./layout/) directory and run the
[`tools/apply_layout.py`](./tools/apply_layout.py) helper to sync it with the
live PHP pages.

```
layout/
├── header.html
├── footer.html
└── pages/
    ├── index.html
    ├── news.html
    └── ...
```

- `header.html` and `footer.html` should only contain the markup you want
  between the `layout:header`/`layout:footer` markers already present in the
  PHP partials. Optional placeholders include `{{ SITE_NAME }}` and
  `{{ PAGE_TITLE }}` (page title), `{{ SERVER_STATUS }}` (status badge),
  `{{ NAVIGATION }}` (dynamic menu), `{{ SITE_LINK }}` (logo link),
  `{{ CURRENT_YEAR }}` and `{{ FOOTER_SCRIPTS }}`.
- Place page-specific fragments inside `layout/pages/*.html`. Each filename must
  match an existing PHP page (for example `index.html` -> `index.php`). The
  script replaces the content between the `<!-- layout:content:start -->` and
  `<!-- layout:content:end -->` markers that now wrap every public page.
- Static paths can use `{{ BASE_PATH }}` (`/N1`) and `{{ ASSET_PATH }}`
  (`/N1/assets`) for convenience.

Apply the template with:

```bash
python tools/apply_layout.py
```

Add `--dry-run` to preview the changes or `--no-assets` to skip copying files
from `layout/assets/` into the project’s [`assets/`](./assets/) directory.

## Security Notes

- Passwords are stored using SHA-1 hashes to remain compatible with the live
  game schema. Consider upgrading to stronger hashing (e.g., bcrypt) if you are
  able to adjust the backend schema.
- CSRF tokens are not implemented in this prototype. If you expose the admin
  area to the public internet, add CSRF protection and access controls as
  needed.
# NexusPortal
