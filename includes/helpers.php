<?php
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function formatRelativeTime(int $timestamp): string
{
    $diff = time() - $timestamp;
    if ($diff < 60) {
        return $diff . ' seconds ago';
    }
    if ($diff < 3600) {
        $mins = (int) floor($diff / 60);
        return $mins . ' minute' . ($mins === 1 ? '' : 's') . ' ago';
    }
    if ($diff < 86400) {
        $hours = (int) floor($diff / 3600);
        return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';
    }
    $days = (int) floor($diff / 86400);
    return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
}

function siteUrl(string $path = ''): string
{
    static $base;

    if ($base === null) {
        $settings = require __DIR__ . '/../config.php';
        $configured = $settings['site']['base_url'] ?? '/';
        if ($configured === '') {
            $configured = '/';
        }
        $base = rtrim($configured, '/');
    }

    $path = ltrim($path, '/');

    if ($base === '' || $base === null) {
        return '/' . $path;
    }

    if ($path === '') {
        return $base;
    }

    return $base . '/' . $path;
}

function assetUrl(string $path): string
{
    return siteUrl('assets/' . ltrim($path, '/'));
}
