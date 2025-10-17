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
