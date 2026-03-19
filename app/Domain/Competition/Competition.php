<?php declare(strict_types=1);

namespace App\Domain\Competition;

use DomainException;

final class Competition
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $description,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $submissionDeadline,
        public readonly float $entryFeeAmount,
        public readonly int $prizeFirstPercent,
        public readonly int $prizeSecondPercent,
        public readonly int $prizeThirdPercent,
        public readonly CompetitionStatus $status,
        public readonly bool $isPublic,
        public readonly ?string $logoPath,
        public readonly int $createdByUserId,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {
    }

    /**
     * @throws DomainException if prize percentages do not total 100.
     */
    public function assertPrizeDistributionValid(): void
    {
        $total = $this->prizeFirstPercent + $this->prizeSecondPercent + $this->prizeThirdPercent;
        if ($total !== 100) {
            throw new DomainException(
                "Prize distribution must total 100% (currently {$total}%).",
            );
        }
    }

    /**
     * Check whether the competition is currently accepting submissions.
     */
    public function isOpen(): bool
    {
        return $this->status === CompetitionStatus::Open
            && !$this->isPastDeadline();
    }

    /**
     * Check whether the submission deadline has passed.
     */
    public function isPastDeadline(): bool
    {
        return time() > strtotime($this->submissionDeadline);
    }

    /**
     * A competition may only transition to Active when it has at least one
     * active section; that check is enforced by the application service.
     */
    public function isDraft(): bool
    {
        return $this->status === CompetitionStatus::Draft;
    }

    public function isActive(): bool
    {
        return $this->status === CompetitionStatus::Active;
    }

    public function isClosed(): bool
    {
        return $this->status === CompetitionStatus::Closed;
    }

    public function isArchived(): bool
    {
        return $this->status === CompetitionStatus::Archived;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            id: self::intValue($row, 'id'),
            name: self::stringValue($row, 'name'),
            slug: self::stringValue($row, 'slug'),
            description: self::stringValue($row, 'description', ''),
            startDate: self::stringValue($row, 'start_date'),
            endDate: self::stringValue($row, 'end_date'),
            submissionDeadline: self::stringValue($row, 'submission_deadline'),
            entryFeeAmount: self::floatValue($row, 'entry_fee_amount'),
            prizeFirstPercent: self::intValue($row, 'prize_first_percent', 60),
            prizeSecondPercent: self::intValue($row, 'prize_second_percent', 30),
            prizeThirdPercent: self::intValue($row, 'prize_third_percent', 10),
            status: CompetitionStatus::from(self::stringValue($row, 'status', CompetitionStatus::Draft->value)),
            isPublic: self::boolValue($row, 'is_public', true),
            logoPath: self::nullableStringValue($row, 'logo_path'),
            createdByUserId: self::intValue($row, 'created_by_user_id'),
            createdAt: self::stringValue($row, 'created_at'),
            updatedAt: self::stringValue($row, 'updated_at'),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function intValue(array $row, string $key, int $default = 0): int
    {
        $value = $row[$key] ?? $default;

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function floatValue(array $row, string $key, float $default = 0.0): float
    {
        $value = $row[$key] ?? $default;

        return is_numeric($value) ? (float) $value : $default;
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
    private static function boolValue(array $row, string $key, bool $default = false): bool
    {
        $value = $row[$key] ?? $default;

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
