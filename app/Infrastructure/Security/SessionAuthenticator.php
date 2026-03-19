<?php declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\User\User;
use App\Domain\User\UserRole;
use App\Support\Sessions\SessionManager;
use PDO;

final class SessionAuthenticator
{
    public function __construct(
        private readonly SessionManager $session,
        private readonly PDO $pdo,
    ) {
    }

    /**
     * Attempt authentication; returns true on success.
     */
    public function attempt(string $email, string $password): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, password_hash, is_active, role FROM users WHERE email = ? LIMIT 1',
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row) || !self::boolValue($row['is_active'] ?? false)) {
            return false;
        }

        if (!password_verify($password, self::stringValue($row['password_hash'] ?? null))) {
            return false;
        }

        // Update last login
        $update = $this->pdo->prepare(
            'UPDATE users SET last_login_at = NOW() WHERE id = ?',
        );
        $update->execute([self::intValue($row['id'] ?? null)]);

        // Store minimal session data
        $this->session->regenerate();
        $this->session->set('user_id', self::intValue($row['id'] ?? null));
        $this->session->set('user_role', self::stringValue($row['role'] ?? null));

        return true;
    }

    public function check(): bool
    {
        return $this->session->has('user_id');
    }

    public function user(): ?User
    {
        $userId = $this->session->get('user_id');
        if ($userId === null) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE id = ? AND is_active = 1 LIMIT 1',
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return null;
        }

        return User::fromArray($row);
    }

    public function isAdmin(): bool
    {
        $user = $this->user();

        return $user !== null && $user->role === UserRole::Admin;
    }

    public function logout(): void
    {
        $this->session->destroy();
    }

    private static function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private static function intValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private static function boolValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
