<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Page
{
    public static function all(): array
    {
        return Database::pdo()->query('SELECT * FROM pages ORDER BY id ASC')->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM pages WHERE slug = :slug AND status = 'published' LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM pages WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function save(array $data, ?int $id = null): int
    {
        $fields = [
            ':title'            => $data['title'],
            ':slug'             => $data['slug'],
            ':quick_answer'     => $data['quick_answer'] ?? null,
            ':content'          => $data['content'],
            ':meta_title'       => $data['meta_title'] ?? null,
            ':meta_description' => $data['meta_description'] ?? null,
            ':focus_keyword'    => $data['focus_keyword'] ?? null,
            ':status'           => $data['status'] ?? 'published',
        ];

        if ($id === null) {
            $stmt = Database::pdo()->prepare(
                'INSERT INTO pages (title, slug, quick_answer, content, meta_title, meta_description, focus_keyword, status)
                 VALUES (:title, :slug, :quick_answer, :content, :meta_title, :meta_description, :focus_keyword, :status)'
            );
            $stmt->execute($fields);
            return (int) Database::pdo()->lastInsertId();
        }

        $fields[':id'] = $id;
        $stmt = Database::pdo()->prepare(
            'UPDATE pages SET title = :title, slug = :slug, quick_answer = :quick_answer, content = :content,
             meta_title = :meta_title, meta_description = :meta_description, focus_keyword = :focus_keyword, status = :status
             WHERE id = :id'
        );
        $stmt->execute($fields);
        return $id;
    }

    public static function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM pages WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
