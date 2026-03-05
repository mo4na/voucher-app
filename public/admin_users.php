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
  WHERE u.deleted_at IS NULL
  ORDER BY u.created_at DESC
")->fetchAll();

$roles = $pdo->query("SELECT * FROM roles ORDER BY name")->fetchAll();

$me = (int)(current_user()['id'] ?? 0);

function is_locked(array $u): bool {
  return !empty($u['locked_until']) && strtotime($u['locked_until']) > time();
}

require_once __DIR__ . '/../app/views/layout_header.php';
?>
<div class="container-fluid">
  <div class="row">
    <?php require __DIR__ . '/../app/views/sidebar.php'; ?>

    <div class="col-12 col-lg-9 col-xl-10 p-4">
      <div class="card shadow-soft card-soft">
        <div class="card-body">

          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="m-0">User Management</h4>

            <div class="d-flex gap-2">
              <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/admin_users_trash.php">Trash</a>
              <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                + Create User
              </button>
            </div>
          </div>

          <!-- Bulk actions -->
          <form method="post" action="../app/modules/admin/users_bulk.php" id="bulkForm" class="d-flex gap-2 align-items-center mb-2">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

            <select class="form-select" name="action" id="bulkAction" style="max-width:220px" required>
              <option value="">Bulk action…</option>
              <option value="disable">Disable</option>
              <option value="enable">Enable</option>
              <option value="lock">Lock (minutes)</option>
              <option value="unlock">Unlock</option>
              <option value="delete">Delete</option>
            </select>

            <input class="form-control" type="number" name="minutes" id="bulkMinutes" placeholder="Minutes" min="1"
                   style="max-width:140px; display:none;">

            <button class="btn btn-outline-primary" id="bulkBtn" disabled>Apply</button>

            <div class="ms-auto small text-muted" id="selectedCount">0 selected</div>
          </form>

          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th style="width:40px"><input type="checkbox" id="checkAll"></th>
                  <th>Name</th>
                  <th>Username</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th style="width:420px">Action</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($users as $x): ?>
                <?php
                  $id = (int)$x['id'];
                  $selfRow = ($id === $me);
                  $locked = is_locked($x);
                ?>
                <tr>
                  <td>
                    <input type="checkbox" class="row-check" form="bulkForm" name="ids[]"
                           value="<?= $id ?>" <?= $selfRow ? 'disabled' : '' ?>>
                  </td>

                  <td><?= h($x['full_name']) ?> <?= $selfRow ? '<span class="badge text-bg-info ms-1">You</span>' : '' ?></td>
                  <td><?= h($x['username']) ?></td>
                  <td><?= h($x['role_name']) ?></td>

                  <td>
                    <?php if (!(int)$x['is_active']): ?>
                      <span class="badge text-bg-secondary">Disabled</span>
                    <?php elseif ($locked): ?>
                      <span class="badge text-bg-warning">Locked</span>
                      <div class="small text-muted">until <?= h($x['locked_until']) ?></div>
                    <?php else: ?>
                      <span class="badge text-bg-success">Active</span>
                    <?php endif; ?>
                  </td>

                  <td class="d-flex gap-1 flex-wrap">
                    <!-- Disable/Enable -->
                    <form method="post" action="../app/modules/admin/user_toggle.php" class="d-inline">
                      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                      <input type="hidden" name="id" value="<?= $id ?>">
                      <button class="btn btn-sm btn-outline-danger" <?= $selfRow ? 'disabled' : '' ?>>
                        <?= (int)$x['is_active'] ? 'Disable' : 'Enable' ?>
                      </button>
                    </form>

                    <!-- Lock quick buttons -->
                    <form method="post" action="../app/modules/admin/user_lock.php" class="d-inline">
                      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                      <input type="hidden" name="id" value="<?= $id ?>">
                      <input type="hidden" name="minutes" value="15">
                      <button class="btn btn-sm btn-outline-warning" <?= $selfRow ? 'disabled' : '' ?>>Lock 15m</button>
                    </form>

                    <form method="post" action="../app/modules/admin/user_lock.php" class="d-inline">
                      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                      <input type="hidden" name="id" value="<?= $id ?>">
                      <input type="hidden" name="minutes" value="30">
                      <button class="btn btn-sm btn-outline-warning" <?= $selfRow ? 'disabled' : '' ?>>Lock 30m</button>
                    </form>

                    <form method="post" action="../app/modules/admin/user_lock.php" class="d-inline">
                      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                      <input type="hidden" name="id" value="<?= $id ?>">
                      <input type="hidden" name="minutes" value="60">
                      <button class="btn btn-sm btn-outline-warning" <?= $selfRow ? 'disabled' : '' ?>>Lock 60m</button>
                    </form>

                    <!-- Unlock -->
                    <form method="post" action="../app/modules/admin/user_unlock.php" class="d-inline">
                      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                      <input type="hidden" name="id" value="<?= $id ?>">
                      <button class="btn btn-sm btn-outline-secondary" <?= $selfRow ? 'disabled' : '' ?>>Unlock</button>
                    </form>

                    <!-- Delete (modal trigger) -->
                    <button type="button"
                            class="btn btn-sm btn-outline-dark"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteUserModal"
                            data-user-id="<?= $id ?>"
                            data-user-name="<?= h($x['full_name']) ?>"
                            <?= $selfRow ? 'disabled' : '' ?>>
                      Delete
                    </button>
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

