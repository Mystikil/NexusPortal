# Layout workspace

Place your HTML template fragments here before running `python tools/apply_layout.py`.

```
layout/
├── header.html      # Optional: replaces the markup between layout:header markers
├── footer.html      # Optional: replaces the markup between layout:footer markers
├── assets/          # Optional: copied into the main assets/ directory
└── pages/
    ├── index.html   # Optional: replaces the layout:content region inside index.php
    └── ...
```

Supported placeholders:

- `{{ SITE_NAME }}` — outputs the configured site title.
- `{{ PAGE_TITLE }}` — outputs the per-page title (falls back to "Game Portal").
- `{{ SERVER_STATUS }}` — injects the dynamic server status badge.
- `{{ NAVIGATION }}` — injects the PHP navigation menu with login/admin logic.
- `{{ SITE_LINK }}` — outputs the header logo link back to the homepage.
- `{{ CURRENT_YEAR }}` — prints the current year (footer).
- `{{ FOOTER_SCRIPTS }}` — inserts the default JavaScript include.
- `{{ BASE_PATH }}` — resolves to `/N1`.
- `{{ ASSET_PATH }}` — resolves to `/N1/assets`.

Any placeholders that are left out will not be added automatically—you can
always write raw PHP inside your template files if you need more control.
