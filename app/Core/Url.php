<?php
declare(strict_types=1);

namespace App\Core;

final class Url
{
    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function basePath(): string
    {
        return self::$config['base_path'] ?? '';
    }

    public static function appUrl(): string
    {
        return self::$config['app_url'] ?? 'http://localhost:8888/GTA6CheatCodes';
    }

    public static function to(string $path = '/'): string
    {
        $path = '/' . ltrim($path, '/');
        if ($path === '//') {
            $path = '/';
        }
        return self::basePath() . $path;
    }

    public static function full(string $path = '/'): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $path = '/' . ltrim($path, '/');
        return self::appUrl() . ($path === '/' ? '/' : $path);
    }

    public static function asset(string $path): string
    {
        return self::basePath() . '/assets/' . ltrim($path, '/');
    }

    public static function currentPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = self::basePath();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        // Also handle direct /public access if someone hits /GTA6CheatCodes/public/...
        if (str_starts_with($path, '/public')) {
            $path = substr($path, 7) ?: '/';
        }
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public static function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text) ?? '';
        $text = trim($text, '-');
        return $text !== '' ? $text : 'item-' . time();
    }
}
