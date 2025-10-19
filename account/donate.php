<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$title = 'Support the Project';
$actionHeading = 'Make a Donation';
$subtitle = 'Fuel new features, art, and server hardware by supporting Nexus One.';
$sections = [
    [
        'title' => 'Why donate?',
        'body' => 'Player donations help cover hosting costs, artist commissions, and seasonal events.',
        'items' => [
            'Priority access to beta features and stress tests.',
            'Unique cosmetic auras that never return to the shop.',
            'Voting power on upcoming dungeon mechanics.',
        ],
    ],
    [
        'title' => 'Available packages',
        'content' => '<ul class="checklist"><li>Traveler Pack — $5</li><li>Champion Pack — $15</li><li>Legend Pack — $30</li></ul>',
    ],
    [
        'title' => 'Ready to contribute?',
        'body' => 'Send your preferred package and account name to our treasury inbox. A game master will confirm delivery in under 24 hours.',
        'cta' => [
            'label' => 'treasury@nexus.one',
            'href' => 'mailto:treasury@nexus.one',
            'variant' => 'secondary',
        ],
    ],
];

require __DIR__ . '/action-template.php';
