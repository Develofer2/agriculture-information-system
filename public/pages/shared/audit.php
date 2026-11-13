<?php
declare(strict_types=1);
if ($route === 'audit') {
    require_admin();
    layout('Audit logs', function () use ($db) {
        $logs = q($db, 'SELECT a.created_at,a.action,a.entity_table,a.record_pk,u.full_name FROM audit_logs a LEFT JOIN app_users u ON u.user_id=a.actor_user_id ORDER BY a.log_id DESC LIMIT 100')->fetchAll();?>
<section class="panel">
<div class="panel-head">
<h2>Recent activity</h2>
<p class="muted">Read-only audit history</p>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Time</th>
<th>Actor</th>
<th>Action</th>
<th>Entity</th>
<th>Record</th>
</tr>
</thead>
<tbody>
<?php foreach ($logs as $l):?>
<tr>
<td>
<?=e($l['created_at'])?>
</td>
<td>
<?=e($l['full_name'] ?? 'System')?>
</td>
<td>
<span class="badge">
<?=e($l['action'])?>
</span>
</td>
<td>
<?=e($l['entity_table'])?>
</td>
<td>
<?=e($l['record_pk'])?>
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
