#!/usr/bin/env python3
"""Synchronise HTML layouts with the PHP front-end.

Drop HTML fragments in ``layout`` (header, footer, and individual pages) and
run this script to overwrite the marked regions in the live PHP files.
"""
from __future__ import annotations

import argparse
import shutil
import sys
from pathlib import Path
from typing import Dict

REPO_ROOT = Path(__file__).resolve().parent.parent
DEFAULT_LAYOUT_DIR = REPO_ROOT / "layout"
PARTIALS_DIR = REPO_ROOT / "partials"
ASSETS_DIR = REPO_ROOT / "assets"

HEADER_START = "<!-- layout:header:start -->"
HEADER_END = "<!-- layout:header:end -->"
FOOTER_START = "<!-- layout:footer:start -->"
FOOTER_END = "<!-- layout:footer:end -->"
CONTENT_START = "<!-- layout:content:start -->"
CONTENT_END = "<!-- layout:content:end -->"

COMMON_PLACEHOLDERS: Dict[str, str] = {
    "{{ BASE_PATH }}": "<?= e(siteUrl()) ?>",
    "{{ ASSET_PATH }}": "<?= e(assetUrl('')) ?>",
}

HEADER_PLACEHOLDERS: Dict[str, str] = {
    "{{ SITE_NAME }}": "<?= e($siteName) ?>",
    "{{ PAGE_TITLE }}": "<?= e($title ?? 'Game Portal') ?>",
    "{{ SERVER_STATUS }}": (
        "<div class=\"server-status <?= $serverOnline ? 'online' : 'offline' ?>\" "
        "role=\"status\" aria-live=\"polite\" title=\"<?= e($statusTitle) ?>\">\n"
        "    <span class=\"indicator\" aria-hidden=\"true\"></span>\n"
        "    <span><?= $serverOnline ? 'Server Online' : 'Server Offline' ?></span>\n"
        "</div>"
    ),
    "{{ NAVIGATION }}": (
        "<nav class=\"main-nav\" aria-label=\"Main navigation\">\n"
        "    <button class=\"nav-toggle\" aria-controls=\"primary-menu\" aria-expanded=\"false\">\n"
        "        <span></span><span></span><span></span>\n"
        "    </button>\n"
        "    <?php\n"
        "    $menuSections = [\n"
        "        'Discover' => [\n"
        "            ['label' => 'Home', 'href' => siteUrl()],\n"
        "            ['label' => 'News', 'href' => siteUrl('news.php')],\n"
        "            ['label' => 'Highscores', 'href' => siteUrl('highscores.php')],\n"
        "        ],\n"
        "        'Intel & Community' => [\n"
        "            ['label' => 'Character Lookup', 'href' => siteUrl('character.php')],\n"
        "            ['label' => 'Guilds', 'href' => siteUrl('guilds.php')],\n"
        "            ['label' => 'Deaths', 'href' => siteUrl('deaths.php')],\n"
        "        ],\n"
        "    ];\n"
        "\n"
        "    if (isLoggedIn()) {\n"
        "        $accountLinks = [\n"
        "            ['label' => 'Account Dashboard', 'href' => siteUrl('dashboard.php')],\n"
        "            ['label' => 'My Characters', 'href' => siteUrl('characters.php')],\n"
        "        ];\n"
        "\n"
        "        if (isAdmin()) {\n"
        "            $accountLinks[] = ['label' => 'Admin Control', 'href' => siteUrl('admin/index.php')];\n"
        "        }\n"
        "\n"
        "        $accountLinks[] = ['label' => 'Logout', 'href' => siteUrl('logout.php')];\n"
        "\n"
        "        $menuSections['Command Console'] = $accountLinks;\n"
        "    } else {\n"
        "        $menuSections['Join the Nexus'] = [\n"
        "            ['label' => 'Create Account', 'href' => siteUrl('register.php'), 'class' => 'cta'],\n"
        "            ['label' => 'Login', 'href' => siteUrl('login.php')],\n"
        "        ];\n"
        "    }\n"
        "    ?>\n"
        "    <ul id=\"primary-menu\" class=\"menu-sections\">\n"
        "        <?php foreach ($menuSections as $sectionTitle => $links): ?>\n"
        "            <li class=\"menu-section\">\n"
        "                <span class=\"section-title\"><?= e($sectionTitle) ?></span>\n"
        "                <ul class=\"menu-links\">\n"
        "                    <?php foreach ($links as $link): ?>\n"
        "                        <?php\n"
        "                        $linkClass = $link['class'] ?? '';\n"
        "                        $linkPath = parse_url($link['href'], PHP_URL_PATH) ?: '';\n"
        "                        $isActive = rtrim($currentPath, '/') === rtrim($linkPath, '/');\n"
        "                        if ($isActive) {\n"
        "                            $linkClass = trim($linkClass . ' active');\n"
        "                        }\n"
        "                        ?>\n"
        "                        <li>\n"
        "                            <a href=\"<?= e($link['href']) ?>\"<?php if ($linkClass !== ''): ?> class=\"<?= e($linkClass) ?>\"<?php endif; ?><?php if ($isActive): ?> aria-current=\"page\"<?php endif; ?>><?= e($link['label']) ?></a>\n"
        "                        </li>\n"
        "                    <?php endforeach; ?>\n"
        "                </ul>\n"
        "            </li>\n"
        "        <?php endforeach; ?>\n"
        "    </ul>\n"
        "</nav>"
    ),
    "{{ SITE_LINK }}": "<a href=\"<?= e(siteUrl()) ?>\"><?= e($siteName) ?></a>",
}

