<?php

namespace App\Support;

use Carbon\Carbon;

class TimeOfDay
{
    public static function toHi(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('H:i');
        }

        if (preg_match('/(\d{1,2}:\d{2})/', (string) $value, $matches)) {
            return strlen($matches[1]) === 4
                ? '0' . $matches[1]
                : substr($matches[1], 0, 5);
        }

        return null;
    }

    public static function toDisplay(mixed $value): ?string
    {
        $hi = self::toHi($value);

        if (! $hi) {
            return null;
        }

        return Carbon::createFromFormat('H:i', $hi)->format('g:i A');
    }

    public static function toStorage(mixed $value): ?string
    {
        $hi = self::toHi($value);

        return $hi ? $hi . ':00' : null;
    }
}
