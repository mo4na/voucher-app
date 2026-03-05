<?php
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/middleware/require_login.php';
require_once __DIR__ . '/../app/lib/auth.php';
require_once __DIR__ . '/../app/lib/csrf.php';

require_any_role(['SUPER_ADMIN']);

$users = $pdo->query("
  SELECT u.*, r.name role_name
  FROM users u
  JOIN roles r ON r.id=u.role_id
  WHERE u.deleted_at IS NOT NULL
  ORDER BY u.deleted_at DESC
")->fetchAll();

require_once __DIR__ . '/../app/views/layout_header.php';
?>
<div class="container-fluid">
  <div class="row">
    <?php require __DIR__ . '/../app/views/sidebar.php'; ?>

    <div class="col-12 col-lg-9 col-xl-10 p-4">
      <div class="card shadow-soft card-soft">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="m-0">Trash (Deleted Users)</h4>
            <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/admin_users.php">Back to Users</a>
          </div>

          <form method="post" action="../app/modules/admin/users_restore_bulk.php" id="restoreBulkForm"
                class="d-flex gap-2 align-items-center mb-2">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <button class="btn btn-outline-success" id="restoreBulkBtn" disabled>Restore Selected</button>
            <div class="ms-auto small text-muted" id="restoreSelectedCount">0 selected</div>
          </form>

          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th style="width:40px"><input type="checkbox" id="trashCheckAll"></th>
                  <th>Name</th>
                  <th>Username</th>
                  <th>Role</th>
                  <th>Deleted At</th>
                  <th style="width:160px">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $x): ?>
                  <tr>
                    <td>
                      <input type="checkbox" class="trash-row-check" form="restoreBulkForm" name="ids[]" value="<?= (int)$x['id'] ?>">
                    </td>
                    <td><?= h($x['full_name']) ?></td>
                    <td><?= h($x['username']) ?></td>
                    <td><?= h($x['role_name']) ?></td>
                    <td><?= h($x['deleted_at']) ?></td>
                    <td>
                      <form method="post" action="../app/modules/admin/user_restore.php" class="d-inline">
                        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                        <input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
                        <button class="btn btn-sm btn-outline-success">Restore</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const trashCheckAll = document.getElementById('trashCheckAll');
  const restoreSelectedCount = document.getElementById('restoreSelectedCount');
  const restoreBulkBtn = document.getElementById('restoreBulkBtn');
  const trashChecks = () => Array.from(document.querySelectorAll('.trash-row-check'));

  function updateRestoreUI(){
    const selected = trashChecks().filter(c => c.checked).length;
    restoreSelectedCount.textContent = selected + " selected";
    restoreBulkBtn.disabled = selected === 0;
  }

  trashCheckAll?.addEventListener('change', () => {
    trashChecks().forEach(c => c.checked = trashCheckAll.checked);
    updateRestoreUI();
  });

  document.addEventListener('change', (e) => {
    if (e.target.classList.contains('trash-row-check')) updateRestoreUI();
  });

  updateRestoreUI();
</script>

<?php require_once __DIR__ . '/../app/views/layout_footer.php'; ?>