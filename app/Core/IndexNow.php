<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Automatic IndexNow Ping Helper (Bing / ChatGPT Search / Yandex / Seznam)
 */
final class IndexNow
{
    public static function pingUrls(array $relativePaths, array $settings = []): array
    {
        $enabled = ($settings['indexnow_enabled'] ?? '1') === '1';
        $key = trim((string) ($settings['indexnow_key'] ?? ''));
        if (!$enabled || $key === '' || $relativePaths === []) {
            return ['sent' => false, 'reason' => 'disabled_or_missing_key'];
        }

        $fullUrls = [];
        foreach ($relativePaths as $p) {
            $fullUrls[] = str_starts_with($p, 'http') ? $p : Url::full($p);
        }
        $fullUrls = array_values(array_unique($fullUrls));

        $firstParsed = parse_url($fullUrls[0]);
        $host = $firstParsed['host'] ?? 'localhost';
        $keyLocation = Url::full('/' . $key . '.txt');

        $payload = [
            'host'        => $host,
            'key'         => $key,
            'keyLocation' => $keyLocation,
            'urlList'     => $fullUrls,
        ];

        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        // In local development, log pings cleanly instead of hitting Bing with localhost URLs
        $isLocal = in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || ($_ENV['APP_ENV'] ?? 'local') !== 'production';

        if ($isLocal) {
            @file_put_contents(
                $logDir . '/indexnow.log',
                sprintf("[%s] [LOCAL-DRYRUN] %s\n", date('c'), json_encode($payload, JSON_UNESCAPED_SLASHES)),
                FILE_APPEND
            );
            return ['sent' => false, 'reason' => 'local_logged', 'urls' => $fullUrls];
        }

        $status = 0;
        $resp = '';
        if (function_exists('curl_init')) {
            $ch = curl_init('https://api.indexnow.org/indexnow');
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 3,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=utf-8'],
                CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_SLASHES),
            ]);
            $resp = (string) curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        }

        @file_put_contents(
            $logDir . '/indexnow.log',
            sprintf("[%s] [HTTP %d] %s | %s\n", date('c'), $status, json_encode($fullUrls), substr($resp, 0, 200)),
            FILE_APPEND
        );

        return ['sent' => ($status >= 200 && $status < 300), 'http_code' => $status, 'urls' => $fullUrls];
    }
}
