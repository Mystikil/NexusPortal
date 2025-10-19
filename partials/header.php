<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/server.php';
$settings = require __DIR__ . '/../config.php';
$siteName = $settings['site']['name'];
$serverStatus = getServerStatus($settings);
$serverOnline = !empty($serverStatus['online']);
$statusTitle = $serverOnline ? 'Game server is online' : 'Game server is offline';
$statusTitle .= sprintf(' (%s:%s)', $serverStatus['host'], $serverStatus['port']);
?>
<!-- layout:header:start -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($siteName) ?> &bull; <?= e($title ?? 'Game Portal') ?></title>
    <link rel="stylesheet" href="/N1/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="site-header">
    <div class="logo">
        <a href="/N1/index.php"><?= e($siteName) ?></a>
    </div>
    <div class="header-actions">
        <div class="server-status <?= $serverOnline ? 'online' : 'offline' ?>" role="status" aria-live="polite" title="<?= e($statusTitle) ?>">
            <span class="indicator" aria-hidden="true"></span>
            <span><?= $serverOnline ? 'Server Online' : 'Server Offline' ?></span>
        </div>
        <nav class="main-nav" aria-label="Main navigation">
            <button class="nav-toggle" aria-controls="primary-menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
            <?php
            $menuSections = [
                'Discover' => [
                    ['label' => 'Home', 'href' => '/N1/index.php'],
                    ['label' => 'News', 'href' => '/N1/news.php'],
                    ['label' => 'Highscores', 'href' => '/N1/highscores.php'],
                ],
                'Intel & Community' => [
                    ['label' => 'Character Lookup', 'href' => '/N1/character.php'],
                    ['label' => 'Guilds', 'href' => '/N1/guilds.php'],
                    ['label' => 'Deaths', 'href' => '/N1/deaths.php'],
                ],
            ];

            if (isLoggedIn()) {
                $accountLinks = [
                    ['label' => 'Account Dashboard', 'href' => '/N1/dashboard.php'],
                    ['label' => 'My Characters', 'href' => '/N1/characters.php'],
                ];

                if (isAdmin()) {
                    $accountLinks[] = ['label' => 'Admin Control', 'href' => '/N1/admin/index.php'];
                }

                $accountLinks[] = ['label' => 'Logout', 'href' => '/N1/logout.php'];

                $menuSections['Command Console'] = $accountLinks;
            } else {
                $menuSections['Join the Nexus'] = [
                    ['label' => 'Create Account', 'href' => '/N1/register.php', 'class' => 'cta'],
                    ['label' => 'Login', 'href' => '/N1/login.php'],
                ];
            }
            ?>
            <ul id="primary-menu" class="menu-sections">
                <?php foreach ($menuSections as $sectionTitle => $links): ?>
                    <li class="menu-section">
                        <span class="section-title"><?= e($sectionTitle) ?></span>
                        <ul class="menu-links">
                            <?php foreach ($links as $link): ?>
                                <?php
                                $linkClass = $link['class'] ?? '';
                                ?>
                                <li>
                                    <a href="<?= e($link['href']) ?>"<?php if ($linkClass !== ''): ?> class="<?= e($linkClass) ?>"<?php endif; ?>><?= e($link['label']) ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>
<main>
<!-- layout:header:end -->
