<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight File-Based Page Cache (/storage/cache/)
 * Serves repeat public GET traffic in <5ms without MySQL queries and auto-invalidates on CMS writes.
 */
final class PageCache
{
    private const TTL = 600; // 10 minutes

    public static function cacheDir(): string
    {
        return dirname(__DIR__, 2) . '/storage/cache';
    }

    public static function setEnabledFlag(bool $enabled): void
    {
        $dir = self::cacheDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($dir . '/.enabled', $enabled ? '1' : '0', LOCK_EX);
    }

    public static function isEnabled(): bool
    {
        $flagFile = self::cacheDir() . '/.enabled';
        if (file_exists($flagFile)) {
            return trim((string) @file_get_contents($flagFile)) === '1';
        }
        return true;
    }

    public static function isCacheableRequest(): bool
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            return false;
        }
        if (!self::isEnabled()) {
            return false;
        }
        if (isset($_GET['nocache']) || isset($_GET['preview'])) {
            return false;
        }
        // Skip caching when query parameters exist (filters/search/loadout links stay dynamic)
        if (!empty($_GET)) {
            return false;
        }
        $path = Url::currentPath();
        $skipPrefixes = ['/admin', '/search', '/newsletter', '/cheats/filter', '/og/', '/sitemap', '/feed.xml'];
        foreach ($skipPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return false;
            }
        }
        // Bypass cache for logged-in admins or active flash notifications
        if (!empty($_SESSION['admin_user_id']) || !empty($_SESSION['_flash'])) {
            return false;
        }
        return true;
    }

    private static function keyFile(string $path): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return self::cacheDir() . '/' . hash('sha256', $host . '|' . $path) . '.html';
    }

    public static function tryServe(): bool
    {
        if (!self::isCacheableRequest()) {
            return false;
        }
        $file = self::keyFile(Url::currentPath());
        if (!file_exists($file)) {
            return false;
        }
        if ((time() - (int) @filemtime($file)) > self::TTL) {
            @unlink($file);
            return false;
        }
        $html = @file_get_contents($file);
        if ($html === false || $html === '') {
            return false;
        }
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
            header('X-VC-Cache: HIT');
        }
        $html = str_replace('<!--VC_CSRF_TOKEN-->', htmlspecialchars(Security::csrfToken(), ENT_QUOTES, 'UTF-8'), $html);
        echo $html;
        return true;
    }

    public static function startCapture(): bool
    {
        if (!self::isCacheableRequest()) {
            return false;
        }
        if (!headers_sent()) {
            header('X-VC-Cache: MISS');
        }
        ob_start();
        return true;
    }

    public static function endCaptureAndStore(): void
    {
        if (ob_get_level() < 1) {
            return;
        }
        $html = ob_get_contents();
        ob_end_flush();

        if ($html !== false && $html !== '' && http_response_code() === 200) {
            $dir = self::cacheDir();
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $token = htmlspecialchars(Security::csrfToken(), ENT_QUOTES, 'UTF-8');
            $storable = str_replace($token, '<!--VC_CSRF_TOKEN-->', $html);
            @file_put_contents(self::keyFile(Url::currentPath()), $storable, LOCK_EX);
        }
    }

    public static function invalidateAll(): void
    {
        $dir = self::cacheDir();
        if (!is_dir($dir)) {
            return;
        }
        foreach (glob($dir . '/*.html') ?: [] as $file) {
            @unlink($file);
        }
    }
}
