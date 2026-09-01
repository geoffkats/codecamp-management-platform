<?php

namespace App\Console\Commands;

use App\Models\AssessmentAttempt;
use App\Models\AssignmentSubmission;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\StudentLessonProgress;
use App\Models\User;
use App\Models\UserPoint;
use App\Models\UserProgress;
use App\Services\PointsService;
use App\Support\LevelSystem;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RestoreXpFromActivity extends Command
{
    protected $signature = 'points:restore-from-activity
                            {--dry-run : Show what would be restored without writing}
                            {--force : Write the restored XP (required on production)}
                            {--user= : Restore only this user id}';

    protected $description = 'Rebuild XP from enrollments, completed lessons, and passed quizzes/assignments after an XP reset';

    private bool $write = false;

    private int $enrollments = 0;

    private int $lessons = 0;

    private int $assessments = 0;

    private int $legacyAssignments = 0;

    private int $courseCompletions = 0;

    public function handle(PointsService $pointsService): int
    {
        $this->write = (bool) $this->option('force') && ! $this->option('dry-run');

        if (! $this->write) {
            $this->warn('Dry run only. Pass --force to actually restore XP.');
        }

        $this->warn('Reset All wiped user_points totals AND the user_progress ledger.');
        $this->line('This rebuilds XP from enrollments, lesson completions, and graded/passed quizzes and assignments.');
        $this->line('Manual XP Manager awards cannot be recovered.');
        $this->newLine();

        if ($this->write) {
            DB::transaction(fn () => $this->restore());
        } else {
            $this->restore();
        }

        if ($this->write) {
            $synced = $pointsService->syncAllLevels();
            $this->info("Levels synced for {$synced} students.");
        }

        $this->newLine();
        $this->info('Rows that would be / were added:');
        $this->table(
            ['Source', 'Count'],
            [
                ['Course enrollments (50 XP)', $this->enrollments],
                ['Completed lessons', $this->lessons],
                ['Passed quizzes / graded assignments', $this->assessments],
                ['Legacy assignment submissions', $this->legacyAssignments],
                ['Finished courses (100 XP)', $this->courseCompletions],
            ]
        );

        if ($this->write) {
            $this->info('XP restore finished. Check the leaderboard and XP Manager.');
        } else {
            $this->comment('On production run:');
            $this->line('php artisan points:restore-from-activity --force');
        }

        return self::SUCCESS;
    }

    private function restore(): void
    {
        $this->restoreEnrollments();
        $this->restoreLessons();
        $this->restoreAssessments();
        $this->restoreLegacyAssignments();
        $this->restoreCourseCompletions();
        $this->rebuildPointTotals();
    }

    private function restoreEnrollments(): void
    {
        $query = CourseEnrollment::query()
            ->whereNotNull('course_id')
            ->whereHas('user', fn ($q) => $q->where(function ($student) {
                $student->whereHas('studentProfile')
                    ->orWhereHas('roles', fn ($r) => $r->where('name', 'student'));
            }));

        $this->applyUserFilter($query, 'user_id');

        $query->with('user')->orderBy('id')->chunkById(200, function ($chunk) {
            foreach ($chunk as $enrollment) {
                $at = $enrollment->created_at ?? now();
                if ($this->addProgressIfMissing(
                    (int) $enrollment->user_id,
                    (int) $enrollment->course_id,
                    'course_enrolled',
                    50,
                    null,
                    $at
                )) {
                    $this->enrollments++;
                }
            }
        });
    }

    private function restoreLessons(): void
    {
        $seen = [];

        StudentLessonProgress::query()
            ->where(function ($q) {
                $q->where('status', 'completed')
                    ->orWhere('progress_percentage', '>=', 100);
            })
            ->when($this->option('user'), fn ($q) => $q->where('user_id', (int) $this->option('user')))
            ->with('lesson.module')
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use (&$seen) {
                foreach ($chunk as $progress) {
                    $this->restoreOneLesson($progress->user_id, $progress->lesson, $progress->completed_at ?? $progress->updated_at, $seen);
                }
            });

        LessonProgress::query()
            ->where('is_completed', true)
            ->when($this->option('user'), fn ($q) => $q->where('user_id', (int) $this->option('user')))
            ->with('lesson.module')
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use (&$seen) {
                foreach ($chunk as $progress) {
                    $this->restoreOneLesson($progress->user_id, $progress->lesson, $progress->completed_at ?? $progress->updated_at, $seen);
                }
            });
    }

    private function restoreOneLesson(int $userId, ?Lesson $lesson, mixed $at, array &$seen): void
    {
        if (! $lesson) {
            return;
        }

        $key = $userId.':'.$lesson->id;
        if (isset($seen[$key])) {
            return;
        }
        $seen[$key] = true;

        $courseId = (int) ($lesson->course_id ?: $lesson->module?->course_id);
        if ($courseId < 1) {
            return;
        }

        $points = $this->lessonPoints($lesson->difficulty_level);
        $when = $at ? Carbon::parse($at) : now();

        if ($this->addProgressIfMissing($userId, $courseId, 'lesson_completed', $points, (int) $lesson->id, $when)) {
            $this->lessons++;
        }
    }

    private function restoreAssessments(): void
    {
        $query = AssessmentAttempt::query()
            ->where(function ($q) {
                $q->where('status', 'completed')->orWhereNotNull('completed_at');
            })
            ->where(function ($q) {
                $q->where('is_passed', true)->orWhereNotNull('score');
            })
            ->with('assessment')
            ->orderBy('id');

        $this->applyUserFilter($query, 'user_id');

        $awarded = [];

        $query->chunkById(200, function ($chunk) use (&$awarded) {
            foreach ($chunk as $attempt) {
                $assessment = $attempt->assessment;
                if (! $assessment) {
                    continue;
                }

                $xp = (int) ($assessment->xp_reward ?? 0);
                if ($xp <= 0) {
                    continue;
                }

                $passed = (bool) $attempt->is_passed;
                if (! $passed && $attempt->score !== null) {
                    $passing = (int) ($assessment->passing_score ?? 70);
                    $percentage = (float) ($attempt->scorePercentage() ?? $attempt->score ?? 0);
                    $passed = $percentage >= $passing || $percentage >= 70;
                }

                if (! $passed) {
                    continue;
                }

                $key = $attempt->user_id.':'.$assessment->id;
                if (isset($awarded[$key])) {
                    continue;
                }
                $awarded[$key] = true;

                $courseId = (int) ($assessment->course_id ?? 0);
                $lessonId = $assessment->lesson_id ? (int) $assessment->lesson_id : null;
                $at = $attempt->completed_at ?? $attempt->updated_at ?? now();

                if ($this->addProgressIfMissing(
                    (int) $attempt->user_id,
                    $courseId,
                    'quiz_completed',
                    $xp,
                    $lessonId,
                    $at,
                    ['source' => 'restore', 'assessment_id' => $assessment->id]
                )) {
                    $this->assessments++;
                }
            }
        });
    }

    private function restoreLegacyAssignments(): void
    {
        $query = AssignmentSubmission::query()
            ->whereIn('status', ['submitted', 'graded', 'returned'])
            ->with('assignment.lesson')
            ->orderBy('id');

        $this->applyUserFilter($query, 'user_id');

        $query->chunkById(200, function ($chunk) {
            foreach ($chunk as $submission) {
                $assignment = $submission->assignment;
                $xp = (int) ($assignment?->lesson?->xp_reward ?? 0);
                if ($xp <= 0 || ! $assignment) {
                    continue;
                }

                $courseId = (int) ($assignment->course_id ?? $assignment->lesson?->module?->course_id ?? 0);
                $lessonId = $assignment->lesson_id ? (int) $assignment->lesson_id : null;
                $at = $submission->submitted_at ?? $submission->created_at ?? now();

                if ($this->addProgressIfMissing(
                    (int) $submission->user_id,
                    $courseId,
                    'quiz_completed',
                    $xp,
                    $lessonId,
                    $at,
                    ['source' => 'restore_legacy_assignment', 'assignment_id' => $assignment->id]
                )) {
                    $this->legacyAssignments++;
                }
            }
        });
    }

    private function restoreCourseCompletions(): void
    {
        $query = CourseEnrollment::query()
            ->whereNotNull('course_id')
            ->where(function ($q) {
                $q->whereNotNull('completed_at')
                    ->orWhere('progress_percentage', '>=', 100);
            })
            ->whereHas('user', fn ($q) => $q->where(function ($student) {
                $student->whereHas('studentProfile')
                    ->orWhereHas('roles', fn ($r) => $r->where('name', 'student'));
            }));

        $this->applyUserFilter($query, 'user_id');

        $query->orderBy('id')->chunkById(200, function ($chunk) {
            foreach ($chunk as $enrollment) {
                $at = $enrollment->completed_at ?? $enrollment->updated_at ?? now();
                if ($this->addProgressIfMissing(
                    (int) $enrollment->user_id,
                    (int) $enrollment->course_id,
                    'course_completed',
                    100,
                    null,
                    $at
                )) {
                    $this->courseCompletions++;
                }
            }
        });
    }

    private function rebuildPointTotals(): void
    {
        $query = User::query()->where(function ($q) {
            $q->whereHas('studentProfile')
                ->orWhereHas('roles', fn ($r) => $r->where('name', 'student'));
        });

        if ($this->option('user')) {
            $query->where('id', (int) $this->option('user'));
        }

        $query->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $total = (int) UserProgress::query()
                    ->where('user_id', $user->id)
                    ->sum('points_earned');

                if (! $this->write) {
                    continue;
                }

                $points = UserPoint::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    ['total_points' => 0, 'level' => 1, 'points_to_next_level' => LevelSystem::XP_PER_LEVEL]
                );

                $points->total_points = $total;
                $points->save();
                LevelSystem::sync($points);
            }
        });
    }

    private function addProgressIfMissing(
        int $userId,
        int $courseId,
        string $type,
        int $points,
        ?int $lessonId,
        mixed $at,
        array $metadata = []
    ): bool {
        if ($userId < 1 || $courseId < 1 || $points <= 0) {
            return false;
        }

        $exists = UserProgress::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('type', $type)
            ->when($lessonId, fn ($q) => $q->where('lesson_id', $lessonId), fn ($q) => $q->whereNull('lesson_id'))
            ->exists();

        if ($exists) {
            return false;
        }

        if (! $this->write) {
            return true;
        }

        $when = $at instanceof Carbon ? $at : Carbon::parse($at);

        $row = new UserProgress([
            'user_id' => $userId,
            'course_id' => $courseId,
            'lesson_id' => $lessonId,
            'type' => $type,
            'points_earned' => $points,
            'completed_at' => $when,
            'metadata' => $metadata ?: null,
        ]);
        $row->created_at = $when;
        $row->updated_at = $when;
        $row->save();

        return true;
    }

    private function applyUserFilter($query, string $column): void
    {
        if ($this->option('user')) {
            $query->where($column, (int) $this->option('user'));
        }
    }

    private function lessonPoints(?string $difficultyLevel): int
    {
        if (! $difficultyLevel) {
            return 10;
        }

        return match (strtolower($difficultyLevel)) {
            'beginner' => 5,
            'intermediate' => 10,
            'advanced' => 15,
            default => 10,
        };
    }
}
