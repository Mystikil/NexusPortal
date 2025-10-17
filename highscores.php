<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/game.php';

$db = getDb();
$title = 'Highscores';

$playerColumns = tableColumns($db, 'players');
$skillColumns = tableExists($db, 'player_skills') ? tableColumns($db, 'player_skills') : [];

$types = [];
if (in_array('level', $playerColumns, true)) {
    $types['level'] = [
        'label' => 'Level',
        'column' => 'level',
        'source' => 'players',
        'value_label' => 'Level',
        'description' => 'Highest level adventurers ranked by experience.',
        'secondary' => in_array('experience', $playerColumns, true) ? 'experience' : null,
    ];
}
if (in_array('maglevel', $playerColumns, true)) {
    $types['maglevel'] = [
        'label' => 'Magic Level',
        'column' => 'maglevel',
        'source' => 'players',
        'value_label' => 'Magic Level',
        'description' => 'Masters of arcane arts with the highest magic level.',
    ];
}

$skillMapPlayers = [
    'fist' => 'skill_fist',
    'club' => 'skill_club',
    'sword' => 'skill_sword',
    'axe' => 'skill_axe',
    'distance' => 'skill_dist',
    'shielding' => 'skill_shielding',
    'fishing' => 'skill_fishing',
];
$skillLabels = [
    'fist' => 'Fist Fighting',
    'club' => 'Club Fighting',
    'sword' => 'Sword Fighting',
    'axe' => 'Axe Fighting',
    'distance' => 'Distance Fighting',
    'shielding' => 'Shielding',
    'fishing' => 'Fishing',
];
foreach ($skillMapPlayers as $key => $column) {
    if (in_array($column, $playerColumns, true)) {
        $types[$key] = [
            'label' => $skillLabels[$key],
            'column' => $column,
            'source' => 'players',
            'value_label' => $skillLabels[$key],
            'description' => 'Top specialists measured by ' . strtolower($skillLabels[$key]) . ' skill.',
        ];
    } elseif (in_array($key, $skillColumns, true)) {
        $types[$key] = [
            'label' => $skillLabels[$key],
            'column' => $key,
            'source' => 'player_skills',
            'value_label' => $skillLabels[$key],
            'description' => 'Top specialists measured by ' . strtolower($skillLabels[$key]) . ' skill.',
        ];
    }
}

if (!$types) {
    $types['level'] = [
        'label' => 'Level',
        'column' => 'level',
        'source' => 'players',
        'value_label' => 'Level',
        'description' => 'Highest level adventurers ranked by experience.',
        'secondary' => null,
    ];
}

$selectedKey = $_GET['type'] ?? 'level';
if (!array_key_exists($selectedKey, $types)) {
    $selectedKey = array_key_first($types);
}
$selectedType = $types[$selectedKey];

$limit = isset($_GET['limit']) ? max(5, min(100, (int) $_GET['limit'])) : 50;

$worlds = [];
$worldColumn = in_array('world_id', $playerColumns, true) ? 'world_id' : null;
if ($worldColumn) {
    try {
        $stmt = $db->query("SELECT DISTINCT $worldColumn FROM players ORDER BY $worldColumn ASC");
        $worlds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    } catch (PDOException $e) {
        $worlds = [];
    }
}
$worldFilter = null;
if ($worldColumn && isset($_GET['world']) && $_GET['world'] !== '') {
    $candidate = (int) $_GET['world'];
    if (!$worlds || in_array($candidate, $worlds, true)) {
        $worldFilter = $candidate;
    }
}

