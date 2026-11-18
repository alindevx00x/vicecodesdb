<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Security;

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => strtolower(trim($email))]);
        return $stmt->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT id, email, name, role, bio, last_login, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function attemptLogin(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        Security::startSession();
        session_regenerate_id(true);
        $_SESSION['admin_user_id'] = (int) $user['id'];

        $stmt = Database::pdo()->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
        $stmt->execute([':id' => $user['id']]);

        return $user;
    }

    public static function current(): ?array
    {
        Security::startSession();
        $id = $_SESSION['admin_user_id'] ?? null;
        if (!$id) {
            return null;
        }
        return self::findById((int) $id);
    }

    public static function isLoggedIn(): bool
    {
        return self::current() !== null;
    }

    public static function logout(): void
    {
        Security::startSession();
        unset($_SESSION['admin_user_id']);
        session_regenerate_id(true);
    }

    public static function verifyPassword(int $id, string $password): bool
    {
        $stmt = Database::pdo()->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $hash = $stmt->fetchColumn();
        return is_string($hash) && $hash !== '' && password_verify($password, $hash);
    }

    public static function updatePassword(int $id, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = Database::pdo()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $stmt->execute([':hash' => $hash, ':id' => $id]);
    }

    public static function updateProfile(int $id, string $name, string $bio): void
    {
        $stmt = Database::pdo()->prepare('UPDATE users SET name = :name, bio = :bio WHERE id = :id');
        $stmt->execute([
            ':name' => trim($name),
            ':bio'  => trim($bio),
            ':id'   => $id,
        ]);
    }
}
