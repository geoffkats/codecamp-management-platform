<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampReport extends Model
{
    public const SOURCES = [
        'ai' => 'Written by AI from instructor reports',
        'compiled' => 'Compiled automatically from camp data',
        'manual' => 'Edited by staff',
    ];

    protected $fillable = [
        'camp_id',
        'summary',
        'highlights',
        'challenges',
        'recommendations',
        'source',
        'generated_by',
        'generated_at',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    public function camp(): BelongsTo
    {
        return $this->belongsTo(CodeCamp::class, 'camp_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Highlights, challenges and recommendations are stored one point per line.
     *
     * @return array<int, string>
     */
    public static function points(?string $text): array
    {
        return array_values(array_filter(array_map(
            fn ($line) => trim(preg_replace('/^\s*([-*•]|\d+[.)])\s*/u', '', $line)),
            explode("\n", (string) $text)
        ), fn ($line) => $line !== ''));
    }
}
