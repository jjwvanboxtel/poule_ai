<?php declare(strict_types=1);

namespace App\Support\Validation;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    /** @var array<string, list<string>> */
    private readonly array $errors;

    /** @param array<string, list<string>> $errors */
    private function __construct(array $errors, string $message = 'Validation failed.')
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    /**
     * @param array<string, list<string>|string> $messages
     */
    public static function withMessages(array $messages): self
    {
        $normalized = [];
        foreach ($messages as $field => $msg) {
            $normalized[$field] = is_array($msg) ? $msg : [$msg];
        }

        return new self($normalized);
    }

    /** @return array<string, list<string>> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(?string $field = null): ?string
    {
        if ($field !== null) {
            return $this->errors[$field][0] ?? null;
        }

        foreach ($this->errors as $messages) {
            if ($messages !== []) {
                return $messages[0];
            }
        }

        return null;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }
}
