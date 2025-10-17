<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$title = 'Account Control';
require_once __DIR__ . '/includes/game.php';
require __DIR__ . '/partials/header.php';
$db = getDb();
$user = currentUser();

$stmt = $db->prepare('SELECT * FROM players WHERE account_id = ? ORDER BY level DESC, name ASC');
$stmt->execute([$user['id']]);
$characters = $stmt->fetchAll();
?>
<!-- layout:content:start -->
<section class="dashboard">
    <aside class="profile">
        <h1><?= e($user['name']) ?></h1>
        <p class="meta">Member since <?= date('F j, Y', (int)$user['creation']) ?></p>
        <dl>
            <div>
                <dt>Account Type</dt>
                <dd><?= (int)$user['type'] >= 3 ? 'Administrator' : ((int)$user['type'] > 1 ? 'Premium' : 'Adventurer') ?></dd>
            </div>
            <div>
                <dt>Email</dt>
                <dd><?= e($user['email']) ?></dd>
            </div>
            <div>
                <dt>Premium Ends</dt>
                <dd><?= $user['premium_ends_at'] ? date('F j, Y', (int)$user['premium_ends_at']) : 'No active premium' ?></dd>
            </div>
        </dl>
    </aside>
    <section class="characters">
        <header>
            <h2>Your Heroes</h2>
            <p>Manage characters linked to this account.</p>
        </header>
        <?php if (!$characters): ?>
            <p class="empty">No characters yet. Create a character in-game to see it listed here.</p>
        <?php else: ?>
            <div class="character-grid">
                <?php foreach ($characters as $character): ?>
                    <article>
                        <h3><?= e($character['name']) ?></h3>
                        <p class="meta">Level <?= e($character['level']) ?> &bull; <?= e(vocationName($db, $character['vocation'] ?? 0)) ?></p>
                        <ul>
                            <li>Health: <?= e($character['health']) ?>/<?= e($character['healthmax']) ?></li>
                            <li>Mana: <?= e($character['mana']) ?>/<?= e($character['manamax']) ?></li>
                            <li>Town: <?= e($character['town_id']) ?></li>
                            <li>Onlinetime: <?= number_format((int)$character['onlinetime'] / 3600, 1) ?> hrs</li>
                        </ul>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
<!-- layout:content:end -->
<?php require __DIR__ . '/partials/footer.php'; ?>
