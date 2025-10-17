<?php

require_once __DIR__ . '/game.php';

function fetchCharacterByName(PDO $db, string $name): ?array
{
    $stmt = $db->prepare('SELECT * FROM players WHERE name = ?');
    $stmt->execute([$name]);
    $character = $stmt->fetch(PDO::FETCH_ASSOC);

    return $character ?: null;
}

function fetchRecentDeathsForCharacter(PDO $db, int $playerId, int $limit = 5): array
{
    if ($playerId <= 0 || !tableExists($db, 'player_deaths')) {
        return [];
    }

    $deathColumns = tableColumns($db, 'player_deaths');
    $playerColumn = in_array('player_id', $deathColumns, true) ? 'player_id' : (in_array('player', $deathColumns, true) ? 'player' : null);
    if (!$playerColumn) {
        return [];
    }

    $timeColumn = in_array('time', $deathColumns, true) ? 'time' : (in_array('date', $deathColumns, true) ? 'date' : null);
    $orderColumn = $timeColumn ? 'd.' . $timeColumn : null;

    $select = [];
    $select[] = $timeColumn ? 'd.' . $timeColumn . ' AS death_time' : '0 AS death_time';
    $select[] = in_array('level', $deathColumns, true) ? 'd.level AS death_level' : '0 AS death_level';
    $select[] = in_array('killed_by', $deathColumns, true) ? 'd.killed_by' : "'' AS killed_by";
    $select[] = in_array('is_player', $deathColumns, true) ? 'd.is_player' : '0 AS is_player';
    if (in_array('mostdamage_by', $deathColumns, true)) {
        $select[] = 'd.mostdamage_by';
    } else {
        $select[] = "'' AS mostdamage_by";
    }
    if (in_array('mostdamage_is_player', $deathColumns, true)) {
        $select[] = 'd.mostdamage_is_player';
    } else {
        $select[] = '0 AS mostdamage_is_player';
    }
    if (in_array('unjustified', $deathColumns, true)) {
        $select[] = 'd.unjustified';
    } else {
        $select[] = '0 AS unjustified';
    }
    if (in_array('mostdamage_unjustified', $deathColumns, true)) {
        $select[] = 'd.mostdamage_unjustified';
    } else {
        $select[] = '0 AS mostdamage_unjustified';
    }

    $selectSql = implode(', ', $select);
    $sql = sprintf('SELECT %s FROM player_deaths d WHERE d.%s = :player%s LIMIT :limit',
        $selectSql,
        $playerColumn,
        $orderColumn ? ' ORDER BY ' . $orderColumn . ' DESC' : ''
    );

    try {
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':player', $playerId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        return [];
    }
}

function fetchAccountCharacters(PDO $db, int $accountId): array
{
    $stmt = $db->prepare('SELECT * FROM players WHERE account_id = ? ORDER BY level DESC, name ASC');
    $stmt->execute([$accountId]);
    return $stmt->fetchAll() ?: [];
}

