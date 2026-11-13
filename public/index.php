<?php

declare(strict_types=1);

require dirname(__DIR__) . '/config/bootstrap.php';
try {
    $db = require dirname(__DIR__) . '/config/database.php';
} catch (Throwable $error) {
    error_log($error->getMessage());
    http_response_code(503);
    exit('Service temporarily unavailable.');
}

$route = $_GET['r'] ?? (empty($_SESSION['user']) ? 'login' : 'dashboard');
require dirname(__DIR__) . '/config/app.php';

if ($route === 'login') {
    require __DIR__ . '/pages/login.php';
}

if ($route === 'logout') {
    require __DIR__ . '/pages/logout.php';
}

require_login();
header('Cache-Control: private, no-store');

$section = $_SESSION['user']['role_name'] === 'Administrator' ? 'admin' : 'user';

if ($route === 'dashboard') {
    require __DIR__ . '/pages/' . $section . '/dashboard.php';
}

if (isset($modules[$route])) {
    require __DIR__ . '/pages/' . $section . '/' . $route . '.php';
}

if ($route === 'reports') {
    require __DIR__ . '/pages/' . $section . '/reports.php';
}

if ($route === 'users' && $section === 'admin') {
    require __DIR__ . '/pages/admin/users.php';
}

if ($route === 'audit' && $section === 'admin') {
    require __DIR__ . '/pages/admin/audit.php';
}

http_response_code(404);
require __DIR__ . '/pages/not-found.php';
