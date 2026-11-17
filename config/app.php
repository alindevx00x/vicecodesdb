<?php
declare(strict_types=1);

/**
 * Application Configuration & Environment Loader
 */
$rootPath = dirname(__DIR__);
$envFile = $rootPath . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $val;
            }
        }
    }
}

$env = static fn(string $key, mixed $default = null): mixed => $_ENV[$key] ?? getenv($key) ?: $default;

// Auto-detect base path so app works both under MAMP (/GTA6CheatCodes) and production root (/)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$detectedBase = '';
if (preg_match('#^(.*?)/public/index\.php$#', $scriptName, $m)) {
    $detectedBase = $m[1];
}

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);
$scheme = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8888';
$basePath = rtrim((string) $env('BASE_PATH', $detectedBase), '/');
$appUrl = rtrim((string) $env('APP_URL', $scheme . '://' . $host . $basePath), '/');

return [
    'env'          => (string) $env('APP_ENV', 'local'),
    'debug'        => filter_var($env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'root_path'    => $rootPath,
    'public_path'  => $rootPath . '/public',
    'views_path'   => $rootPath . '/views',
    'storage_path' => $rootPath . '/storage',
    'base_path'    => $basePath,
    'app_url'      => $appUrl,
    'timezone'     => 'UTC',
    'upload_max_bytes' => 5 * 1024 * 1024, // 5 MB
    'allowed_mimes'    => [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ],
];
