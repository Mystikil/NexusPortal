<?php
require_once __DIR__ . '/includes/auth.php';
$title = 'Create Account';
$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = registerAccount($_POST);
    if (!$errors) {
        $success = true;
    }
}
require __DIR__ . '/partials/header.php';
?>
<!-- layout:content:start -->
<section class="form-section">
    <div class="card">
        <h1>Initialize Your Command Deck</h1>
        <p class="subtitle">Create an account to track progress, manage heroes, and access premium drops.</p>
        <?php if ($errors): ?>
            <div class="alert error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($success): ?>
            <div class="alert success">Account created successfully. You are now logged in!</div>
        <?php endif; ?>
        <form method="post" class="form-layout">
            <div class="form-panel">
                <h2>Account Access</h2>
                <p class="section-note">Choose a unique account name and protect it with a strong password.</p>
                <label>
                    <span>Account Name</span>
                    <input type="text" name="name" required minlength="3" maxlength="32" value="<?= e($_POST['name'] ?? '') ?>">
                </label>
                <div class="columns">
                    <label>
                        <span>Password</span>
                        <input type="password" name="password" required minlength="6">
                    </label>
                    <label>
                        <span>Confirm Password</span>
                        <input type="password" name="password_confirm" required minlength="6">
                    </label>
                </div>
            </div>
            <div class="form-panel">
                <h2>Contact & Recovery</h2>
                <p class="section-note">Provide details so we can reach you and help recover access if needed.</p>
                <label>
                    <span>Email</span>
                    <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
                </label>
                <label>
                    <span>Security Secret (16 characters)</span>
                    <input type="text" name="secret" minlength="16" maxlength="16" value="<?= e($_POST['secret'] ?? '') ?>" placeholder="Optional - used for two-factor reset">
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn primary full">Create Account</button>
            </div>
        </form>
        <p class="meta">Already have an account? <a href="<?= e(siteUrl('login.php')) ?>">Sign in.</a></p>
    </div>
</section>
<!-- layout:content:end -->
<?php require __DIR__ . '/partials/footer.php'; ?>
