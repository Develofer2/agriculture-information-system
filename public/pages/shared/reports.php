<?php
declare(strict_types=1);
if ($route === 'reports') {
    layout('Reports', function () use ($db) {
        $rows = q($db,
 "SELECT f.farm_name,
 c.crop_name,
 c.variety,
 count(h.harvest_id) harvest_count,
 coalesce(sum(h.quantity_kg),
0) total_kg,
 sum(h.quantity_kg*h.selling_price_per_kg) estimated_value FROM farms f LEFT JOIN plantings p ON p.farm_id=f.farm_id LEFT JOIN crops c ON c.crop_id=p.crop_id LEFT JOIN harvests h ON h.planting_id=p.planting_id GROUP BY f.farm_name,
c.crop_name,
c.variety ORDER BY total_kg DESC")->fetchAll();?>
<div class="toolbar">
<p class="muted">Harvest totals by farm and crop. Values update directly from the database.</p>
<button class="button secondary" data-print>Print report</button>
</div>
<section class="panel">
<div class="panel-head">
<h2>Harvest performance</h2>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Farm</th>
<th>Crop</th>
<th>Variety</th>
<th>Harvests</th>
<th>Total kg</th>
<th>Estimated value</th>
</tr>
</thead>
<tbody>
<?php foreach ($rows as $r):?>
<tr>
<td>
<?=e($r['farm_name'])?>
</td>
<td>
<?=e($r['crop_name'] ?? '-')?>
</td>
<td>
<?=e($r['variety'] ?? '-')?>
</td>
<td>
<?=$r['harvest_count']?>
</td>
<td>
<?=number_format((float)$r['total_kg'], 2)?>
</td>
<td>
<?=$r['estimated_value'] === null ? 'Not available' : '&#8369;'.number_format((float)$r['estimated_value'], 2)?>
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
