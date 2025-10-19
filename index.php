<?php
$title = 'Home Base';
require __DIR__ . '/partials/header.php';
require_once __DIR__ . '/includes/news.php';

function safeCount(PDO $db, string $query): int {
    try {
        return (int) $db->query($query)->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

$news = fetchLatestNews(3);
$db = getDb();
$playerCount = safeCount($db, 'SELECT COUNT(*) FROM players');
$accountCount = safeCount($db, 'SELECT COUNT(*) FROM accounts');
$onlineCount = safeCount($db, 'SELECT COUNT(*) FROM players_online');
?>
<!-- layout:content:start -->
<section class="hero">
    <div class="hero-content">
        <h1>Command the Next Evolution</h1>
        <p>Launch into an interdimensional MMORPG with stunning visuals, seasonal raids, and a thriving economy. Your squad is waiting.</p>
        <div class="actions">
            <a class="btn primary" href="<?= e(siteUrl('register.php')) ?>">Create Account</a>
            <a class="btn secondary" href="<?= e(siteUrl('news.php')) ?>">Latest Intel</a>
        </div>
    </div>
    <div class="hero-stats">
        <div class="stat">
            <span class="value"><?= number_format($onlineCount) ?></span>
            <span class="label">Players Online</span>
        </div>
        <div class="stat">
            <span class="value"><?= number_format($playerCount) ?></span>
            <span class="label">Heroes Created</span>
        </div>
        <div class="stat">
            <span class="value"><?= number_format($accountCount) ?></span>
            <span class="label">Registered Commanders</span>
        </div>
    </div>
</section>

<section class="feature-grid">
    <article>
        <h2>Seasonal Raids</h2>
        <p>Battle through cinematic raid events with unique mechanics and leaderboard rewards updated every season.</p>
    </article>
    <article>
        <h2>Guild Arsenal</h2>
        <p>Powerful guild management tools with live war tracking, automatic rank promotions, and shared stash visibility.</p>
    </article>
    <article>
        <h2>Player-Driven Markets</h2>
        <p>Master the auction house with live graphs, cross-world trades, and secure escrow for high value deals.</p>
    </article>
</section>

<section class="panel">
    <header>
        <h2>Latest Dispatches</h2>
        <a href="<?= e(siteUrl('news.php')) ?>" class="link">View all</a>
    </header>
    <?php if (!$news): ?>
        <p class="empty">No news yet. Admins can deploy the first transmission from the dashboard.</p>
    <?php else: ?>
        <div class="news-list">
            <?php foreach ($news as $entry): ?>
                <article>
                    <h3><?= e($entry['title']) ?></h3>
                    <p class="meta">By <?= e($entry['author_name']) ?> &bull; <?= e(formatRelativeTime((int)$entry['created_at'])) ?></p>
                    <p><?= nl2br(e(truncateText($entry['body'], 220))) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="cta-band">
    <h2>Ready to Ascend?</h2>
    <p>Secure your Nexus One account and unlock exclusive launch rewards, including the Aetherial Wyvern mount.</p>
    <a class="btn primary" href="<?= e(siteUrl('register.php')) ?>">Get Started</a>
</section>
<!-- layout:content:end -->
<?php require __DIR__ . '/partials/footer.php'; ?>