function createCharacter(PDO $db, array $account, array $input): array
{
    $errors = [];
    $name = trim($input['name'] ?? '');
    if ($name !== '') {
        $name = preg_replace('/\s+/', ' ', $name);
    }
    $sex = isset($input['sex']) ? (int) $input['sex'] : 1;
    $vocation = isset($input['vocation']) ? (int) $input['vocation'] : 1;
    $townId = isset($input['town_id']) ? (int) $input['town_id'] : null;

    if ($name === '' || strlen($name) < 3 || strlen($name) > 25) {
        $errors[] = 'Character name must be between 3 and 25 characters.';
    }

    if ($name !== '' && !preg_match("/^[A-Za-z][A-Za-z '\\-]+$/", $name)) {
        $errors[] = 'Name may only contain letters, spaces, apostrophes, and hyphens.';
    }

    if (!in_array($sex, [0, 1], true)) {
        $errors[] = 'Invalid gender selection.';
    }

    $vocations = getVocationOptions($db);
    if (!array_key_exists($vocation, $vocations)) {
        $errors[] = 'Invalid vocation selected.';
    }

    $towns = getTownOptions($db);
    if ($townId === null) {
        $townId = $towns[0]['id'] ?? 1;
    }

    $availableTownIds = array_column($towns, 'id');
    if (!in_array($townId, $availableTownIds, true)) {
        $errors[] = 'Invalid home town selected.';
    }

    $characters = fetchAccountCharacters($db, (int) $account['id']);
    if (count($characters) >= 10) {
        $errors[] = 'You have reached the maximum number of characters for this account.';
    }

    if ($errors) {
        return $errors;
    }

    $stmt = $db->prepare('SELECT 1 FROM players WHERE name = ?');
    $stmt->execute([$name]);
    if ($stmt->fetchColumn()) {
        return ['A character with this name already exists.'];
    }

    $town = null;
    foreach ($towns as $candidate) {
        if ((int) $candidate['id'] === $townId) {
            $town = $candidate;
            break;
        }
    }

    if (!$town) {
        $town = $towns[0];
    }

    $columns = tableColumns($db, 'players');
    if (!$columns) {
        return ['Unable to determine player table schema.'];
    }

    $looktypeMale = 128;
    $looktypeFemale = 136;
    $defaults = [
        'name' => $name,
        'account_id' => (int) $account['id'],
        'sex' => $sex,
        'vocation' => $vocation,
        'level' => 1,
        'maglevel' => 0,
        'experience' => 0,
        'health' => 150,
        'healthmax' => 150,
        'mana' => 0,
        'manamax' => 0,
        'soul' => 0,
        'cap' => 400,
        'town_id' => $town['id'],
        'posx' => $town['temple_x'] ?? 0,
        'posy' => $town['temple_y'] ?? 0,
        'posz' => $town['temple_z'] ?? 0,
        'looktype' => $sex === 0 ? $looktypeFemale : $looktypeMale,
        'lookhead' => 78,
        'lookbody' => 68,
        'looklegs' => 58,
        'lookfeet' => 76,
        'lookaddons' => 0,
        'lookmount' => 0,
        'stamina' => 151200000,
        'lastlogin' => 0,
        'lastlogout' => 0,
        'blessings' => 0,
        'direction' => 2,
        'world_id' => 0,
        'group_id' => 1,
        'balance' => 0,
        'skill_fist' => 10,
        'skill_club' => 10,
        'skill_sword' => 10,
        'skill_axe' => 10,
        'skill_dist' => 10,
        'skill_shielding' => 10,
        'skill_fishing' => 10,
        'skill_fist_tries' => 0,
        'skill_club_tries' => 0,
        'skill_sword_tries' => 0,
        'skill_axe_tries' => 0,
        'skill_dist_tries' => 0,
        'skill_shielding_tries' => 0,
        'skill_fishing_tries' => 0,
        'online' => 0,
    ];

    $insertData = [];
    foreach ($defaults as $column => $value) {
        if (in_array($column, $columns, true)) {
            $insertData[$column] = $value;
        }
    }

    // Required fields fallback
    foreach (['name', 'account_id', 'sex', 'vocation', 'level', 'experience', 'health', 'town_id'] as $required) {
        if (!array_key_exists($required, $insertData)) {
            $insertData[$required] = $defaults[$required] ?? 0;
        }
    }

    $columnSql = implode(', ', array_map(fn($col) => "`$col`", array_keys($insertData)));
    $placeholders = implode(', ', array_fill(0, count($insertData), '?'));

    try {
        $stmt = $db->prepare(sprintf('INSERT INTO players (%s) VALUES (%s)', $columnSql, $placeholders));
        $stmt->execute(array_values($insertData));
        $playerId = (int) $db->lastInsertId();
    } catch (PDOException $e) {
        return ['Failed to create character. Please try again later.'];
    }

    if ($playerId && tableExists($db, 'player_skills')) {
        try {
            $skillColumns = tableColumns($db, 'player_skills');
            $baseSkills = [
                'player_id' => $playerId,
                'fist' => 10,
                'club' => 10,
                'sword' => 10,
                'axe' => 10,
                'distance' => 10,
                'shield' => 10,
                'fishing' => 10,
                'fist_tries' => 0,
                'club_tries' => 0,
                'sword_tries' => 0,
                'axe_tries' => 0,
                'distance_tries' => 0,
                'shield_tries' => 0,
                'fishing_tries' => 0,
            ];

            $skillData = [];
            foreach ($baseSkills as $column => $value) {
                if (in_array($column, $skillColumns, true)) {
                    $skillData[$column] = $value;
                }
            }

            if ($skillData) {
                $skillColumnsSql = implode(', ', array_map(fn($col) => "`$col`", array_keys($skillData)));
                $skillPlaceholders = implode(', ', array_fill(0, count($skillData), '?'));
                $stmt = $db->prepare(sprintf('INSERT INTO player_skills (%s) VALUES (%s)', $skillColumnsSql, $skillPlaceholders));
                $stmt->execute(array_values($skillData));
            }
        } catch (PDOException $e) {
            // ignore skill insertion failures; character already created
        }
    }

    return [];
}

