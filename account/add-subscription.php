<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = currentUser();
$premiumEnds = empty($user['premium_ends_at'])
    ? 'No active premium'
    : date('F j, Y', (int) $user['premium_ends_at']);

ob_start();
?>
<div class="stack">
    <p class="meta">Account: <strong><?= e($user['name']) ?></strong></p>
    <p class="meta">Premium status: <strong><?= e($premiumEnds) ?></strong></p>
</div>
<?php
$statusBlock = ob_get_clean();

$title = 'Add Subscription';
$actionHeading = 'Add Subscription Time';
$subtitle = 'Extend your command privileges with premium benefits and exclusive drops.';
$sections = [
    [
        'title' => 'Current premium status',
        'content' => $statusBlock,
    ],
    [
        'title' => 'Redeem a premium code',
        'body' => 'Premium codes are currently issued manually while we finalize the billing pipeline.',
        'items' => [
            'Reach out to the team with your account name and desired duration.',
            'Once confirmed, you will receive a secure code for activation.',
            'Premium time is applied instantly after our staff validates the code.',
        ],
        'cta' => [
            'label' => 'Email Support',
            'href' => 'mailto:support@example.com',
        ],
    ],
    [
        'title' => 'Want to support development?',
        'body' => 'Consider making a donation to unlock cosmetics and priority queue access.',
        'cta' => [
            'label' => 'View Donation Options',
            'href' => siteUrl('account/donate.php'),
            'variant' => 'primary',
        ],
    ],
];

require __DIR__ . '/action-template.php';
