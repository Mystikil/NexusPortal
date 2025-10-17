<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/game.php';
require_once __DIR__ . '/includes/characters.php';

$db = getDb();
$title = 'Character Lookup';

$query = trim($_GET['name'] ?? '');
$nameInput = $query;
$character = null;
$recentDeaths = [];
$deathsAvailable = tableExists($db, 'player_deaths');
$towns = getTownOptions($db);
$townLookup = [];
foreach ($towns as $town) {
    $townLookup[(int) $town['id']] = $town['name'];
}
$vocations = getVocationOptions($db);
$deathColumns = $deathsAvailable ? tableColumns($db, 'player_deaths') : [];

if ($query !== '') {
    $normalized = preg_replace('/\s+/', ' ', $query);
    $character = fetchCharacterByName($db, $normalized);
    if ($character) {
        $nameInput = $character['name'] ?? $normalized;
        if ($deathsAvailable) {
            $recentDeaths = fetchRecentDeathsForCharacter($db, (int) ($character['id'] ?? 0), 5);
        }
    }
}

require __DIR__ . '/partials/header.php';
?>
<!-- layout:content:start -->
<section class="character-lookup">
    <header class="page-header">
        <h1>Character Directory</h1>
        <p class="subtitle">Search for heroes, review their public profile, and study their recent battles.</p>
    </header>
    <form method="get" class="character-search">
        <label>
            <span>Character name</span>
            <input type="text" name="name" value="<?= e($nameInput) ?>" placeholder="e.g. Nova Storm" maxlength="30" required>
        </label>
        <button type="submit" class="btn">Search</button>
    </form>
    <?php if ($query !== '' && !$character): ?>
        <p class="empty">No character named <strong><?= e($query) ?></strong> was found.</p>
    <?php endif; ?>
    <?php if ($character): ?>
        <?php
            $level = isset($character['level']) ? (int) $character['level'] : null;
            $vocationId = isset($character['vocation']) ? (int) $character['vocation'] : 0;
            $vocationLabel = $vocations[$vocationId] ?? vocationName($db, $vocationId);
            $sexLabel = 'Unknown';
            if (isset($character['sex'])) {
                $sex = (int) $character['sex'];
                $sexLabel = $sex === 0 ? 'Female' : ($sex === 1 ? 'Male' : 'Other');
            }
            $townName = $townLookup[(int) ($character['town_id'] ?? 0)] ?? 'Unknown';
            $lastLoginTimestamp = isset($character['lastlogin']) ? (int) $character['lastlogin'] : 0;
            $lastLogin = $lastLoginTimestamp > 0 ? date('Y-m-d H:i', $lastLoginTimestamp) : 'Never';
            $lastLoginRelative = $lastLoginTimestamp > 0 ? formatRelativeTime($lastLoginTimestamp) : null;
            $lastLogoutTimestamp = isset($character['lastlogout']) ? (int) $character['lastlogout'] : 0;
            $lastLogout = $lastLogoutTimestamp > 0 ? date('Y-m-d H:i', $lastLogoutTimestamp) : 'Never';
            $lastLogoutRelative = $lastLogoutTimestamp > 0 ? formatRelativeTime($lastLogoutTimestamp) : null;
            $createdTimestamp = isset($character['created']) ? (int) $character['created'] : 0;
            $createdAt = $createdTimestamp > 0 ? date('Y-m-d H:i', $createdTimestamp) : null;
            $createdRelative = $createdTimestamp > 0 ? formatRelativeTime($createdTimestamp) : null;
            $magLevel = isset($character['maglevel']) ? (int) $character['maglevel'] : null;
            $experience = isset($character['experience']) ? number_format((int) $character['experience']) : null;
            $worldId = isset($character['world_id']) ? (int) $character['world_id'] : null;
            $onlineStatus = formatOnlineStatus($character);
            $onlineClass = strtolower($onlineStatus);
            $comment = isset($character['comment']) ? trim((string) $character['comment']) : '';
            $deathCount = count($recentDeaths);
        ?>
        <article class="character-profile">
            <header>
                <div>
                    <h2><?= e($character['name'] ?? $nameInput) ?></h2>
                    <p class="meta">
                        <?= $level !== null ? 'Level ' . $level : 'Level unknown' ?>
                        &bull; <?= e($vocationLabel) ?>
                        &bull; <?= e($sexLabel) ?>
                    </p>
                </div>
                <span class="status <?= e($onlineClass) ?>"><?= e($onlineStatus) ?></span>
            </header>
            <dl>
                <div>
                    <dt>Residence</dt>
                    <dd><?= e($townName) ?></dd>
                </div>
                <div>
                    <dt>Last Login</dt>
                    <dd>
                        <?= e($lastLogin) ?>
                        <?php if ($lastLoginRelative): ?>
                            <span class="meta">(<?= e($lastLoginRelative) ?>)</span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt>Last Logout</dt>
                    <dd>
                        <?= e($lastLogout) ?>
                        <?php if ($lastLogoutRelative): ?>
                            <span class="meta">(<?= e($lastLogoutRelative) ?>)</span>
                        <?php endif; ?>
                    </dd>
                </div>
                <?php if ($createdAt): ?>
                    <div>
                        <dt>Created</dt>
                        <dd>
                            <?= e($createdAt) ?>
                            <?php if ($createdRelative): ?>
                                <span class="meta">(<?= e($createdRelative) ?>)</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                <?php endif; ?>
                <?php if ($magLevel !== null): ?>
                    <div>
                        <dt>Magic Level</dt>
                        <dd><?= $magLevel ?></dd>
                    </div>
                <?php endif; ?>
                <?php if ($experience !== null): ?>
                    <div>
                        <dt>Experience</dt>
                        <dd><?= e($experience) ?></dd>
                    </div>
                <?php endif; ?>
                <?php if ($worldId !== null): ?>
                    <div>
                        <dt>World ID</dt>
                        <dd><?= $worldId ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
            <?php if ($comment !== ''): ?>
                <p class="description"><?= nl2br(e($comment)) ?></p>
            <?php endif; ?>
        </article>
        <div class="character-sections">
            <section class="character-deaths">
                <header>
                    <h3>Recent Deaths</h3>
                    <span class="meta"><?= $deathsAvailable ? 'Showing ' . $deathCount . ' of the last 5 records' : 'History unavailable' ?></span>
                </header>
                <?php if (!$deathsAvailable): ?>
                    <p class="empty">Death history is not available for this server.</p>
                <?php elseif (!$recentDeaths): ?>
                    <p class="empty">No recorded deaths for this character.</p>
                <?php else: ?>
                    <?php
                        $showMostDamage = in_array('mostdamage_by', $deathColumns, true);
                        $showMostDamagePlayer = in_array('mostdamage_is_player', $deathColumns, true);
                        $showUnjustified = in_array('unjustified', $deathColumns, true);
                    ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Level</th>
                                    <th>Killer</th>
                                    <th>Type</th>
                                    <?php if ($showMostDamage): ?>
                                        <th>Most Damage</th>
                                    <?php endif; ?>
                                    <?php if ($showUnjustified): ?>
                                        <th>Unjustified?</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentDeaths as $death): ?>
                                    <tr>
                                        <td><?= !empty($death['death_time']) ? date('Y-m-d H:i', (int) $death['death_time']) : 'Unknown' ?></td>
                                        <td><?= isset($death['death_level']) ? (int) $death['death_level'] : '—' ?></td>
                                        <td>
                                            <?= e($death['killed_by'] ?? 'Unknown') ?>
                                            <span class="meta"><?= !empty($death['is_player']) ? 'Player' : 'Creature' ?></span>
                                        </td>
                                        <td><?= !empty($death['is_player']) ? 'PvP' : 'PvE' ?></td>
                                        <?php if ($showMostDamage): ?>
                                            <td>
                                                <?= isset($death['mostdamage_by']) && $death['mostdamage_by'] !== '' ? e($death['mostdamage_by']) : '—' ?>
                                                <?php if ($showMostDamagePlayer && isset($death['mostdamage_is_player'])): ?>
                                                    <span class="meta"><?= !empty($death['mostdamage_is_player']) ? 'Player' : 'Creature' ?></span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        <?php if ($showUnjustified): ?>
                                            <td><?= !empty($death['unjustified']) ? 'Yes' : 'No' ?></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    <?php endif; ?>
</section>
<!-- layout:content:end -->
<?php require __DIR__ . '/partials/footer.php'; ?>
