<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$title = 'Delete a Character';
$actionHeading = 'Delete a Character';
$subtitle = 'Retire a hero permanently when you are ready to clear roster space.';
$sections = [
    [
        'title' => 'Delete safely',
        'body' => 'Character removal is permanent. Double check equipment and houses before confirming.',
        'items' => [
            'Log out of the game world before requesting a deletion.',
            'Transfer valuables to a trusted character first.',
            'House ownership is released instantly after removal.',
        ],
    ],
    [
        'title' => 'Ready to proceed?',
        'body' => 'Use the management interface to remove a character tied to your account.',
        'cta' => [
            'label' => 'Manage Characters',
            'href' => siteUrl('characters.php'),
            'variant' => 'secondary',
        ],
    ],
    [
        'title' => 'Need help recovering a mistake?',
        'body' => 'Reach out within 24 hours and the team will investigate if recovery is possible.',
        'cta' => [
            'label' => 'Contact Support',
            'href' => 'mailto:support@example.com',
        ],
    ],
];

require __DIR__ . '/action-template.php';
