<?php

declare(strict_types=1);
if ($route === 'logout') {
    verify_csrf();
    if (!empty($_SESSION['user'])) {
        audit($db, 'LOGOUT', 'app_users', (string)$_SESSION['user']['user_id']);
    } $_SESSION = [];
    session_destroy();
    header('Location: '.url('login'));
    exit;
}
