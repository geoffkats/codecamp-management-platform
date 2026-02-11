<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Results extends Component
{
    public Assessment $assessment;
    public AssessmentAttempt $attempt;
    public $percentage = null;
    public $maxScore = 100;
    public $isPending = false;
    public $incorrectQuestions = [];
    public $correctQuestions = [];

    public function mount(Assessment $assessment, AssessmentAttempt $attempt)
    {
        $this->assessment = $assessment->load(['questions', 'course', 'lesson']);
        $this->attempt = $attempt->load(['assessment.course', 'assessment.questions.options', 'user']);

        if ($this->attempt->assessment_id !== $this->assessment->id) {
            abort(404, 'Assessment attempt not found.');
        }

        $this->authorizeAccess();

        $this->maxScore = $this->assessment->questions?->sum('points') ?: 100;
        $this->isPending = $this->assessment->assessment_type === 'assignment' && $this->attempt->score === null;

        if ($this->isPending) {
            $this->percentage = null;
            return;
        }

        if ($this->attempt->auto_scored) {
            $this->percentage = $this->maxScore > 0
                ? round(($this->attempt->score / $this->maxScore) * 100, 2)
                : 0;
        } else {
            $this->percentage = $this->attempt->score !== null ? round($this->attempt->score, 2) : null;
        }

        $this->incorrectQuestions = $this->buildIncorrectQuestions();
        $this->correctQuestions = $this->buildCorrectQuestions();
    }

    protected function buildCorrectQuestions(): array
    {
        if (!$this->attempt->auto_scored) {
            return [];
        }

        $answers = $this->attempt->answers ?? [];
        $correct = [];

        foreach ($this->assessment->questions ?? [] as $question) {
            $type = $question->question_type;
            if (!in_array($type, ['multiple_choice', 'multiple_select', 'choice', 'true_false'], true)) {
                continue;
            }

            $userAnswer = $answers[$question->id] ?? null;
            $correctOptionIds = $question->options->where('is_correct', true)->pluck('id')->map(fn ($id) => (int) $id)->toArray();

            $isCorrect = false;
            if ($type === 'multiple_select') {
                $userAnswerIds = is_array($userAnswer)
                    ? array_values(array_map(fn ($id) => (int) $id, $userAnswer))
                    : [];

                sort($userAnswerIds);
                sort($correctOptionIds);

                $isCorrect = $userAnswerIds === $correctOptionIds;
            } else {
                $userAnswerId = is_array($userAnswer) ? null : (is_numeric($userAnswer) ? (int) $userAnswer : null);
                $isCorrect = $userAnswerId !== null && in_array($userAnswerId, $correctOptionIds, true);
            }

            if (!$isCorrect) {
                continue;
            }

            $optionMap = $question->options->pluck('option_text', 'id')->mapWithKeys(function ($text, $id) {
                return [(int) $id => $text];
            })->toArray();

            $userText = 'No answer';
            if (is_array($userAnswer)) {
                $userText = collect($userAnswer)
                    ->map(fn ($id) => is_numeric($id) ? (int) $id : null)
                    ->filter(fn ($id) => $id !== null)
                    ->map(fn ($id) => $optionMap[$id] ?? 'N/A')
                    ->values()
                    ->implode(', ');
            } elseif (is_numeric($userAnswer)) {
                $userText = $optionMap[(int) $userAnswer] ?? 'N/A';
            }

            $correct[] = [
                'question' => $question->question_text,
                'your_answer' => $userText,
            ];
        }

        return $correct;
    }

    protected function buildIncorrectQuestions(): array
    {
        if (!$this->attempt->auto_scored) {
            return [];
        }

        $answers = $this->attempt->answers ?? [];
        $incorrect = [];

        foreach ($this->assessment->questions ?? [] as $question) {
            $type = $question->question_type;
            if (!in_array($type, ['multiple_choice', 'multiple_select', 'choice', 'true_false'], true)) {
                continue;
            }

            $userAnswer = $answers[$question->id] ?? null;
            $correctOptionIds = $question->options->where('is_correct', true)->pluck('id')->map(fn ($id) => (int) $id)->toArray();

            if ($type === 'multiple_select') {
                $userAnswerIds = is_array($userAnswer)
                    ? array_values(array_map(fn ($id) => (int) $id, $userAnswer))
                    : [];

                sort($userAnswerIds);
                sort($correctOptionIds);

                if ($userAnswerIds === $correctOptionIds) {
                    continue;
                }
            } else {
                $userAnswerId = is_array($userAnswer) ? null : (is_numeric($userAnswer) ? (int) $userAnswer : null);

                if ($userAnswerId !== null && in_array($userAnswerId, $correctOptionIds, true)) {
                    continue;
                }
            }

            $optionMap = $question->options->pluck('option_text', 'id')->mapWithKeys(function ($text, $id) {
                return [(int) $id => $text];
            })->toArray();

            $correctText = collect($correctOptionIds)
                ->map(fn ($id) => $optionMap[$id] ?? 'N/A')
                ->values()
                ->implode(', ');

            $userText = 'No answer';
            if (is_array($userAnswer)) {
                $userText = collect($userAnswer)
                    ->map(fn ($id) => is_numeric($id) ? (int) $id : null)
                    ->filter(fn ($id) => $id !== null)
                    ->map(fn ($id) => $optionMap[$id] ?? 'N/A')
                    ->values()
                    ->implode(', ');
            } elseif (is_numeric($userAnswer)) {
                $userText = $optionMap[(int) $userAnswer] ?? 'N/A';
            }

            $incorrect[] = [
                'question' => $question->question_text,
                'your_answer' => $userText,
                'correct_answer' => $correctText,
            ];
        }

        return $incorrect;
    }

    protected function authorizeAccess(): void
    {
        $user = Auth::user();

        if ($user->hasRole('student')) {
            if ($this->attempt->user_id !== $user->id) {
                abort(403, 'You can only view your own results.');
            }

            return;
        }

        if ($user->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();

            if (!$schoolId || $this->attempt->student_type !== 'ict' || (int) $this->attempt->school_id !== (int) $schoolId) {
                abort(403, 'You can only view results from your school.');
            }

            return;
        }

        if ($user->isTeacher()) {
            if ($this->attempt->student_type !== 'codecamp') {
                abort(403, 'You can only view results from your own courses.');
            }

            $course = $this->attempt->assessment->course ?? null;
            if (!$course || $course->instructor_id !== $user->id) {
                abort(403, 'You can only view results from your own courses.');
            }

            return;
        }

        if (!$user->isAdmin() && !$user->isSupervisor()) {
            abort(403, 'You do not have permission to view this result.');
        }
    }

    public function render()
    {
        return view('livewire.assessments.results');
    }
}
