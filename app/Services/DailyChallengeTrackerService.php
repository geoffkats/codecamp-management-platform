<?php

namespace App\Services;

use App\Models\AssignmentSubmission;
use App\Models\AssessmentAttempt;
use App\Models\CourseEnrollment;
use App\Models\DailyChallenge;
use App\Models\DailyChallengeAttempt;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\StudentLessonProgress;
use App\Models\User;
use App\Models\UserPoint;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DailyChallengeTrackerService
{
    public function evaluate(DailyChallenge $challenge, int $userId): array
    {
        $requirements = $challenge->requirements ?? [];
        $challengeDate = $this->challengeDate($challenge);

        $status = match ($challenge->type) {
            'lesson_completion' => $this->evaluateLessonCompletion($userId, $requirements, $challengeDate),
            'quiz_score' => $this->evaluateQuizScore($userId, $requirements, $challengeDate),
            'study_time' => $this->evaluateStudyTime($userId, $requirements, $challengeDate),
            'course_progress' => $this->evaluateCourseProgress($userId, $requirements, $challengeDate, $challenge->course_id),
            'forum_participation' => $this->evaluateForumParticipation($userId, $requirements, $challengeDate, $challenge->course_id),
            'assignment_submission' => $this->evaluateAssignmentSubmission($userId, $requirements, $challengeDate, $challenge->course_id),
            default => [
                'met' => false,
                'current' => 0,
                'required' => 0,
                'message' => 'Unknown challenge type.',
                'evidence' => [],
            ],
        };

        return $status;
    }

    public function syncForumProgressForUser(int $userId, ?int $courseId = null): void
    {
        $challenges = $this->activeForumChallengesForUser($userId, $courseId);

        foreach ($challenges as $challenge) {
            $attempt = DailyChallengeAttempt::firstOrCreate(
                [
                    'user_id' => $userId,
                    'challenge_id' => $challenge->id,
                ],
                [
                    'attempted_at' => now(),
                    'is_completed' => false,
                    'points_earned' => 0,
                ]
            );

            if ($attempt->is_completed) {
                continue;
            }

            $status = $this->evaluate($challenge, $userId);
            $attempt->update(['progress_data' => $status]);
        }
    }

    /**
     * @return Collection<int, DailyChallenge>
     */
    public function activeForumChallengesForUser(int $userId, ?int $courseId = null): Collection
    {
        $userCourseIds = User::find($userId)?->enrollments()->pluck('course_id')->filter()->unique() ?? collect();

        return DailyChallenge::query()
            ->where('is_active', true)
            ->where('type', 'forum_participation')
            ->where(function ($q) use ($courseId, $userCourseIds) {
                if ($courseId) {
                    $q->whereNull('course_id')->orWhere('course_id', $courseId);
                } else {
                    $q->whereNull('course_id');
                    if ($userCourseIds->isNotEmpty()) {
                        $q->orWhereIn('course_id', $userCourseIds);
                    }
                }
            })
            ->where(function ($q) {
                $windowStart = now()->subDays(config('daily_challenges.completion_window_days', 7))->toDateString();
                $q->whereNull('date')
                    ->orWhere('date', '>=', $windowStart);
            })
            ->get();
    }

    public function tryComplete(DailyChallenge $challenge, int $userId): array
    {
        if (! $challenge->is_active) {
            return ['success' => false, 'message' => 'This challenge is not active.'];
        }

        if (! $this->isWithinCompletionWindow($challenge)) {
            return ['success' => false, 'message' => 'The completion window for this challenge has expired.'];
        }

        $attempt = DailyChallengeAttempt::firstOrCreate(
            [
                'user_id' => $userId,
                'challenge_id' => $challenge->id,
            ],
            [
                'attempted_at' => now(),
                'is_completed' => false,
                'points_earned' => 0,
            ]
        );

        if ($attempt->is_completed) {
            return ['success' => false, 'message' => 'You have already completed this challenge.'];
        }

        $status = $this->evaluate($challenge, $userId);

        if (! ($status['met'] ?? false)) {
            $attempt->update(['progress_data' => $status]);

            return [
                'success' => false,
                'message' => $status['message'] ?? 'Challenge requirements not yet met.',
            ];
        }

        return DB::transaction(function () use ($attempt, $challenge, $userId, $status) {
            $attempt->refresh();

            if ($attempt->is_completed) {
                return ['success' => false, 'message' => 'You have already completed this challenge.'];
            }

            $attempt->update([
                'is_completed' => true,
                'completed_at' => now(),
                'points_earned' => $challenge->reward_points,
                'progress_data' => $status,
                'details' => [
                    'verified_at' => now()->toIso8601String(),
                    'evidence' => $status['evidence'] ?? [],
                ],
            ]);

            $user = User::findOrFail($userId);
            $userPoints = $user->points ?? $user->points()->create([
                'total_points' => 0,
                'level' => 1,
                'points_to_next_level' => 100,
            ]);

            $userPoints->addPoints((int) $challenge->reward_points);

            app(BadgeAwardingService::class)->checkChallengeBadges($user);

            if ($challenge->is_competition) {
                $this->awardCompetitionWinnerBadge($challenge, $user);
            }

            return [
                'success' => true,
                'message' => 'Challenge completed! You earned '.$challenge->reward_points.' points.',
            ];
        });
    }

    protected function evaluateLessonCompletion(int $userId, array $requirements, Carbon $challengeDate): array
    {
        $requiredCount = (int) ($requirements['count'] ?? 0);

        $completed = StudentLessonProgress::where('user_id', $userId)
            ->where('status', 'completed')
            ->whereDate('completed_at', $challengeDate)
            ->count();

        $completed += LessonProgress::where('user_id', $userId)
            ->where('is_completed', true)
            ->whereDate('completed_at', $challengeDate)
            ->count();

        return [
            'met' => $completed >= $requiredCount,
            'current' => $completed,
            'required' => $requiredCount,
            'message' => "Completed {$completed} of {$requiredCount} lessons on ".$challengeDate->toFormattedDateString(),
            'evidence' => ['date' => $challengeDate->toDateString(), 'count' => $completed],
        ];
    }

    protected function evaluateQuizScore(int $userId, array $requirements, Carbon $challengeDate): array
    {
        $requiredScore = (int) ($requirements['score'] ?? 100);

        $passed = QuizAttempt::where('user_id', $userId)
            ->where('score', '>=', $requiredScore)
            ->whereDate('completed_at', $challengeDate)
            ->exists();

        return [
            'met' => $passed,
            'current' => $passed ? 1 : 0,
            'required' => 1,
            'message' => $passed
                ? "Achieved {$requiredScore}% or higher on a quiz on ".$challengeDate->toFormattedDateString()
                : "Need {$requiredScore}% or higher on a quiz on ".$challengeDate->toFormattedDateString(),
            'evidence' => ['date' => $challengeDate->toDateString(), 'score_required' => $requiredScore],
        ];
    }

    protected function evaluateStudyTime(int $userId, array $requirements, Carbon $challengeDate): array
    {
        $requiredMinutes = (int) ($requirements['minutes'] ?? 0);

        $minutes = (int) StudentLessonProgress::where('user_id', $userId)
            ->whereDate('last_accessed_at', $challengeDate)
            ->sum('time_spent_minutes');

        $minutes += (int) (LessonProgress::where('user_id', $userId)
            ->whereDate('updated_at', $challengeDate)
            ->sum('time_spent') / 60);

        return [
            'met' => $minutes >= $requiredMinutes,
            'current' => $minutes,
            'required' => $requiredMinutes,
            'message' => "Studied {$minutes} of {$requiredMinutes} minutes on ".$challengeDate->toFormattedDateString(),
            'evidence' => ['date' => $challengeDate->toDateString(), 'minutes' => $minutes],
        ];
    }

    protected function evaluateCourseProgress(int $userId, array $requirements, Carbon $challengeDate, ?int $courseId): array
    {
        $requiredPercentage = (int) ($requirements['percentage'] ?? 0);

        $query = CourseEnrollment::where('user_id', $userId)
            ->where('progress_percentage', '>=', $requiredPercentage)
            ->whereDate('updated_at', $challengeDate);

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        $met = $query->exists();

        return [
            'met' => $met,
            'current' => $met ? $requiredPercentage : 0,
            'required' => $requiredPercentage,
            'message' => $met
                ? "Reached {$requiredPercentage}% course progress on ".$challengeDate->toFormattedDateString()
                : "Need {$requiredPercentage}% course progress on ".$challengeDate->toFormattedDateString(),
            'evidence' => ['date' => $challengeDate->toDateString()],
        ];
    }

    protected function evaluateForumParticipation(int $userId, array $requirements, Carbon $challengeDate, ?int $courseId): array
    {
        $required = (int) ($requirements['posts'] ?? $requirements['count'] ?? 1);
        $mode = $requirements['mode'] ?? 'both';
        $minReplyChars = (int) ($requirements['min_characters'] ?? config('daily_challenges.forum.min_reply_characters', 40));
        $minDiscussionChars = (int) config('daily_challenges.forum.min_discussion_characters', 40);
        $minTitleChars = (int) config('daily_challenges.forum.min_title_characters', 10);

        $actions = collect();

        if (in_array($mode, ['both', 'posts', 'discussions'], true)) {
            $discussions = Discussion::query()
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->whereDate('created_at', $challengeDate)
                ->whereRaw('CHAR_LENGTH(content) >= ?', [$minDiscussionChars])
                ->whereRaw('CHAR_LENGTH(title) >= ?', [$minTitleChars])
                ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
                ->when(! $courseId, fn ($q) => $q->whereNotNull('course_id'))
                ->get(['id', 'title', 'course_id', 'created_at']);

            foreach ($discussions as $discussion) {
                $actions->push([
                    'type' => 'discussion',
                    'id' => $discussion->id,
                    'discussion_id' => $discussion->id,
                    'course_id' => $discussion->course_id,
                    'created_at' => $discussion->created_at?->toIso8601String(),
                ]);
            }
        }

        if (in_array($mode, ['both', 'replies'], true)) {
            $replies = DiscussionReply::query()
                ->with('discussion:id,course_id,status')
                ->where('user_id', $userId)
                ->whereDate('created_at', $challengeDate)
                ->whereRaw('CHAR_LENGTH(content) >= ?', [$minReplyChars])
                ->whereHas('discussion', function ($q) use ($courseId, $challengeDate) {
                    $q->where('status', 'active')
                        ->when($courseId, fn ($query) => $query->where('course_id', $courseId))
                        ->when(! $courseId, fn ($query) => $query->whereNotNull('course_id'));
                })
                ->orderBy('created_at')
                ->get(['id', 'discussion_id', 'content', 'created_at']);

            $countedPerDiscussion = [];

            foreach ($replies as $reply) {
                $discussionId = $reply->discussion_id;
                $countedPerDiscussion[$discussionId] = ($countedPerDiscussion[$discussionId] ?? 0) + 1;

                if ($countedPerDiscussion[$discussionId] > config('daily_challenges.forum.max_qualifying_replies_per_discussion', 1)) {
                    continue;
                }

                $actions->push([
                    'type' => 'reply',
                    'id' => $reply->id,
                    'discussion_id' => $discussionId,
                    'course_id' => $reply->discussion?->course_id,
                    'created_at' => $reply->created_at?->toIso8601String(),
                ]);
            }
        }

        $current = $actions->count();

        return [
            'met' => $current >= $required,
            'current' => $current,
            'required' => $required,
            'message' => $this->forumProgressMessage($current, $required, $minReplyChars, $challengeDate),
            'evidence' => [
                'date' => $challengeDate->toDateString(),
                'qualifying_actions' => $actions->values()->all(),
                'rules' => [
                    'min_characters' => $minReplyChars,
                    'one_reply_per_thread' => true,
                    'course_scoped' => (bool) $courseId,
                ],
            ],
        ];
    }

    protected function evaluateAssignmentSubmission(int $userId, array $requirements, Carbon $challengeDate, ?int $courseId): array
    {
        $required = (int) ($requirements['count'] ?? 1);

        $query = AssignmentSubmission::query()
            ->where('user_id', $userId)
            ->whereDate('submitted_at', $challengeDate);

        if ($courseId) {
            $query->whereHas('assignment', fn ($q) => $q->where('course_id', $courseId));
        }

        $legacyCount = $query->count();

        $assessmentCount = AssessmentAttempt::query()
            ->where('user_id', $userId)
            ->whereDate('completed_at', $challengeDate)
            ->whereHas('assessment', function ($q) use ($courseId) {
                $q->where('assessment_type', 'assignment');
                if ($courseId) {
                    $q->where('course_id', $courseId);
                }
            })
            ->count();

        $count = $legacyCount + $assessmentCount;

        return [
            'met' => $count >= $required,
            'current' => $count,
            'required' => $required,
            'message' => "Submitted {$count} of {$required} assignment(s) on ".$challengeDate->toFormattedDateString(),
            'evidence' => ['date' => $challengeDate->toDateString(), 'count' => $count],
        ];
    }

    protected function forumProgressMessage(int $current, int $required, int $minChars, Carbon $date): string
    {
        $dateLabel = $date->toFormattedDateString();

        if ($current >= $required) {
            return "Qualifying forum activity on {$dateLabel}: {$current}/{$required} (verified).";
        }

        return "Qualifying forum posts on {$dateLabel}: {$current}/{$required}. "
            ."Each post or reply must be at least {$minChars} characters, on-topic, in your course, "
            .'and only one reply per thread counts.';
    }

    protected function challengeDate(DailyChallenge $challenge): Carbon
    {
        return $challenge->date
            ? Carbon::parse($challenge->date)->startOfDay()
            : Carbon::today();
    }

    protected function isWithinCompletionWindow(DailyChallenge $challenge): bool
    {
        if (! $challenge->date) {
            return true;
        }

        $challengeDay = Carbon::parse($challenge->date)->startOfDay();
        $windowDays = (int) config('daily_challenges.completion_window_days', 7);

        return $challengeDay->lte(Carbon::today())
            && $challengeDay->gte(Carbon::today()->subDays($windowDays));
    }

    protected function awardCompetitionWinnerBadge(DailyChallenge $challenge, User $user): void
    {
        $completionCount = DailyChallengeAttempt::where('challenge_id', $challenge->id)
            ->where('is_completed', true)
            ->count();

        if ($completionCount !== 1) {
            return;
        }

        app(BadgeAwardingService::class)->checkAllBadges($user);
        $winnerBadge = \App\Models\Badge::where('slug', 'week-winner')->first();

        if ($winnerBadge && ! $user->badges()->where('badge_id', $winnerBadge->id)->exists()) {
            $user->badges()->attach($winnerBadge->id, ['earned_at' => now()]);
        }
    }
}
