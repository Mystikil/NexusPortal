<?php
return [
    'db' => [
        'host' => getenv('DEVNEXUS_DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DEVNEXUS_DB_PORT') ?: '3306',
        'name' => getenv('DEVNEXUS_DB_NAME') ?: '1098',
        'user' => getenv('DEVNEXUS_DB_USER') ?: 'root',
        'password' => getenv('DEVNEXUS_DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'site' => [
        'name' => 'Nexus One',
        'base_url' => '/',
    ],
    'server' => [
        'host' => getenv('DEVNEXUS_GAME_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('DEVNEXUS_GAME_PORT') ?: 7172),
        'timeout' => (float) (getenv('DEVNEXUS_GAME_TIMEOUT') ?: 0.5),
    ],
];
