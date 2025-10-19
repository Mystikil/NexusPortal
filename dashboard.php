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
            <p>Handle subscription options and manage your characters without leaving the portal.</p>
        </header>
        <div class="action-grid">
            <article>
                <h3>Change Password</h3>
                <p>Update your account password regularly to keep your credentials secure.</p>
                <a class="btn secondary" href="<?= e(siteUrl('account/change-password.php')) ?>">Update Password</a>
            </article>
            <article>
                <h3>Add Subscription</h3>
                <p>Unlock premium perks by activating a new subscription for your account.</p>
                <a class="btn secondary" href="<?= e(siteUrl('account/add-subscription.php')) ?>">Activate Subscription</a>
            </article>
            <article>
                <h3>Cancel Subscription</h3>
                <p>Need a break? Manage and cancel your active subscription here.</p>
                <a class="btn secondary" href="<?= e(siteUrl('account/cancel-subscription.php')) ?>">Manage Subscription</a>
            </article>
            <article>
                <h3>Donate</h3>
                <p>Support the server and help fund new features with a one-time donation.</p>
                <a class="btn secondary" href="<?= e(siteUrl('account/donate.php')) ?>">Make a Donation</a>
            </article>
            <article>
                <h3>Character Creation</h3>
                <p>Create a brand-new hero to join your adventures across the realm.</p>
                <a class="btn secondary" href="<?= e(siteUrl('account/create-character.php')) ?>">Create Character</a>
            </article>
            <article>
                <h3>Character Deletion</h3>
                <p>Retire characters you no longer play. Deleted characters cannot be recovered.</p>
                <a class="btn secondary" href="<?= e(siteUrl('account/delete-character.php')) ?>">Delete Character</a>
            </article>
            <article>
                <h3>Character Transfer</h3>
                <p>Move a character to another account or world while keeping their progress.</p>
                <a class="btn secondary" href="<?= e(siteUrl('account/transfer-character.php')) ?>">Transfer Character</a>
            </article>
            <article>
                <h3>Character Rename</h3>
                <p>Give your hero a fresh identity with a new name and renewed fame.</p>
                <a class="btn secondary" href="<?= e(siteUrl('account/rename-character.php')) ?>">Rename Character</a>
            </article>
        </div>
    </section>
</section>
<!-- layout:content:end -->
<?php require __DIR__ . '/partials/footer.php'; ?>
