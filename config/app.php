<?php
declare(strict_types=1);
$modules = [
 'farmers' => ['title' => 'Farmers',
'table' => 'farmers',
'pk' => 'farmer_id',
'fields' => ['first_name' => 'First name',
'last_name' => 'Last name',
'contact_number' => 'Contact number',
'address' => 'Address',
'date_registered' => 'Date registered'],
'search' => ['first_name',
'last_name',
'address'],
'list' => "CONCAT(first_name,
 ' ',
 last_name) AS name,
 contact_number,
 address,
 date_registered"],

 'farms' => ['title' => 'Farms',
'table' => 'farms',
'pk' => 'farm_id',
'fields' => ['farmer_id' => 'Farmer',
'farm_name' => 'Farm name',
'location' => 'Location',
'area_hectares' => 'Area (ha)',
'soil_type_id' => 'Soil type',
'irrigation_type_id' => 'Irrigation'],
'search' => ['farm_name',
'location'],
'list' => "f.farm_name,
 CONCAT(fr.first_name,
 ' ',
 fr.last_name) AS farmer,
 f.location,
 f.area_hectares,
 st.soil_type_name AS soil,
 it.irrigation_type_name AS irrigation",
'from' => 'farms f JOIN farmers fr ON fr.farmer_id=f.farmer_id JOIN soil_types st ON st.soil_type_id=f.soil_type_id JOIN irrigation_types it ON it.irrigation_type_id=f.irrigation_type_id'],

 'crops' => ['title' => 'Crops',
'table' => 'crops',
'pk' => 'crop_id',
'fields' => ['crop_name' => 'Crop name',
'variety' => 'Variety',
'category_id' => 'Category',
'growing_period_days' => 'Growing period (days)',
'season_id' => 'Recommended season'],
'search' => ['crop_name',
'variety'],
'list' => 'c.crop_name,
 c.variety,
 cc.category_name AS category,
 c.growing_period_days,
 gs.season_name AS season',
'from' => 'crops c JOIN crop_categories cc ON cc.category_id=c.category_id JOIN growing_seasons gs ON gs.season_id=c.season_id'],

 'plantings' => ['title' => 'Planting Records',
'table' => 'plantings',
'pk' => 'planting_id',
'fields' => ['farm_id' => 'Farm',
'crop_id' => 'Crop',
'planting_date' => 'Planting date',
'area_planted_ha' => 'Area planted (ha)',
'seed_quantity_kg' => 'Seed quantity (kg)',
'expected_harvest_date' => 'Expected harvest date',
'status_id' => 'Status'],
'search' => [],
'list' => "p.planting_date,
 f.farm_name AS farm,
 c.crop_name || ' - ' || c.variety AS crop,
 p.area_planted_ha,
 ps.status_name AS status,
 p.expected_harvest_date",
'from' => 'plantings p JOIN farms f ON f.farm_id=p.farm_id JOIN crops c ON c.crop_id=p.crop_id JOIN planting_statuses ps ON ps.status_id=p.status_id'],

 'harvests' => ['title' => 'Harvest Records',
'table' => 'harvests',
'pk' => 'harvest_id',
'fields' => ['planting_id' => 'Planting',
'harvest_date' => 'Harvest date',
'quantity_kg' => 'Quantity (kg)',
'selling_price_per_kg' => 'Selling price/kg',
'quality_grade_id' => 'Quality grade',
'remark_id' => 'Harvest remark'],
'search' => [],
'list' => 'h.harvest_date,
 f.farm_name AS farm,
 c.crop_name,
 h.quantity_kg,
 h.selling_price_per_kg,
 q.grade_name AS grade,
 r.remark_description AS remark',
'from' => 'harvests h JOIN plantings p ON p.planting_id=h.planting_id JOIN farms f ON f.farm_id=p.farm_id JOIN crops c ON c.crop_id=p.crop_id JOIN harvest_quality_grades q ON q.quality_grade_id=h.quality_grade_id JOIN harvest_remarks r ON r.remark_id=h.remark_id'],

];
function q(PDO $db, string $sql, array $params = []): PDOStatement
{
    $s = $db->prepare($sql);
    $s->execute($params);
    return $s;
}
function audit(PDO $db, string $action, string $entity, string $id, array $values = []): void
{
    q($db, 'INSERT INTO audit_logs(actor_user_id,action,entity_table,record_pk,new_values) VALUES(?,?,?,?,?::jsonb)', [$_SESSION['user']['user_id'] ?? null,$action,$entity,$id,json_encode($values)]);
}
function choices(PDO $db): array
{
    return [
     'farmer_id' => q($db, "SELECT farmer_id id, first_name || ' ' || last_name label FROM farmers ORDER BY last_name,first_name")->fetchAll(),
     'soil_type_id' => q($db, 'SELECT soil_type_id id,soil_type_name label FROM soil_types WHERE is_active ORDER BY label')->fetchAll(), 'irrigation_type_id' => q($db, 'SELECT irrigation_type_id id,irrigation_type_name label FROM irrigation_types WHERE is_active ORDER BY label')->fetchAll(),
     'category_id' => q($db, 'SELECT category_id id,category_name label FROM crop_categories WHERE is_active ORDER BY label')->fetchAll(), 'season_id' => q($db, 'SELECT season_id id,season_name label FROM growing_seasons WHERE is_active ORDER BY label')->fetchAll(),
     'farm_id' => q($db,
 'SELECT farm_id id,
farm_name label FROM farms ORDER BY label')->fetchAll(),
 'crop_id' => q($db,
 "SELECT crop_id id,
crop_name || ' - ' || variety label FROM crops ORDER BY label")->fetchAll(),
 'status_id' => q($db,
 'SELECT status_id id,
status_name label FROM planting_statuses WHERE is_active ORDER BY id')->fetchAll(),

     'planting_id' => q($db,
 "SELECT p.planting_id id,
 f.farm_name || ' - ' || c.crop_name || ' (' || ps.status_name || ')' label
 FROM plantings p
 JOIN farms f ON f.farm_id=p.farm_id
 JOIN crops c ON c.crop_id=p.crop_id
 JOIN planting_statuses ps ON ps.status_id=p.status_id
 WHERE ps.status_name IN ('Ready for Harvest', 'Harvested')
 ORDER BY p.planting_date DESC")->fetchAll(),
 'quality_grade_id' => q($db,
 'SELECT quality_grade_id id,
grade_name label FROM harvest_quality_grades WHERE is_active ORDER BY id')->fetchAll(),
 'remark_id' => q($db,
 'SELECT remark_id id,
remark_description label FROM harvest_remarks WHERE is_active ORDER BY id')->fetchAll(),

     ];
}
function nav_icon(string $name): string
{
    $paths = ['dashboard' => 'M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z',
'farmers' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-3a3 3 0 0 1 0 6m4 7v-2a4 4 0 0 0-3-3.87',
'farms' => 'M3 21h18M5 21V9l7-5 7 5v12M9 21v-6h6v6',
'crops' => 'M12 21V11m0 0c-4 0-7-2.5-7-7 4.5 0 7 2.5 7 7Zm0 0c4 0 7-2.5 7-7-4.5 0-7 2.5-7 7Z',
'plantings' => 'M6 3v4m12-4v4M4 10h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z',
'harvests' => 'M12 3v18m0-14c-3 0-5-1.5-5-4 3 0 5 1.5 5 4Zm0 0c3 0 5-1.5 5-4-3 0-5 1.5-5 4Z',
'reports' => 'M5 3h10l4 4v14H5V3Zm10 0v5h4M8 13h8M8 17h6',
'users' => 'M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-3a3 3 0 0 1 0 6m4 7v-2a4 4 0 0 0-3-3.87',
'audit' => 'M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
'logout' => 'M10 17l5-5-5-5m5 5H3m9-9V3h9v18h-9v-3'];
    $path = $paths[$name] ?? $paths['dashboard'];
    return '<svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
<path d="'.$path.'"/>
</svg>';
}
function layout(string $title, callable $body): void
{
    $user = $_SESSION['user'];
    $f = flash();
    $links = ['dashboard' => 'Dashboard','farmers' => 'Farmers','farms' => 'Farms','crops' => 'Crops','plantings' => 'Plantings','harvests' => 'Harvests','reports' => 'Reports']; ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#17324D">
<link rel="manifest" href="manifest.webmanifest">
<link rel="stylesheet" href="assets/css/app.css?v=3">
<title>
<?=e($title)?> | Verdant Vale</title>
</head>
<body>
<aside class="sidebar" id="sidebar">
<div class="brand">
<span class="brand-mark">
<?=nav_icon('crops')?>
</span>
<span class="brand-name">Verdant Vale</span>
</div>
<button class="sidebar-toggle" id="sidebar-toggle" aria-label="Collapse sidebar" title="Collapse sidebar" aria-expanded="true">
</button>
<nav aria-label="Main navigation">
<?php foreach ($links as $r => $n): ?>
<a class="<?=($r === ($_GET['r'] ?? 'dashboard') ? 'active' : '')?>" href="<?=url($r)?>" title="<?=e($n)?>">
<?=nav_icon($r)?>
<span>
<?=$n?>
</span>
</a>
<?php endforeach;
    if ($user['role_name'] === 'Administrator'): ?>
<div class="nav-divider">
<span>Administration</span>
</div>
<a class="<?=($_GET['r'] ?? '') === 'users' ? 'active' : ''?>" href="<?=url('users')?>" title="Users">
<?=nav_icon('users')?>
<span>Users</span>
</a>
<a class="<?=($_GET['r'] ?? '') === 'audit' ? 'active' : ''?>" href="<?=url('audit')?>" title="Audit logs">
<?=nav_icon('audit')?>
<span>Audit logs</span>
</a>
<?php endif; ?>
</nav>
<form method="post" action="<?=url('logout')?>">
<input type="hidden" name="csrf_token" value="<?=e($_SESSION['csrf_token'])?>">
<button class="logout" title="Sign out">
<?=nav_icon('logout')?>
<span>Sign out</span>
</button>
</form>
</aside>
<main>
<header>
<button class="mobile-menu icon-button" aria-label="Open navigation">&#9776;</button>
<div class="page-heading">
<p class="eyebrow">AGRICULTURE INFORMATION SYSTEM</p>
<h1>
<?=e($title)?>
</h1>
</div>
<div class="user-chip">
<span class="user-details">
<span class="user-name">
<?=e($user['full_name'])?>
</span>
<small class="user-role">
<?=e($user['role_name'])?>
</small>
</span>
<span class="user-divider" aria-hidden="true"></span>
<button id="theme-toggle" class="icon-button theme-button" aria-label="Toggle dark mode" title="Toggle dark mode">&#9681;</button>
</div>
</header>
<?php if ($f): ?>
<div class="alert <?=$f['type'] === 'error' ? 'error' : 'success'?>">
<?=e($f['message'])?>
</div>
<?php endif;
    $body(); ?>
</main>
<div class="backdrop">
</div>
<script src="assets/js/app.js?v=3">
</script>
</body>
</html>
<?php }
