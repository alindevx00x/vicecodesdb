<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Security;

final class TrustBadge
{
    public static function ensureTable(): void
    {
        Database::pdo()->exec(
            "CREATE TABLE IF NOT EXISTS `trust_badges` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `label` VARCHAR(120) NOT NULL DEFAULT '',
              `badge_html` TEXT NOT NULL,
              `sort_order` INT NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_trust_badges_active_sort` (`is_active`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public static function all(): array
    {
        try {
            return Database::pdo()
                ->query('SELECT * FROM trust_badges ORDER BY sort_order ASC, id ASC')
                ->fetchAll();
        } catch (\PDOException $e) {
            self::ensureTable();
            return Database::pdo()
                ->query('SELECT * FROM trust_badges ORDER BY sort_order ASC, id ASC')
                ->fetchAll();
        }
    }

    public static function active(): array
    {
        try {
            return Database::pdo()
                ->query('SELECT * FROM trust_badges WHERE is_active = 1 ORDER BY sort_order ASC, id ASC')
                ->fetchAll();
        } catch (\PDOException) {
            return [];
        }
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM trust_badges WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $label, string $badgeHtml, int $sortOrder = 0, bool $isActive = true): int
    {
        $cleanHtml = self::sanitizeBadgeHtml($badgeHtml);
        $resolvedLabel = trim($label) !== '' ? trim($label) : self::inferLabel($cleanHtml);
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO trust_badges (label, badge_html, sort_order, is_active) VALUES (:label, :html, :sort, :active)'
        );
        $stmt->execute([
            ':label'  => mb_substr($resolvedLabel, 0, 120),
            ':html'   => $cleanHtml,
            ':sort'   => $sortOrder,
            ':active' => $isActive ? 1 : 0,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, string $label, string $badgeHtml, int $sortOrder = 0, bool $isActive = true): void
    {
        $cleanHtml = self::sanitizeBadgeHtml($badgeHtml);
        $resolvedLabel = trim($label) !== '' ? trim($label) : self::inferLabel($cleanHtml);
        $stmt = Database::pdo()->prepare(
            'UPDATE trust_badges SET label = :label, badge_html = :html, sort_order = :sort, is_active = :active WHERE id = :id'
        );
        $stmt->execute([
            ':label'  => mb_substr($resolvedLabel, 0, 120),
            ':html'   => $cleanHtml,
            ':sort'   => $sortOrder,
            ':active' => $isActive ? 1 : 0,
            ':id'     => $id,
        ]);
    }

    public static function toggleActive(int $id): void
    {
        $stmt = Database::pdo()->prepare('UPDATE trust_badges SET is_active = IF(is_active = 1, 0, 1) WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM trust_badges WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function sanitizeBadgeHtml(string $html): string
    {
        return Security::sanitizeRichHtml($html);
    }

    public static function inferLabel(string $html): string
    {
        if (preg_match('/alt\s*=\s*["\']([^"\']+)["\']/i', $html, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/href\s*=\s*["\']https?:\/\/([^\/"\']+)/i', $html, $m)) {
            return trim($m[1]);
        }
        return 'Trust Badge';
    }
}
