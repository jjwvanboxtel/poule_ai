<?php declare(strict_types=1);

namespace App\Domain\User;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $phoneNumber,
        public readonly UserRole $role,
        public readonly bool $isActive,
        public readonly ?string $lastLoginAt,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isParticipant(): bool
    {
        return $this->role === UserRole::Participant;
    }

    public function fullName(): string
    {
        return trim("{$this->firstName} {$this->lastName}");
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            id: self::intValue($row, 'id'),
            firstName: self::stringValue($row, 'first_name'),
            lastName: self::stringValue($row, 'last_name'),
            email: self::stringValue($row, 'email'),
            phoneNumber: self::stringValue($row, 'phone_number', ''),
            role: UserRole::from(self::stringValue($row, 'role')),
            isActive: self::boolValue($row, 'is_active'),
            lastLoginAt: self::nullableStringValue($row, 'last_login_at'),
            createdAt: self::stringValue($row, 'created_at'),
            updatedAt: self::stringValue($row, 'updated_at'),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function intValue(array $row, string $key): int
    {
        $value = $row[$key] ?? null;

        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function stringValue(array $row, string $key, string $default = ''): string
    {
        $value = $row[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function nullableStringValue(array $row, string $key): ?string
    {
        $value = $row[$key] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function boolValue(array $row, string $key): bool
    {
        $value = $row[$key] ?? false;

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
