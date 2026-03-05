<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$DB_HOST = "127.0.0.1";
$DB_NAME = "voucher_system";
$DB_USER = "root";
$DB_PASS = ""; // set your mysql password if any

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
  http_response_code(500);
  exit("DB connection failed.");
}