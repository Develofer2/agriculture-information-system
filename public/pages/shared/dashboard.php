<?php
declare(strict_types=1);
if ($route === 'dashboard') {
    layout('Dashboard', function () use ($db) {
        $stats = q($db, 'SELECT (SELECT count(*) FROM farmers) farmers,(SELECT count(*) FROM farms) farms,(SELECT count(*) FROM crops) crops,(SELECT count(*) FROM plantings) plantings,(SELECT count(*) FROM harvests) harvests')->fetch();
        $statuses = q($db, 'SELECT ps.status_name,count(p.planting_id) count FROM planting_statuses ps LEFT JOIN plantings p ON p.status_id=ps.status_id GROUP BY ps.status_id ORDER BY ps.status_id')->fetchAll();
        $total = q($db, 'SELECT coalesce(sum(quantity_kg),0) kg, sum(quantity_kg*selling_price_per_kg) value FROM harvests')->fetch();
        $recent = q($db, "SELECT p.planting_date,f.farm_name,c.crop_name,ps.status_name FROM plantings p JOIN farms f ON f.farm_id=p.farm_id JOIN crops c ON c.crop_id=p.crop_id JOIN planting_statuses ps ON ps.status_id=p.status_id ORDER BY p.created_at DESC LIMIT 6")->fetchAll(); ?>
<section class="metrics dashboard-metrics">
<?php foreach (['farmers' => 'Farmers','farms' => 'Farms','crops' => 'Crops','plantings' => 'Plantings','harvests' => 'Harvests'] as $k => $v):?>
<a class="metric" href="<?=url($k)?>">
<span class="metric-icon">
<?=nav_icon($k)?>
</span>
<span class="metric-label">
<?=$v?>
</span>
<strong class="metric-value">
<?=e($stats[$k])?>
</strong>
<small class="metric-link">View records <span aria-hidden="true">&rarr;</span>
</small>
</a>
<?php endforeach?>
</section>
<section class="content-grid">
<article class="panel dashboard-panel status-panel">
<div class="panel-head">
<div>
<span class="section-kicker">OPERATIONS</span>
<h2>Planting status</h2>
</div>
<span class="panel-note">Live breakdown</span>
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
<article class="panel dashboard-panel harvest-panel">
<div class="panel-head">
<div>
<span class="section-kicker">OUTPUT</span>
<h2>Harvest overview</h2>
</div>
</div>
<strong class="big-number harvest-total">
<?=number_format((float)$total['kg'], 2)?> <small>kg</small>
</strong>
<p class="muted">Total harvested quantity</p>
<div class="value-summary">
<span>Estimated value</span>
<b>
<?= $total['value'] === null ? 'Not available' : '&#8369;'.number_format((float)$total['value'], 2)?>
</b>
</div>
</article>
</section>
<section class="panel dashboard-panel recent-panel">
<div class="panel-head">
<div>
<span class="section-kicker">LATEST ACTIVITY</span>
<h2>Recent plantings</h2>
</div>
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
