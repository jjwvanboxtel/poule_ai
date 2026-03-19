<?php declare(strict_types=1);

namespace App\Domain\Competition;

final class CompetitionSection
{
    public function __construct(
        public readonly int $id,
        public readonly int $competitionId,
        public readonly SectionType $sectionType,
        public readonly string $label,
        public readonly bool $isActive,
        public readonly int $displayOrder,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            id: self::intValue($row, 'id'),
            competitionId: self::intValue($row, 'competition_id'),
            sectionType: SectionType::from(self::stringValue($row, 'section_type')),
            label: self::stringValue($row, 'label'),
            isActive: self::boolValue($row, 'is_active'),
            displayOrder: self::intValue($row, 'display_order'),
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
    private static function stringValue(array $row, string $key): string
    {
        $value = $row[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
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
