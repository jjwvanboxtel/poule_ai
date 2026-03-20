<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Application\Auth\UserRepositoryInterface;
use App\Application\Competitions\UserReadRepositoryInterface;
use App\Domain\User\User;
use App\Domain\User\UserRole;

final class PdoUserRepository extends AbstractPdoRepository implements UserRepositoryInterface, UserReadRepositoryInterface
{
    public function findById(int $id): ?User
    {
        $row = $this->fetchOne(
            'SELECT * FROM users WHERE id = ? LIMIT 1',
            [$id],
        );

        return $row !== null ? User::fromArray($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->fetchOne(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [$email],
        );

        return $row !== null ? User::fromArray($row) : null;
    }

    /**
     * @return list<User>
     */
    public function findAll(): array
    {
        return array_map(
            User::fromArray(...),
            $this->fetchAll('SELECT * FROM users ORDER BY created_at DESC'),
        );
    }

    /**
     * @return list<User>
     */
    public function findAdmins(): array
    {
        return array_map(
            User::fromArray(...),
            $this->fetchAll(
                "SELECT * FROM users WHERE role = 'admin' ORDER BY created_at ASC",
            ),
        );
    }

    public function countActiveAdmins(): int
    {
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM users WHERE role = 'admin' AND is_active = 1",
        );

        $count = $row['cnt'] ?? 0;

        return is_numeric($count) ? (int) $count : 0;
    }

    public function save(User $user): void
    {
        $this->execute(
            'UPDATE users SET
                first_name    = ?,
                last_name     = ?,
                email         = ?,
                phone_number  = ?,
                role          = ?,
                is_active     = ?,
                last_login_at = ?
             WHERE id = ?',
            [
                $user->firstName,
                $user->lastName,
                $user->email,
                $user->phoneNumber,
                $user->role->value,
                $user->isActive ? 1 : 0,
                $user->lastLoginAt,
                $user->id,
            ],
        );
    }

    /**
     * Insert a new user and return the new ID.
     */
    public function insert(
        string $firstName,
        string $lastName,
        string $email,
        string $phoneNumber,
        string $passwordHash,
        UserRole $role = UserRole::Participant,
    ): int {
        $this->execute(
            'INSERT INTO users (first_name, last_name, email, phone_number, password_hash, role)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$firstName, $lastName, $email, $phoneNumber, $passwordHash, $role->value],
        );

        return $this->lastInsertId();
    }
}
