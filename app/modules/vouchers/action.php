<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/csrf.php';
require_once __DIR__ . '/../../lib/logger.php';
require_once __DIR__ . '/../../lib/voucher_flow.php';
require_once __DIR__ . '/../../lib/return_logic.php';
require_once __DIR__ . '/../../lib/compat.php';

require_login();
csrf_check();

$u = current_user();

$action    = $_POST['action'] ?? '';
$voucherId = (int)($_POST['voucher_id'] ?? 0);
$remarks   = trim($_POST['remarks'] ?? '');

$stmt = $pdo->prepare("SELECT * FROM vouchers WHERE id=?");
$stmt->execute([$voucherId]);
$v = $stmt->fetch();

if (!$v) {
  http_response_code(404);
  exit("Voucher not found");
}

$fromStatus = $v['status'];
$fromOffice = $v['current_office'];

function write_history(
  PDO $pdo,
  int $voucherId,
  ?string $fromStatus,
  string $toStatus,
  ?string $fromOffice,
  ?string $toOffice,
  ?string $remarks,
  int $userId
): void {
  $h = $pdo->prepare("
    INSERT INTO voucher_history(voucher_id, from_status, to_status, from_office, to_office, remarks, acted_by)
    VALUES(?,?,?,?,?,?,?)
  ");
  $h->execute([
    $voucherId,
    $fromStatus,
    $toStatus,
    $fromOffice,
    $toOffice,
    ($remarks !== '' ? $remarks : null),
    $userId
  ]);
}

/**
 * Access rules:
 * - SUPER_ADMIN: can act on any voucher (all offices)
 * - END_USER: only own vouchers
 * - BUDGET/ACCOUNTING/CASHIER: only vouchers assigned to their office
 */
function ensure_office_access(array $u, array $v): void {
  if ($u['role'] === 'SUPER_ADMIN') return;

  if ($u['role'] === 'END_USER') {
    if ((int)$v['created_by'] !== (int)$u['id']) {
      http_response_code(403);
      exit("Forbidden");
    }
    return;
  }

  if ($v['current_office'] !== $u['role']) {
    http_response_code(403);
    exit("Forbidden");
  }
}

ensure_office_access($u, $v);

if ($action === 'release') {
  // END_USER releases to BUDGET (automatic hierarchy)
  if (!has_role('END_USER')) {
    http_response_code(403);
    exit("Forbidden");
  }

  if (!in_array($v['status'], ['DRAFT', 'RETURNED_TO_ENDUSER'], true)) {
    http_response_code(400);
    exit("Not releasable");
  }

  $toOffice = next_office('END_USER'); // always BUDGET
  if (!$toOffice) {
    http_response_code(400);
    exit("No next office");
  }

  $toStatus = for_receiving_status($toOffice);

  $pdo->prepare("UPDATE vouchers SET current_office=?, status=? WHERE id=?")
      ->execute([$toOffice, $toStatus, $voucherId]);

  write_history($pdo, $voucherId, $fromStatus, $toStatus, $fromOffice, $toOffice, $remarks, (int)$u['id']);
  log_action($pdo, (int)$u['id'], "Release voucher", "voucher", $voucherId, "To=$toOffice");
}

elseif ($action === 'receive') {
  // Current office receives incoming voucher
  if (!in_array($u['role'], ['BUDGET', 'ACCOUNTING', 'CASHIER', 'SUPER_ADMIN'], true)) {
    http_response_code(403);
    exit("Forbidden");
  }

  if (!starts_with($v['status'], 'FOR_RECEIVING_')) {
    http_response_code(400);
    exit("Not in receiving state");
  }

  $toStatus = received_status($v['current_office']);

  $pdo->prepare("UPDATE vouchers SET status=? WHERE id=?")
      ->execute([$toStatus, $voucherId]);

  write_history($pdo, $voucherId, $fromStatus, $toStatus, $fromOffice, $fromOffice, $remarks, (int)$u['id']);
  log_action($pdo, (int)$u['id'], "Receive voucher", "voucher", $voucherId, "Office=" . $v['current_office']);
}

elseif ($action === 'approve_forward') {
  // BUDGET -> ACCOUNTING, ACCOUNTING -> CASHIER (automatic hierarchy)
  if (!in_array($u['role'], ['BUDGET', 'ACCOUNTING', 'SUPER_ADMIN'], true)) {
    http_response_code(403);
    exit("Forbidden");
  }

  if (!starts_with($v['status'], 'RECEIVED_')) {
    http_response_code(400);
    exit("Must receive first");
  }

  $toOffice = next_office($v['current_office']);
  if (!$toOffice) {
    http_response_code(400);
    exit("No next office");
  }

  $toStatus = for_receiving_status($toOffice);

  $pdo->prepare("UPDATE vouchers SET current_office=?, status=? WHERE id=?")
      ->execute([$toOffice, $toStatus, $voucherId]);

  write_history($pdo, $voucherId, $fromStatus, $toStatus, $fromOffice, $toOffice, $remarks, (int)$u['id']);
  log_action($pdo, (int)$u['id'], "Forward voucher", "voucher", $voucherId, "To=$toOffice");
}

elseif ($action === 'return') {
  // Return to previous office OR selected office
  if (!in_array($u['role'], ['BUDGET', 'ACCOUNTING', 'CASHIER', 'SUPER_ADMIN'], true)) {
    http_response_code(403);
    exit("Forbidden");
  }

  if ($v['status'] === 'PAID') {
    http_response_code(400);
    exit("Already paid");
  }

  if ($remarks === '') {
    http_response_code(400);
    exit("Remarks required");
  }

  $returnMode = $_POST['return_mode'] ?? 'previous'; // previous | select
  $targetOffice = null;

  if ($returnMode === 'select') {
    $targetOffice = $_POST['return_to_office'] ?? '';
  } else {
    $targetOffice = get_previous_office($pdo, $voucherId, $v['current_office']);
  }

  if (!$targetOffice) $targetOffice = 'END_USER';

  $allowedTargets = ['END_USER','BUDGET','ACCOUNTING','CASHIER'];
  if (!in_array($targetOffice, $allowedTargets, true)) {
    http_response_code(400);
    exit("Invalid target");
  }

  // Prevent returning "forward" for non-superadmin
  $order = ['END_USER'=>1,'BUDGET'=>2,'ACCOUNTING'=>3,'CASHIER'=>4];
  if ($u['role'] !== 'SUPER_ADMIN' && $order[$targetOffice] > $order[$v['current_office']]) {
    http_response_code(400);
    exit("Cannot return to a forward office.");
  }

  $toOffice = $targetOffice;
  $toStatus = for_receiving_status($toOffice); // END_USER -> RETURNED_TO_ENDUSER

  $pdo->prepare("UPDATE vouchers SET current_office=?, status=? WHERE id=?")
      ->execute([$toOffice, $toStatus, $voucherId]);

  write_history($pdo, $voucherId, $fromStatus, $toStatus, $fromOffice, $toOffice, $remarks, (int)$u['id']);
  log_action($pdo, (int)$u['id'], "Return voucher", "voucher", $voucherId, "To=$toOffice | $remarks");
}

elseif ($action === 'mark_paid') {
  // Cashier marks paid (requires received in cashier)
  if (!in_array($u['role'], ['CASHIER', 'SUPER_ADMIN'], true)) {
    http_response_code(403);
    exit("Forbidden");
  }

  if ($v['current_office'] !== 'CASHIER') {
    http_response_code(400);
    exit("Not in cashier");
  }

  if ($v['status'] !== 'RECEIVED_CASHIER') {
    http_response_code(400);
    exit("Must be received in cashier first");
  }

  $toStatus = 'PAID';

  $pdo->prepare("UPDATE vouchers SET status=?, date_paid=NOW() WHERE id=?")
      ->execute([$toStatus, $voucherId]);

  write_history($pdo, $voucherId, $fromStatus, $toStatus, $fromOffice, $fromOffice, $remarks, (int)$u['id']);
  log_action($pdo, (int)$u['id'], "Mark paid", "voucher", $voucherId, null);
}

else {
  http_response_code(400);
  exit("Unknown action");
}

header("Location: " . BASE_URL . "/dashboard.php");
exit;