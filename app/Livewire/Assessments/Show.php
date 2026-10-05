<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Show extends Component
{
    use WithPagination;

    public Assessment $assessment;

    // Student-facing state (scalars only — no collections stored as properties)
    public bool $hasTaken = false;
    public ?float $bestScore = null;
    public $attemptsRemaining = null;

    // Teacher submissions filter
    public string $search = '';
    public string $statusFilter = 'all';

    public function mount(Assessment $assessment)
    {
        $this->assessment = $assessment->load([
            'course',
            'lesson',
            'questions' => fn ($q) => $q->orderBy('order')->with('options'),
        ]);

        $user = Auth::user();

        if (! $user->hasAnyRole(['admin', 'supervisor', 'teacher', 'codecamp_trainer'])) {
            if ($this->assessment->is_locked || $this->assessment->lesson?->is_locked) {
                abort(403, 'This assessment is currently locked. Wait for your instructor to unlock the lesson.');
            }
        }

        $attemptQuery = AssessmentAttempt::where('assessment_id', $assessment->id)
            ->where('user_id', $user->id);

        $attemptCount = (clone $attemptQuery)->count();
        $this->hasTaken = $attemptCount > 0;

        if ($this->hasTaken) {
            // Each attempt is measured against its own question set's maximum
            $best = (clone $attemptQuery)->whereNotNull('score')->get()
                ->map(fn (AssessmentAttempt $attempt) => $attempt->scorePercentage())
                ->filter(fn ($percentage) => $percentage !== null)
                ->max();

            $this->bestScore = $assessment->assessment_type === 'assignment' ? $best : ($best ?? 0);
        }

        $this->attemptsRemaining = $assessment->max_attempts > 0
            ? max(0, $assessment->max_attempts - $attemptCount)
            : 'unlimited';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $isTeacher = $user->hasAnyRole(['teacher', 'admin', 'supervisor', 'operations_manager']);
        $maxScore = $this->assessment->assessment_type === 'assignment'
            ? $this->assessment->max_points
            : ($this->assessment->questions()->sum('points') ?: 100);

        // Student's own last 5 attempts — queried fresh, not stored in state
        $userAttempts = collect();
        if ($this->hasTaken) {
            $userAttempts = AssessmentAttempt::where('assessment_id', $this->assessment->id)
                ->where('user_id', $user->id)
                ->latest('completed_at')
                ->limit(5)
                ->get();
        }

        // Teacher: stats using DB aggregates — no ->get() over all rows
        $stats = null;
        $submissions = null;
        if ($isTeacher) {
            $stats = $this->getStatisticsFromDb($maxScore);

            $submissionsQuery = AssessmentAttempt::visibleTo($user)
                ->where('assessment_id', $this->assessment->id)
                ->with('user:id,name,email')
                ->latest('completed_at');

            if ($this->search !== '') {
                $search = $this->search;
                $submissionsQuery->whereHas('user', fn ($q) =>
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                );
            }

            match ($this->statusFilter) {
                'passed'  => $submissionsQuery->where('is_passed', true),
                'failed'  => $submissionsQuery->where('is_passed', false)->whereNotNull('score'),
                'pending' => $submissionsQuery->whereNull('score'),
                default   => null,
            };

            $submissions = $submissionsQuery->paginate(25);
        }

        return view('livewire.assessments.show', [
            'userAttempts' => $userAttempts,
            'stats'        => $stats,
            'submissions'  => $submissions,
            'isTeacher'    => $isTeacher,
            'maxScore'     => $maxScore,
        ]);
    }

    // DB aggregates only — never loads rows into PHP
    private function getStatisticsFromDb(int|float $maxScore): array
    {
        $query = AssessmentAttempt::visibleTo(Auth::user())
            ->where('assessment_id', $this->assessment->id);

        $total = (clone $query)->count();

        if ($total === 0) {
            return ['total_attempts' => 0, 'unique_students' => 0, 'average_score' => 0, 'pass_rate' => 0];
        }

        // Each attempt is scored against its own frozen question set; pooled assessments differ per attempt.
        $avgPct = $maxScore > 0 ? (float) ((clone $query)
            ->whereNotNull('score')
            ->selectRaw(
                'AVG(score / COALESCE(NULLIF((SELECT SUM(aq.points) FROM assessment_attempt_questions aq WHERE aq.assessment_attempt_id = assessment_attempts.id), 0), ?)) * 100 AS avg_pct',
                [$maxScore]
            )
            ->value('avg_pct') ?? 0) : 0;
        $passed    = (clone $query)->where('is_passed', true)->count();
        $unique    = (clone $query)->distinct('user_id')->count('user_id');

        return [
            'total_attempts'  => $total,
            'unique_students' => $unique,
            'average_score'   => round($avgPct, 1),
            'pass_rate'       => round(($passed / $total) * 100, 1),
        ];
    }
}
