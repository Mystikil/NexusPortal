<?php
$MENU = [
    [
        'label' => 'Home',
        'href' => '/N1/index.php',
        'active_key' => 'index',
    ],
    [
        'label' => 'Game World',
        'href' => '/N1/news.php',
        'active_key' => 'game',
        'children' => [
            [
                'heading' => 'Live Service',
                'description' => 'Stay briefed on world events and rankings.',
                'links' => [
                    [
                        'label' => 'News & Updates',
                        'href' => '/N1/news.php',
                        'description' => 'Latest communications from command.',
                    ],
                    [
                        'label' => 'Highscores',
                        'href' => '/N1/highscores.php',
                        'description' => 'Top-performing squads and lone wolves.',
                    ],
                    [
                        'label' => 'Recent Deaths',
                        'href' => '/N1/deaths.php',
                        'description' => 'Monitor fallen heroes and rival guilds.',
                    ],
                ],
            ],
            [
                'heading' => 'Intelligence',
                'description' => 'Essential intel before every deployment.',
                'links' => [
                    [
                        'label' => 'Living World Atlas',
                        'href' => '/N1/living-world.php',
                        'description' => 'Dynamic events shifting across the realms.',
                    ],
                    [
                        'label' => 'Guild Directory',
                        'href' => '/N1/guilds.php',
                        'description' => 'Meet allied forces and dominant factions.',
                    ],
                    [
                        'label' => 'Character Lookup',
                        'href' => '/N1/character.php',
                        'description' => 'Search operatives by name or vocation.',
                    ],
                ],
            ],
            [
                'heading' => 'Get Started',
                'description' => 'Secure your access and manage your roster.',
                'links' => [
                    [
                        'label' => 'Create Account',
                        'href' => '/N1/register.php',
                        'description' => 'Claim launch rewards and reserve your alias.',
                    ],
                    [
                        'label' => 'Account Dashboard',
                        'href' => '/N1/dashboard.php',
                        'description' => 'Command center for characters and assets.',
                    ],
                ],
            ],
        ],
    ],
    [
        'label' => 'Community',
        'href' => '/N1/guilds.php',
        'active_key' => 'community',
        'children' => [
            [
                'links' => [
                    [
                        'label' => 'Guild Directory',
                        'href' => '/N1/guilds.php',
                    ],
                    [
                        'label' => 'Character Lookup',
                        'href' => '/N1/character.php',
                    ],
                ],
            ],
        ],
    ],
    [
        'label' => 'Newsroom',
        'href' => '/N1/news.php',
        'active_key' => 'news',
    ],
    [
        'label' => 'Account',
        'href' => '/N1/login.php',
        'active_key' => 'account',
        'utility' => true,
        'children' => [
            'guest' => [
                [
                    'label' => 'Create Account',
                    'href' => '/N1/register.php',
                    'description' => 'Unlock exclusive launch rewards.',
                ],
                [
                    'label' => 'Login',
                    'href' => '/N1/login.php',
                    'description' => 'Enter the Nexus and continue your mission.',
                ],
            ],
            'auth' => [
                [
                    'label' => 'Dashboard',
                    'href' => '/N1/dashboard.php',
                    'description' => 'Review alerts, stats, and account tools.',
                ],
                [
                    'label' => 'Characters',
                    'href' => '/N1/characters.php',
                    'description' => 'Manage your roster and gear loadouts.',
                ],
                [
                    'label' => 'Admin Console',
                    'href' => '/N1/admin/index.php',
                    'description' => 'Administrative access for command staff.',
                    'requires_admin' => true,
                ],
                [
                    'label' => 'Logout',
                    'href' => '/N1/logout.php',
                    'description' => 'Securely close your current session.',
                ],
            ],
        ],
    ],
    [
        'type' => 'search',
        'utility' => true,
        'placeholder' => 'Search the portal',
    ],
    [
        'type' => 'theme-toggle',
        'utility' => true,
    ],
];

return $MENU;
