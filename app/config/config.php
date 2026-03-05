<?php
declare(strict_types=1);


$appName = getenv('APP_NAME');
define('APP_NAME', ($appName !== false && $appName !== '') ? $appName : 'Voucher Tracking System');

$baseUrl = getenv('BASE_URL');
$baseUrl = ($baseUrl !== false) ? $baseUrl : '/voucher-app/public';
$baseUrl = rtrim((string)$baseUrl, '/');
define('BASE_URL', $baseUrl);

define('TZ', 'Asia/Manila');
date_default_timezone_set(TZ);

/* If using XAMPP: http://localhost/voucher-app/public
define('APP_NAME', 'Voucher Tracking System');
define('BASE_URL', '/voucher-app/public'); */