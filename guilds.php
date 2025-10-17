<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/game.php';

$db = getDb();
$title = 'Guild Directory';

if (!tableExists($db, 'guilds')) {
    $guilds = [];
    $selectedGuild = null;
    $members = [];
} else {
    $guildColumns = tableColumns($db, 'guilds');
    $guildIdColumn = in_array('guild_id', $guildColumns, true) ? 'guild_id' : (in_array('id', $guildColumns, true) ? 'id' : null);
    $guildIdColumn = $guildIdColumn ?: 'guild_id';
    $nameColumn = in_array('name', $guildColumns, true) ? 'name' : $guildIdColumn;
    $motdColumn = in_array('motd', $guildColumns, true) ? 'motd' : null;
    $createdColumn = in_array('created', $guildColumns, true) ? 'created' : null;
    $balanceColumn = in_array('balance', $guildColumns, true) ? 'balance' : null;
    $worldColumn = in_array('world_id', $guildColumns, true) ? 'world_id' : null;

    $selectParts = ["g.$guildIdColumn AS id", "g.$nameColumn AS name"];
    if ($motdColumn) {
        $selectParts[] = "g.$motdColumn AS motd";
    }
    if ($createdColumn) {
        $selectParts[] = "g.$createdColumn AS created";
    }
    if ($balanceColumn) {
        $selectParts[] = "g.$balanceColumn AS balance";
    }
    if ($worldColumn) {
        $selectParts[] = "g.$worldColumn AS world_id";
    }

    $selectSql = implode(', ', $selectParts);
    $playerGuildColumns = tableExists($db, 'player_guild') ? tableColumns($db, 'player_guild') : [];
    $playerGuildGuildColumn = in_array('guild_id', $playerGuildColumns, true) ? 'guild_id' : (in_array('id', $playerGuildColumns, true) ? 'id' : null);
    $playerGuildGuildColumn = $playerGuildGuildColumn ?: 'guild_id';
    $playerGuildPlayerColumn = in_array('player_id', $playerGuildColumns, true) ? 'player_id' : null;

    $joinClause = '';
    if ($playerGuildPlayerColumn) {
        $joinClause = sprintf('LEFT JOIN player_guild pg ON pg.%s = g.%s', $playerGuildGuildColumn, $guildIdColumn);
    }

    $memberCountSql = $playerGuildPlayerColumn ? ', COUNT(pg.' . $playerGuildPlayerColumn . ') AS members' : ', 0 AS members';

    $sql = sprintf('SELECT %s%s FROM guilds g %s GROUP BY g.%s ORDER BY g.%s ASC',
        $selectSql,
        $memberCountSql,
        $joinClause,
        $guildIdColumn,
        $nameColumn
    );

    try {
        $stmt = $db->query($sql);
        $guilds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $guilds = [];
    }

    $selectedGuild = null;
    $members = [];
    if ($guilds) {
        $requested = $_GET['guild'] ?? '';
        if ($requested !== '') {
            if (ctype_digit($requested)) {
                $stmt = $db->prepare(sprintf('SELECT %s FROM guilds WHERE %s = ?', $selectSql, $guildIdColumn));
                $stmt->execute([(int) $requested]);
            } else {
                $stmt = $db->prepare(sprintf('SELECT %s FROM guilds WHERE %s = ?', $selectSql, $nameColumn));
                $stmt->execute([$requested]);
            }
            $selectedGuild = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        if (!$selectedGuild) {
            $selectedGuild = $guilds[0];
        }

        if ($selectedGuild && $playerGuildPlayerColumn) {
            $guildRanks = tableExists($db, 'guild_ranks');
            $rankColumns = $guildRanks ? tableColumns($db, 'guild_ranks') : [];
            $rankTitleColumn = in_array('title', $rankColumns, true) ? 'title' : null;
            $rankIdColumn = in_array('rank_id', $rankColumns, true) ? 'rank_id' : null;
            $rankGuildColumn = in_array('guild_id', $rankColumns, true) ? 'guild_id' : null;

            $playerColumns = tableColumns($db, 'players');
            $playerLevelColumn = in_array('level', $playerColumns, true) ? 'level' : null;
            $playerVocationColumn = in_array('vocation', $playerColumns, true) ? 'vocation' : null;
            $playerLastLoginColumn = in_array('lastlogin', $playerColumns, true) ? 'lastlogin' : null;

            $memberSelect = ['p.name'];
            if ($playerLevelColumn) {
                $memberSelect[] = 'p.' . $playerLevelColumn . ' AS level';
            }
            if ($playerVocationColumn) {
                $memberSelect[] = 'p.' . $playerVocationColumn . ' AS vocation';
            }
            if ($playerLastLoginColumn) {
                $memberSelect[] = 'p.' . $playerLastLoginColumn . ' AS lastlogin';
            }
            $memberSelect[] = 'pg.guild_title AS title';
            if (in_array('joined', $playerGuildColumns, true)) {
                $memberSelect[] = 'pg.joined';
            }
            if ($guildRanks && $rankTitleColumn && $rankIdColumn && $rankGuildColumn) {
                $memberSelect[] = 'gr.' . $rankTitleColumn . ' AS rank_title';
                $joinRank = sprintf('LEFT JOIN guild_ranks gr ON gr.%s = pg.guild_rank AND gr.%s = pg.%s', $rankIdColumn, $rankGuildColumn, $playerGuildGuildColumn);
            } else {
                $joinRank = '';
            }

            $memberSql = sprintf(
                'SELECT %s FROM player_guild pg INNER JOIN players p ON p.id = pg.%s %s WHERE pg.%s = :guild ORDER BY %s',
                implode(', ', $memberSelect),
                $playerGuildPlayerColumn,
                $joinRank,
                $playerGuildGuildColumn,
                $playerLevelColumn ? 'p.' . $playerLevelColumn . ' DESC, p.name ASC' : 'p.name ASC'
            );

            try {
                $stmt = $db->prepare($memberSql);
                $stmt->bindValue(':guild', (int) $selectedGuild['id'], PDO::PARAM_INT);
                $stmt->execute();
                $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $members = [];
            }
        }
    }
}

require __DIR__ . '/partials/header.php';
?>
<!-- layout:content:start -->
<section class="guilds">
    <header class="page-header">
        <h1>Guild Directory</h1>
        <p class="subtitle">Browse active guilds, review their rosters, and keep up with your allies.</p>
    </header>
    <?php if (empty($guilds)): ?>
        <p class="empty">Guild data is not available in the database.</p>
    <?php else: ?>
        <div class="guild-layout">
            <aside class="guild-list">
                <?php foreach ($guilds as $guild): ?>
                    <?php $isActive = isset($selectedGuild['id']) && (int) $selectedGuild['id'] === (int) $guild['id']; ?>
                    <a class="guild-row <?= $isActive ? 'active' : '' ?>" href="?guild=<?= (int) $guild['id'] ?>">
                        <h3><?= e($guild['name']) ?></h3>
                        <p class="meta">Members: <?= isset($guild['members']) ? (int) $guild['members'] : 0 ?><?= isset($guild['world_id']) ? ' &bull; World ' . (int) $guild['world_id'] : '' ?></p>
                    </a>
                <?php endforeach; ?>
            </aside>
            <section class="guild-detail">
                <?php if ($selectedGuild): ?>
                    <header>
                        <h2><?= e($selectedGuild['name']) ?></h2>
                        <?php if (!empty($selectedGuild['motd'])): ?>
                            <p class="motd">“<?= e($selectedGuild['motd']) ?>”</p>
                        <?php endif; ?>
                    </header>
                    <dl class="guild-stats">
                        <?php if (isset($selectedGuild['created'])): ?>
                            <div>
                                <dt>Founded</dt>
                                <dd>
                                    <?php if (is_numeric($selectedGuild['created'])): ?>
                                        <?= date('F j, Y', (int) $selectedGuild['created']) ?>
                                    <?php else: ?>
                                        <?= e($selectedGuild['created']) ?>
                                    <?php endif; ?>
                                </dd>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($selectedGuild['balance'])): ?>
                            <div>
                                <dt>Treasury</dt>
                                <dd><?= number_format((int) $selectedGuild['balance']) ?> gold</dd>
                            </div>
                        <?php endif; ?>
                        <div>
                            <dt>Roster Size</dt>
                            <dd><?= count($members) ?></dd>
                        </div>
                    </dl>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Vocation</th>
                                    <th>Level</th>
                                    <th>Rank</th>
                                    <th>Title</th>
                                    <th>Joined</th>
                                    <th>Last Login</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$members): ?>
                                    <tr>
                                        <td colspan="7" class="empty">No members recorded for this guild.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($members as $member): ?>
                                        <tr>
                                            <td><?= e($member['name']) ?></td>
                                            <td><?= isset($member['vocation']) ? e(vocationName($db, $member['vocation'])) : '—' ?></td>
                                            <td><?= isset($member['level']) ? (int) $member['level'] : '—' ?></td>
                                            <td><?= e($member['rank_title'] ?? 'Member') ?></td>
                                            <td><?= e($member['title'] ?? '') ?></td>
                                            <td>
                                                <?php if (isset($member['joined']) && (int) $member['joined'] > 0): ?>
                                                    <?= date('Y-m-d', (int) $member['joined']) ?>
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($member['lastlogin']) && (int) $member['lastlogin'] > 0): ?>
                                                    <?= date('Y-m-d', (int) $member['lastlogin']) ?>
                                                <?php else: ?>
                                                    Never
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
