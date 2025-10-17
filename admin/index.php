<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$title = 'Admin Command Center';
require __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../includes/news.php';

$db = getDb();

function safeCount(PDO $db, string $query): int {
    try {
        return (int) $db->query($query)->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

$stats = [
    'accounts' => safeCount($db, 'SELECT COUNT(*) FROM accounts'),
    'players' => safeCount($db, 'SELECT COUNT(*) FROM players'),
    'guilds' => safeCount($db, 'SELECT COUNT(*) FROM guilds'),
    'bans' => safeCount($db, 'SELECT COUNT(*) FROM account_bans'),
];

$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_news') {
        $errors = createNews($_POST['title'] ?? '', $_POST['body'] ?? '', (int) currentUser()['id']);
        $flash = $errors ? ['type' => 'error', 'messages' => $errors] : ['type' => 'success', 'messages' => ['News broadcast published.']];
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_news') {
        deleteNews((int) $_POST['news_id']);
        $flash = ['type' => 'success', 'messages' => ['News post deleted.']];
    }
}

$news = fetchLatestNews(25);
?>
<section class="admin">
    <header>
        <h1>Admin Command Center</h1>
        <p class="subtitle">Monitor world health, sanction offenders, and broadcast updates.</p>
    </header>
    <div class="stat-grid">
        <div>
            <span class="label">Total Accounts</span>
            <span class="value"><?= number_format($stats['accounts']) ?></span>
        </div>
        <div>
            <span class="label">Total Characters</span>
            <span class="value"><?= number_format($stats['players']) ?></span>
        </div>
        <div>
            <span class="label">Guilds Active</span>
            <span class="value"><?= number_format($stats['guilds']) ?></span>
        </div>
        <div>
            <span class="label">Active Bans</span>
            <span class="value"><?= number_format($stats['bans']) ?></span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert <?= e($flash['type']) ?>">
            <ul>
                <?php foreach ($flash['messages'] as $message): ?>
                    <li><?= e($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="panel">
        <header>
            <h2>Broadcast News</h2>
            <p>Share updates, patch notes, or event announcements with your community.</p>
        </header>
        <form method="post" class="stack">
            <input type="hidden" name="action" value="create_news">
            <label>
                <span>Title</span>
                <input type="text" name="title" required maxlength="150">
            </label>
            <label>
                <span>Content</span>
                <textarea name="body" rows="6" required minlength="20" placeholder="Detail the latest update, event, or warning..."></textarea>
            </label>
            <button type="submit" class="btn primary">Publish Transmission</button>
        </form>
    </section>

    <section class="panel">
        <header>
            <h2>Recent Broadcasts</h2>
        </header>
        <?php if (!$news): ?>
            <p class="empty">No news posts created yet.</p>
        <?php else: ?>
            <div class="news-list admin">
                <?php foreach ($news as $entry): ?>
                    <article>
                        <h3><?= e($entry['title']) ?></h3>
                        <p class="meta">By <?= e($entry['author_name']) ?> &bull; <?= date('Y-m-d H:i', (int)$entry['created_at']) ?></p>
                        <p><?= nl2br(e($entry['body'])) ?></p>
                        <form method="post" class="inline">
                            <input type="hidden" name="action" value="delete_news">
                            <input type="hidden" name="news_id" value="<?= (int)$entry['id'] ?>">
                            <button type="submit" class="btn danger" onclick="return confirm('Delete this news post?')">Delete</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
