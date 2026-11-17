<?php
declare(strict_types=1);

namespace App\Core;

final class Security
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function csrfToken(): string
    {
        self::startSession();
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verifyCsrf(?string $token = null): bool
    {
        self::startSession();
        $sessionToken = $_SESSION['_csrf_token'] ?? '';
        $submitted = $token ?? ($_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if ($sessionToken === '' || $submitted === '') {
            return false;
        }
        return hash_equals($sessionToken, (string) $submitted);
    }

    public static function clientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Simple file-based rate limiter (login, newsletter, contact)
     */
    public static function checkRateLimit(string $bucket, int $maxAttempts = 10, int $windowSeconds = 300): bool
    {
        $dir = dirname(__DIR__, 2) . '/storage/logs/rate_limits';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $key = hash('sha256', $bucket . '|' . self::clientIp());
        $file = $dir . '/' . $key . '.json';
        $now = time();
        $attempts = [];

        if (file_exists($file)) {
            $raw = @file_get_contents($file);
            $decoded = $raw ? json_decode($raw, true) : [];
            if (is_array($decoded)) {
                $attempts = array_values(array_filter($decoded, static fn($ts) => is_int($ts) && ($now - $ts) < $windowSeconds));
            }
        }

        if (count($attempts) >= $maxAttempts) {
            return false;
        }

        $attempts[] = $now;
        @file_put_contents($file, json_encode($attempts), LOCK_EX);
        return true;
    }

    /**
     * Sanitize rich HTML from TinyMCE while preserving editorial tags, tables, images, and links.
     */
    public static function sanitizeRichHtml(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // Remove dangerous tags completely
        $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button|meta|link|base)\b[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button|meta|link|base)\b[^>]*\/?>#is', '', $html) ?? '';

        // Strip inline JS event handlers (onclick, onerror, onload, etc.)
        $html = preg_replace('/\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';

        // Strip javascript: and data:text/html URIs
        $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*(?:javascript|vbscript|data:text\/html):[^"\']*\2/i', '$1=$2#$2', $html) ?? '';

        return trim($html);
    }

    public static function sendSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }

    public static function isIpAllowed(string $allowlistCsv): bool
    {
        $list = array_filter(array_map('trim', explode(',', $allowlistCsv)));
        if ($list === []) {
            return true;
        }
        return in_array(self::clientIp(), $list, true);
    }

    public static function generateTotpSecret(int $length = 16): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, 31)];
        }
        return $secret;
    }

    public static function verifyTotp(string $base32Secret, string $code, int $window = 1): bool
    {
        $cleanCode = preg_replace('/\D/', '', $code);
        if (strlen($cleanCode) !== 6) {
            return false;
        }
        $secretBytes = self::base32Decode(strtoupper(trim($base32Secret)));
        if ($secretBytes === '') {
            return false;
        }
        $timeSlice = (int) floor(time() / 30);
        for ($i = -$window; $i <= $window; $i++) {
            $calc = self::computeTotp($secretBytes, $timeSlice + $i);
            if (hash_equals($calc, $cleanCode)) {
                return true;
            }
        }
        return false;
    }

    private static function computeTotp(string $secretBytes, int $timeSlice): string
    {
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hm = hash_hmac('sha1', $time, $secretBytes, true);
        $offset = ord(substr($hm, -1)) & 0x0F;
        $hashPart = substr($hm, $offset, 4);
        $value = unpack('N', $hashPart)[1] & 0x7FFFFFFF;
        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private static function base32Decode(string $b32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $b32 = rtrim($b32, '=');
        $binary = '';
        for ($i = 0, $len = strlen($b32); $i < $len; $i++) {
            $pos = strpos($alphabet, $b32[$i]);
            if ($pos === false) {
                continue;
            }
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) === 8) {
                $out .= chr((int) bindec($byte));
            }
        }
        return $out;
    }
}
