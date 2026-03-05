<?php
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/middleware/require_login.php';
require_once __DIR__ . '/../app/lib/auth.php';
require_once __DIR__ . '/../app/lib/csrf.php';
require_once __DIR__ . '/../app/lib/helpers.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM vouchers WHERE id=?");
$stmt->execute([$id]);
$v = $stmt->fetch();
if (!$v) { http_response_code(404); exit("Not found"); }

$u = current_user();

function can_edit_voucher(array $user, array $v): bool {
  if ($user['role'] === 'SUPER_ADMIN') return true;
  if ($v['status'] === 'PAID') return false;

  if ($user['role'] === 'END_USER') {
    return ((int)$v['created_by'] === (int)$user['id'])
      && in_array($v['status'], ['DRAFT','RETURNED_TO_ENDUSER'], true);
  }

  return $user['role'] === $v['current_office'];
}

if (!can_edit_voucher($u, $v)) {
  http_response_code(403);
  exit("Forbidden to edit this voucher.");
}

require_once __DIR__ . '/../app/views/layout_header.php';
?>
<div class="container-fluid">
  <div class="row">
    <?php require __DIR__ . '/../app/views/sidebar.php'; ?>

    <div class="col-12 col-lg-9 col-xl-10 p-4">
      <div class="card shadow-sm card-soft">
        <div class="card-body">
          <h4>Edit Voucher - <?= h($v['voucher_number']) ?></h4>

          <form method="post" action="../app/modules/vouchers/edit_save.php">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">

            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Payee Type</label>
                <select class="form-select" name="payee_type">
                  <option value="INTERNAL" <?= $v['payee_type']==='INTERNAL'?'selected':'' ?>>Internal</option>
                  <option value="EXTERNAL" <?= $v['payee_type']==='EXTERNAL'?'selected':'' ?>>External</option>
                </select>
              </div>

              <div class="col-md-8">
                <label class="form-label">Payee</label>
                <input class="form-control" name="payee" value="<?= h($v['payee']) ?>" required>
              </div>

              <div class="col-md-12">
                <label class="form-label">Address</label>
                <input class="form-control" name="address" value="<?= h($v['address'] ?? '') ?>">
              </div>

              <div class="col-md-12">
                <label class="form-label">Particulars</label>
                <textarea class="form-control" name="particulars" rows="4" required><?= h($v['particulars']) ?></textarea>
              </div>

              <div class="col-md-4">
                <label class="form-label">Amount</label>
                <input class="form-control" name="amount" type="number" step="0.01" min="0" value="<?= h((string)$v['amount']) ?>" required>
              </div>

              <div class="col-md-8">
                <label class="form-label">Remarks</label>
                <input class="form-control" name="remarks" value="<?= h($v['remarks'] ?? '') ?>">
              </div>
            </div>

            <div class="mt-3 d-flex gap-2">
              <button class="btn btn-primary">Save Changes</button>
              <a class="btn btn-outline-secondary" href="dashboard.php">Cancel</a>
            </div>
          </form>

        </div>
      </div>
    </div>

  </div>
</div>
<?php require_once __DIR__ . '/../app/views/layout_footer.php'; ?>