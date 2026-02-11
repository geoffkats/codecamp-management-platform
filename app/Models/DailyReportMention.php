<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DailyReportMention extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_report_id',
        'mentionable_type',
        'mentionable_id',
        'role',
        'note',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }

    public function mentionable(): MorphTo
    {
        return $this->morphTo();
    }
}
