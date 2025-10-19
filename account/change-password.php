<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = currentUser();
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $errors = [];

    if ($currentPassword === '' || !hash_equals($user['password'], sha1($currentPassword))) {
        $errors[] = 'Your current password is incorrect.';
    }

    if ($newPassword === '' || strlen($newPassword) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New password and confirmation must match.';
    }

    if (!$errors) {
        $stmt = getDb()->prepare('UPDATE accounts SET password = ? WHERE id = ?');
        $stmt->execute([sha1($newPassword), $user['id']]);
        $user['password'] = sha1($newPassword);
        $flash = ['type' => 'success', 'message' => 'Your password has been updated.'];
    } else {
        $flash = ['type' => 'error', 'message' => implode(' ', $errors)];
    }
}

ob_start();
?>
<form method="post" class="stack">
    <label>
        <span>Current Password</span>
        <input type="password" name="current_password" required autocomplete="current-password">
    </label>
    <label>
        <span>New Password</span>
        <input type="password" name="new_password" required minlength="6" autocomplete="new-password">
    </label>
    <label>
        <span>Confirm New Password</span>
        <input type="password" name="confirm_password" required minlength="6" autocomplete="new-password">
    </label>
    <button type="submit" class="btn primary">Update Password</button>
</form>
<?php
$form = ob_get_clean();

$title = 'Change Password';
$actionHeading = 'Change Password';
$subtitle = 'Refresh your credentials to keep your Nexus One account secure.';
$sections = [
    [
        'title' => 'Update your password',
        'subtitle' => 'Provide your current credentials and choose a new password.',
        'content' => $form,
    ],
    [
        'title' => 'Security recommendations',
        'body' => 'Strengthen your account by following these quick tips when picking a new password.',
        'items' => [
            'Use at least twelve characters mixing letters, numbers, and symbols.',
            'Avoid reusing passwords from other games or services.',
            'Update your password every few months or after playing on shared devices.',
        ],
    ],
];

require __DIR__ . '/action-template.php';
