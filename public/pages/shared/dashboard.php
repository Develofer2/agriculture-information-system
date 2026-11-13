<?php
declare(strict_types=1);
if ($route === 'dashboard') {
    layout('Dashboard', function () use ($db) {
        $stats = q($db, 'SELECT (SELECT count(*) FROM farmers) farmers,(SELECT count(*) FROM farms) farms,(SELECT count(*) FROM crops) crops,(SELECT count(*) FROM plantings) plantings,(SELECT count(*) FROM harvests) harvests')->fetch();
        $statuses = q($db, 'SELECT ps.status_name,count(p.planting_id) count FROM planting_statuses ps LEFT JOIN plantings p ON p.status_id=ps.status_id GROUP BY ps.status_id ORDER BY ps.status_id')->fetchAll();
        $total = q($db, 'SELECT coalesce(sum(quantity_kg),0) kg, sum(quantity_kg*selling_price_per_kg) value FROM harvests')->fetch();
        $recent = q($db, "SELECT p.planting_date,f.farm_name,c.crop_name,ps.status_name FROM plantings p JOIN farms f ON f.farm_id=p.farm_id JOIN crops c ON c.crop_id=p.crop_id JOIN planting_statuses ps ON ps.status_id=p.status_id ORDER BY p.created_at DESC LIMIT 6")->fetchAll(); ?>
<section class="metrics">
<?php foreach (['farmers' => 'Farmers','farms' => 'Farms','crops' => 'Crops','plantings' => 'Plantings','harvests' => 'Harvests'] as $k => $v):?>
<a class="metric" href="<?=url($k)?>">
<span>
<?=$v?>
</span>
<strong>
<?=e($stats[$k])?>
</strong>
<small>View records ->
</small>
</a>
<?php endforeach?>
</section>
<section class="content-grid">
<article class="panel">
<div class="panel-head">
<h2>Planting status</h2>
<span class="muted">Live breakdown</span>
</div>
<?php foreach ($statuses as $s):?>
<div class="bar-row">
<span>
<?=e($s['status_name'])?>
</span>
<div class="bar">
<i style="width:<?=min(100, (int)$s['count'] * 12)?>%">
</i>
</div>
<b>
<?=$s['count']?>
</b>
</div>
<?php endforeach?>
</article>
<article class="panel">
<div class="panel-head">
<h2>Harvest overview</h2>
</div>
<strong class="big-number">
<?=number_format((float)$total['kg'], 2)?> <small>kg</small>
</strong>
<p class="muted">Total harvested quantity</p>
<p>Estimated value: <b>
<?= $total['value'] === null ? 'Not available' : '&#8369;'.number_format((float)$total['value'], 2)?>
</b>
</p>
</article>
</section>
<section class="panel">
<div class="panel-head">
<h2>Recent plantings</h2>
<a class="button secondary button-sm" href="<?=url('plantings')?>">
View all <span aria-hidden="true">&rarr;</span>
</a>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Date</th>
<th>Farm</th>
<th>Crop</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php foreach ($recent as $x):?>
<tr>
<td>
<?=e($x['planting_date'])?>
</td>
<td>
<?=e($x['farm_name'])?>
</td>
<td>
<?=e($x['crop_name'])?>
</td>
<td>
<span class="badge">
<?=e($x['status_name'])?>
</span>
</td>
</tr>
<?php endforeach;
        if (!$recent):?>
<tr>
<td colspan="4" class="empty">No planting records yet.</td>
</tr>
<?php endif?>
</tbody>
</table>
</div>
</section>
<?php });
    exit;
}
