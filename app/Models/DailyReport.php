<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_date',
        'course_id',
        'instructor_id',
        'status',
        'summary',
        'challenges',
        'issues',
        'follow_up_required',
        'submitted_at',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'follow_up_required' => 'boolean',
            'submitted_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
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
