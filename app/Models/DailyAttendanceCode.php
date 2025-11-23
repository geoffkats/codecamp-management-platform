<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DailyAttendanceCode extends Model
{
    protected $fillable = ['code', 'date', 'is_active'];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    public static function generateCode(): string
    {
        return strtoupper(Str::random(6));
    }

    public static function getTodayCode()
    {
        return self::where('date', today())
            ->where('is_active', true)
            ->first();
    }

    public static function createTodayCode(): self
    {
        return self::updateOrCreate(
            ['date' => today()],
            [
                'code' => self::generateCode(),
                'is_active' => true,
            ]
        );
    }
}
