<?php
$menuPath = __DIR__ . '/../public/data/menu.json';
$menu = [];
if (is_readable($menuPath)) {
    $json = file_get_contents($menuPath);
    $decoded = json_decode($json, true);
    if (is_array($decoded)) {
        $menu = $decoded;
    }
}

if (empty($menu)) {
    return;
}

$brand = $menu['brand'] ?? [];
$primary = $menu['primary'] ?? [];
$utility = $menu['utility'] ?? [];
$searchPlaceholder = 'Search…';
foreach ($utility as $utilItem) {
    if (($utilItem['type'] ?? '') === 'search') {
        $searchPlaceholder = $utilItem['placeholder'] ?? 'Search…';
        break;
    }
}

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$slugify = static function (string $value, string $fallback = 'item'): string {
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $value) ?? ''));
    $slug = trim($slug, '-');
    return $slug !== '' ? $slug : $fallback;
};
?>

<svg xmlns="http://www.w3.org/2000/svg" class="nav-icon-sprite" aria-hidden="true" focusable="false">
    <symbol id="icon-download" viewBox="0 0 24 24">
        <path d="M5 20h14v-2H5v2zm7-18v11.17l3.59-3.58L17 11l-5 5-5-5 1.41-1.41L11 13.17V2h1z" />
    </symbol>
    <symbol id="icon-plug" viewBox="0 0 24 24">
        <path d="M7 2v5h2V2h2v5h2V2h2v5.5a4.5 4.5 0 0 1-4 4.47V22h-2v-10.03A4.5 4.5 0 0 1 7 7.5V2h0z" />
    </symbol>
    <symbol id="icon-user" viewBox="0 0 24 24">
        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-4 0-7 2-7 4v2h14v-2c0-2-3-4-7-4z" />
    </symbol>
    <symbol id="icon-user-plus" viewBox="0 0 24 24">
        <path d="M15 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-7 0a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm0 2c-2.7 0-8 1.34-8 4v2h8.26a6 6 0 0 1-.26-1.5 6 6 0 0 1 .52-2.47A8.34 8.34 0 0 1 8 14zm7 0a7.12 7.12 0 0 0-4.5 1.25A7.37 7.37 0 0 0 13 18.5a7.37 7.37 0 0 0 2.5 5.25H23v-2c0-2.66-5.3-5-9-5zm4-4h2v-2h-2V6h-2v2h-2v2h2v2h2z" />
    </symbol>
    <symbol id="icon-dragon" viewBox="0 0 24 24">
        <path d="M4 4l4 1 4-3 4 3 4-1-1 4 3 4-3 4 1 4-4-1-4 3-4-3-4 1 1-4-3-4 3-4-1-4zm8 3a5 5 0 1 0 5 5 5 5 0 0 0-5-5zm0 2a3 3 0 1 1-3 3 3 3 0 0 1 3-3z" />
    </symbol>
    <symbol id="icon-crown" viewBox="0 0 24 24">
        <path d="M5 16l-2-9 5 4 4-6 4 6 5-4-2 9H5zm-1 4h16v2H4v-2z" />
    </symbol>
    <symbol id="icon-map" viewBox="0 0 24 24">
        <path d="M15 4l-6 2-6-2v16l6 2 6-2 6 2V6l-6-2zm-6 2.47l4-1.33v14.39l-4 1.33zm-2 .01v14.38l-2-.66V5.82zm12 12.43l-2-.66V5.82l2 .66z" />
    </symbol>
    <symbol id="icon-layers" viewBox="0 0 24 24">
        <path d="M12 2L1 7l11 5 9-4.09V14h2V7L12 2zm0 11L1 8v3l11 5 9-4.09V18h2v-7l-11 5z" />
    </symbol>
    <symbol id="icon-hammer" viewBox="0 0 24 24">
        <path d="M3 21l1.5-1.5 7-7 2 2-7 7L3 21zm9.58-9.17l-2.41-2.41 1.08-1.08-2.12-2.12L9.17 7 7.05 4.88l1.41-1.41 2.12 2.12 1.41-1.41 5.66 5.66z" />
    </symbol>
    <symbol id="icon-sparkles" viewBox="0 0 24 24">
        <path d="M11 2l1.5 4.5L17 8l-4.5 1.5L11 14l-1.5-4.5L5 8l4.5-1.5zm7 6l.75 2.25L21 11l-2.25.75L18 14l-.75-2.25L15 11l2.25-.75zm-14 5l1 3 3 1-3 1-1 3-1-3-3-1 3-1z" />
    </symbol>
    <symbol id="icon-coins" viewBox="0 0 24 24">
        <path d="M12 2C7 2 3 3.79 3 6s4 4 9 4 9-1.79 9-4-4-4-9-4zm0 6c-3.87 0-7-1.12-7-2s3.13-2 7-2 7 1.12 7 2-3.13 2-7 2zm0 4c-5 0-9-1.79-9-4v4c0 2.21 4 4 9 4s9-1.79 9-4V8c0 2.21-4 4-9 4zm0 4c-3.87 0-7-1.12-7-2v4c0 2.21 4 4 9 4s9-1.79 9-4v-4c0 2.21-4 4-9 4z" />
    </symbol>
    <symbol id="icon-discord" viewBox="0 0 24 24">
        <path d="M20 5a19.41 19.41 0 0 0-4.89-1.5l-.23.46A18.71 18.71 0 0 0 12 3a18.71 18.71 0 0 0-2.88.96l-.23-.46A19.41 19.41 0 0 0 4 5C1.78 8.27 1 11.45 1 14.58a19.5 19.5 0 0 0 6.13 1.95l.84-1.12a12.75 12.75 0 0 1-1.91-.92l.46-.35c3.72 1.73 7.79 1.73 11.49 0l.46.35a12.21 12.21 0 0 1-1.91.92l.84 1.12A19.5 19.5 0 0 0 23 14.58C23 11.45 22.22 8.27 20 5zM9.5 12.75c-.84 0-1.53-.76-1.53-1.69s.68-1.69 1.53-1.69 1.53.75 1.53 1.69-.69 1.69-1.53 1.69zm5 0c-.84 0-1.53-.76-1.53-1.69s.68-1.69 1.53-1.69 1.53.75 1.53 1.69-.69 1.69-1.53 1.69z" />
    </symbol>
    <symbol id="icon-message" viewBox="0 0 24 24">
        <path d="M4 4h16v12H5.17L4 17.17V4zm0-2a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z" />
    </symbol>
    <symbol id="icon-life-buoy" viewBox="0 0 24 24">
        <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm6.83 9H15.9a3.92 3.92 0 0 0-1.9-1.9V5.17A8 8 0 0 1 18.83 11zM13 5.17v4.73a3.92 3.92 0 0 0-2 0V5.17a8 8 0 0 1 2 0zM5.17 11A8 8 0 0 1 11 5.17v4.73a3.92 3.92 0 0 0-1.9 1.9H5.17zm0 2h3.93a3.92 3.92 0 0 0 1.9 1.9v4.73A8 8 0 0 1 5.17 13zm6.83 6.83v-4.73a3.92 3.92 0 0 0 2 0v4.73a8 8 0 0 1-2 0zm2-6.83a2 2 0 1 1-2-2 2 2 0 0 1 2 2zm2.9 0h3.93A8 8 0 0 1 13 19.83v-4.73a3.92 3.92 0 0 0 1.9-1.9z" />
    </symbol>
    <symbol id="icon-alert" viewBox="0 0 24 24">
        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2zm0-4h-2v-4h2z" />
    </symbol>
    <symbol id="icon-log-in" viewBox="0 0 24 24">
        <path d="M10 17l1.41-1.41L9.83 14H20v-2H9.83l1.58-1.59L10 9l-4 4 4 4zM4 5h8V3H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8v-2H4z" />
    </symbol>
    <symbol id="icon-search" viewBox="0 0 24 24">
        <path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16a6.47 6.47 0 0 0 4.23-1.57l.27.28v.79l5 4.99L20.49 19zm-6 0A4.5 4.5 0 1 1 14 9.5 4.5 4.5 0 0 1 9.5 14z" />
    </symbol>
    <symbol id="icon-sun" viewBox="0 0 24 24">
        <path d="M6.76 4.84l-1.8-1.79L2.81 5.2 4.6 7 6.76 4.84zM1 13h3v-2H1v2zm10 10h2v-3h-2v3zm9-10v-2h-3v2h3zm-2.76-8.16L17.24 7 19.4 5.2l-2.15-2.15-1.01 1.01zM12 6a6 6 0 1 0 6 6 6 6 0 0 0-6-6zm0-5h-2v3h2V1zm7.4 17l1.79 1.79 2.15-2.15-1.79-1.79L19.4 18zM4.6 18l-1.79 1.79 2.15 2.15L7 20.15z" />
    </symbol>
    <symbol id="icon-moon" viewBox="0 0 24 24">
        <path d="M9.37 5.51a7 7 0 0 0 9.12 9.12A8 8 0 1 1 9.37 5.51z" />
    </symbol>
