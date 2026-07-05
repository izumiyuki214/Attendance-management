<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__), '.env.testing');
$dotenv->safeLoad();

$host     = $_ENV['DB_HOST']     ?? '127.0.0.1';
$port     = $_ENV['DB_PORT']     ?? 3306;
$user     = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';
$dbName   = $_ENV['DB_DATABASE'] ?? 'demo_test';

try {
    $pdo = new PDO("mysql:host={$host};port={$port}", $user, $password);
    $pdo->exec(
        "CREATE DATABASE IF NOT EXISTS \`{$dbName}\`
         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );
} catch (PDOException \$e) {
    // 接続失敗時はLaravel側のエラーメッセージに任せる
}
