<?php

declare(strict_types=1);
return (static function (): PDO {
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $host = getenv('SUPABASE_DB_HOST');
    $user = getenv('SUPABASE_DB_USER');
    $password = getenv('SUPABASE_DB_PASSWORD');
    if (!$host || !$user || !$password) {
        throw new RuntimeException('Database configuration is incomplete.');
    }
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s;sslmode=%s', $host, getenv('SUPABASE_DB_PORT') ?: '5432', getenv('SUPABASE_DB_NAME') ?: 'postgres', getenv('SUPABASE_DB_SSLMODE') ?: 'verify-full');
    if ($cert = getenv('SUPABASE_DB_SSLROOTCERT')) {
        $certPath = realpath($cert);
        if ($certPath === false || !is_readable($certPath)) {
            throw new RuntimeException('Configured database SSL root certificate was not found or is not readable.');
        }
        putenv('PGSSLROOTCERT=' . $certPath);
    }
    return $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
})();
