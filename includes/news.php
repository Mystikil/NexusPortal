<?php
require_once __DIR__ . '/auth.php';

function ensureNewsTable(PDO $db): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    $db->exec(
        'CREATE TABLE IF NOT EXISTS site_news (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(150) NOT NULL,
            body TEXT NOT NULL,
            created_at INT UNSIGNED NOT NULL,
            author_id INT NOT NULL,
            FOREIGN KEY (author_id) REFERENCES accounts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $ensured = true;
}

function fetchLatestNews(int $limit = 5): array
{
    $db = getDb();
    ensureNewsTable($db);

    $stmt = $db->prepare('SELECT n.*, a.name AS author_name FROM site_news n JOIN accounts a ON a.id = n.author_id ORDER BY created_at DESC LIMIT ?');
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function createNews(string $title, string $body, int $authorId): array
{
    $errors = [];
    if (trim($title) === '' || strlen($title) < 5) {
        $errors[] = 'Title must be at least 5 characters.';
    }

    if (trim($body) === '' || strlen($body) < 20) {
        $errors[] = 'Content must be at least 20 characters.';
    }

    if ($errors) {
        return $errors;
    }

    $db = getDb();
    ensureNewsTable($db);
    $stmt = $db->prepare('INSERT INTO site_news (title, body, created_at, author_id) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $title,
        $body,
        time(),
        $authorId,
    ]);

    return [];
}

function deleteNews(int $id): void
{
    $db = getDb();
    ensureNewsTable($db);
    $stmt = $db->prepare('DELETE FROM site_news WHERE id = ?');
    $stmt->execute([$id]);
}
