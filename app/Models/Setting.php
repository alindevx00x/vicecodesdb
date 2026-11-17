<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Setting
{
    private static ?array $cache = null;

    public static function allKeyed(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $stmt = Database::pdo()->query('SELECT setting_key, setting_value FROM settings');
        $rows = $stmt->fetchAll();
        $out = [];
        foreach ($rows as $row) {
            $out[$row['setting_key']] = $row['setting_value'];
        }
        self::$cache = $out;
        return $out;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = self::allKeyed();
        return $all[$key] ?? $default;
    }

    public static function setMany(array $pairs): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        foreach ($pairs as $key => $val) {
            $stmt->execute([':k' => $key, ':v' => (string) $val]);
        }
        self::$cache = null;
    }
}