function renameCharacter(PDO $db, array $account, array $input): array
{
    $playerId = isset($input['player_id']) ? (int) $input['player_id'] : 0;
    $newName = trim($input['new_name'] ?? '');

    if ($playerId <= 0 || $newName === '') {
        return ['Invalid request.'];
    }

    if (strlen($newName) < 3 || strlen($newName) > 25) {
        return ['New name must be between 3 and 25 characters.'];
    }

    $newName = preg_replace('/\s+/', ' ', $newName);

    if (!preg_match("/^[A-Za-z][A-Za-z '\\-]+$/", $newName)) {
        return ['Name may only contain letters, spaces, apostrophes, and hyphens.'];
    }

    $stmt = $db->prepare('SELECT id FROM players WHERE name = ?');
    $stmt->execute([$newName]);
    if ($stmt->fetch()) {
        return ['A character with this name already exists.'];
    }

    $stmt = $db->prepare('SELECT name FROM players WHERE id = ? AND account_id = ?');
    $stmt->execute([$playerId, (int) $account['id']]);
    $currentName = $stmt->fetchColumn();
    if ($currentName === false) {
        return ['Character not found.'];
    }

    if (strcasecmp($currentName, $newName) === 0) {
        return [];
    }

    try {
        $stmt = $db->prepare('UPDATE players SET name = ? WHERE id = ?');
        $stmt->execute([$newName, $playerId]);
    } catch (PDOException $e) {
        return ['Failed to rename character.'];
    }

    return [];
}

function deleteCharacter(PDO $db, array $account, int $playerId): array
{
    if ($playerId <= 0) {
        return ['Invalid character selected.'];
    }

    $stmt = $db->prepare('SELECT * FROM players WHERE id = ? AND account_id = ?');
    $stmt->execute([$playerId, (int) $account['id']]);
    $character = $stmt->fetch();

    if (!$character) {
        return ['Character not found.'];
    }

    if (array_key_exists('online', $character) && (int) $character['online'] === 1) {
        return ['Please log out the character in-game before deleting it.'];
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare('DELETE FROM players WHERE id = ?');
        $stmt->execute([$playerId]);

        $cleanupTables = [
            'player_deaths' => 'player_id',
            'player_skills' => 'player_id',
            'player_guild' => 'player_id',
            'guild_invites' => 'player_id',
        ];

        foreach ($cleanupTables as $table => $column) {
            if (tableExists($db, $table)) {
                $sql = sprintf('DELETE FROM %s WHERE %s = ?', $table, $column);
                $stmt = $db->prepare($sql);
                $stmt->execute([$playerId]);
            }
        }

        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        return ['Failed to delete character.'];
    }

    return [];
}
