<?php
require __DIR__ . '/auth_bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// Optional CSRF for login
if (empty($_SESSION['login_csrf'])) {
  $_SESSION['login_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['login_csrf'];

$error = '';
$postedEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $postedEmail = trim((string)($_POST['email'] ?? ''));
  $postedCsrf  = $_POST['csrf'] ?? '';

  if (!hash_equals($_SESSION['login_csrf'] ?? '', $postedCsrf)) {
    $error = 'Your session expired. Please try again.';
  }
  else {
    try {
      $remember = !empty($_POST['remember']) ? 60 * 60 * 24 * 30 : null; // 30 days
      $auth->login($postedEmail, $_POST['password'] ?? '', $remember);
      session_regenerate_id(true);
      $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
      . '://' . $_SERVER['HTTP_HOST']
      . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
        header('Location: ' . $base . 'records_new.php');
        exit;
    }
    catch (\Delight\Auth\InvalidEmailException | \Delight\Auth\InvalidPasswordException $e) {
      $error = 'Invalid email or password.';
    }
    catch (\Delight\Auth\EmailNotVerifiedException $e) {
      $error = 'Please verify your email.';
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
      $error = 'Too many attempts. Try again later.';
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sign in · Success Student Management System</title>
  <link rel="icon" href="successlogo.png">
  <style>
    :root{
      --bg: #0f172a;        /* slate-900 */
      --bg2:#111827;        /* gray-900 */
      --card:#ffffff;       /* white */
      --text:#0f172a;       /* slate-900 */
      --muted:#6b7280;      /* gray-500 */
      --line:#e5e7eb;       /* gray-200 */
      --primary:#0b5fff;    /* brand */
      --primary-600:#0a53e8;
      --danger:#ef4444;
    }
    *{ box-sizing:border-box; }
    html,body{ height:100%; }
    body{
      margin:0; font:16px/1.5 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
      color:var(--text);
      background:
        radial-gradient(1200px 600px at 10% 10%, #1f2937 0%, transparent 60%),
        radial-gradient(1200px 600px at 90% 90%, #0b5fff22 0%, transparent 60%),
        linear-gradient(160deg,var(--bg),var(--bg2));
      display:grid; place-items:center;
      padding:24px;
    }
    .card{
      width:100%; max-width:420px; background:var(--card); color:var(--text);
      border:1px solid var(--line); border-radius:16px; padding:28px; box-shadow:0 20px 60px rgba(0,0,0,.20);
      animation: pop .25s ease-out;
    }
    @keyframes pop{ from{ transform:translateY(6px); opacity:.0 } to{ transform:none; opacity:1 } }

    .brand{
      display:flex; align-items:center; gap:12px; margin-bottom:18px;
    }
    .brand img{ width:48px; height:48px; object-fit:contain; }
    .brand h1{ font-size:20px; margin:0; line-height:1.25; }
    .muted{ color:var(--muted); font-size:14px; margin:0 0 16px; }

    label{ display:block; font-weight:600; font-size:14px; margin:14px 0 6px; }
    input[type=email], input[type=password]{
      width:100%; padding:12px 14px; border:1px solid var(--line); border-radius:10px; outline:none;
      transition:border-color .15s, box-shadow .15s; background:#fff;
    }
    input:focus{ border-color:var(--primary); box-shadow:0 0 0 3px #0b5fff22; }

    .actions{ display:flex; align-items:center; justify-content:space-between; margin-top:12px; gap:10px; }
    .checkbox{ display:flex; align-items:center; gap:8px; font-size:14px; color:var(--muted); }
    .btn{
      appearance:none; border:0; background:var(--primary); color:#fff; font-weight:700;
      padding:12px 16px; border-radius:12px; cursor:pointer; width:100%;
      transition:transform .02s ease, background .15s;
    }
    .btn:active{ transform:translateY(1px); }
    .btn:hover{ background:var(--primary-600); }

    .error{
      background:#fee2e2; border:1px solid #fca5a5; color:#991b1b;
      padding:10px 12px; border-radius:10px; font-size:14px; margin:0 0 12px;
    }

    .footer{
      text-align:center; color:var(--muted); font-size:13px; margin-top:16px;
    }
    .helper{
      display:flex; justify-content:space-between; align-items:center; margin-top:10px; font-size:14px;
    }
    .helper a{ color:var(--primary); text-decoration:none; }
    .helper a:hover{ text-decoration:underline; }

    .submit-row{ margin-top:16px; }
  </style>
</head>
<body>
  <main class="card" role="main" aria-labelledby="title">
    <div class="brand">
      <img src="successlogo.png" alt="">
      <div>
        <h1 id="title">Sign in</h1>
        <p class="muted">Success Student Management System</p>
      </div>
    </div>

    <?php if ($error): ?>
      <div class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" autocomplete="username" required
             value="<?= htmlspecialchars($postedEmail, ENT_QUOTES) ?>">

      <label for="password">Password</label>
      <input id="password" name="password" type="password" autocomplete="current-password" required>

      <div class="helper">
        <label class="checkbox">
          <input type="checkbox" name="remember" value="1"> Remember me
        </label>
      </div>

      <div class="submit-row">
        <button class="btn" type="submit">Sign in</button>
      </div>
    </form>

    <p class="footer">© <?= date('Y') ?> Success Laventille Secondary School<br><a target="_blank" href="https://linkedin.com/in/keronlewis"> Designed by Keron Lewis</a></p>
    
  </main>
</body>
</html>
