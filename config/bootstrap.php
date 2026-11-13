<?php

declare(strict_types=1);

/** Application bootstrap. Keep config/ outside the public document root. */
$root = dirname(__DIR__);
if (is_file($root . '/.env')) {
    foreach (file($root . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if (str_starts_with(ltrim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"");
        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
        }
    }
}
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Asia/Manila');
$isProduction = (getenv('APP_ENV') ?: 'local') === 'production';
ini_set('display_errors', (!$isProduction && getenv('APP_DEBUG') === 'true') ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', $root . '/storage/logs/php-error.log');
if (!is_dir($root . '/storage/logs')) {
    mkdir($root . '/storage/logs', 0775, true);
}

session_set_cookie_params([
    'lifetime' => 0, 'path' => '/', 'secure' => getenv('SESSION_SECURE') === 'true',
    'httponly' => true, 'samesite' => 'Lax',
]);
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'; img-src 'self' data:");

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function url(string $path = ''): string
{
    if ($path === '') {
        return '/index.php';
    }
    [$route, $query] = array_pad(explode('&', $path, 2), 2, '');
    return '/index.php?r=' . rawurlencode($route) . ($query !== '' ? '&' . $query : '');
}
function redirect(string $route, string $message = '', string $type = 'success'): never
{
    if ($message) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
    header('Location: ' . url($route));
    exit;
}
function require_login(): void
{
    if (empty($_SESSION['user'])) {
        redirect('login');
    }
}
function require_admin(): void
{
    require_login();
    if (($_SESSION['user']['role_name'] ?? '') !== 'Administrator') {
        http_response_code(403);
        exit('Access denied.');
    }
}
function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('Invalid request. Please refresh and try again.');
    }
}
function flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
