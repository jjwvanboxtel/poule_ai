<?php declare(strict_types=1);

namespace App\Support\View;

final class Escaper
{
    private function __construct()
    {
    }

    /**
     * Escape for HTML body context.
     */
    public static function html(mixed $value): string
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        } elseif (!is_scalar($value) && $value !== null) {
            $value = '';
        }

        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
    }

    /**
     * Escape for HTML attribute context (same as html but named for clarity).
     */
    public static function attr(mixed $value): string
    {
        return self::html($value);
    }

    /**
     * Encode a value as JSON for use in a JavaScript context.
     */
    public static function js(mixed $value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: 'null';
    }

    /**
     * Percent-encode a value for use in URL query parameters.
     */
    public static function url(mixed $value): string
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        } elseif (!is_scalar($value) && $value !== null) {
            $value = '';
        }

        return urlencode((string) $value);
    }
}
