<?php
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/lib/auth.php';
require_once __DIR__ . '/../app/lib/csrf.php';
require_once __DIR__ . '/../app/lib/helpers.php';

if (current_user()) { header("Location: dashboard.php"); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("
    SELECT u.*, r.name AS role_name
    FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE u.username = ? AND u.is_active = 1
    LIMIT 1
  ");
  $stmt->execute([$username]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password_hash'])) {
    login_user($user);
    header("Location: dashboard.php");
    exit;
  }
  $error = "Invalid login or account disabled.";
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h(APP_NAME) ?> • Login</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Optional premium font (online). Remove if offline-only -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

  <!-- Theme CSS -->
  <link href="assets/app.css" rel="stylesheet">

  <style>
    /* login-specific (keeps the rest of app.css intact) */
    .login-wrap{
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 2.5rem 1rem;
    }
    .login-card{
      width: 100%;
      max-width: 420px;
      border-radius: 18px;
      overflow: hidden;
    }
    .login-hero{
      padding: 18px 18px 14px 18px;
      background: linear-gradient(135deg, rgba(37,99,235,0.14), rgba(22,163,74,0.12));
      border-bottom: 1px solid rgba(226,232,240,0.9);
    }
    .login-badge{
      width: 44px;
      height: 44px;
      border-radius: 16px;
      display: grid;
      place-items: center;
      font-weight: 900;
      color: #fff;
      background: linear-gradient(135deg, #2563eb, #16a34a);
      box-shadow: 0 14px 28px rgba(37,99,235,0.25);
      flex: 0 0 auto;
    }
    .login-title{
      font-weight: 900;
      letter-spacing: -0.02em;
      margin: 0;
      line-height: 1.1;
    }
    .login-sub{
      margin: 0;
      color: rgba(100,116,139,0.95);
      font-weight: 600;
      font-size: .9rem;
    }
    .hint{
      border: 1px dashed rgba(100,116,139,0.35);
      border-radius: 12px;
      padding: .75rem .85rem;
      background: rgba(255,255,255,0.75);
      color: rgba(100,116,139,0.95);
    }
  </style>
</head>
<body>
  <!-- top gradient line -->
  <div style="height:4px;background:linear-gradient(90deg,#2563eb,#16a34a);"></div>

  <div class="login-wrap">
    <div class="card shadow-sm login-card card-soft">
      <div class="login-hero">
        <div class="d-flex align-items-center gap-3">
          <div class="login-badge">V</div>
          <div>
            <h4 class="login-title"><?= h(APP_NAME) ?></h4>
            <p class="login-sub">Sign in to continue</p>
          </div>
        </div>
      </div>

      <div class="card-body p-4">
        <?php if ($error): ?>
          <div class="alert alert-danger mb-3">
            <?= h($error) ?>
          </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

          <div class="mb-3">
            <label class="form-label">Username</label>
            <input class="form-control" name="username" required autofocus placeholder="Enter username">
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
              <input id="pw" class="form-control" name="password" type="password" required placeholder="Enter password">
              <button class="btn btn-outline-secondary" type="button" id="togglePw" style="border-radius: 0 12px 12px 0;">
                Show
              </button>
            </div>
          </div>

          <button class="btn btn-primary w-100">Login</button>
        </form>

        <div class="hint small mt-3">
          First time setup? Run <code>/install.php</code> once to create the Super Admin.
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS (for alerts/modals if needed later) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Show/Hide password
    (function(){
      const pw = document.getElementById('pw');
      const btn = document.getElementById('togglePw');
      if (!pw || !btn) return;

      btn.addEventListener('click', function(){
        const isPw = pw.type === 'password';
        pw.type = isPw ? 'text' : 'password';
        btn.textContent = isPw ? 'Hide' : 'Show';
      });
    })();
  </script>
</body>
</html>