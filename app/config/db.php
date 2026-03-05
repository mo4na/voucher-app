<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$databaseUrl = getenv('DATABASE_URL');

if ($databaseUrl !== false && $databaseUrl !== '') {
  $parts = parse_url($databaseUrl);
  $DB_HOST = (string)($parts['host'] ?? '127.0.0.1');
  $DB_PORT = isset($parts['port']) ? (int)$parts['port'] : null;
  $DB_NAME = isset($parts['path']) ? ltrim((string)$parts['path'], '/') : 'voucher_system';
  $DB_USER = (string)($parts['user'] ?? 'root');
  $DB_PASS = (string)($parts['pass'] ?? '');
} else {
  $DB_HOST = (string)(getenv('DB_HOST') ?: '127.0.0.1');
  $DB_PORT = getenv('DB_PORT') !== false ? (int)getenv('DB_PORT') : null;
  $DB_NAME = (string)(getenv('DB_NAME') ?: 'voucher_system');
  $DB_USER = (string)(getenv('DB_USER') ?: 'root');
  $DB_PASS = (string)(getenv('DB_PASS') ?: '');
}

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $dsn = "mysql:host=$DB_HOST;" . ($DB_PORT ? "port=$DB_PORT;" : "") . "dbname=$DB_NAME;charset=utf8mb4";
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
  http_response_code(500);
  exit("DB connection failed.");
}

/* $DB_HOST = "127.0.0.1";
$DB_NAME = "voucher_system";
$DB_USER = "root";
$DB_PASS = ""; // set your mysql password if any */