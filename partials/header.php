<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/server.php';
$settings = require __DIR__ . '/../config.php';
$menuData = require __DIR__ . '/../includes/menu.php';
$siteName = $settings['site']['name'];
$serverStatus = getServerStatus($settings);
$serverOnline = !empty($serverStatus['online']);
$statusTitle = $serverOnline ? 'Game server is online' : 'Game server is offline';
$statusTitle .= sprintf(' (%s:%s)', $serverStatus['host'], $serverStatus['port']);

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$slugify = static function (string $value, string $fallback = 'item'): string {
    $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $value)));
    $slug = trim($slug, '-');
    return $slug !== '' ? $slug : $fallback;
};

$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$currentPath = strtok($currentPath, '?') ?: '/';

$primaryMenu = array_values(array_filter($menuData, static function (array $item): bool {
    return empty($item['utility']) && ($item['type'] ?? '') === '';
}));

$utilityMenu = array_values(array_filter($menuData, static function (array $item): bool {
    return !empty($item['utility']) || ($item['type'] ?? '') !== '';
}));

$accountConfig = null;
$searchConfig = null;
$themeToggleConfig = null;

foreach ($utilityMenu as $utilityItem) {
    $type = $utilityItem['type'] ?? '';
    if ($type === 'search') {
        $searchConfig = $utilityItem;
        continue;
    }
    if ($type === 'theme-toggle') {
        $themeToggleConfig = $utilityItem;
        continue;
    }
    if (($utilityItem['label'] ?? '') === 'Account') {
        $accountConfig = $utilityItem;
    }
}

$accountLinks = [];
if ($accountConfig) {
    $accountLinks = isLoggedIn()
        ? ($accountConfig['children']['auth'] ?? [])
        : ($accountConfig['children']['guest'] ?? []);
    if (!isAdmin()) {
        $accountLinks = array_values(array_filter($accountLinks, static function (array $link): bool {
            return empty($link['requires_admin']);
        }));
    }
}

$isActive = static function (array $item) use ($currentPath): bool {
    $key = $item['active_key'] ?? '';
    if ($key === 'index') {
        return in_array($currentPath, ['/', '/N1/', '/N1/index.php'], true);
    }
    if ($key && stripos($currentPath, $key) !== false) {
        return true;
    }
    $href = $item['href'] ?? '';
    if ($href && stripos($currentPath, trim($href)) !== false) {
        return true;
    }
    $children = $item['children'] ?? [];
    foreach ($children as $group) {
        $links = $group['links'] ?? [];
        foreach ($links as $link) {
            $childHref = $link['href'] ?? '';
            if ($childHref && stripos($currentPath, trim($childHref)) !== false) {
                return true;
            }
        }
    }
    return false;
};
?>
<!-- layout:header:start -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($siteName) ?> &bull; <?= e($title ?? 'Game Portal') ?></title>
    <link rel="stylesheet" href="/N1/assets/css/style.css">
    <link rel="stylesheet" href="/N1/public/assets/css/nav.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
    <script defer src="/N1/public/assets/js/nav.js"></script>
