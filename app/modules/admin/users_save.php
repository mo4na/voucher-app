<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/csrf.php';
require_once __DIR__ . '/../../lib/logger.php';

require_any_role(['SUPER_ADMIN']);
csrf_check();

$full = trim($_POST['full_name'] ?? '');
$user = trim($_POST['username'] ?? '');
$pass = $_POST['password'] ?? '';
$role = $_POST['role_name'] ?? 'END_USER';

$roleRow = $pdo->prepare("SELECT id FROM roles WHERE name=?");
$roleRow->execute([$role]);
$rid = $roleRow->fetch()['id'] ?? null;
if (!$rid) exit("Invalid role");

$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users(full_name, username, password_hash, role_id) VALUES(?,?,?,?)");
$stmt->execute([$full, $user, $hash, $rid]);

log_action($pdo, current_user()['id'], "Create user", "user", (int)$pdo->lastInsertId(), "role=$role");

header("Location: " . BASE_URL . "/admin_users.php");
exit;