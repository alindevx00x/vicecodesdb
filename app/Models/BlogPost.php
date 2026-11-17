<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class BlogPost
{
    public static function published(array $filters = [], int $limit = 12, int $offset = 0): array
    {
        $where = ["bp.deleted_at IS NULL", "bp.status = 'published'", "(bp.published_at IS NULL OR bp.published_at <= NOW())"];
        $params = [];

        if (!empty($filters['category_slug'])) {
            $where[] = 'cat.slug = :cat_slug';
            $params[':cat_slug'] = $filters['category_slug'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(bp.title LIKE :q1 OR bp.excerpt LIKE :q2 OR bp.content LIKE :q3)';
            $like = '%' . $filters['q'] . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        $sql = 'SELECT bp.*, cat.name AS category_name, cat.slug AS category_slug, u.name AS author_name
                FROM blog_posts bp
                LEFT JOIN categories cat ON cat.id = bp.category_id
                LEFT JOIN users u ON u.id = bp.author_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY bp.published_at DESC, bp.id DESC
                LIMIT :lim OFFSET :off';

        $stmt = Database::pdo()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function allAdmin(): array
    {
        return Database::pdo()->query(
            'SELECT bp.*, cat.name AS category_name, u.name AS author_name
             FROM blog_posts bp
             LEFT JOIN categories cat ON cat.id = bp.category_id
             LEFT JOIN users u ON u.id = bp.author_id
             WHERE bp.deleted_at IS NULL
             ORDER BY bp.created_at DESC'
        )->fetchAll();
    }

    public static function findBySlug(string $slug, bool $allowDraft = false): ?array
    {
        $statusFilter = $allowDraft ? '' : "AND bp.status = 'published'";
        $stmt = Database::pdo()->prepare(
            "SELECT bp.*, cat.name AS category_name, cat.slug AS category_slug, u.name AS author_name, u.bio AS author_bio
             FROM blog_posts bp
             LEFT JOIN categories cat ON cat.id = bp.category_id
             LEFT JOIN users u ON u.id = bp.author_id
             WHERE bp.slug = :slug AND bp.deleted_at IS NULL {$statusFilter}
             LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM blog_posts WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function related(int $postId, ?int $categoryId, int $limit = 3): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT bp.*, cat.name AS category_name, cat.slug AS category_slug
             FROM blog_posts bp
             LEFT JOIN categories cat ON cat.id = bp.category_id
             WHERE bp.deleted_at IS NULL AND bp.status = 'published' AND bp.id != :id
             ORDER BY (bp.category_id = :cat_id) DESC, bp.published_at DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':id', $postId, \PDO::PARAM_INT);
        $stmt->bindValue(':cat_id', (int) $categoryId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function incrementViews(int $id): void
    {
        $stmt = Database::pdo()->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function countAll(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM blog_posts WHERE deleted_at IS NULL')->fetchColumn();
    }

    public static function allSlugsForSitemap(): array
    {
        return Database::pdo()->query(
            "SELECT slug, updated_at, published_at FROM blog_posts WHERE deleted_at IS NULL AND status = 'published' ORDER BY published_at DESC"
        )->fetchAll();
    }

    public static function save(array $data, ?int $id = null): int
    {
        $plain = strip_tags((string) ($data['content'] ?? ''));
        $words = str_word_count($plain);
        $readingTime = max(1, (int) ceil($words / 200));

        $fields = [
            ':title'              => $data['title'],
            ':slug'               => $data['slug'],
            ':excerpt'            => $data['excerpt'] ?? null,
            ':quick_answer'       => $data['quick_answer'] ?? null,
            ':content'            => $data['content'],
            ':category_id'        => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            ':featured_image'     => $data['featured_image'] ?? null,
            ':featured_image_alt' => $data['featured_image_alt'] ?? null,
            ':status'             => $data['status'] ?? 'draft',
            ':author_id'          => (int) ($data['author_id'] ?? 1),
            ':meta_title'         => $data['meta_title'] ?? null,
            ':meta_description'   => $data['meta_description'] ?? null,
            ':focus_keyword'      => $data['focus_keyword'] ?? null,
            ':faqs_json'          => $data['faqs_json'] ?? '[]',
            ':reading_time'       => $readingTime,
            ':published_at'       => !empty($data['published_at']) ? $data['published_at'] : date('Y-m-d H:i:s'),
        ];

        if ($id === null) {
            $sql = 'INSERT INTO blog_posts (title, slug, excerpt, quick_answer, content, category_id, featured_image, featured_image_alt,
                    status, author_id, meta_title, meta_description, focus_keyword, faqs_json, reading_time, published_at)
                    VALUES (:title, :slug, :excerpt, :quick_answer, :content, :category_id, :featured_image, :featured_image_alt,
                    :status, :author_id, :meta_title, :meta_description, :focus_keyword, :faqs_json, :reading_time, :published_at)';
            $stmt = Database::pdo()->prepare($sql);
            $stmt->execute($fields);
            return (int) Database::pdo()->lastInsertId();
        }

        $fields[':id'] = $id;
        $sql = 'UPDATE blog_posts SET title = :title, slug = :slug, excerpt = :excerpt, quick_answer = :quick_answer,
                content = :content, category_id = :category_id, featured_image = :featured_image, featured_image_alt = :featured_image_alt,
                status = :status, author_id = :author_id, meta_title = :meta_title, meta_description = :meta_description,
                focus_keyword = :focus_keyword, faqs_json = :faqs_json, reading_time = :reading_time, published_at = :published_at
                WHERE id = :id';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($fields);
        return $id;
    }

    public static function softDelete(int $id): void
    {
        $stmt = Database::pdo()->prepare('UPDATE blog_posts SET deleted_at = NOW() WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function searchFullText(string $query, int $limit = 12): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT bp.*, cat.name AS category_name, cat.slug AS category_slug
             FROM blog_posts bp
             LEFT JOIN categories cat ON cat.id = bp.category_id
             WHERE bp.deleted_at IS NULL AND bp.status = 'published'
               AND (MATCH(bp.title, bp.excerpt, bp.quick_answer, bp.content) AGAINST (:q1 IN BOOLEAN MODE)
                    OR bp.title LIKE :q2 OR bp.excerpt LIKE :q3)
             ORDER BY bp.published_at DESC
             LIMIT :lim"
        );
        $like = '%' . $query . '%';
        $stmt->bindValue(':q1', $query . '*');
        $stmt->bindValue(':q2', $like);
        $stmt->bindValue(':q3', $like);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
