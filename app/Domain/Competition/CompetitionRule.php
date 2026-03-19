<?php declare(strict_types=1);

namespace App\Domain\Competition;

final class CompetitionRule
{
    /** @var array<string, mixed>|null */
    public readonly ?array $ruleConfig;

    /**
     * @param array<string, mixed>|null $ruleConfig
     */
    public function __construct(
        public readonly int $id,
        public readonly int $competitionId,
        public readonly int $competitionSectionId,
        public readonly string $ruleKey,
        public readonly int $pointsValue,
        ?array $ruleConfig,
        public readonly bool $isActive,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {
        $this->ruleConfig = $ruleConfig;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        $config = null;
        if (isset($row['rule_config_json']) && $row['rule_config_json'] !== null) {
            $decoded = json_decode(self::stringValue($row, 'rule_config_json'), true);
            $config = is_array($decoded) ? $decoded : null;
        }

        return new self(
            id: self::intValue($row, 'id'),
            competitionId: self::intValue($row, 'competition_id'),
            competitionSectionId: self::intValue($row, 'competition_section_id'),
            ruleKey: self::stringValue($row, 'rule_key'),
            pointsValue: self::intValue($row, 'points_value'),
            ruleConfig: $config,
            isActive: self::boolValue($row, 'is_active'),
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
