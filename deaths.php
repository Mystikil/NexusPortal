<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/game.php';

$db = getDb();
$title = 'Death Records';

$limit = isset($_GET['limit']) ? max(10, min(100, (int) $_GET['limit'])) : 50;
$playerQuery = trim($_GET['player'] ?? '');

$deathsAvailable = tableExists($db, 'player_deaths');
$rows = [];
$playerColumns = tableColumns($db, 'players');
$vocationColumn = in_array('vocation', $playerColumns, true) ? 'vocation' : null;
$currentLevelColumn = in_array('level', $playerColumns, true) ? 'level' : null;

if ($deathsAvailable) {
    $deathColumns = tableColumns($db, 'player_deaths');
    $select = ['d.time'];
    $select[] = in_array('level', $deathColumns, true) ? 'd.level AS death_level' : '0 AS death_level';
    if (in_array('killed_by', $deathColumns, true)) {
        $select[] = 'd.killed_by';
    } else {
        $select[] = "'' AS killed_by";
    }
    if (in_array('is_player', $deathColumns, true)) {
        $select[] = 'd.is_player';
    } else {
        $select[] = '0 AS is_player';
    }
    if (in_array('mostdamage_by', $deathColumns, true)) {
        $select[] = 'd.mostdamage_by';
    }
    if (in_array('mostdamage_is_player', $deathColumns, true)) {
        $select[] = 'd.mostdamage_is_player';
    }
    if (in_array('unjustified', $deathColumns, true)) {
        $select[] = 'd.unjustified';
    }
    if (in_array('mostdamage_unjustified', $deathColumns, true)) {
        $select[] = 'd.mostdamage_unjustified';
    }
    $select[] = 'p.name AS player_name';
    if ($vocationColumn) {
        $select[] = 'p.' . $vocationColumn . ' AS vocation';
    }
    if ($currentLevelColumn) {
        $select[] = 'p.' . $currentLevelColumn . ' AS current_level';
    }

    $selectSql = implode(', ', $select);
    $conditions = [];
    $params = [];
    if ($playerQuery !== '') {
        $conditions[] = 'p.name LIKE :player';
        $params[':player'] = '%' . $playerQuery . '%';
    }
    $whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $sql = sprintf('SELECT %s FROM player_deaths d INNER JOIN players p ON p.id = d.player_id %s ORDER BY d.time DESC LIMIT :limit', $selectSql, $whereSql);

    try {
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $rows = [];
    }
}

require __DIR__ . '/partials/header.php';
?>
<!-- layout:content:start -->
<section class="deaths">
    <header class="page-header">
        <h1>Recent Deaths</h1>
        <p class="subtitle">Track the latest fallen heroes, discover deadly foes, and study battlefield trends.</p>
    </header>
    <?php if (!$deathsAvailable): ?>
        <p class="empty">The <code>player_deaths</code> table is not available in this database.</p>
    <?php else: ?>
        <form method="get" class="filter-form inline">
            <label>
                <span>Filter by player</span>
                <input type="text" name="player" value="<?= e($playerQuery) ?>" placeholder="Character name">
            </label>
            <label>
                <span>Results</span>
                <select name="limit">
                    <?php foreach ([25, 50, 75, 100] as $option): ?>
                        <option value="<?= $option ?>" <?= $limit === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="btn">Apply</button>
        </form>
        <?php if (!$rows): ?>
            <p class="empty">No deaths recorded for the current filters.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Victim</th>
                            <th>Death Level</th>
                            <th>Killer</th>
                            <?php if (in_array('mostdamage_by', $deathColumns, true)): ?>
                                <th>Most Damage</th>
                            <?php endif; ?>
                            <?php if (in_array('unjustified', $deathColumns, true)): ?>
                                <th>Unjustified?</th>
                            <?php endif; ?>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= !empty($row['time']) ? date('Y-m-d H:i', (int) $row['time']) : 'Unknown' ?></td>
                                <td>
                                    <strong><?= e($row['player_name']) ?></strong><br>
                                    <span class="meta">
                                        <?= $vocationColumn ? e(vocationName($db, $row['vocation'] ?? 0)) : 'Adventurer' ?>
                                        <?php if ($currentLevelColumn && isset($row['current_level'])): ?>
                                            &bull; Level <?= (int) $row['current_level'] ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><?= isset($row['death_level']) ? (int) $row['death_level'] : '—' ?></td>
                                <td>
                                    <?= e($row['killed_by'] ?? 'Unknown') ?>
                                    <span class="meta"><?= !empty($row['is_player']) ? 'Player' : 'Creature' ?></span>
                                </td>
                                <?php if (in_array('mostdamage_by', $deathColumns, true)): ?>
                                    <td>
                                        <?= isset($row['mostdamage_by']) ? e($row['mostdamage_by']) : '—' ?>
                                        <?php if (isset($row['mostdamage_is_player'])): ?>
                                            <span class="meta"><?= !empty($row['mostdamage_is_player']) ? 'Player' : 'Creature' ?></span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <?php if (in_array('unjustified', $deathColumns, true)): ?>
                                    <td><?= !empty($row['unjustified']) ? 'Yes' : 'No' ?></td>
                                <?php endif; ?>
                                <td><?= !empty($row['is_player']) ? 'PvP' : 'PvE' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
<!-- layout:content:end -->
<?php require __DIR__ . '/partials/footer.php'; ?>
