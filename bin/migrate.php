#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * ViceCodes Environment-Safe Database Migration & Seeding CLI
 * Usage:
 *   php bin/migrate.php          # Apply schema + safe column migrations
 *   php bin/migrate.php --seed   # Apply schema/migrations + idempotent seed data
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from CLI.\n");
    exit(1);
}

$root = dirname(__DIR__);
$appConfig = require $root . '/config/app.php';
$dbConfig  = require $root . '/config/database.php';

$withSeed = in_array('--seed', $argv, true);

// Autoloader
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = $root . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

echo "[ViceCodes Migrate] Environment: {$appConfig['env']} | DB: {$dbConfig['name']}\n";

try {
    $pdo = \App\Core\Database::connect($dbConfig);
} catch (\Throwable $e) {
    fwrite(STDERR, "[ERROR] Database connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

/**
 * Check if a column exists in the current database.
 */
$columnExists = static function (PDO $pdo, string $table, string $column) use ($dbConfig): bool {
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl AND COLUMN_NAME = :col'
    );
    $stmt->execute([':db' => $dbConfig['name'], ':tbl' => $table, ':col' => $column]);
    return (int) $stmt->fetchColumn() > 0;
};

/**
 * Check if an index exists on a table.
 */
$indexExists = static function (PDO $pdo, string $table, string $indexName) use ($dbConfig): bool {
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl AND INDEX_NAME = :idx'
    );
    $stmt->execute([':db' => $dbConfig['name'], ':tbl' => $table, ':idx' => $indexName]);
    return (int) $stmt->fetchColumn() > 0;
};

$splitSql = static function (string $raw): array {
    $raw = preg_replace('/^\s*--.*$/m', '', $raw) ?? $raw;
    return array_filter(array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', $raw) ?: []));
};

// 1. Ensure base tables exist (schema.sql statements without CREATE DATABASE / USE)
$schemaSql = file_get_contents($root . '/database/schema.sql') ?: '';
$schemaSql = preg_replace('/^\s*CREATE DATABASE.*?;/ims', '', $schemaSql) ?? $schemaSql;
$schemaSql = preg_replace('/^\s*USE\s+`?[^;]+`;/im', '', $schemaSql) ?? $schemaSql;

foreach ($splitSql($schemaSql) as $sql) {
    if ($sql === '') {
        continue;
    }
    $pdo->exec($sql);
}
echo "[OK] Base tables verified.\n";

// 2. Idempotent column/enum migrations for existing installations
$migrations = [
    ['cheats', 'game_patch', "ALTER TABLE `cheats` ADD COLUMN `game_patch` VARCHAR(32) NULL DEFAULT 'v1.00' AFTER `last_verified_at`"],
    ['cheats', 'votes_working', "ALTER TABLE `cheats` ADD COLUMN `votes_working` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `game_patch`"],
    ['cheats', 'votes_broken', "ALTER TABLE `cheats` ADD COLUMN `votes_broken` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `votes_working`"],
    ['cheats', 'needs_retest', "ALTER TABLE `cheats` ADD COLUMN `needs_retest` TINYINT(1) NOT NULL DEFAULT 0 AFTER `votes_broken`"],
    ['cheats', 'preview_image', "ALTER TABLE `cheats` ADD COLUMN `preview_image` VARCHAR(255) NULL AFTER `og_image`"],
    ['cheats', 'preview_image_alt', "ALTER TABLE `cheats` ADD COLUMN `preview_image_alt` VARCHAR(255) NULL AFTER `preview_image`"],
];

foreach ($migrations as [$table, $col, $alterSql]) {
    if (!$columnExists($pdo, $table, $col)) {
        $pdo->exec($alterSql);
        echo "[MIGRATED] Added {$table}.{$col}\n";
    }
}

$pdo->exec(
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

if (!$indexExists($pdo, 'cheats', 'idx_cheats_retest')) {
    $pdo->exec("ALTER TABLE `cheats` ADD KEY `idx_cheats_retest` (`needs_retest`)");
    echo "[MIGRATED] Added idx_cheats_retest\n";
}

// Ensure contact_messages.status includes 'archived'
$stCol = $pdo->prepare(
    'SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = "contact_messages" AND COLUMN_NAME = "status"'
);
$stCol->execute([':db' => $dbConfig['name']]);
$enumType = (string) $stCol->fetchColumn();
if ($enumType !== '' && !str_contains($enumType, 'archived')) {
    $pdo->exec("ALTER TABLE `contact_messages` MODIFY COLUMN `status` ENUM('new', 'read', 'replied', 'archived') NOT NULL DEFAULT 'new'");
    echo "[MIGRATED] Updated contact_messages.status enum with 'archived'\n";
}

// 3. Ensure writable runtime directories exist
$dirs = [
    $root . '/storage/cache',
    $root . '/storage/logs',
    $root . '/storage/logs/rate_limits',
    $root . '/public/og-cache',
    $root . '/public/uploads/editor',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "[DIR] Created {$dir}\n";
    }
}

// 4. Optional idempotent seeding (safe defaults, preserves existing edits on settings if already present unless ON DUPLICATE)
if ($withSeed) {
    $seedFile = $root . '/database/seed.sql';
    if (file_exists($seedFile)) {
        $seedSql = file_get_contents($seedFile) ?: '';
        $seedSql = preg_replace('/^\s*USE\s+`?[^;]+`;/im', '', $seedSql) ?? $seedSql;
        // Preserve customized settings when re-seeding via CLI
        $seedSql = preg_replace(
            '/INSERT INTO `settings`(.*?)ON DUPLICATE KEY UPDATE[^;]+/is',
            'INSERT IGNORE INTO `settings`$1',
            $seedSql
        ) ?? $seedSql;
        foreach ($splitSql($seedSql) as $sql) {
            if ($sql === '') {
                continue;
            }
            $pdo->exec($sql);
        }
        echo "[SEED] Applied idempotent seed data.\n";
    }
}

echo "[DONE] Migration complete.\n";