</svg>

<nav class="site-nav" role="navigation" aria-label="Primary" data-nav data-menu-source="/public/data/menu.json">
    <div class="nav-bar">
        <div class="nav-section nav-section--brand">
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav-drawer">
                <span class="nav-toggle__bar" aria-hidden="true"></span>
                <span class="nav-toggle__bar" aria-hidden="true"></span>
                <span class="nav-toggle__bar" aria-hidden="true"></span>
                <span class="nav-toggle__label">Menu</span>
            </button>
            <?php if (!empty($brand['href'])): ?>
                <a class="nav-logo" href="<?= $escape($brand['href']); ?>"><?= $escape($brand['logoText'] ?? 'Brand'); ?></a>
            <?php else: ?>
                <span class="nav-logo"><?= $escape($brand['logoText'] ?? 'Brand'); ?></span>
            <?php endif; ?>
        </div>
        <div class="nav-section nav-section--primary" role="menubar">
            <?php foreach ($primary as $index => $item):
                $label = $item['label'] ?? '';
                $panelId = 'nav-panel-' . $slugify($label, 'panel-' . ($index + 1));
                $triggerId = 'nav-trigger-' . $slugify($label, 'trigger-' . ($index + 1));
                $panelWidth = $item['panelWidth'] ?? 'container';
                ?>
                <div class="nav-primary-item" role="none">
                    <button
                        id="<?= $escape($triggerId); ?>"
                        class="nav-trigger"
                        type="button"
                        role="menuitem"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="<?= $escape($panelId); ?>"
                        data-panel-width="<?= $escape($panelWidth); ?>"
                    >
                        <span><?= $escape($label); ?></span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="nav-section nav-section--utility">
            <?php foreach ($utility as $util):
                $type = $util['type'] ?? '';
                if ($type === 'search'):
                    ?>
                    <form class="nav-search" role="search">
                        <label class="sr-only" for="nav-search-input">Search</label>
                        <input id="nav-search-input" name="q" type="search" placeholder="<?= $escape($searchPlaceholder); ?>" />
                        <button class="nav-search__submit" type="submit" aria-label="Submit search">
                            <svg class="icon" aria-hidden="true"><use href="#icon-search"></use></svg>
                        </button>
                    </form>
                    <button class="nav-search__toggle" type="button" aria-expanded="false" aria-controls="nav-search-overlay" aria-label="Open search">
                        <svg class="icon" aria-hidden="true"><use href="#icon-search"></use></svg>
                    </button>
                <?php elseif ($type === 'link'): ?>
                    <a class="nav-utility-link" href="<?= $escape($util['href'] ?? '#'); ?>">
                        <?php if (!empty($util['icon'])): ?>
                            <svg class="icon" aria-hidden="true"><use href="#icon-<?= $escape($slugify($util['icon'])); ?>"></use></svg>
                        <?php endif; ?>
                        <span><?= $escape($util['label'] ?? ''); ?></span>
                    </a>
                <?php elseif ($type === 'theme-toggle'): ?>
                    <button class="nav-theme-toggle" type="button" aria-label="Toggle theme" aria-pressed="false">
                        <svg class="icon icon--sun" aria-hidden="true"><use href="#icon-sun"></use></svg>
                        <svg class="icon icon--moon" aria-hidden="true"><use href="#icon-moon"></use></svg>
                    </button>
                <?php endif;
            endforeach; ?>
        </div>
    </div>
    <div class="nav-panels" data-nav-panels>
        <?php foreach ($primary as $index => $item):
            $label = $item['label'] ?? '';
            $panelId = 'nav-panel-' . $slugify($label, 'panel-' . ($index + 1));
            $triggerId = 'nav-trigger-' . $slugify($label, 'trigger-' . ($index + 1));
            $panelWidth = $item['panelWidth'] ?? 'container';
            ?>
            <div
                id="<?= $escape($panelId); ?>"
                class="nav-panel nav-panel--<?= $escape($panelWidth); ?>"
                role="menu"
                aria-labelledby="<?= $escape($triggerId); ?>"
                aria-hidden="true"
                data-panel
                hidden
            >
                <?php if (!empty($item['groups']) && is_array($item['groups'])): ?>
                    <div class="nav-panel__content">
                        <?php foreach ($item['groups'] as $group): ?>
                            <div class="nav-panel__group">
                                <?php if (!empty($group['title'])): ?>
                                    <p class="nav-panel__group-title"><?= $escape($group['title']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($group['items']) && is_array($group['items'])): ?>
                                    <ul class="nav-panel__list" role="none">
                                        <?php foreach ($group['items'] as $link):
                                            $linkId = $triggerId . '-' . $slugify($link['label'] ?? 'item');
                                            ?>
                                            <li class="nav-panel__item" role="none">
                                                <a
                                                    id="<?= $escape($linkId); ?>"
                                                    class="nav-panel__link"
                                                    href="<?= $escape($link['href'] ?? '#'); ?>"
                                                    role="menuitem"
                                                    tabindex="-1"
                                                >
                                                    <?php if (!empty($link['icon'])): ?>
                                                        <span class="nav-panel__icon" aria-hidden="true">
                                                            <svg class="icon"><use href="#icon-<?= $escape($slugify($link['icon'])); ?>"></use></svg>
                                                        </span>
                                                    <?php endif; ?>
                                                    <span class="nav-panel__text">
                                                        <span class="nav-panel__label"><?= $escape($link['label'] ?? ''); ?></span>
                                                        <?php if (!empty($link['desc'])): ?>
                                                            <span class="nav-panel__desc"><?= $escape($link['desc']); ?></span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <?php if (!empty($link['badge'])): ?>
                                                        <span class="nav-panel__badge"><?= $escape($link['badge']); ?></span>
                                                    <?php endif; ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="nav-search-overlay" id="nav-search-overlay" role="dialog" aria-modal="true" aria-labelledby="nav-search-overlay-title" aria-hidden="true" hidden>
        <div class="nav-search-overlay__content">
            <div class="nav-search-overlay__header">
                <p id="nav-search-overlay-title">Search</p>
                <button type="button" class="nav-search-overlay__close" aria-label="Close search">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form role="search" class="nav-search-overlay__form">
                <label class="sr-only" for="nav-search-overlay-input">Search query</label>
                <input id="nav-search-overlay-input" name="q" type="search" placeholder="<?= $escape($searchPlaceholder); ?>" />
                <button type="submit" class="nav-search-overlay__submit">Search</button>
            </form>
        </div>
    </div>
    <div class="nav-drawer" id="site-nav-drawer" aria-hidden="true" tabindex="-1">
        <div class="nav-drawer__backdrop" data-drawer-close></div>
        <div class="nav-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="site-nav-drawer-title">
            <div class="nav-drawer__header">
                <p id="site-nav-drawer-title">Menu</p>
                <button type="button" class="nav-drawer__close" data-drawer-close aria-label="Close menu">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="nav-drawer__body" data-drawer-body>
                <?php foreach ($primary as $index => $item):
                    $label = $item['label'] ?? '';
                    $sectionId = 'drawer-section-' . $slugify($label, 'section-' . ($index + 1));
                    ?>
                    <section class="drawer-section">
                        <h2>
                            <button
                                class="drawer-accordion__trigger"
                                type="button"
                                aria-expanded="false"
                                aria-controls="<?= $escape($sectionId); ?>"
                            >
                                <span><?= $escape($label); ?></span>
                                <span class="drawer-accordion__icon" aria-hidden="true"></span>
                            </button>
                        </h2>
                        <div id="<?= $escape($sectionId); ?>" class="drawer-accordion__panel" role="region" hidden>
                            <?php if (!empty($item['groups']) && is_array($item['groups'])): ?>
                                <?php foreach ($item['groups'] as $group): ?>
                                    <div class="drawer-group">
                                        <?php if (!empty($group['title'])): ?>
                                            <p class="drawer-group__title"><?= $escape($group['title']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($group['items']) && is_array($group['items'])): ?>
                                            <ul class="drawer-group__list">
                                                <?php foreach ($group['items'] as $link): ?>
                                                    <li>
                                                        <a href="<?= $escape($link['href'] ?? '#'); ?>">
                                                            <?php if (!empty($link['icon'])): ?>
                                                                <svg class="icon" aria-hidden="true"><use href="#icon-<?= $escape($slugify($link['icon'])); ?>"></use></svg>
                                                            <?php endif; ?>
                                                            <span><?= $escape($link['label'] ?? ''); ?></span>
                                                            <?php if (!empty($link['badge'])): ?>
                                                                <span class="drawer-group__badge"><?= $escape($link['badge']); ?></span>
                                                            <?php endif; ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
                <div class="drawer-utility">
                    <?php foreach ($utility as $util):
                        $type = $util['type'] ?? '';
                        if ($type === 'search'): ?>
                            <form role="search" class="drawer-search">
                                <label class="sr-only" for="drawer-search-input">Search</label>
                                <input id="drawer-search-input" name="q" type="search" placeholder="<?= $escape($searchPlaceholder); ?>" />
                                <button type="submit" aria-label="Submit search">
                                    <svg class="icon" aria-hidden="true"><use href="#icon-search"></use></svg>
                                </button>
                            </form>
                        <?php elseif ($type === 'link'): ?>
                            <a class="drawer-utility__link" href="<?= $escape($util['href'] ?? '#'); ?>">
                                <?php if (!empty($util['icon'])): ?>
                                    <svg class="icon" aria-hidden="true"><use href="#icon-<?= $escape($slugify($util['icon'])); ?>"></use></svg>
                                <?php endif; ?>
                                <span><?= $escape($util['label'] ?? ''); ?></span>
                            </a>
                        <?php elseif ($type === 'theme-toggle'): ?>
                            <button class="drawer-theme-toggle" type="button" aria-label="Toggle theme" aria-pressed="false">
                                <svg class="icon" aria-hidden="true"><use href="#icon-sun"></use></svg>
                                <svg class="icon" aria-hidden="true"><use href="#icon-moon"></use></svg>
                                <span>Theme</span>
                            </button>
                        <?php endif;
                    endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</nav>
