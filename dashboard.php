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
    <section class="account-actions">
        <header>
            <h2>Account Actions</h2>
            <p>Find common account workflows grouped by what you want to accomplish.</p>
        </header>
        <div class="action-groups">
            <article class="action-group">
                <h3>Account Security</h3>
                <p class="group-summary">Protect your credentials and keep your login details up to date.</p>
                <ul class="action-list">
                    <li>
                        <div class="action-card">
                            <h4>Change Password</h4>
                            <p>Regularly refresh your password to prevent unwanted access.</p>
                            <a class="btn secondary" href="#">Update Password</a>
                        </div>
                    </li>
                </ul>
            </article>
            <article class="action-group">
                <h3>Membership &amp; Support</h3>
                <p class="group-summary">Manage premium time or support the server directly.</p>
                <ul class="action-list">
                    <li>
                        <div class="action-card">
                            <h4>Add Subscription</h4>
                            <p>Enable premium status and unlock exclusive account benefits.</p>
                            <a class="btn secondary" href="#">Activate Subscription</a>
                        </div>
                    </li>
                    <li>
                        <div class="action-card">
                            <h4>Cancel Subscription</h4>
                            <p>Adjust your membership if you no longer need premium time.</p>
                            <a class="btn secondary" href="#">Manage Subscription</a>
                        </div>
                    </li>
                    <li>
                        <div class="action-card">
                            <h4>Donate</h4>
                            <p>Chip in with a one-time donation to keep development rolling.</p>
                            <a class="btn secondary" href="#">Make a Donation</a>
                        </div>
                    </li>
                </ul>
            </article>
            <article class="action-group">
                <h3>Character Management</h3>
                <p class="group-summary">Create, maintain, or move the characters tied to your account.</p>
                <ul class="action-list">
                    <li>
                        <div class="action-card">
                            <h4>Character Creation</h4>
                            <p>Start a new adventure with a freshly forged hero.</p>
                            <a class="btn secondary" href="#">Create Character</a>
                        </div>
                    </li>
                    <li>
                        <div class="action-card">
                            <h4>Character Deletion</h4>
                            <p>Retire a hero you no longer need. Deletions are permanent.</p>
                            <a class="btn secondary" href="#">Delete Character</a>
                        </div>
                    </li>
                    <li>
                        <div class="action-card">
                            <h4>Character Transfer</h4>
                            <p>Move a character between accounts or worlds while preserving progress.</p>
                            <a class="btn secondary" href="#">Transfer Character</a>
                        </div>
                    </li>
                    <li>
                        <div class="action-card">
                            <h4>Character Rename</h4>
                            <p>Give an existing hero a new identity and start fresh.</p>
                            <a class="btn secondary" href="#">Rename Character</a>
                        </div>
                    </li>
                </ul>
            </article>
        </div>
    </section>
</section>
<!-- layout:content:end -->
<?php require __DIR__ . '/partials/footer.php'; ?>
