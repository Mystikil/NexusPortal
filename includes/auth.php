<?php
session_start();
require_once __DIR__ . '/helpers.php';

function getDb(): PDO
{
    static $db;
    if (!$db) {
        $db = require __DIR__ . '/db.php';
    }
    return $db;
}

function currentUser(): ?array
{
    if (empty($_SESSION['account_id'])) {
        return null;
    }

    static $cached;
    if ($cached && $cached['id'] === $_SESSION['account_id']) {
        return $cached;
    }

    $stmt = getDb()->prepare('SELECT * FROM accounts WHERE id = ?');
    $stmt->execute([$_SESSION['account_id']]);
    $cached = $stmt->fetch();
    return $cached ?: null;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function isAdmin(): bool
{
    $user = currentUser();
    return $user && (int) $user['type'] >= 3;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . siteUrl('login.php'));
        exit;
    }
}

function requireAdmin(): void
{
    if (!isAdmin()) {
        header('Location: ' . siteUrl('login.php'));
        exit;
    }
}

function login(string $name, string $password): bool
{
    $stmt = getDb()->prepare('SELECT * FROM accounts WHERE name = ?');
    $stmt->execute([$name]);
    $account = $stmt->fetch();
    if (!$account) {
        return false;
    }

    $hash = sha1($password);
    if (!hash_equals($account['password'], $hash)) {
        return false;
    }

    $_SESSION['account_id'] = $account['id'];
    return true;
}

function logout(): void
{
    session_destroy();
}

function registerAccount(array $data): array
{
    $errors = [];

    $name = trim($data['name'] ?? '');
    $password = $data['password'] ?? '';
    $passwordConfirm = $data['password_confirm'] ?? '';
    $email = trim($data['email'] ?? '');
    $secret = trim($data['secret'] ?? '');

    if ($name === '' || strlen($name) < 3 || strlen($name) > 32) {
        $errors[] = 'Account name must be between 3 and 32 characters.';
    }

    if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
        $errors[] = 'Account name may only contain letters, numbers, and underscores.';
    }

    if ($password === '' || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = 'Passwords do not match.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if ($secret !== '' && strlen($secret) !== 16) {
        $errors[] = 'Secret must be 16 characters if provided.';
    }

    if ($errors) {
        return $errors;
    }

    $db = getDb();
    $stmt = $db->prepare('SELECT id FROM accounts WHERE name = ?');
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        return ['An account with this name already exists.'];
    }

    $stmt = $db->prepare('INSERT INTO accounts (name, password, secret, email, creation) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([
        $name,
        sha1($password),
        $secret !== '' ? $secret : null,
        $email,
        time()
    ]);

    $_SESSION['account_id'] = (int) $db->lastInsertId();
    return [];
}
