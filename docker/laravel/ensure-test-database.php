<?php

$envFile = '/var/www/html/.env';
$envValues = [];

if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $envValues[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

$env = fn (string $key, string $default): string => getenv($key) ?: ($envValues[$key] ?? $default);

$testDatabase = $env('DB_TEST_DATABASE', 'video_monitor_test');

if (! preg_match('/^[A-Za-z0-9_]+$/', $testDatabase)) {
    fwrite(STDERR, "Invalid DB_TEST_DATABASE value: {$testDatabase}\n");
    exit(1);
}

$host = $env('DB_HOST', 'database');
$port = $env('DB_PORT', '5432');
$username = $env('DB_USERNAME', 'postgres');
$password = $env('DB_PASSWORD', 'secret');

$dsn = "pgsql:host={$host};port={$port};dbname=postgres";

$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$statement = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = :database');
$statement->execute(['database' => $testDatabase]);

if ($statement->fetchColumn()) {
    echo "Test database {$testDatabase} already exists.\n";
    exit(0);
}

$pdo->exec(sprintf('CREATE DATABASE "%s"', $testDatabase));

echo "Created test database {$testDatabase}.\n";
