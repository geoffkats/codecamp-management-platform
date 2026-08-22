<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'camp_id',
        'club_id',
        'enrolled_at',
        'completed_at',
        'progress_percentage',
        'lessons_completed',
        'quizzes_completed',
        'average_quiz_score',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
            'progress_percentage' => 'decimal:2',
            'average_quiz_score' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function camp(): BelongsTo
    {
        return $this->belongsTo(CodeCamp::class, 'camp_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(CodeClub::class, 'club_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $enrollment) {
            $user = User::find($enrollment->user_id);

            if (empty($enrollment->club_id) && $user?->studentProfile?->program_type === 'codeclub') {
                $activeClubId = CodeClubMembership::where('student_id', $enrollment->user_id)
                    ->where('status', 'active')
                    ->value('code_club_id');
                if ($activeClubId) {
                    $enrollment->club_id = $activeClubId;
                }
            }

            if (empty($enrollment->camp_id) && $user?->studentProfile?->program_type !== 'codeclub') {
                $activeCampId = CampEnrollment::where('student_id', $enrollment->user_id)
                    ->where('status', 'active')
                    ->value('camp_id');
                if ($activeCampId) {
                    $enrollment->camp_id = $activeCampId;
                }
            }
        });
    }
}

