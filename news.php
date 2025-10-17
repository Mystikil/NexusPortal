<?php
$title = 'News Feed';
require __DIR__ . '/partials/header.php';
require_once __DIR__ . '/includes/news.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;
$db = getDb();
ensureNewsTable($db);

$total = (int)$db->query('SELECT COUNT(*) FROM site_news')->fetchColumn();
$stmt = $db->prepare('SELECT n.*, a.name AS author_name FROM site_news n JOIN accounts a ON a.id = n.author_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$entries = $stmt->fetchAll();
$totalPages = max(1, (int) ceil($total / $perPage));
?>
<section class="news-feed">
    <header>
        <h1>Command Center Updates</h1>
        <p class="subtitle">Stay informed with the latest patch notes, events, and community highlights.</p>
    </header>
    <?php if (!$entries): ?>
        <p class="empty">No news posts yet. Check back soon!</p>
    <?php else: ?>
        <div class="news-grid">
            <?php foreach ($entries as $entry): ?>
                <article>
                    <h2><?= e($entry['title']) ?></h2>
                    <p class="meta">By <?= e($entry['author_name']) ?> &bull; <?= date('F j, Y', (int)$entry['created_at']) ?></p>
                    <p><?= nl2br(e($entry['body'])) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="News pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
