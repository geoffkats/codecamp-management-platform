<?php

namespace App\Services;

use App\Models\AssessmentAttempt;

class AssessmentAttemptReview
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function rows(AssessmentAttempt $attempt): array
    {
        $attempt->loadMissing(['assessment.questions.options']);
        $answers = $attempt->answers ?? [];
        $rows = [];

        $questions = collect($attempt->assessment?->questions ?? [])
            ->sortBy('order')
            ->values();

        foreach ($questions as $index => $question) {
            $type = $this->questionType($question);
            $userAnswer = $answers[$question->id] ?? $answers[(string) $question->id] ?? null;
            $points = (float) ($question->points ?? 0);
            $needsManual = $this->isManualType($type)
                || ($type === 'short_answer' && ! $this->shortAnswerHasCorrectKey($question));

            if ($type === 'file_upload') {
                $files = [];
                if (is_array($userAnswer)) {
                    $files = $userAnswer['files'] ?? (isset($userAnswer['path']) ? [$userAnswer] : []);
                }

                $rows[] = $this->baseRow($question, $index, $type, $points, true, [
                    'is_correct' => null,
                    'earned' => null,
                    'student_answer' => null,
                    'correct_answer' => null,
                    'files' => array_values(array_filter($files)),
                    'options' => [],
                ]);
                continue;
            }

            if (in_array($type, ['multiple_choice', 'multiple_select', 'choice', 'true_false'], true)) {
                $evaluation = $this->evaluateChoiceQuestion($question, $userAnswer);
                $rows[] = $this->baseRow($question, $index, $type, $points, false, [
                    'is_correct' => $evaluation['is_correct'],
                    'earned' => $evaluation['is_correct'] ? $points : 0,
                    'student_answer' => $evaluation['student_text'],
                    'correct_answer' => $evaluation['correct_text'],
                    'files' => [],
                    'options' => $evaluation['options'],
                ]);
                continue;
            }

            if ($type === 'short_answer' && ! $needsManual) {
                $text = is_string($userAnswer) ? $userAnswer : (is_numeric($userAnswer) ? (string) $userAnswer : '');
                $isCorrect = $this->isShortAnswerCorrect($question, $text);
                $rows[] = $this->baseRow($question, $index, $type, $points, false, [
                    'is_correct' => $isCorrect,
                    'earned' => $isCorrect ? $points : 0,
                    'student_answer' => filled($text) ? $text : '— No answer —',
                    'correct_answer' => $this->shortAnswerCorrectText($question),
                    'files' => [],
                    'options' => [],
                ]);
                continue;
            }

            $display = '— No answer —';
            if (is_numeric($userAnswer) && $question->options?->isNotEmpty()) {
                $option = $question->options->firstWhere('id', (int) $userAnswer);
                $display = $option?->option_text ?: (string) $userAnswer;
            } elseif ($type === 'code_submission' && filled($userAnswer) && ! is_array($userAnswer)) {
                $display = (string) $userAnswer;
            } elseif (is_array($userAnswer)) {
                $display = trim((string) ($userAnswer['value'] ?? $userAnswer['text'] ?? $userAnswer['answer'] ?? ''));
                if ($display === '') {
                    $display = '— No answer —';
                }
            } elseif (filled($userAnswer)) {
                $display = (string) $userAnswer;
            }

            $rows[] = $this->baseRow($question, $index, $type, $points, true, [
                'is_correct' => null,
                'earned' => null,
                'student_answer' => $display,
                'correct_answer' => null,
                'files' => [],
                'options' => [],
            ]);
        }

        return $rows;
    }

    public function autoEarnedPoints(AssessmentAttempt $attempt): float
    {
        $total = 0.0;
        foreach ($this->rows($attempt) as $row) {
            if ($row['needs_manual']) {
                continue;
            }
            $total += (float) ($row['earned'] ?? 0);
        }

        return round($total, 1);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function baseRow($question, int $index, string $type, float $points, bool $needsManual, array $extra): array
    {
        return array_merge([
            'id' => (int) $question->id,
            'number' => $index + 1,
            'question' => $question->question_text,
            'type' => $type,
            'type_label' => $this->typeLabel($type),
            'points' => $points,
            'needs_manual' => $needsManual,
        ], $extra);
    }

    private function questionType($question): string
    {
        return str_replace(' ', '_', strtolower(trim((string) $question->question_type)));
    }

    private function isManualType(string $type): bool
    {
        return in_array($type, [
            'short_answer',
            'essay',
            'code_submission',
            'file_upload',
            'rubric_criteria',
        ], true);
    }

    /**
     * @return array{is_correct: bool, student_text: string, correct_text: string, options: array<int, array{text: string, is_correct: bool, selected: bool}>}
     */
    private function evaluateChoiceQuestion($question, $userAnswer): array
    {
        $type = $this->questionType($question);
        $options = $question->options ?? collect();
        $correctIds = $options->where('is_correct', true)->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        $selectedIds = [];
        if (is_array($userAnswer)) {
            if (array_key_exists('value', $userAnswer)) {
                $userAnswer = $userAnswer['value'];
            }
            if (is_array($userAnswer)) {
                $selectedIds = array_values(array_filter(array_map(
                    fn ($id) => is_numeric($id) ? (int) $id : null,
                    $userAnswer
                )));
            } elseif (is_numeric($userAnswer)) {
                $selectedIds = [(int) $userAnswer];
            }
        } elseif (is_numeric($userAnswer)) {
            $selectedIds = [(int) $userAnswer];
        }

        $optionRows = [];
        foreach ($options->sortBy('order') as $option) {
            $oid = (int) $option->id;
            $optionRows[] = [
                'text' => (string) $option->option_text,
                'is_correct' => (bool) $option->is_correct,
                'selected' => in_array($oid, $selectedIds, true),
            ];
        }

        if ($type === 'multiple_select') {
            $a = $selectedIds;
            $b = $correctIds;
            sort($a);
            sort($b);
            $isCorrect = $a === $b && $a !== [];
        } else {
            $isCorrect = count($selectedIds) === 1 && in_array($selectedIds[0], $correctIds, true);
        }

        $optionMap = $options->pluck('option_text', 'id')->mapWithKeys(
            fn ($text, $id) => [(int) $id => $text]
        )->all();

        $studentText = $selectedIds === []
            ? '— No answer —'
            : collect($selectedIds)->map(fn ($id) => $optionMap[$id] ?? "Option #{$id}")->implode(', ');

        $correctText = collect($correctIds)->map(fn ($id) => $optionMap[$id] ?? "Option #{$id}")->implode(', ');

        return [
            'is_correct' => $isCorrect,
            'student_text' => $studentText,
            'correct_text' => $correctText ?: '—',
            'options' => $optionRows,
        ];
    }

    private function shortAnswerHasCorrectKey($question): bool
    {
        return filled($this->shortAnswerCorrectText($question));
    }

    private function shortAnswerCorrectText($question): string
    {
        $settings = $question->settings ?? [];
        $correct = trim((string) ($settings['correct_answer'] ?? $settings['short_answer']['correct_answer'] ?? ''));

        if ($correct === '') {
            $correctOption = ($question->options ?? collect())->where('is_correct', true)->first();
            $correct = $correctOption ? trim((string) $correctOption->option_text) : '';
        }

        return $correct;
    }

    private function isShortAnswerCorrect($question, string $userAnswer): bool
    {
        $userAnswer = trim($userAnswer);
        $correctAnswer = $this->shortAnswerCorrectText($question);
        if ($userAnswer === '' || $correctAnswer === '') {
            return false;
        }

        $settings = $question->settings ?? [];
        $caseSensitive = (bool) ($settings['case_sensitive'] ?? false);
        $userCmp = $caseSensitive ? $userAnswer : mb_strtolower($userAnswer);
        $correctCmp = $caseSensitive ? $correctAnswer : mb_strtolower($correctAnswer);

        if ($userCmp === $correctCmp) {
            return true;
        }

        foreach ($settings['alternative_answers'] ?? [] as $alt) {
            $altCmp = $caseSensitive ? trim((string) $alt) : mb_strtolower(trim((string) $alt));
            if ($userCmp === $altCmp) {
                return true;
            }
        }

        return false;
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'multiple_choice', 'choice' => 'Multiple Choice',
            'multiple_select' => 'Multiple Select',
            'true_false' => 'True / False',
            'short_answer' => 'Short Answer',
            'essay' => 'Essay',
            'code_submission' => 'Code Submission',
            'file_upload' => 'File Upload',
            'fill_blank' => 'Fill in the Blank',
            'ordering' => 'Ordering',
            'matching' => 'Matching',
            'rating' => 'Rating',
            'rubric_criteria' => 'Rubric',
            default => ucwords(str_replace('_', ' ', $type)),
        };
    }
}
