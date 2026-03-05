<?php
declare(strict_types=1);

$publicDir = realpath(__DIR__ . '/../public');
if ($publicDir === false) {
  http_response_code(500);
  exit('Missing public directory.');
}

$requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
$path = parse_url($requestUri, PHP_URL_PATH);
$path = ($path === null || $path === false || $path === '') ? '/' : (string)$path;
$path = str_replace("\0", '', $path);

if ($path === '/') {
  $path = '/index.php';
}

if (str_ends_with($path, '/')) {
  $path .= 'index.php';
}

$candidatePaths = [$path];
if (!str_ends_with($path, '.php') && !str_contains(basename($path), '.')) {
  $candidatePaths[] = $path . '.php';
  $candidatePaths[] = rtrim($path, '/') . '/index.php';
}

$target = false;
foreach ($candidatePaths as $candidate) {
  $resolved = realpath($publicDir . $candidate);
  if ($resolved === false) continue;
  if (!str_starts_with($resolved, $publicDir . DIRECTORY_SEPARATOR)) continue;
  if (is_dir($resolved)) continue;
  if (pathinfo($resolved, PATHINFO_EXTENSION) !== 'php') continue;
  $target = $resolved;
  break;
}

if ($target === false) {
  http_response_code(404);
  exit('Not found.');
}

chdir($publicDir);
require $target;
