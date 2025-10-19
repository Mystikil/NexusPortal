<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$title = 'Create a Character';
$actionHeading = 'Create a New Hero';
$subtitle = 'Launch a fresh warrior directly from the portal before entering the game world.';
$sections = [
    [
        'title' => 'Create from the portal',
        'body' => 'Use the management suite to craft a new adventurer with the perfect vocation and hometown.',
        'cta' => [
            'label' => 'Open Character Management',
            'href' => siteUrl('characters.php'),
            'variant' => 'primary',
        ],
    ],
    [
        'title' => 'Character slots and limits',
        'items' => [
            'You can maintain up to ten heroes on a single account.',
            'Names can include spaces, apostrophes, and dashes.',
            'Need more slots? Reach out to support after hitting the cap.',
        ],
    ],
    [
        'title' => 'Tips before you create',
        'body' => 'Think about your vocation path, as some spells and equipment are vocation locked.',
        'items' => [
            'Vocations can be changed later via the transfer service.',
            'Premium accounts unlock two additional starting towns.',
            'If you play with friends, coordinate hometowns for the shared quest line.',
        ],
    ],
];

require __DIR__ . '/action-template.php';
