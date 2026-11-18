<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Multi-Driver Mailer (Local Log, Resend HTTP API, Brevo HTTP API, SMTP, PHP mail)
 */
final class Mailer
{
    public static function send(string $to, string $subject, string $htmlBody, string $textBody, array $settings = []): array
    {
        $driver = strtolower(trim((string) ($settings['mail_driver'] ?? 'log')));
        $from = trim((string) ($settings['mail_from'] ?? $settings['contact_email'] ?? 'noreply@vicecodesdb.com'));
        $siteName = trim((string) ($settings['site_name'] ?? 'ViceCodes'));

        $logLine = sprintf(
            "[%s] [driver=%s] To: %s | Subject: %s | Text: %s\n",
            date('c'),
            $driver,
            $to,
            $subject,
            preg_replace('/\s+/', ' ', $textBody)
        );
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        @file_put_contents($logDir . '/newsletter_emails.log', $logLine, FILE_APPEND);

        if ($driver === 'log') {
            return ['ok' => true, 'driver' => 'log'];
        }

        if ($driver === 'resend') {
            return self::sendViaResend($from, $siteName, $to, $subject, $htmlBody, $textBody, (string) ($settings['mail_api_key'] ?? ''));
        }

        if ($driver === 'brevo') {
            return self::sendViaBrevo($from, $siteName, $to, $subject, $htmlBody, $textBody, (string) ($settings['mail_api_key'] ?? ''));
        }

        if ($driver === 'smtp') {
            return self::sendViaSmtp($from, $siteName, $to, $subject, $textBody, $htmlBody, $settings);
        }

        $headers = "From: {$siteName} <{$from}>\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
        $sent = @mail($to, $subject, $htmlBody, $headers);
        return ['ok' => $sent, 'driver' => 'mail'];
    }

    private static function sendViaResend(string $from, string $siteName, string $to, string $subject, string $html, string $text, string $apiKey): array
    {
        if ($apiKey === '' || !function_exists('curl_init')) {
            return ['ok' => false, 'driver' => 'resend', 'error' => 'Missing Resend API key or curl'];
        }
        $payload = [
            'from'    => "{$siteName} <{$from}>",
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $html,
            'text'    => $text,
        ];
        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_SLASHES),
        ]);
        $resp = (string) curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['ok' => ($code >= 200 && $code < 300), 'driver' => 'resend', 'http_code' => $code, 'response' => $resp];
    }

    private static function sendViaBrevo(string $from, string $siteName, string $to, string $subject, string $html, string $text, string $apiKey): array
    {
        if ($apiKey === '' || !function_exists('curl_init')) {
            return ['ok' => false, 'driver' => 'brevo', 'error' => 'Missing Brevo API key or curl'];
        }
        $payload = [
            'sender'      => ['name' => $siteName, 'email' => $from],
            'to'          => [['email' => $to]],
            'subject'     => $subject,
            'htmlContent' => $html,
            'textContent' => $text,
        ];
        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_HTTPHEADER     => [
                'api-key: ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_SLASHES),
        ]);
        $resp = (string) curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['ok' => ($code >= 200 && $code < 300), 'driver' => 'brevo', 'http_code' => $code, 'response' => $resp];
    }

    private static function sendViaSmtp(string $from, string $siteName, string $to, string $subject, string $text, string $html, array $settings): array
    {
        $host = trim((string) ($settings['smtp_host'] ?? ''));
        $port = (int) ($settings['smtp_port'] ?? 587);
        $user = trim((string) ($settings['smtp_user'] ?? ''));
        $pass = (string) ($settings['smtp_pass'] ?? '');
        $enc  = strtolower(trim((string) ($settings['smtp_encryption'] ?? 'tls')));

        if ($host === '' || $port <= 0) {
            return ['ok' => false, 'driver' => 'smtp', 'error' => 'SMTP host not configured'];
        }

        $target = ($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $fp = @stream_socket_client($target, $errno, $errstr, 6);
        if (!$fp) {
            return ['ok' => false, 'driver' => 'smtp', 'error' => "Connection failed: {$errstr}"];
        }
        stream_set_timeout($fp, 6);

        $read = static function () use ($fp): string {
            $data = '';
            while (($line = fgets($fp, 515)) !== false) {
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            return $data;
        };
        $cmd = static function (string $c) use ($fp, $read): string {
            fwrite($fp, $c . "\r\n");
            return $read();
        };

        $read(); // greeting
        $ehloHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $cmd('EHLO ' . preg_replace('/[^a-zA-Z0-9.-]/', '', $ehloHost));

        if ($enc === 'tls') {
            $resp = $cmd('STARTTLS');
            if (str_starts_with($resp, '220')) {
                @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $cmd('EHLO ' . preg_replace('/[^a-zA-Z0-9.-]/', '', $ehloHost));
            }
        }

        if ($user !== '' && $pass !== '') {
            $cmd('AUTH LOGIN');
            $cmd(base64_encode($user));
            $authResp = $cmd(base64_encode($pass));
            if (!str_starts_with($authResp, '235')) {
                fclose($fp);
                return ['ok' => false, 'driver' => 'smtp', 'error' => 'SMTP auth failed'];
            }
        }

        $mailFromResp = $cmd("MAIL FROM:<{$from}>");
        if (!str_starts_with($mailFromResp, '250')) {
            $cmd('QUIT');
            fclose($fp);
            return ['ok' => false, 'driver' => 'smtp', 'error' => 'MAIL FROM rejected: ' . trim($mailFromResp)];
        }

        $rcptResp = $cmd("RCPT TO:<{$to}>");
        if (!str_starts_with($rcptResp, '250') && !str_starts_with($rcptResp, '251')) {
            $cmd('QUIT');
            fclose($fp);
            return ['ok' => false, 'driver' => 'smtp', 'error' => 'RCPT TO rejected: ' . trim($rcptResp)];
        }

        $dataStart = $cmd('DATA');
        if (!str_starts_with($dataStart, '354')) {
            $cmd('QUIT');
            fclose($fp);
            return ['ok' => false, 'driver' => 'smtp', 'error' => 'DATA rejected: ' . trim($dataStart)];
        }

        $boundary = 'vc_' . bin2hex(random_bytes(8));
        $msg  = "From: {$siteName} <{$from}>\r\n";
        $msg .= "To: <{$to}>\r\n";
        $msg .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";
        $msg .= "--{$boundary}\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n{$text}\r\n\r\n";
        $msg .= "--{$boundary}\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$html}\r\n\r\n";
        $msg .= "--{$boundary}--\r\n.";

        $dataResp = $cmd($msg);
        $cmd('QUIT');
        fclose($fp);

        $ok = str_starts_with($dataResp, '250');
        return [
            'ok'       => $ok,
            'driver'   => 'smtp',
            'response' => trim($dataResp),
            'error'    => $ok ? null : ('SMTP rejected message: ' . trim($dataResp)),
        ];
    }
}
