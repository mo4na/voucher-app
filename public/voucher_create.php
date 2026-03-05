<?php
require_once __DIR__ . '/../app/middleware/require_login.php';
require_once __DIR__ . '/../app/lib/auth.php';
require_once __DIR__ . '/../app/lib/csrf.php';

require_any_role(['END_USER','SUPER_ADMIN']);
require_once __DIR__ . '/../app/views/layout_header.php';
?>
<div class="container-fluid">
  <div class="row">
    <?php require __DIR__ . '/../app/views/sidebar.php'; ?>

    <div class="col-12 col-lg-9 col-xl-10 p-4">
      <div class="card shadow-sm card-soft">
        <div class="card-body">
          <h4>Create Voucher</h4>

          <form method="post" action="../app/modules/vouchers/create_save.php">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Payee Type</label>
                <select class="form-select" name="payee_type">
                  <option value="INTERNAL">Internal</option>
                  <option value="EXTERNAL">External</option>
                </select>
              </div>

              <div class="col-md-8">
                <label class="form-label">Payee</label>
                <input class="form-control" name="payee" required>
              </div>

              <div class="col-md-12">
                <label class="form-label">Address</label>
                <input class="form-control" name="address">
              </div>

              <div class="col-md-12">
                <label class="form-label">Particulars</label>
                <textarea class="form-control" name="particulars" rows="4" required></textarea>
              </div>

              <div class="col-md-4">
                <label class="form-label">Amount</label>
                <input class="form-control" name="amount" type="number" step="0.01" min="0" required>
              </div>

              <div class="col-md-8">
                <label class="form-label">Remarks</label>
                <input class="form-control" name="remarks">
              </div>
            </div>

            <div class="mt-3 d-flex gap-2">
              <button class="btn btn-primary">Save</button>
              <a class="btn btn-outline-secondary" href="dashboard.php">Cancel</a>
            </div>

          </form>
        </div>
      </div>
    </div>

  </div>
</div>
<?php require_once __DIR__ . '/../app/views/layout_footer.php'; ?>