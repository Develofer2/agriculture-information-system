<?php

declare(strict_types=1);
require dirname(__DIR__) . '/config/bootstrap.php';
if (PHP_SAPI !== 'cli') {
    exit("CLI only\n");
}
[$script,$name,$email,$password] = array_pad($argv, 4, '');
if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
    exit("Usage: php scripts/create-admin.php \"Full Name\" email@example.com \"12+ character password\"\n");
}
$db = require dirname(__DIR__).'/config/database.php';
$role = (int)$db->query("SELECT role_id FROM app_roles WHERE role_name='Administrator'")->fetchColumn();
$s = $db->prepare('INSERT INTO app_users(full_name,email,password_hash,role_id) VALUES(?,?,?,?)');
$s->execute([$name,strtolower($email),password_hash($password, PASSWORD_DEFAULT),$role]);
echo "Administrator created. Sign in, then store the password securely.\n";
