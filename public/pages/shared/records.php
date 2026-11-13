<?php
declare(strict_types=1);
if (isset($modules[$route])) {
    $m = $modules[$route];
    $action = $_GET['action'] ?? 'list';
    if ($action === 'delete') {
        require_admin();
        verify_csrf();
        $id = (int)$_POST['id'];
        try {
            $db->beginTransaction();
            q($db, "DELETE FROM {$m['table']} WHERE {$m['pk']}=?", [$id]);
            audit($db, 'DELETE', $m['table'], (string)$id);
            $db->commit();
            redirect($route, 'Record deleted.');
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }redirect($route, 'This record is referenced by related data and cannot be deleted.', 'error');
        }
    }
    if ($action === 'form') {
        $id = (int)($_GET['id'] ?? 0);
        $record = $id ? q($db, "SELECT * FROM {$m['table']} WHERE {$m['pk']}=?", [$id])->fetch() : [];
        if (!$record && $id) {
            redirect($route, 'Record not found.', 'error');
        } $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $data = [];
            foreach ($m['fields'] as $f => $label) {
                $data[$f] = trim((string)($_POST[$f] ?? ''));
                if ($data[$f] === '' && !in_array($f, ['contact_number','selling_price_per_kg'], true)) {
                    $errors[$f] = "$label is required.";
                }
            } foreach (['area_hectares','area_planted_ha','seed_quantity_kg','quantity_kg','selling_price_per_kg'] as $f) {
                if (isset($data[$f]) && $data[$f] !== '' && !is_numeric($data[$f])) {
                    $errors[$f] = 'Enter a valid number.';
                }
            } if (isset($data['growing_period_days']) && (!ctype_digit($data['growing_period_days']) || (int)$data['growing_period_days'] < 1)) {
                $errors['growing_period_days'] = 'Use at least 1 day.';
            }if (isset($data['expected_harvest_date'],$data['planting_date']) && $data['expected_harvest_date'] < $data['planting_date']) {
                $errors['expected_harvest_date'] = 'Expected date must be on or after planting.';
            }if (!$errors) {
                try {
                    $db->beginTransaction();
                    $cols = array_keys($data);
                    if ($id) {
                        $sets = implode(',', array_map(fn ($f) => "$f=?", $cols));
                        q($db, "UPDATE {$m['table']} SET $sets WHERE {$m['pk']}=?", [...array_values($data),$id]);
                        audit($db, 'UPDATE', $m['table'], (string)$id, $data);
                        $msg = 'Record updated.';
                    } else {
                        $names = implode(',', $cols);
                        $marks = implode(',', array_fill(0, count($cols), '?'));
                        q($db, "INSERT INTO {$m['table']} ($names) VALUES ($marks)", array_values($data));
                        $id = (int)$db->lastInsertId();
                        audit($db, 'INSERT', $m['table'], (string)$id, $data);
                        $msg = 'Record created.';
                    } $db->commit();
                    redirect($route, $msg);
                } catch (Throwable $e) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }error_log($e->getMessage());
                    $errors['_'] = 'Unable to save this record. Check that values are unique and linked records exist.';
                }
            }
        } $opts = choices($db);
        layout(($id ? 'Edit ' : 'Add ').$m['title'], function () use ($m, $record, $errors, $opts, $route, $id) {?>
<section class="panel form-panel">
<div class="panel-head">
<div>
<h2>
<?= $id ? 'Update record' : 'New record'?>
</h2>
<p class="muted">Fields marked required must be completed.</p>
</div>
<a class="button secondary" href="<?=url($route)?>">Cancel</a>
</div>
<?php if (isset($errors['_'])):?>
<div class="alert error">
<?=e($errors['_'])?>
</div>
<?php endif?>
<form method="post" class="form-grid" data-dirty="true">
<input type="hidden" name="csrf_token" value="<?=e($_SESSION['csrf_token'])?>">
<?php foreach ($m['fields'] as $f => $label):$value = $record[$f] ?? ($_POST[$f] ?? '');
            $isChoice = isset($opts[$f]);
            $type = str_contains($f, 'date') ? 'date' : (str_contains($f, 'area') || str_contains($f, 'quantity') || str_contains($f, 'price') || str_contains($f, 'period') ? 'number' : 'text');?>
<label>
<?=e($label)?>
<?=!in_array($f, ['contact_number','selling_price_per_kg'], true) ? ' <em>*</em>' : ''?>
<?php if ($isChoice):?>
<select name="<?=e($f)?>" required>
<option value="">Choose <?=e(strtolower($label))?>
</option>
<?php foreach ($opts[$f] as $o):?>
<option value="<?=$o['id']?>" <?=$value !== '' && (string)$value === (string)$o['id'] ? 'selected' : ''?>>
<?=e($o['label'])?>
</option>
<?php endforeach?>
</select>
<?php else:?>
<input name="<?=e($f)?>" type="<?=$type?>" value="<?=e($value)?>" <?=$type === 'number' ? 'step="0.01" min="0"' : ''?> <?=!in_array($f, ['contact_number','selling_price_per_kg'], true) ? 'required' : ''?>>
<?php endif?>
<?php if (isset($errors[$f])):?>
<small class="field-error">
<?=e($errors[$f])?>
</small>
<?php endif?>
</label>
<?php endforeach?>
<div class="form-actions">
<button class="button primary">
<?= $id ? 'Save changes' : 'Create record'?>
</button>
</div>
</form>
</section>
<?php });
        exit;
    }
    $term = trim($_GET['q'] ?? '');
    $from = $m['from'] ?? $m['table'];
    $where = [];
    $params = [];
    if ($term && $m['search']) {
        $parts = [];
        foreach ($m['search'] as $field) {
            $parts[] = "$field ILIKE ?";
            $params[] = '%'.$term.'%';
        }$where[] = '('.implode(' OR ', $parts).')';
    }$whereSql = $where ? ' WHERE '.implode(' AND ', $where) : '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per = 20;
    $count = (int)q($db, "SELECT count(*) FROM $from $whereSql", $params)->fetchColumn();
    $rows = q($db, "SELECT {$m['pk']}, {$m['list']} FROM $from $whereSql ORDER BY {$m['pk']} DESC LIMIT $per OFFSET ".(($page - 1) * $per), $params)->fetchAll();
    layout($m['title'], function () use ($m, $route, $rows, $term, $count, $page, $per) {?>
<div class="toolbar">
<form method="get" class="search">
<input type="hidden" name="r" value="<?=e($route)?>">
<input name="q" value="<?=e($term)?>" placeholder="Search records">
<button class="button secondary">Search</button>
</form>
<a class="button primary" href="<?=url($route.'&action=form')?>">+ Add <?=e(rtrim($m['title'], 's'))?>
</a>
</div>
<section class="panel">
<div class="panel-head">
<p class="muted">
<?=number_format($count)?> record<?= $count === 1 ? '' : 's'?>
</p>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<?php foreach (array_keys($rows[0] ?? array_fill_keys([$m['pk'],...array_keys($m['fields'])], '')) as $head):?>
<th>
<?=e(ucwords(str_replace('_', ' ', $head)))?>
</th>
<?php endforeach?>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($rows as $row):?>
<tr>
<?php foreach ($row as $k => $v):?>
<td>
<?=e($v ?? '—')?>
</td>
<?php endforeach?>
<td class="actions">
<a href="<?=url($route.'&action=form&id='.$row[$m['pk']])?>">Edit</a>
<?php if ($_SESSION['user']['role_name'] === 'Administrator'):?>
<form method="post" action="<?=url($route.'&action=delete')?>" data-confirm="Delete this record permanently?">
<input type="hidden" name="csrf_token" value="<?=e($_SESSION['csrf_token'])?>">
<input type="hidden" name="id" value="<?=$row[$m['pk']]?>">
<button class="link danger">Delete</button>
</form>
<?php endif?>
</td>
</tr>
<?php endforeach;
        if (!$rows):?>
<tr>
<td colspan="99" class="empty">No records found. Add the first one to get started.</td>
</tr>
<?php endif?>
</tbody>
</table>
</div>
<?php if ($count > $per):?>
<div class="pagination">
<?php for ($i = 1;$i <= ceil($count / $per);$i++):?>
<a class="<?=$i === $page ? 'active' : ''?>" href="<?=url($route.'&q='.rawurlencode($term).'&page='.$i)?>">
<?=$i?>
</a>
<?php endfor?>
</div>
<?php endif?>
</section>
<?php });
    exit;
}
