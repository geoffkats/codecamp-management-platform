<?php

namespace App\Support;

class DiscussionSanitizer
{
    public static function title(string $value): string
    {
        return self::plainText($value, 255);
    }

    public static function body(string $value, int $maxLength = 10000): string
    {
        return self::plainText($value, $maxLength);
    }

    public static function reply(string $value): string
    {
        return self::plainText($value, 5000);
    }

    public static function codeSnippet(string $value): string
    {
        return self::plainText($value, 20000);
    }

    public static function scratchProjectId(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    private static function plainText(string $value, int $maxLength): string
    {
        $value = strip_tags($value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? $value;

        return mb_substr(trim($value), 0, $maxLength);
    }
}
