<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/csrf.php';
require_once __DIR__ . '/../../lib/logger.php';

require_login();
csrf_check();

$u = current_user();
$id = (int)($_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM vouchers WHERE id=?");
$stmt->execute([$id]);
$v = $stmt->fetch();
if (!$v) { http_response_code(404); exit("Not found"); }

function can_edit_voucher(array $user, array $v): bool {
  if ($user['role'] === 'SUPER_ADMIN') return true;
  if ($v['status'] === 'PAID') return false;

  if ($user['role'] === 'END_USER') {
    return ((int)$v['created_by'] === (int)$user['id'])
      && in_array($v['status'], ['DRAFT','RETURNED_TO_ENDUSER'], true);
  }

  return $user['role'] === $v['current_office'];
}

if (!can_edit_voucher($u, $v)) { http_response_code(403); exit("Forbidden"); }

$payee_type  = $_POST['payee_type'] ?? $v['payee_type'];
$payee       = trim($_POST['payee'] ?? $v['payee']);
$address     = trim($_POST['address'] ?? '');
$particulars = trim($_POST['particulars'] ?? $v['particulars']);
$amount      = (float)($_POST['amount'] ?? $v['amount']);
$remarks     = trim($_POST['remarks'] ?? '');

$up = $pdo->prepare("
  UPDATE vouchers
  SET payee_type=?, payee=?, address=?, particulars=?, amount=?, remarks=?
  WHERE id=?
");
$up->execute([
  $payee_type, $payee, ($address ?: null), $particulars, $amount, ($remarks ?: null), $id
]);

log_action($pdo, $u['id'], "Edit voucher", "voucher", $id, null);

header("Location: " . BASE_URL . "/voucher_view.php?id=" . $id);
exit;