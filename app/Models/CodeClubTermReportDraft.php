<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeClubTermReportDraft extends Model
{
    protected $fillable = [
        'code_club_id',
        'student_id',
        'term_key',
        'term_label',
        'period_start',
        'period_end',
        'summary',
        'overall_label',
        'instructor_comment',
        'track_notes',
        'behavior',
        'achievements',
        'improvements',
        'goals',
        'metrics_overrides',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'track_notes' => 'array',
            'behavior' => 'array',
            'achievements' => 'array',
            'improvements' => 'array',
            'goals' => 'array',
            'metrics_overrides' => 'array',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(CodeClub::class, 'code_club_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public static function defaultBehavior(): array
    {
        return [
            'participation' => 4,
            'collaboration' => 4,
            'initiative' => 3,
            'responsibility' => 4,
        ];
    }

    public static function normalizeList(?array $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            $items
        ), fn ($item) => $item !== ''));
    }
}
