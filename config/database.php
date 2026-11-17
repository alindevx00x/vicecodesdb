<?php
declare(strict_types=1);

/**
 * MySQL 8.0+ PDO Database Configuration (MAMP & Production Ready)
 */
$env = static fn(string $key, mixed $default = null): mixed => $_ENV[$key] ?? getenv($key) ?: $default;

return [
    'host'    => (string) $env('DB_HOST', '127.0.0.1'),
    'port'    => (int) $env('DB_PORT', 8889),
    'socket'  => (string) $env('DB_SOCKET', '/Applications/MAMP/tmp/mysql/mysql.sock'),
    'name'    => (string) $env('DB_NAME', 'gta6cheatcodes'),
    'user'    => (string) $env('DB_USER', 'root'),
    'pass'    => (string) $env('DB_PASS', 'root'),
    'charset' => 'utf8mb4',
];
