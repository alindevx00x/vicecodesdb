<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Newsletter
{
    public static function subscribe(string $email, ?string $name, string $ip): array
    {
        $email = strtolower(trim($email));
        $stmt = Database::pdo()->prepare('SELECT * FROM newsletter_subscribers WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $existing = $stmt->fetch();

        $token = bin2hex(random_bytes(24));

        if ($existing) {
            if ($existing['status'] === 'subscribed') {
                return ['state' => 'already_subscribed', 'token' => $existing['token']];
            }
            $upd = Database::pdo()->prepare(
                "UPDATE newsletter_subscribers SET name = COALESCE(:name, name), status = 'pending', token = :token, consent_ip = :ip WHERE id = :id"
            );
            $upd->execute([':name' => $name, ':token' => $token, ':ip' => $ip, ':id' => $existing['id']]);
            return ['state' => 'pending', 'token' => $token];
        }

        $ins = Database::pdo()->prepare(
            "INSERT INTO newsletter_subscribers (email, name, status, token, consent_ip)
             VALUES (:email, :name, 'pending', :token, :ip)"
        );
        $ins->execute([':email' => $email, ':name' => $name, ':token' => $token, ':ip' => $ip]);
        return ['state' => 'pending', 'token' => $token];
    }

    public static function confirmByToken(string $token): bool
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE newsletter_subscribers SET status = 'subscribed', confirmed_at = NOW() WHERE token = :token"
        );
        $stmt->execute([':token' => $token]);
        return $stmt->rowCount() > 0;
    }

    public static function unsubscribeByToken(string $token): bool
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE newsletter_subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE token = :token"
        );
        $stmt->execute([':token' => $token]);
        return $stmt->rowCount() > 0;
    }

    public static function all(): array
    {
        return Database::pdo()->query('SELECT * FROM newsletter_subscribers ORDER BY created_at DESC')->fetchAll();
    }

    public static function countSubscribed(): int
    {
        return (int) Database::pdo()->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'subscribed'")->fetchColumn();
    }

    public static function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, ['pending', 'subscribed', 'unsubscribed'], true)) {
            return;
        }
        $stmt = Database::pdo()->prepare('UPDATE newsletter_subscribers SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM newsletter_subscribers WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