<!-- Create user modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form class="modal-body" method="post" action="../app/modules/admin/users_save.php" id="createUserForm">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="m-0">Create User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="row g-2">
          <div class="col-md-5">
            <label class="form-label">Full name</label>
            <input class="form-control" name="full_name" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Username</label>
            <input class="form-control" name="username" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Temporary password</label>
            <input class="form-control" name="password" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Role</label>
            <select class="form-select" name="role_name" required>
              <?php foreach ($roles as $r): ?>
                <option value="<?= $r['name'] ?>"><?= $r['name'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="d-flex gap-2 justify-content-end mt-3">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" id="createUserBtn">
            <span class="btn-text">Save</span>
            <span class="spinner-border spinner-border-sm ms-2 d-none" id="createSpinner"></span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete confirmation modal (requires SUPER_ADMIN password) -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="../app/modules/admin/user_delete.php" id="deleteUserForm">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="id" id="deleteUserId" value="">

          <div class="alert alert-warning mb-3">
            Soft-delete: <strong id="deleteUserName">User</strong>
          </div>

          <label class="form-label">SUPER_ADMIN password</label>
          <input type="password" class="form-control" name="admin_password" id="deleteAdminPassword" required>
          <div class="form-text">Required to confirm deletion.</div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-dark" id="deleteConfirmBtn">
            <span>Delete</span>
            <span class="spinner-border spinner-border-sm ms-2 d-none" id="deleteSpinner"></span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Bulk selection UX
  const checkAll = document.getElementById('checkAll');
  const bulkBtn = document.getElementById('bulkBtn');
  const bulkAction = document.getElementById('bulkAction');
  const bulkMinutes = document.getElementById('bulkMinutes');
  const selectedCount = document.getElementById('selectedCount');
  const getRowChecks = () => Array.from(document.querySelectorAll('.row-check'));

  function updateBulkUI() {
    const selected = getRowChecks().filter(c => c.checked).length;
    selectedCount.textContent = selected + " selected";
    bulkBtn.disabled = selected === 0 || !bulkAction.value;

    if (bulkAction.value === 'lock') {
      bulkMinutes.style.display = 'block';
      bulkMinutes.required = true;
    } else {
      bulkMinutes.style.display = 'none';
      bulkMinutes.required = false;
      bulkMinutes.value = '';
    }
  }

  checkAll?.addEventListener('change', () => {
    getRowChecks().forEach(c => { if (!c.disabled) c.checked = checkAll.checked; });
    updateBulkUI();
  });

  document.addEventListener('change', (e) => {
    if (e.target.classList.contains('row-check')) updateBulkUI();
    if (e.target.id === 'bulkAction') updateBulkUI();
  });

  // Create user "saving..." animation
  const createForm = document.getElementById('createUserForm');
  const createBtn = document.getElementById('createUserBtn');
  const createSpinner = document.getElementById('createSpinner');

  createForm?.addEventListener('submit', () => {
    createBtn.disabled = true;
    createSpinner.classList.remove('d-none');
  });

  // Delete modal wiring + saving animation
  const deleteModal = document.getElementById('deleteUserModal');
  const deleteUserId = document.getElementById('deleteUserId');
  const deleteUserName = document.getElementById('deleteUserName');
  const deleteAdminPassword = document.getElementById('deleteAdminPassword');
  const deleteSpinner = document.getElementById('deleteSpinner');
  const deleteConfirmBtn = document.getElementById('deleteConfirmBtn');
  const deleteUserForm = document.getElementById('deleteUserForm');

  deleteModal?.addEventListener('show.bs.modal', (event) => {
    const btn = event.relatedTarget;
    deleteUserId.value = btn.getAttribute('data-user-id');
    deleteUserName.textContent = btn.getAttribute('data-user-name') || 'User';
    deleteAdminPassword.value = '';
    deleteConfirmBtn.disabled = false;
    deleteSpinner.classList.add('d-none');
  });

  deleteUserForm?.addEventListener('submit', () => {
    deleteConfirmBtn.disabled = true;
    deleteSpinner.classList.remove('d-none');
  });

  updateBulkUI();
</script>

<?php require_once __DIR__ . '/../app/views/layout_footer.php'; ?>