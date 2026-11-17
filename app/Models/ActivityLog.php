<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Security;

final class ActivityLog
{
    public static function record(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void
    {
        try {
            $stmt = Database::pdo()->prepare(
                'INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address)
                 VALUES (:uid, :action, :etype, :eid, :details, :ip)'
            );
            $stmt->execute([
                ':uid'     => $userId,
                ':action'  => $action,
                ':etype'   => $entityType,
                ':eid'     => $entityId,
                ':details' => $details,
                ':ip'      => Security::clientIp(),
            ]);
        } catch (\Throwable) {
            // Do not interrupt workflow if logging fails
        }
    }

    public static function recent(int $limit = 15): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT al.*, u.name AS user_name
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