FOOTER_PLACEHOLDERS: Dict[str, str] = {
    "{{ CURRENT_YEAR }}": "<?= date('Y') ?>",
    "{{ FOOTER_SCRIPTS }}": "<script src=\"<?= e(assetUrl('js/app.js')) ?>\"></script>",
}


class LayoutError(RuntimeError):
    """Raised when the template cannot be applied."""


def replace_placeholders(content: str, extra: Dict[str, str] | None = None) -> str:
    placeholders = dict(COMMON_PLACEHOLDERS)
    if extra:
        placeholders.update(extra)
    for needle, replacement in placeholders.items():
        content = content.replace(needle, replacement)
    return content


def update_between_markers(
    path: Path,
    start_marker: str,
    end_marker: str,
    new_content: str,
    *,
    dry_run: bool,
) -> bool:
    text = path.read_text(encoding="utf-8")
    start_index = text.find(start_marker)
    if start_index == -1:
        raise LayoutError(f"Marker '{start_marker}' not found in {path.relative_to(REPO_ROOT)}")
    start_index += len(start_marker)
    end_index = text.find(end_marker, start_index)
    if end_index == -1:
        raise LayoutError(f"Marker '{end_marker}' not found in {path.relative_to(REPO_ROOT)}")

    normalized = new_content.strip("\n")
    replacement = "\n" + normalized + "\n"
    updated = text[:start_index] + replacement + text[end_index:]

    if updated == text:
        return False

    if dry_run:
        print(f"[dry-run] Would update {path.relative_to(REPO_ROOT)}")
        return True

    path.write_text(updated, encoding="utf-8")
    print(f"Updated {path.relative_to(REPO_ROOT)}")
    return True


def apply_header(template_dir: Path, *, dry_run: bool) -> bool:
    template_path = template_dir / "header.html"
    if not template_path.exists():
        return False
    html = template_path.read_text(encoding="utf-8")
    html = replace_placeholders(html, HEADER_PLACEHOLDERS)
    return update_between_markers(
        PARTIALS_DIR / "header.php",
        HEADER_START,
        HEADER_END,
        html,
        dry_run=dry_run,
    )


def apply_footer(template_dir: Path, *, dry_run: bool) -> bool:
    template_path = template_dir / "footer.html"
    if not template_path.exists():
        return False
    html = template_path.read_text(encoding="utf-8")
    html = replace_placeholders(html, FOOTER_PLACEHOLDERS)
    return update_between_markers(
        PARTIALS_DIR / "footer.php",
        FOOTER_START,
        FOOTER_END,
        html,
        dry_run=dry_run,
    )


def apply_pages(template_dir: Path, *, dry_run: bool) -> int:
    pages_dir = template_dir / "pages"
    if not pages_dir.exists():
        return 0

    changed = 0
    for html_path in sorted(pages_dir.glob("*.html")):
        target_name = html_path.stem + ".php"
        target_path = REPO_ROOT / target_name
        if not target_path.exists():
            print(f"Skipping {html_path.name}: no matching {target_name} in project")
            continue
        html = html_path.read_text(encoding="utf-8")
        html = replace_placeholders(html)
        try:
            updated = update_between_markers(
                target_path,
                CONTENT_START,
                CONTENT_END,
                html,
                dry_run=dry_run,
            )
        except LayoutError as exc:
            print(f"Cannot update {target_name}: {exc}")
            continue
        if updated:
            changed += 1
    return changed


def copy_assets(template_dir: Path, *, dry_run: bool) -> int:
    assets_dir = template_dir / "assets"
    if not assets_dir.exists():
        return 0

    files_copied = 0
    for src in sorted(assets_dir.rglob("*")):
        if not src.is_file():
            continue
        relative = src.relative_to(assets_dir)
        destination = ASSETS_DIR / relative
        if dry_run:
            print(f"[dry-run] Would copy {relative} to assets/")
            files_copied += 1
            continue
        destination.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(src, destination)
        print(f"Copied assets/{relative}")
        files_copied += 1
    return files_copied


def parse_args(argv: list[str]) -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--template-dir",
        type=Path,
        default=DEFAULT_LAYOUT_DIR,
        help="Directory containing header.html, footer.html, and pages/ content",
    )
    parser.add_argument(
        "--no-assets",
        action="store_true",
        help="Do not copy assets from the template directory",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Preview the changes without touching the filesystem",
    )
    return parser.parse_args(argv)


def main(argv: list[str]) -> int:
    args = parse_args(argv)
    template_dir = args.template_dir.resolve()
    if not template_dir.exists():
        print(f"Template directory {template_dir} does not exist", file=sys.stderr)
        return 1

    print(f"Using template directory: {template_dir}")
    changes = 0
    try:
        if apply_header(template_dir, dry_run=args.dry_run):
            changes += 1
        if apply_footer(template_dir, dry_run=args.dry_run):
            changes += 1
        changes += apply_pages(template_dir, dry_run=args.dry_run)
    except LayoutError as exc:
        print(exc, file=sys.stderr)
        return 2

    if not args.no_assets:
        changes += copy_assets(template_dir, dry_run=args.dry_run)

    if changes:
        print(f"Layout applied. {changes} item(s) processed.")
    else:
        print("Nothing to update. Did you provide header/footer/pages templates?")
    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv[1:]))
