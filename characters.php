<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/characters.php';

$db = getDb();
$user = currentUser();
$title = 'Character Management';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = [];

    if ($action === 'create') {
        $result = createCharacter($db, $user, $_POST);
        $flash = $result ? ['type' => 'error', 'messages' => $result] : ['type' => 'success', 'messages' => ['Character created successfully.']];
    } elseif ($action === 'rename') {
        $result = renameCharacter($db, $user, $_POST);
        $flash = $result ? ['type' => 'error', 'messages' => $result] : ['type' => 'success', 'messages' => ['Character renamed successfully.']];
    } elseif ($action === 'delete') {
        $result = deleteCharacter($db, $user, (int) ($_POST['player_id'] ?? 0));
        $flash = $result ? ['type' => 'error', 'messages' => $result] : ['type' => 'success', 'messages' => ['Character deleted successfully.']];
    }

    $_SESSION['flash'] = $flash;
    header('Location: characters.php');
    exit;
}

$characters = fetchAccountCharacters($db, (int) $user['id']);
$towns = getTownOptions($db);
$vocations = getVocationOptions($db);
$townLookup = [];
foreach ($towns as $town) {
    $townLookup[(int) $town['id']] = $town['name'];
}
require_once __DIR__ . '/partials/header.php';
?>
<section class="character-management">
    <header class="page-header">
        <h1>Character Management</h1>
        <p class="subtitle">Forge new heroes, rename veterans, or retire characters tied to your account.</p>
    </header>

    <?php if ($flash): ?>
        <div class="alert <?= e($flash['type']) ?>">
            <ul>
                <?php foreach ($flash['messages'] as $message): ?>
                    <li><?= e($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="manager-grid">
        <section class="panel">
            <header>
                <h2>Create a New Character</h2>
                <p class="meta">You can create up to 10 characters per account.</p>
            </header>
            <form method="post" class="stack">
                <input type="hidden" name="action" value="create">
                <label>
                    <span>Character Name</span>
                    <input type="text" name="name" required minlength="3" maxlength="25" pattern="[A-Za-z '\-]+" placeholder="E.g. Nova Storm">
                </label>
                <div class="columns">
                    <label>
                        <span>Gender</span>
                        <select name="sex">
                            <option value="1">Male</option>
                            <option value="0">Female</option>
                        </select>
                    </label>
                    <label>
                        <span>Vocation</span>
                        <select name="vocation">
                            <?php foreach ($vocations as $id => $label): ?>
                                <option value="<?= (int) $id ?>"><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>Home Town</span>
                        <select name="town_id">
                            <?php foreach ($towns as $town): ?>
                                <option value="<?= (int) $town['id'] ?>"><?= e($town['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <button type="submit" class="btn primary">Create Character</button>
            </form>
        </section>

        <section class="panel">
            <header>
                <h2>Your Roster</h2>
                <p class="meta">Manage <?= count($characters) ?> character<?= count($characters) === 1 ? '' : 's' ?> linked to <?= e($user['name']) ?>.</p>
            </header>
            <?php if (!$characters): ?>
                <p class="empty">No characters yet. Create a character above or log in to the game to create one.</p>
            <?php else: ?>
                <div class="character-card-grid">
                    <?php foreach ($characters as $character): ?>
                        <article class="character-card">
                            <header>
                                <div>
                                    <h3><?= e($character['name']) ?></h3>
                                    <p class="meta">Level <?= isset($character['level']) ? (int) $character['level'] : '?' ?> &bull; <?= e(vocationName($db, $character['vocation'] ?? 0)) ?></p>
                                </div>
                                <span class="status <?= strtolower(formatOnlineStatus($character)) ?>"><?= formatOnlineStatus($character) ?></span>
                            </header>
                            <dl>
                                <div>
                                    <dt>Town</dt>
                                    <?php $townName = $townLookup[(int) ($character['town_id'] ?? 0)] ?? 'Unknown'; ?>
                                    <dd><?= e($townName) ?></dd>
                                </div>
                                <div>
                                    <dt>Last Login</dt>
                                    <dd><?= isset($character['lastlogin']) && (int) $character['lastlogin'] > 0 ? date('Y-m-d H:i', (int) $character['lastlogin']) : 'Never' ?></dd>
                                </div>
                                <div>
                                    <dt>Onlinetime</dt>
                                    <dd><?= isset($character['onlinetime']) ? number_format((int) $character['onlinetime'] / 3600, 1) . ' hrs' : 'n/a' ?></dd>
                                </div>
                            </dl>
                            <div class="card-actions">
                                <form method="post" class="rename-form">
                                    <input type="hidden" name="action" value="rename">
                                    <input type="hidden" name="player_id" value="<?= (int) $character['id'] ?>">
                                    <label>
                                        <span>Rename</span>
                                        <input type="text" name="new_name" required minlength="3" maxlength="25" pattern="[A-Za-z '\-]+" placeholder="New name">
                                    </label>
                                    <button type="submit" class="btn">Rename</button>
                                </form>
                                <form method="post" onsubmit="return confirm('Delete <?= e($character['name']) ?>? This cannot be undone.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="player_id" value="<?= (int) $character['id'] ?>">
                                    <button type="submit" class="btn danger">Delete</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
