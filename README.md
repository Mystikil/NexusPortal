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

## Security Notes

- Passwords are stored using SHA-1 hashes to remain compatible with the live
  game schema. Consider upgrading to stronger hashing (e.g., bcrypt) if you are
  able to adjust the backend schema.
- CSRF tokens are not implemented in this prototype. If you expose the admin
  area to the public internet, add CSRF protection and access controls as
  needed.
# NexusPortal
