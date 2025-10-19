<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = currentUser();
$premiumEnds = empty($user['premium_ends_at'])
    ? 'No active premium'
    : date('F j, Y', (int) $user['premium_ends_at']);

$title = 'Cancel Subscription';
$actionHeading = 'Cancel or Pause Premium';
$subtitle = 'Take a break while keeping full control of your Nexus One subscription time.';
$sections = [
    [
        'title' => 'Before you cancel',
        'body' => 'Premium access remains available until the end of your billing cycle.',
        'items' => [
            'Current premium status: ' . $premiumEnds,
            'Any unused time will continue to tick down until the scheduled expiration.',
            'You can reactivate at any time without losing account progress.',
        ],
    ],
    [
        'title' => 'Request a cancellation',
        'body' => 'Our billing add-on is still rolling out, so cancellations are handled by the support crew.',
        'items' => [
            'Send us a ticket with your account name and the reason for cancelling.',
            'We will confirm the change and send you a final expiration timestamp.',
            'Log back in to re-enable premium whenever you are ready.',
        ],
        'cta' => [
            'label' => 'Open Support Ticket',
            'href' => 'mailto:support@example.com',
        ],
    ],
    [
        'title' => 'Need a downgrade instead?',
        'body' => 'You can swap to a shorter plan or gifting option without fully cancelling.',
        'cta' => [
            'label' => 'Explore Subscription Options',
            'href' => siteUrl('account/add-subscription.php'),
        ],
    ],
];

require __DIR__ . '/action-template.php';
