<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    use HasFactory;

    public const PEDAGOGICAL_APPROACHES = [
        'instant_reward' => [
            'label' => 'Instant reward',
            'hint' => 'Points, shout-outs, stickers, or other immediate recognition',
        ],
        'project_wall' => [
            'label' => 'Project wall',
            'hint' => 'Student work displayed for peers to see',
        ],
        'choice_menu' => [
            'label' => 'Choice menu',
            'hint' => 'Students chose an activity, path, or challenge',
        ],
        'five_minute_breaks' => [
            'label' => '5-minute breaks',
            'hint' => 'Short movement or brain breaks during the session',
        ],
    ];

    protected $fillable = [
        'report_date',
        'course_id',
        'camp_id',
        'instructor_id',
        'status',
        'summary',
        'challenges',
        'issues',
        'pedagogical_approaches',
        'follow_up_required',
        'submitted_at',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'follow_up_required' => 'boolean',
            'pedagogical_approaches' => 'array',
            'submitted_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public static function emptyApproaches(): array
    {
        $rows = [];

        foreach (array_keys(self::PEDAGOGICAL_APPROACHES) as $key) {
            $rows[$key] = [
                'used' => false,
                'description' => '',
            ];
        }

        return $rows;
    }

    public function appliedApproaches(): array
    {
        $saved = is_array($this->pedagogical_approaches) ? $this->pedagogical_approaches : [];
        $applied = [];

        foreach (self::PEDAGOGICAL_APPROACHES as $key => $meta) {
            $row = $saved[$key] ?? null;

            if (! is_array($row) || empty($row['used'])) {
                continue;
            }

            $applied[] = [
                'key' => $key,
                'label' => $meta['label'],
                'description' => trim((string) ($row['description'] ?? '')),
            ];
        }

        return $applied;
    }

    public function approachReportRows(): array
    {
        $saved = is_array($this->pedagogical_approaches) ? $this->pedagogical_approaches : [];
        $rows = [];

        foreach (self::PEDAGOGICAL_APPROACHES as $key => $meta) {
            $row = $saved[$key] ?? [];
            $used = is_array($row) && ! empty($row['used']);

            $rows[] = [
                'key' => $key,
                'label' => $meta['label'],
                'hint' => $meta['hint'],
                'used' => $used,
                'description' => $used ? trim((string) ($row['description'] ?? '')) : '',
            ];
        }

        return $rows;
    }

    public function usedApproachCount(): int
    {
        return count($this->appliedApproaches());
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function camp(): BelongsTo
    {
        return $this->belongsTo(CodeCamp::class, 'camp_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(DailyReportAttendance::class);
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(DailyReportMention::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DailyReportAttachment::class);
    }

    public function reportIssues(): HasMany
    {
        return $this->hasMany(DailyReportIssue::class);
    }
}
