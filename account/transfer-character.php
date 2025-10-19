<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$title = 'Transfer a Character';
$actionHeading = 'Transfer a Character';
$subtitle = 'Move a hero to a new account or shard without losing their progress.';
$sections = [
    [
        'title' => 'How transfers work',
        'items' => [
            'Characters must be offline for at least 10 minutes before transfer.',
            'House ownership and guild membership are cleared automatically.',
            'Premium accounts can transfer between shards once per 30 days.',
        ],
    ],
    [
        'title' => 'Submit a transfer request',
        'body' => 'Transfers are currently coordinated by the admin team while automation is in development.',
        'items' => [
            'Send us the origin and destination account names.',
            'Include the target world if you are switching shards.',
            'We will notify both account owners before the move is finalized.',
        ],
        'cta' => [
            'label' => 'Email Transfer Team',
            'href' => 'mailto:transfers@nexus.one',
        ],
    ],
    [
        'title' => 'Need to move multiple characters?',
        'body' => 'Let us know in your ticket and we will batch them into a single maintenance window.',
    ],
];

require __DIR__ . '/action-template.php';