</head>
<body>
<header class="site-header" data-site-header>
    <div class="site-header__inner">
        <div class="site-header__brand">
            <button class="site-nav__toggle" type="button" aria-expanded="false" aria-controls="site-nav-drawer" data-nav-toggle>
                <span class="site-nav__toggle-line" aria-hidden="true"></span>
                <span class="site-nav__toggle-line" aria-hidden="true"></span>
                <span class="site-nav__toggle-line" aria-hidden="true"></span>
                <span class="sr-only">Open menu</span>
            </button>
            <a class="site-logo" href="/N1/index.php"><?= e($siteName) ?></a>
        </div>
        <nav class="site-nav" role="navigation" aria-label="Primary" data-site-nav>
            <ul class="site-nav__list" role="menubar" data-nav-list>
                <?php foreach ($primaryMenu as $index => $item):
                    $label = $item['label'] ?? '';
                    $hasChildren = !empty($item['children']) && is_array($item['children']);
                    $triggerId = 'nav-trigger-' . $slugify($label, 'trigger-' . ($index + 1));
                    $panelId = 'nav-panel-' . $slugify($label, 'panel-' . ($index + 1));
                    $itemClasses = ['site-nav__item'];
                    if ($hasChildren) {
                        $itemClasses[] = 'site-nav__item--has-children';
                    }
                    if ($isActive($item)) {
                        $itemClasses[] = 'is-active';
                    }
                    $itemClassAttr = implode(' ', $itemClasses);
                    ?>
                    <li class="<?= $escape($itemClassAttr); ?>" role="none">
                        <?php if ($hasChildren): ?>
                            <button
                                id="<?= $escape($triggerId); ?>"
                                class="site-nav__link"
                                type="button"
                                role="menuitem"
                                data-nav-trigger
                                aria-haspopup="true"
                                aria-expanded="false"
                                aria-controls="<?= $escape($panelId); ?>"
                            >
                                <span><?= $escape($label); ?></span>
                            </button>
                        <?php else: ?>
                            <a
                                id="<?= $escape($triggerId); ?>"
                                class="site-nav__link"
                                href="<?= $escape($item['href'] ?? '#'); ?>"
                                role="menuitem"
                                data-nav-link
                            >
                                <span><?= $escape($label); ?></span>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <div class="site-header__actions">
            <div class="server-status <?= $serverOnline ? 'online' : 'offline' ?>" role="status" aria-live="polite" title="<?= e($statusTitle) ?>">
                <span class="indicator" aria-hidden="true"></span>
                <span><?= $serverOnline ? 'Server Online' : 'Server Offline' ?></span>
            </div>
            <div class="site-nav__utilities">
                <?php if ($searchConfig): ?>
                    <form class="site-nav__search" role="search" data-nav-search>
                        <label class="sr-only" for="site-nav-search">Search</label>
                        <input id="site-nav-search" name="q" type="search" placeholder="<?= $escape($searchConfig['placeholder'] ?? 'Search'); ?>" autocomplete="off">
                        <button type="submit" aria-label="Submit search">
                            <span class="sr-only">Submit</span>
                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16a6.47 6.47 0 0 0 4.23-1.57l.27.28v.79l5 4.99L20.49 19zm-6 0A4.5 4.5 0 1 1 14 9.5 4.5 4.5 0 0 1 9.5 14z" />
                            </svg>
                        </button>
                    </form>
                <?php endif; ?>
                <?php if ($themeToggleConfig !== null): ?>
                    <button class="site-nav__theme-toggle" type="button" data-theme-toggle aria-pressed="false" aria-label="Toggle theme">
                        <svg class="icon icon--sun" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 4a1 1 0 0 1-1-1V1h2v2a1 1 0 0 1-1 1zm0 18a1 1 0 0 1-1-1v-2h2v2a1 1 0 0 1-1 1zm10-10a1 1 0 0 1-1 1h-2v-2h2a1 1 0 0 1 1 1zM5 12a1 1 0 0 1-1 1H2v-2h2a1 1 0 0 1 1 1zm13.66-6.66-1.41 1.41-1.42-1.42 1.41-1.41a1 1 0 0 1 1.42 1.42zM8.17 17.83 6.76 19.24a1 1 0 0 1-1.42-1.42l1.41-1.41 1.42 1.42zm9.66 1.41-1.41-1.41 1.42-1.42 1.41 1.41a1 1 0 1 1-1.42 1.42zM6.76 4.76 5.34 3.34a1 1 0 1 1 1.42-1.42l1.41 1.41-1.41 1.42zM12 8a4 4 0 1 1-4 4 4 4 0 0 1 4-4z" />
                        </svg>
                        <svg class="icon icon--moon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M11.05 2.05a1 1 0 0 1 1.29 1.27A7 7 0 1 0 20.68 12a1 1 0 0 1 1.28-1.29 9 9 0 1 1-10.91-8.66z" />
                        </svg>
                    </button>
                <?php endif; ?>
                <?php if ($accountConfig):
                    $accountPanelId = 'account-menu-panel';
                    ?>
                    <div class="site-nav__account" data-account-menu>
                        <button class="btn primary site-nav__account-trigger" type="button" aria-expanded="false" aria-controls="<?= $escape($accountPanelId); ?>">
                            Account
                        </button>
                        <div class="site-nav__account-panel" id="<?= $escape($accountPanelId); ?>" role="menu" hidden>
                            <ul>
                                <?php foreach ($accountLinks as $link): ?>
                                    <li role="none">
                                        <a role="menuitem" href="<?= $escape($link['href'] ?? '#'); ?>">
                                            <span class="site-nav__account-label"><?= $escape($link['label'] ?? ''); ?></span>
                                            <?php if (!empty($link['description'])): ?>
                                                <span class="site-nav__account-description"><?= $escape($link['description']); ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="site-nav__panels" data-nav-panels>
        <?php foreach ($primaryMenu as $index => $item):
            $label = $item['label'] ?? '';
            $groups = $item['children'] ?? [];
            if (empty($groups) || !is_array($groups)) {
                continue;
            }
            $panelId = 'nav-panel-' . $slugify($label, 'panel-' . ($index + 1));
            $triggerId = 'nav-trigger-' . $slugify($label, 'trigger-' . ($index + 1));
            $isMega = count(array_filter($groups, static function ($group) {
                return !empty($group['heading']);
            })) > 1;
            $panelClasses = ['site-nav__panel'];
            if ($isMega) {
                $panelClasses[] = 'site-nav__panel--mega';
            }
            ?>
            <div
                id="<?= $escape($panelId); ?>"
                class="<?= $escape(implode(' ', $panelClasses)); ?>"
                role="menu"
                aria-labelledby="<?= $escape($triggerId); ?>"
                aria-hidden="true"
                hidden
                data-nav-panel
            >
                <div class="site-nav__panel-inner">
                    <?php foreach ($groups as $groupIndex => $group):
                        $links = $group['links'] ?? [];
                        if (empty($links) || !is_array($links)) {
                            continue;
                        }
                        $heading = $group['heading'] ?? '';
                        $description = $group['description'] ?? '';
                        ?>
                        <div class="site-nav__panel-group">
                            <?php if ($heading): ?>
                                <p class="site-nav__panel-heading"><?= $escape($heading); ?></p>
                            <?php endif; ?>
                            <?php if ($description): ?>
                                <p class="site-nav__panel-description"><?= $escape($description); ?></p>
                            <?php endif; ?>
                            <ul class="site-nav__panel-list" role="none">
                                <?php foreach ($links as $linkIndex => $link):
                                    $linkId = $triggerId . '-' . $groupIndex . '-' . $linkIndex;
                                    ?>
                                    <li role="none">
                                        <a
                                            id="<?= $escape($linkId); ?>"
                                            class="site-nav__panel-link"
                                            href="<?= $escape($link['href'] ?? '#'); ?>"
                                            role="menuitem"
                                            tabindex="-1"
                                            data-panel-link
                                        >
                                            <span class="site-nav__panel-link-label"><?= $escape($link['label'] ?? ''); ?></span>
                                            <?php if (!empty($link['description'])): ?>
                                                <span class="site-nav__panel-link-description"><?= $escape($link['description']); ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</header>
