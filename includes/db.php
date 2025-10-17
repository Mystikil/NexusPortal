<?php
$settings = require __DIR__ . '/../config.php';

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $settings['db']['host'],
    $settings['db']['port'],
    $settings['db']['name'],
    $settings['db']['charset']
);

try {
    $pdo = new PDO($dsn, $settings['db']['user'], $settings['db']['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo '<h1>Database connection failed</h1>';
    if (ini_get('display_errors')) {
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
    exit;
}

return $pdo;
