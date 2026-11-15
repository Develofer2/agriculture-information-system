<?php
declare(strict_types=1);
if ($route === 'login') {
    if (!empty($_SESSION['user'])) {
        redirect('dashboard');
    } $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $email = strtolower(trim($_POST['email'] ?? ''));
        $u = q($db, 'SELECT u.*,r.role_name FROM app_users u JOIN app_roles r ON r.role_id=u.role_id WHERE lower(email)=? LIMIT 1', [$email])->fetch();
        if (!$u || !$u['is_active'] || !password_verify($_POST['password'] ?? '', $u['password_hash'])) {
            $error = 'Invalid email or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = ['user_id' => $u['user_id'],'full_name' => $u['full_name'],'role_name' => $u['role_name']];
            q($db, 'UPDATE app_users SET last_login_at=now() WHERE user_id=?', [$u['user_id']]);
            audit($db, 'LOGIN', 'app_users', (string)$u['user_id']);
            redirect('dashboard', 'Welcome back, '.$u['full_name'].'.');
        }
    } ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="assets/css/app.css">
<title>Sign in | Verdant Vale</title>
</head>
<body class="login-page">
<section class="login-card">
<div class="login-mark" aria-hidden="true">&#127793;</div>
<p class="eyebrow">AGRICULTURE INFORMATION SYSTEM</p>
<h1>Welcome back</h1>
<p class="muted">Sign in to manage farm operations securely.</p>
<?php if ($error):?>
<div class="alert error">
<?=e($error)?>
</div>
<?php endif?>
<form method="post" class="form-grid single">
<input type="hidden" name="csrf_token" value="<?=e($_SESSION['csrf_token'])?>">
<label>Email<input type="email" name="email" autocomplete="email" required>
</label>
<label>Password<input type="password" name="password" autocomplete="current-password" required>
</label>
<button class="button primary">Sign in</button>
</form>
<p class="hint">Need an account? Ask your administrator.</p>
</section>
<script src="assets/js/app.js">
</script>
</body>
</html>
<?php exit;
}