<div class="site-nav__drawer" id="site-nav-drawer" hidden data-nav-drawer>
    <div class="site-nav__drawer-overlay" data-drawer-overlay></div>
    <aside class="site-nav__drawer-panel" role="dialog" aria-modal="true" aria-label="Mobile navigation">
        <div class="site-nav__drawer-header">
            <span>Navigation</span>
            <button type="button" class="site-nav__drawer-close" data-drawer-close aria-label="Close menu">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="site-nav__drawer-content" data-drawer-content>
            <?php if ($searchConfig): ?>
                <form class="site-nav__drawer-search" role="search" data-nav-search-mobile>
                    <label class="sr-only" for="site-nav-search-mobile">Search</label>
                    <input id="site-nav-search-mobile" name="q" type="search" placeholder="<?= $escape($searchConfig['placeholder'] ?? 'Search'); ?>" autocomplete="off">
                    <button type="submit" aria-label="Submit search">
                        <span class="sr-only">Submit</span>
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16a6.47 6.47 0 0 0 4.23-1.57l.27.28v.79l5 4.99L20.49 19zm-6 0A4.5 4.5 0 1 1 14 9.5 4.5 4.5 0 0 1 9.5 14z" />
                        </svg>
                    </button>
                </form>
            <?php endif; ?>
            <ul class="site-nav__drawer-list">
                <?php foreach ($primaryMenu as $index => $item):
                    $label = $item['label'] ?? '';
                    $hasChildren = !empty($item['children']) && is_array($item['children']);
                    $accordionId = 'drawer-accordion-' . $slugify($label, 'accordion-' . ($index + 1));
                    ?>
                    <li>
                        <?php if ($hasChildren): ?>
                            <button
                                class="site-nav__drawer-accordion"
                                type="button"
                                aria-expanded="false"
                                aria-controls="<?= $escape($accordionId); ?>"
                                data-drawer-accordion
                            >
                                <span><?= $escape($label); ?></span>
                                <span class="site-nav__drawer-icon" aria-hidden="true"></span>
                            </button>
                            <div class="site-nav__drawer-panel-links" id="<?= $escape($accordionId); ?>" hidden>
                                <ul>
                                    <?php foreach (($item['children'] ?? []) as $group):
                                        $links = $group['links'] ?? [];
                                        foreach ($links as $link): ?>
                                            <li>
                                                <a href="<?= $escape($link['href'] ?? '#'); ?>">
                                                    <span><?= $escape($link['label'] ?? ''); ?></span>
                                                    <?php if (!empty($link['description'])): ?>
                                                        <span class="site-nav__drawer-description"><?= $escape($link['description']); ?></span>
                                                    <?php endif; ?>
                                                </a>
                                            </li>
                                        <?php endforeach;
                                    endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a class="site-nav__drawer-link" href="<?= $escape($item['href'] ?? '#'); ?>">
                                <span><?= $escape($label); ?></span>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($accountLinks): ?>
                <div class="site-nav__drawer-section">
                    <p class="site-nav__drawer-heading">Account</p>
                    <ul>
                        <?php foreach ($accountLinks as $link): ?>
                            <li>
                                <a href="<?= $escape($link['href'] ?? '#'); ?>">
                                    <span><?= $escape($link['label'] ?? ''); ?></span>
                                    <?php if (!empty($link['description'])): ?>
                                        <span class="site-nav__drawer-description"><?= $escape($link['description']); ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if ($themeToggleConfig !== null): ?>
                <div class="site-nav__drawer-theme">
                    <button class="site-nav__drawer-theme-toggle" type="button" data-theme-toggle aria-pressed="false">
                        <span class="site-nav__drawer-theme-icon" aria-hidden="true"></span>
                        <span>Toggle theme</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </aside>
</div>
<main>
<!-- layout:header:end -->
