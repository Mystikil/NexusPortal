<?php

declare(strict_types=1);

/**
 * Determine if the game server is reachable via TCP.
 */
function getServerStatus(array $settings): array
{
    static $cache = null;

    $serverConfig = $settings['server'] ?? [];
    $host = is_array($serverConfig) && isset($serverConfig['host']) ? (string) $serverConfig['host'] : '127.0.0.1';
    $port = is_array($serverConfig) && isset($serverConfig['port']) ? (int) $serverConfig['port'] : 7172;
    $timeout = is_array($serverConfig) && isset($serverConfig['timeout']) ? (float) $serverConfig['timeout'] : 0.5;

    $cacheKey = $host . ':' . $port . ':' . $timeout;
    $now = time();

    if ($cache && $cache['key'] === $cacheKey && ($now - $cache['timestamp']) < 30) {
        return $cache['value'];
    }

    $online = false;
    if (function_exists('fsockopen') && $host !== '' && $port > 0) {
        $errno = 0;
        $errstr = '';
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if ($socket) {
            $online = true;
            fclose($socket);
        }
    }

    $status = [
        'online' => $online,
        'host' => $host,
        'port' => $port,
        'checked_at' => $now,
    ];

    $cache = [
        'key' => $cacheKey,
        'timestamp' => $now,
        'value' => $status,
    ];

    return $status;
}
