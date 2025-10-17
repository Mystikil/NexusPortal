<?php


function tableColumns(PDO $db, string $table): array
{
    static $cache = [];
    if (isset($cache[$table])) {
        return $cache[$table];
    }

    try {
        $stmt = $db->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
        $stmt->execute([$table]);
        $cache[$table] = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    } catch (PDOException $e) {
        $cache[$table] = [];
    }

    return $cache[$table];
}

function tableExists(PDO $db, string $table): bool
{
    return !empty(tableColumns($db, $table));
}

function getVocationOptions(PDO $db): array
{
    static $cache;
    if ($cache !== null) {
        return $cache;
    }

    $options = [];
    if (tableExists($db, 'vocations')) {
        try {
            $stmt = $db->query('SELECT id, name FROM vocations ORDER BY id ASC');
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $options[(int) $row['id']] = $row['name'];
            }
        } catch (PDOException $e) {
            // ignore and fallback to defaults
        }
    }

    if (!$options) {
        $options = [
            0 => 'Rookie',
            1 => 'Sorcerer',
            2 => 'Druid',
            3 => 'Paladin',
            4 => 'Knight',
            5 => 'Master Sorcerer',
            6 => 'Elder Druid',
            7 => 'Royal Paladin',
            8 => 'Elite Knight',
        ];
    }

    $cache = $options;
    return $options;
}

function vocationName(PDO $db, $id): string
{
    $options = getVocationOptions($db);
    $id = (int) $id;
    return $options[$id] ?? ('Vocation #' . $id);
}

function getTownOptions(PDO $db): array
{
    static $cache;
    if ($cache !== null) {
        return $cache;
    }

    $towns = [];
    if (tableExists($db, 'towns')) {
        $columns = tableColumns($db, 'towns');
        $idColumn = in_array('town_id', $columns, true) ? 'town_id' : (in_array('id', $columns, true) ? 'id' : null);
        $nameColumn = in_array('name', $columns, true) ? 'name' : null;
        $xColumn = in_array('temple_x', $columns, true) ? 'temple_x' : (in_array('posx', $columns, true) ? 'posx' : null);
        $yColumn = in_array('temple_y', $columns, true) ? 'temple_y' : (in_array('posy', $columns, true) ? 'posy' : null);
        $zColumn = in_array('temple_z', $columns, true) ? 'temple_z' : (in_array('posz', $columns, true) ? 'posz' : null);

        if ($idColumn && $nameColumn) {
            $select = sprintf('SELECT %s AS id, %s AS name', $idColumn, $nameColumn);
            if ($xColumn) {
                $select .= sprintf(', %s AS temple_x', $xColumn);
            } else {
                $select .= ', 0 AS temple_x';
            }
            if ($yColumn) {
                $select .= sprintf(', %s AS temple_y', $yColumn);
            } else {
                $select .= ', 0 AS temple_y';
            }
            if ($zColumn) {
                $select .= sprintf(', %s AS temple_z', $zColumn);
            } else {
                $select .= ', 0 AS temple_z';
            }
            $select .= ' FROM towns ORDER BY name ASC';

            try {
                $stmt = $db->query($select);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $towns[] = [
                        'id' => (int) $row['id'],
                        'name' => $row['name'],
                        'temple_x' => (int) ($row['temple_x'] ?? 0),
                        'temple_y' => (int) ($row['temple_y'] ?? 0),
                        'temple_z' => (int) ($row['temple_z'] ?? 0),
                    ];
                }
            } catch (PDOException $e) {
                $towns = [];
            }
        }
    }

    if (!$towns) {
        $towns = [[
            'id' => 1,
            'name' => 'Mainland',
            'temple_x' => 100,
            'temple_y' => 100,
            'temple_z' => 7,
        ]];
    }

    $cache = $towns;
    return $towns;
}

function formatOnlineStatus(array $character): string
{
    if (!array_key_exists('online', $character)) {
        return 'Offline';
    }
    return ((int) $character['online']) === 1 ? 'Online' : 'Offline';
}
