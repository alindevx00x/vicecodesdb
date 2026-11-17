<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Url;

final class Tag
{
    public static function allWithCounts(): array
    {
        return Database::pdo()->query(
            'SELECT t.*,
                    (SELECT COUNT(*) FROM cheat_tags ct JOIN cheats c ON c.id = ct.cheat_id WHERE ct.tag_id = t.id AND c.deleted_at IS NULL) AS cheat_count,
                    (SELECT COUNT(*) FROM blog_post_tags bpt JOIN blog_posts bp ON bp.id = bpt.blog_post_id WHERE bpt.tag_id = t.id AND bp.deleted_at IS NULL AND bp.status = "published") AS post_count
             FROM tags t
             ORDER BY t.name ASC'
        )->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM tags WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public static function forCheat(int $cheatId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT t.* FROM tags t
             JOIN cheat_tags ct ON ct.tag_id = t.id
             WHERE ct.cheat_id = :cid
             ORDER BY t.name ASC'
        );
        $stmt->execute([':cid' => $cheatId]);
        return $stmt->fetchAll();
    }

    public static function forBlogPost(int $postId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT t.* FROM tags t
             JOIN blog_post_tags bpt ON bpt.tag_id = t.id
             WHERE bpt.blog_post_id = :pid
             ORDER BY t.name ASC'
        );
        $stmt->execute([':pid' => $postId]);
        return $stmt->fetchAll();
    }

    public static function cheatsByTag(int $tagId, int $limit = 60): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT c.*, cat.name AS category_name, cat.slug AS category_slug
             FROM cheats c
             JOIN cheat_tags ct ON ct.cheat_id = c.id
             LEFT JOIN categories cat ON cat.id = c.category_id
             WHERE ct.tag_id = :tid AND c.deleted_at IS NULL
             ORDER BY c.is_featured DESC, c.sort_order ASC, c.id ASC
             LIMIT :lim'
        );
        $stmt->bindValue(':tid', $tagId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return Cheat::attachPlatforms($stmt->fetchAll());
    }

    public static function postsByTag(int $tagId, int $limit = 24): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT bp.*, cat.name AS category_name, cat.slug AS category_slug, u.name AS author_name
             FROM blog_posts bp
             JOIN blog_post_tags bpt ON bpt.blog_post_id = bp.id
             LEFT JOIN categories cat ON cat.id = bp.category_id
             LEFT JOIN users u ON u.id = bp.author_id
             WHERE bpt.tag_id = :tid AND bp.deleted_at IS NULL AND bp.status = 'published'
             ORDER BY bp.published_at DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':tid', $tagId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find blog guides sharing tags with a cheat (fallback to latest published posts).
     */
    public static function relatedPostsForCheat(int $cheatId, int $limit = 3): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT bp.*, cat.name AS category_name, cat.slug AS category_slug, COUNT(bpt.tag_id) AS shared_tags
             FROM blog_posts bp
             LEFT JOIN categories cat ON cat.id = bp.category_id
             LEFT JOIN blog_post_tags bpt ON bpt.blog_post_id = bp.id
             LEFT JOIN cheat_tags ct ON ct.tag_id = bpt.tag_id AND ct.cheat_id = :cid
             WHERE bp.deleted_at IS NULL AND bp.status = 'published'
             GROUP BY bp.id
             ORDER BY (COUNT(ct.tag_id) > 0) DESC, COUNT(ct.tag_id) DESC, bp.published_at DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':cid', $cheatId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function syncForCheat(int $cheatId, string $csvTags): void
    {
        $tagIds = self::resolveCsvTagIds($csvTags);
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM cheat_tags WHERE cheat_id = :cid')->execute([':cid' => $cheatId]);
        if ($tagIds === []) {
            return;
        }
        $ins = $pdo->prepare('INSERT IGNORE INTO cheat_tags (cheat_id, tag_id) VALUES (:cid, :tid)');
        foreach ($tagIds as $tid) {
            $ins->execute([':cid' => $cheatId, ':tid' => $tid]);
        }
    }

    public static function syncForBlogPost(int $postId, string $csvTags): void
    {
        $tagIds = self::resolveCsvTagIds($csvTags);
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM blog_post_tags WHERE blog_post_id = :pid')->execute([':pid' => $postId]);
        if ($tagIds === []) {
            return;
        }
        $ins = $pdo->prepare('INSERT IGNORE INTO blog_post_tags (blog_post_id, tag_id) VALUES (:pid, :tid)');
        foreach ($tagIds as $tid) {
            $ins->execute([':pid' => $postId, ':tid' => $tid]);
        }
    }

    private static function resolveCsvTagIds(string $csvTags): array
    {
        $parts = array_filter(array_map('trim', explode(',', $csvTags)));
        if ($parts === []) {
            return [];
        }
        $pdo = Database::pdo();
        $findStmt = $pdo->prepare('SELECT id FROM tags WHERE slug = :slug LIMIT 1');
        $insStmt = $pdo->prepare('INSERT INTO tags (name, slug) VALUES (:name, :slug)');

        $ids = [];
        foreach ($parts as $rawName) {
            $name = mb_substr($rawName, 0, 80);
            $slug = Url::slugify($name);
            if ($slug === '') {
                continue;
            }
            $findStmt->execute([':slug' => $slug]);
            $existingId = $findStmt->fetchColumn();
            if ($existingId) {
                $ids[] = (int) $existingId;
            } else {
                $insStmt->execute([':name' => $name, ':slug' => $slug]);
                $ids[] = (int) $pdo->lastInsertId();
            }
        }
        return array_values(array_unique($ids));
    }
}
