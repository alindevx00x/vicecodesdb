<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Category
{
    public static function byType(string $type): array
    {
        if ($type === 'cheat') {
            $sql = "SELECT c.*, COUNT(ch.id) AS item_count
                    FROM categories c
                    LEFT JOIN cheats ch ON ch.category_id = c.id AND ch.deleted_at IS NULL
                    WHERE c.type = 'cheat'
                    GROUP BY c.id
                    ORDER BY c.sort_order ASC, c.name ASC";
        } else {
            $sql = "SELECT c.*, COUNT(bp.id) AS item_count
                    FROM categories c
                    LEFT JOIN blog_posts bp ON bp.category_id = c.id AND bp.deleted_at IS NULL AND bp.status = 'published'
                    WHERE c.type = 'blog'
                    GROUP BY c.id
                    ORDER BY c.sort_order ASC, c.name ASC";
        }
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function all(): array
    {
        return Database::pdo()->query('SELECT * FROM categories ORDER BY type ASC, sort_order ASC, name ASC')->fetchAll();
    }

    public static function findBySlug(string $slug, string $type = 'cheat'): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM categories WHERE slug = :slug AND type = :type LIMIT 1');
        $stmt->execute([':slug' => $slug, ':type' => $type]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO categories (name, slug, type, icon, description, meta_title, meta_description, sort_order)
             VALUES (:name, :slug, :type, :icon, :description, :meta_title, :meta_description, :sort_order)'
        );
        $stmt->execute([
            ':name'             => $data['name'],
            ':slug'             => $data['slug'],
            ':type'             => $data['type'],
            ':icon'             => $data['icon'] ?? 'bolt',
            ':description'      => $data['description'] ?? null,
            ':meta_title'       => $data['meta_title'] ?? null,
            ':meta_description' => $data['meta_description'] ?? null,
            ':sort_order'       => (int) ($data['sort_order'] ?? 0),
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE categories SET name = :name, slug = :slug, type = :type, icon = :icon,
             description = :description, meta_title = :meta_title, meta_description = :meta_description,
             sort_order = :sort_order WHERE id = :id'
        );
        $stmt->execute([
            ':id'               => $id,
            ':name'             => $data['name'],
            ':slug'             => $data['slug'],
            ':type'             => $data['type'],
            ':icon'             => $data['icon'] ?? 'bolt',
            ':description'      => $data['description'] ?? null,
            ':meta_title'       => $data['meta_title'] ?? null,
            ':meta_description' => $data['meta_description'] ?? null,
            ':sort_order'       => (int) ($data['sort_order'] ?? 0),
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
