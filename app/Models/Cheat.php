<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Cheat
{
    public static function listFiltered(array $filters = [], int $limit = 60, int $offset = 0): array
    {
        $where = ['c.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['category_slug'])) {
            $where[] = 'cat.slug = :cat_slug';
            $params[':cat_slug'] = $filters['category_slug'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = 'c.category_id = :cat_id';
            $params[':cat_id'] = (int) $filters['category_id'];
        }
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[] = 'c.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['featured'])) {
            $where[] = 'c.is_featured = 1';
        }
        if (!empty($filters['platform']) && in_array($filters['platform'], ['ps5', 'xbox', 'pc'], true)) {
            $where[] = 'EXISTS (SELECT 1 FROM cheat_platforms cp2 WHERE cp2.cheat_id = c.id AND cp2.platform = :platform)';
            $params[':platform'] = $filters['platform'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(c.title LIKE :q1 OR c.short_description LIKE :q2 OR c.effects LIKE :q3)';
            $like = '%' . $filters['q'] . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        $orderBy = match ($filters['sort'] ?? 'order') {
            'popular' => 'c.views DESC, c.sort_order ASC',
            'newest'  => 'c.updated_at DESC, c.id DESC',
            'title'   => 'c.title ASC',
            default   => 'c.is_featured DESC, c.sort_order ASC, c.id ASC',
        };

        $sql = 'SELECT c.*, cat.name AS category_name, cat.slug AS category_slug, cat.icon AS category_icon
                FROM cheats c
                LEFT JOIN categories cat ON cat.id = c.category_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY ' . $orderBy . '
                LIMIT :lim OFFSET :off';

        $stmt = Database::pdo()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $cheats = $stmt->fetchAll();

        return self::attachPlatforms($cheats);
    }

    public static function attachPlatforms(array $cheats): array
    {
        if ($cheats === []) {
            return [];
        }
        $ids = array_map(static fn($r) => (int) $r['id'], $cheats);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::pdo()->prepare("SELECT * FROM cheat_platforms WHERE cheat_id IN ({$placeholders}) ORDER BY FIELD(platform, 'ps5', 'xbox', 'pc')");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int) $row['cheat_id']][$row['platform']] = $row;
        }

        foreach ($cheats as &$cheat) {
            $cheat['platforms'] = $grouped[(int) $cheat['id']] ?? [];
        }
        return $cheats;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT c.*, cat.name AS category_name, cat.slug AS category_slug
             FROM cheats c
             LEFT JOIN categories cat ON cat.id = c.category_id
             WHERE c.slug = :slug AND c.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([':slug' => $slug]);
        $cheat = $stmt->fetch();
        if (!$cheat) {
            return null;
        }
        $attached = self::attachPlatforms([$cheat]);
        return $attached[0];
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM cheats WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => $id]);
        $cheat = $stmt->fetch();
        if (!$cheat) {
            return null;
        }
        $attached = self::attachPlatforms([$cheat]);
        return $attached[0];
    }

    public static function related(int $cheatId, ?int $categoryId, int $limit = 4): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT c.*, cat.name AS category_name, cat.slug AS category_slug
             FROM cheats c
             LEFT JOIN categories cat ON cat.id = c.category_id
             WHERE c.deleted_at IS NULL AND c.id != :id
             ORDER BY (c.category_id = :cat_id) DESC, c.is_featured DESC, c.views DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':id', $cheatId, \PDO::PARAM_INT);
        $stmt->bindValue(':cat_id', (int) $categoryId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return self::attachPlatforms($stmt->fetchAll());
    }

    public static function incrementViews(int $id): void
    {
        $stmt = Database::pdo()->prepare('UPDATE cheats SET views = views + 1 WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function statusCounts(): array
    {
        $stmt = Database::pdo()->query(
            "SELECT status, COUNT(*) AS cnt FROM cheats WHERE deleted_at IS NULL GROUP BY status"
        );
        $counts = ['verified' => 0, 'unconfirmed' => 0, 'debunked' => 0, 'coming_soon' => 0, 'total' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['cnt'];
            $counts['total'] += (int) $row['cnt'];
        }
        return $counts;
    }

    public static function allSlugsForSitemap(): array
    {
        return Database::pdo()->query('SELECT slug, updated_at FROM cheats WHERE deleted_at IS NULL ORDER BY updated_at DESC')->fetchAll();
    }

    public static function save(array $data, ?int $id = null): int
    {
        $pdo = Database::pdo();
        $fields = [
            ':title'              => $data['title'],
            ':slug'               => $data['slug'],
            ':short_description'  => $data['short_description'],
            ':content'            => $data['content'] ?? null,
            ':effects'            => $data['effects'] ?? null,
            ':verification_notes' => $data['verification_notes'] ?? null,
            ':category_id'        => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            ':status'             => $data['status'] ?? 'coming_soon',
            ':is_featured'        => !empty($data['is_featured']) ? 1 : 0,
            ':sort_order'         => (int) ($data['sort_order'] ?? 0),
            ':last_verified_at'   => !empty($data['last_verified_at']) ? $data['last_verified_at'] : date('Y-m-d H:i:s'),
            ':game_patch'         => trim((string) ($data['game_patch'] ?? 'v1.00')) ?: 'v1.00',
            ':needs_retest'       => !empty($data['needs_retest']) ? 1 : 0,
            ':meta_title'         => $data['meta_title'] ?? null,
            ':meta_description'   => $data['meta_description'] ?? null,
            ':focus_keyword'      => $data['focus_keyword'] ?? null,
            ':og_image'           => $data['og_image'] ?? null,
            ':preview_image'      => !empty($data['preview_image']) ? $data['preview_image'] : null,
            ':preview_image_alt'  => !empty($data['preview_image_alt']) ? $data['preview_image_alt'] : null,
            ':faqs_json'          => $data['faqs_json'] ?? '[]',
            ':changelog_json'     => $data['changelog_json'] ?? '[]',
        ];

        if ($id === null) {
            $sql = 'INSERT INTO cheats (title, slug, short_description, content, effects, verification_notes, category_id, status,
                    is_featured, sort_order, last_verified_at, game_patch, needs_retest, meta_title, meta_description, focus_keyword, og_image, preview_image, preview_image_alt, faqs_json, changelog_json, published_at)
                    VALUES (:title, :slug, :short_description, :content, :effects, :verification_notes, :category_id, :status,
                    :is_featured, :sort_order, :last_verified_at, :game_patch, :needs_retest, :meta_title, :meta_description, :focus_keyword, :og_image, :preview_image, :preview_image_alt, :faqs_json, :changelog_json, NOW())';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($fields);
            $id = (int) $pdo->lastInsertId();
        } else {
            $fields[':id'] = $id;
            $sql = 'UPDATE cheats SET title = :title, slug = :slug, short_description = :short_description, content = :content,
                    effects = :effects, verification_notes = :verification_notes, category_id = :category_id, status = :status,
                    is_featured = :is_featured, sort_order = :sort_order, last_verified_at = :last_verified_at,
                    game_patch = :game_patch, needs_retest = :needs_retest,
                    meta_title = :meta_title, meta_description = :meta_description, focus_keyword = :focus_keyword,
                    og_image = :og_image, preview_image = :preview_image, preview_image_alt = :preview_image_alt, faqs_json = :faqs_json, changelog_json = :changelog_json WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($fields);
        }

        // Save platform sequences
        if (isset($data['platforms']) && is_array($data['platforms'])) {
            $del = $pdo->prepare('DELETE FROM cheat_platforms WHERE cheat_id = :cid');
            $del->execute([':cid' => $id]);

            $ins = $pdo->prepare(
                'INSERT INTO cheat_platforms (cheat_id, platform, code_sequence, cell_number, notes)
                 VALUES (:cid, :platform, :seq, :cell, :notes)'
            );
            foreach (['ps5', 'xbox', 'pc'] as $plat) {
                $pData = $data['platforms'][$plat] ?? [];
                $seq = trim((string) ($pData['code_sequence'] ?? ''));
                $cell = trim((string) ($pData['cell_number'] ?? ''));
                $notes = trim((string) ($pData['notes'] ?? ''));
                if ($seq !== '' || $cell !== '' || $notes !== '') {
                    $ins->execute([
                        ':cid'      => $id,
                        ':platform' => $plat,
                        ':seq'      => $seq,
                        ':cell'     => $cell !== '' ? $cell : null,
                        ':notes'    => $notes !== '' ? $notes : null,
                    ]);
                }
            }
        }

        return $id;
    }

    public static function recordFeedback(int $id, bool $isWorking): ?array
    {
        $col = $isWorking ? 'votes_working' : 'votes_broken';
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("UPDATE cheats SET {$col} = {$col} + 1 WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);

        $cheat = self::find($id);
        if (!$cheat) {
            return null;
        }

        $w = (int) $cheat['votes_working'];
        $b = (int) $cheat['votes_broken'];
        $total = $w + $b;

        // Auto-flag for admin re-test if broken reports spike (>=3 votes and >=30% broken)
        $shouldFlag = ($b >= 3 && $total > 0 && ($b / $total) >= 0.30);
        if ($shouldFlag && (int) $cheat['needs_retest'] === 0) {
            $pdo->prepare('UPDATE cheats SET needs_retest = 1 WHERE id = :id')->execute([':id' => $id]);
            $cheat['needs_retest'] = 1;
        }

        return $cheat;
    }

    public static function flaggedForRetest(): array
    {
        $rows = Database::pdo()->query(
            'SELECT id, title, slug, status, game_patch, votes_working, votes_broken, last_verified_at
             FROM cheats
             WHERE deleted_at IS NULL AND needs_retest = 1
             ORDER BY votes_broken DESC, updated_at DESC'
        )->fetchAll();
        return $rows;
    }

    public static function findBySlugs(array $slugs): array
    {
        $clean = array_values(array_filter(array_unique(array_map(static fn($s) => preg_replace('/[^a-z0-9\-]/', '', strtolower(trim((string) $s))), $slugs))));
        if ($clean === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($clean), '?'));
        $stmt = Database::pdo()->prepare(
            "SELECT c.*, cat.name AS category_name, cat.slug AS category_slug
             FROM cheats c
             LEFT JOIN categories cat ON cat.id = c.category_id
             WHERE c.deleted_at IS NULL AND c.slug IN ({$placeholders})
             ORDER BY FIELD(c.slug, {$placeholders})"
        );
        $stmt->execute(array_merge($clean, $clean));
        return self::attachPlatforms($stmt->fetchAll());
    }

    public static function bulkUpdateStatus(array $ids, string $status): void
    {
        if ($ids === [] || !in_array($status, ['verified', 'unconfirmed', 'debunked', 'coming_soon'], true)) {
            return;
        }
        $cleanIds = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
        $retestReset = $status === 'verified' ? ', needs_retest = 0' : '';
        $stmt = Database::pdo()->prepare("UPDATE cheats SET status = ?, last_verified_at = NOW(){$retestReset} WHERE id IN ({$placeholders})");
        $stmt->execute(array_merge([$status], $cleanIds));
    }

    public static function softDelete(int $id): void
    {
        $stmt = Database::pdo()->prepare('UPDATE cheats SET deleted_at = NOW() WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function quickVerify(int $id, string $verifierName = 'Editor'): ?array
    {
        $cheat = self::find($id);
        if (!$cheat) {
            return null;
        }
        $changelog = json_decode((string) ($cheat['changelog_json'] ?? '[]'), true);
        if (!is_array($changelog)) {
            $changelog = [];
        }
        $patch = trim((string) ($cheat['game_patch'] ?? 'v1.00')) ?: 'v1.00';
        array_unshift($changelog, [
            'date' => date('Y-m-d H:i'),
            'note' => 'Verified on ' . $patch . ' by ' . $verifierName,
        ]);
        $changelog = array_slice($changelog, 0, 20);

        $stmt = Database::pdo()->prepare(
            "UPDATE cheats
             SET status = 'verified', last_verified_at = NOW(), needs_retest = 0, changelog_json = :cl
             WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([
            ':cl' => json_encode($changelog, JSON_UNESCAPED_UNICODE),
            ':id' => $id,
        ]);

        return self::find($id);
    }

    public static function recentLiveUpdates(int $limit = 6): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT c.id, c.title, c.slug, c.status, c.game_patch, c.short_description,
                    c.verification_notes, c.changelog_json, c.last_verified_at, c.updated_at,
                    cat.name AS category_name, cat.slug AS category_slug
             FROM cheats c
             LEFT JOIN categories cat ON cat.id = c.category_id
             WHERE c.deleted_at IS NULL
             ORDER BY FIELD(c.status, 'verified', 'unconfirmed', 'debunked', 'coming_soon'),
                      COALESCE(c.last_verified_at, c.updated_at) DESC, c.id DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $cl = json_decode((string) ($row['changelog_json'] ?? '[]'), true);
            $latestNote = '';
            $latestDate = $row['last_verified_at'] ?: $row['updated_at'];
            if (is_array($cl) && !empty($cl[0]['note'])) {
                $latestNote = (string) $cl[0]['note'];
                if (!empty($cl[0]['date'])) {
                    $latestDate = (string) $cl[0]['date'];
                }
            } elseif (!empty($row['verification_notes'])) {
                $latestNote = (string) $row['verification_notes'];
            } else {
                $latestNote = (string) $row['short_description'];
            }
            $row['live_note'] = $latestNote;
            $row['live_time'] = $latestDate;
        }
        return $rows;
    }

    public static function searchFullText(string $query, int $limit = 15): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT c.*, cat.name AS category_name, cat.slug AS category_slug
             FROM cheats c
             LEFT JOIN categories cat ON cat.id = c.category_id
             WHERE c.deleted_at IS NULL
               AND (MATCH(c.title, c.short_description, c.content, c.effects) AGAINST (:q1 IN BOOLEAN MODE)
                    OR c.title LIKE :q2 OR c.short_description LIKE :q3)
             ORDER BY c.is_featured DESC, c.views DESC
             LIMIT :lim'
        );
        $like = '%' . $query . '%';
        $stmt->bindValue(':q1', $query . '*');
        $stmt->bindValue(':q2', $like);
        $stmt->bindValue(':q3', $like);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return self::attachPlatforms($stmt->fetchAll());
    }
}
