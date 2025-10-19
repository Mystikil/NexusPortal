<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$title = 'Rename a Character';
$actionHeading = 'Rename a Character';
$subtitle = 'Give an existing hero a fresh identity without losing reputation.';
$sections = [
    [
        'title' => 'Eligibility checklist',
        'items' => [
            'Characters must be at least level 20 to request a rename.',
            'You can submit one rename request per character every 7 days.',
            'Names must follow the same rules as character creation.',
        ],
    ],
    [
        'title' => 'Requesting a rename',
        'body' => 'Rename actions are bundled with the character management panel while the automated feature is in QA.',
        'cta' => [
            'label' => 'Open Character Management',
            'href' => siteUrl('characters.php'),
            'variant' => 'primary',
        ],
    ],
    [
        'title' => 'Need moderator approval?',
        'body' => 'Lore-sensitive names may require a quick review. Provide your roleplay notes if the name is unusual.',
        'cta' => [
            'label' => 'Message Game Staff',
            'href' => 'mailto:support@example.com',
        ],
    ],
];

require __DIR__ . '/action-template.php';
