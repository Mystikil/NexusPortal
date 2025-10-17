<?php
require_once __DIR__ . '/includes/auth.php';
$title = 'Login';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login($name, $password)) {
        header('Location: /N1/dashboard.php');
        exit;
    }
    $error = 'Invalid credentials. Please try again.';
}
require __DIR__ . '/partials/header.php';
?>
<section class="form-section">
    <div class="card">
        <h1>Welcome Back</h1>
        <p class="subtitle">Log into your Nexus One account to continue the adventure.</p>
        <?php if ($error): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" class="stack">
            <label>
                <span>Account Name</span>
                <input type="text" name="name" required maxlength="32" autocomplete="username">
            </label>
            <label>
                <span>Password</span>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button type="submit" class="btn primary full">Log In</button>
        </form>
        <p class="meta">Need an account? <a href="/N1/register.php">Create one now.</a></p>
    </div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
