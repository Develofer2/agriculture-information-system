<?php
declare(strict_types=1);
if ($route === 'users') {
    require_admin();
    $userAction = $_GET['action'] ?? 'list';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        if ($userAction === 'create') {
            $name = trim($_POST['full_name'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';
            $role = (int)($_POST['role_id'] ?? 0);
            if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12 || !q($db, 'SELECT 1 FROM app_roles WHERE role_id=?', [$role])->fetchColumn()) {
                redirect('users', 'Use a valid name, email, role, and a password of at least 12 characters.', 'error');
            }try {
                $db->beginTransaction();
                q($db, 'INSERT INTO app_users(full_name,email,password_hash,role_id) VALUES(?,?,?,?)', [$name,$email,password_hash($password, PASSWORD_DEFAULT),$role]);
                $id = (int)$db->lastInsertId();
                audit($db, 'INSERT', 'app_users', (string)$id, ['full_name' => $name,'email' => $email]);
                $db->commit();
                redirect('users', 'User account created.');
            } catch (Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }redirect('users', 'This email address is already in use.', 'error');
            }
        }if ($userAction === 'toggle') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id === (int)$_SESSION['user']['user_id']) {
                redirect('users', 'You cannot deactivate your own account.', 'error');
            }q($db, 'UPDATE app_users SET is_active=NOT is_active WHERE user_id=?', [$id]);
            audit($db, 'UPDATE', 'app_users', (string)$id, ['is_active' => 'toggled']);
            redirect('users', 'User access updated.');
        }
    }$roles = q($db, 'SELECT role_id,role_name FROM app_roles ORDER BY role_id')->fetchAll();
    layout('Users', function () use ($db, $roles) {
        $users = q($db, 'SELECT u.user_id,u.full_name,u.email,r.role_name,u.is_active,u.last_login_at FROM app_users u JOIN app_roles r ON r.role_id=u.role_id ORDER BY u.created_at DESC')->fetchAll();?>
<section class="panel form-panel">
<div class="panel-head">
<div>
<h2>Add user</h2>
<p class="muted">Only administrators can create staff or administrator accounts.</p>
</div>
</div>
<form method="post" action="<?=url('users&action=create')?>" class="form-grid">
<input type="hidden" name="csrf_token" value="<?=e($_SESSION['csrf_token'])?>">
<label>Full name <em>*</em>
<input name="full_name" required maxlength="100">
</label>
<label>Email <em>*</em>
<input type="email" name="email" required>
</label>
<label>Role <em>*</em>
<select name="role_id" required>
<?php foreach ($roles as $r):?>
<option value="<?=$r['role_id']?>">
<?=e($r['role_name'])?>
</option>
<?php endforeach?>
</select>
</label>
<label>Temporary password <em>*</em>
<input type="password" name="password" minlength="12" required>
<small class="muted">At least 12 characters.</small>
</label>
<div class="form-actions">
<button class="button primary">Create user</button>
</div>
</form>
</section>
<section class="panel">
<div class="panel-head">
<h2>System users</h2>
<p class="muted">Deactivate access without removing audit history.</p>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Status</th>
<th>Last sign in</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach ($users as $u):?>
<tr>
<td>
<?=e($u['full_name'])?>
</td>
<td>
<?=e($u['email'])?>
</td>
<td>
<?=e($u['role_name'])?>
</td>
<td>
<span class="badge">
<?=$u['is_active'] ? 'Active' : 'Inactive'?>
</span>
</td>
<td>
<?php if ($u['last_login_at']): ?>
<?=e((new DateTimeImmutable($u['last_login_at']))->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d H:i'))?>
<?php else: ?>
Not signed in yet
<?php endif; ?>
</td>
<td>
<?php if ((int)$u['user_id'] !== (int)$_SESSION['user']['user_id']):?>
<form method="post" action="<?=url('users&action=toggle')?>" data-confirm="Change this user's access?">
<input type="hidden" name="csrf_token" value="<?=e($_SESSION['csrf_token'])?>">
<input type="hidden" name="id" value="<?=$u['user_id']?>">
<button class="link">
<?=$u['is_active'] ? 'Deactivate' : 'Activate'?>
</button>
</form>
<?php endif?>
</td>
</tr>
<?php endforeach?>
</tbody>
</table>
</div>
</section>
<?php });
    exit;
}
