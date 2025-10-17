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
            <ul id="primary-menu">
                <li><a href="/N1/index.php">Home</a></li>
                <li><a href="/N1/news.php">News</a></li>
                <li><a href="/N1/highscores.php">Highscores</a></li>
                <li><a href="/N1/character.php">Character Lookup</a></li>
                <li><a href="/N1/guilds.php">Guilds</a></li>
                <li><a href="/N1/deaths.php">Deaths</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="/N1/dashboard.php">Account</a></li>
                    <li><a href="/N1/characters.php">Characters</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="/N1/admin/index.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="/N1/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/N1/register.php" class="cta">Create Account</a></li>
                    <li><a href="/N1/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<main>
<!-- layout:header:end -->