$where = [];
$params = [];
if (in_array('group_id', $playerColumns, true)) {
    $where[] = 'p.group_id <= 1';
}
if (in_array('deletion', $playerColumns, true)) {
    $where[] = 'p.deletion = 0';
}
if (in_array('deleted', $playerColumns, true)) {
    $where[] = 'p.deleted = 0';
}
if (in_array('is_deleted', $playerColumns, true)) {
    $where[] = 'p.is_deleted = 0';
}
if (in_array('hide_char', $playerColumns, true)) {
    $where[] = 'p.hide_char = 0';
}
if ($worldFilter !== null && $worldColumn) {
    $where[] = "p.$worldColumn = :world";
    $params[':world'] = $worldFilter;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$orderParts = ['score DESC'];
if (!empty($selectedType['secondary'])) {
    $orderParts[] = 'p.' . $selectedType['secondary'] . ' DESC';
}
$orderParts[] = 'p.name ASC';
$orderSql = 'ORDER BY ' . implode(', ', $orderParts);

$rows = [];
try {
    if ($selectedType['source'] === 'player_skills') {
        $sql = sprintf(
            'SELECT p.id, p.name, p.level, p.vocation%s, s.%s AS score FROM players p INNER JOIN player_skills s ON s.player_id = p.id %s %s LIMIT :limit',
            $worldColumn ? ', p.' . $worldColumn : '',
            $selectedType['column'],
            $whereSql ? ' ' . $whereSql : '',
            $orderSql
        );
    } else {
        $sql = sprintf(
            'SELECT p.id, p.name, p.level, p.vocation%s, p.%s AS score%s FROM players p %s %s LIMIT :limit',
            $worldColumn ? ', p.' . $worldColumn : '',
            $selectedType['column'],
            !empty($selectedType['secondary']) ? ', p.' . $selectedType['secondary'] . ' AS secondary_value' : '',
            $whereSql ? ' ' . $whereSql : '',
            $orderSql
        );
    }
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $rows = [];
}

require __DIR__ . '/partials/header.php';
?>
<!-- layout:content:start -->
<section class="highscores">
    <header class="page-header">
        <h1>Highscores</h1>
        <p class="subtitle"><?= e($selectedType['description']) ?></p>
    </header>
    <div class="tab-bar">
        <?php foreach ($types as $key => $type): ?>
            <?php $url = '?type=' . urlencode($key) . ($worldFilter !== null ? '&world=' . urlencode((string) $worldFilter) : ''); ?>
            <a href="<?= e($url) ?>" class="tab <?= $key === $selectedKey ? 'active' : '' ?>"><?= e($type['label']) ?></a>
        <?php endforeach; ?>
    </div>
    <?php if ($worldColumn && $worlds): ?>
        <form method="get" class="filter-form">
            <input type="hidden" name="type" value="<?= e($selectedKey) ?>">
            <label>
                <span>World</span>
                <select name="world" onchange="this.form.submit()">
                    <option value="">All Worlds</option>
                    <?php foreach ($worlds as $worldId): ?>
                        <option value="<?= $worldId ?>" <?= $worldFilter === $worldId ? 'selected' : '' ?>>World <?= $worldId ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>
    <?php endif; ?>
    <?php if (!$rows): ?>
        <p class="empty">No data available for this leaderboard.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Vocation</th>
                        <th>Level</th>
                        <th><?= e($selectedType['value_label']) ?></th>
                        <?php if (!empty($selectedType['secondary'])): ?>
                            <th>Experience</th>
                        <?php endif; ?>
                        <?php if ($worldColumn): ?>
                            <th>World</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= $rank++ ?></td>
                            <td><?= e($row['name']) ?></td>
                            <td><?= e(vocationName($db, $row['vocation'] ?? 0)) ?></td>
                            <td><?= isset($row['level']) ? (int) $row['level'] : '—' ?></td>
                            <td><?= isset($row['score']) ? (int) $row['score'] : '—' ?></td>
                            <?php if (!empty($selectedType['secondary'])): ?>
                                <td><?= isset($row['secondary_value']) ? number_format((int) $row['secondary_value']) : '—' ?></td>
                            <?php endif; ?>
                            <?php if ($worldColumn): ?>
                                <td><?= isset($row[$worldColumn]) ? (int) $row[$worldColumn] : '—' ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<!-- layout:content:end -->
<?php require __DIR__ . '/partials/footer.php'; ?>
