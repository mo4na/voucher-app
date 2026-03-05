<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/csrf.php';
require_once __DIR__ . '/../../lib/logger.php';

require_any_role(['SUPER_ADMIN']);
csrf_check();

$action = $_POST['action'] ?? '';
$minutes = (int)($_POST['minutes'] ?? 0);
$ids = $_POST['ids'] ?? [];
$me = (int)(current_user()['id'] ?? 0);

if (!is_array($ids) || count($ids) === 0) exit("No users selected.");

$ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($x) => $x > 0)));
$ids = array_values(array_filter($ids, fn($x) => $x !== $me)); // never touch self in bulk

if (count($ids) === 0) exit("No valid users selected.");

$placeholders = implode(',', array_fill(0, count($ids), '?'));

switch ($action) {
  case 'disable':
    $sql = "UPDATE users SET is_active=0 WHERE deleted_at IS NULL AND id IN ($placeholders)";
    $pdo->prepare($sql)->execute($ids);
    log_action($pdo, $me, "Bulk disable users", "user", 0, "count=" . count($ids));
    break;

  case 'enable':
    $sql = "UPDATE users SET is_active=1 WHERE deleted_at IS NULL AND id IN ($placeholders)";
    $pdo->prepare($sql)->execute($ids);
    log_action($pdo, $me, "Bulk enable users", "user", 0, "count=" . count($ids));
    break;

  case 'lock':
    if ($minutes <= 0) exit("Minutes required for lock.");
    $sql = "UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL $minutes MINUTE)
            WHERE deleted_at IS NULL AND id IN ($placeholders)";
    $pdo->prepare($sql)->execute($ids);
    log_action($pdo, $me, "Bulk lock users", "user", 0, "minutes=$minutes count=" . count($ids));
    break;

  case 'unlock':
    $sql = "UPDATE users SET locked_until=NULL WHERE deleted_at IS NULL AND id IN ($placeholders)";
    $pdo->prepare($sql)->execute($ids);
    log_action($pdo, $me, "Bulk unlock users", "user", 0, "count=" . count($ids));
    break;

  case 'delete':
    $sql = "UPDATE users SET deleted_at=NOW() WHERE deleted_at IS NULL AND id IN ($placeholders)";
    $pdo->prepare($sql)->execute($ids);
    log_action($pdo, $me, "Bulk soft delete users", "user", 0, "count=" . count($ids));
    break;

  default:
    exit("Invalid action.");
}

header("Location: " . BASE_URL . "/admin_users.php");
exit;