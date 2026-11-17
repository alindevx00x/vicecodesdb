<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connect(array $config): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $socket = $config['socket'] ?? '';
        if ($socket !== '' && file_exists($socket)) {
            $dsn = sprintf(
                'mysql:unix_socket=%s;dbname=%s;charset=%s',
                $socket,
                $config['name'],
                $config['charset'] ?? 'utf8mb4'
            );
        } else {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                (int) $config['port'],
                $config['name'],
                $config['charset'] ?? 'utf8mb4'
            );
        }

        try {
            self::$pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        } catch (PDOException $e) {
            // Fallback to TCP if socket connection failed
            if ($socket !== '') {
                $tcpDsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    $config['host'],
                    (int) $config['port'],
                    $config['name'],
                    $config['charset'] ?? 'utf8mb4'
                );
                self::$pdo = new PDO($tcpDsn, $config['user'], $config['pass'], $options);
            } else {
                throw $e;
            }
        }

        return self::$pdo;
    }

    public static function pdo(): PDO
    {
        if (!self::$pdo instanceof PDO) {
            $config = require dirname(__DIR__, 2) . '/config/database.php';
            return self::connect($config);
        }
        return self::$pdo;
    }
}
