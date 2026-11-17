<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Media
{
    public static function all(): array
    {
        return Database::pdo()->query('SELECT * FROM media ORDER BY created_at DESC')->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO media (filename, original_name, filepath, url_path, mime_type, file_size, alt_text, uploaded_by)
             VALUES (:filename, :original_name, :filepath, :url_path, :mime_type, :file_size, :alt_text, :uploaded_by)'
        );
        $stmt->execute([
            ':filename'      => $data['filename'],
            ':original_name' => $data['original_name'],
            ':filepath'      => $data['filepath'],
            ':url_path'      => $data['url_path'],
            ':mime_type'     => $data['mime_type'],
            ':file_size'     => (int) $data['file_size'],
            ':alt_text'      => $data['alt_text'] ?? null,
            ':uploaded_by'   => $data['uploaded_by'] ?? null,
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM media WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function delete(int $id): void
    {
        $item = self::find($id);
        if ($item) {
            $abs = dirname(__DIR__, 2) . '/public' . $item['filepath'];
            if (is_file($abs)) {
                @unlink($abs);
            }
            $stmt = Database::pdo()->prepare('DELETE FROM media WHERE id = :id');
            $stmt->execute([':id' => $id]);
        }
    }
}
