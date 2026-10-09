<?php

namespace App\Services\Reports;

use App\Services\GeminiAIService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Writes the summary, highlights, challenges and recommendations for a camp report,
 * either with Gemini (reading every instructor's daily notes) or compiled from the numbers.
 */
class CampReportNarrator
{
    public function __construct(private GeminiAIService $gemini) {}

    public function aiAvailable(): bool
    {
        return $this->gemini->isConfigured();
    }

    /**
     * @return array{summary: string, highlights: string, challenges: string, recommendations: string, source: string, ai_error?: string}
     */
    public function write(array $data, bool $useAi = true): array
    {
        if ($useAi && $this->aiAvailable()) {
            $result = $this->gemini->generateContent($this->prompt($data));

            if (($result['success'] ?? false) && ($parsed = $this->parse($result['content'] ?? ''))) {
                return [...$parsed, 'source' => 'ai'];
            }

            $error = $result['error'] ?? 'The AI reply could not be read.';
            Log::warning('Camp report AI narrative failed, using compiled version', ['camp_id' => $data['camp']->id, 'error' => $error]);

            return [...$this->compile($data), 'source' => 'compiled', 'ai_error' => $error];
        }

        return [...$this->compile($data), 'source' => 'compiled'];
    }

    /**
     * @return array{summary: string, highlights: string, challenges: string, recommendations: string}
     */
    public function compile(array $data): array
    {
        $camp = $data['camp'];
        $e = $data['enrollment'];
        $a = $data['attendance'];
        $r = $data['reports'];
        $l = $data['learning'];
        $f = $data['feedback'];
        $days = $data['period']['teaching_days'];

        $summary = sprintf(
            '%s ran from %s%s with %d %s enrolled%s.',
            $camp->name,
            $camp->start_date->format('j M Y'),
            $camp->end_date ? ' to '.$camp->end_date->format('j M Y') : '',
            $e['total'],
            Str::plural('student', $e['total']),
            $days ? ' over '.$days.' teaching '.Str::plural('day', $days) : ''
        );

        if ($a['rate'] !== null) {
            $summary .= sprintf(' Average attendance was %s%%, with about %s students in class each day.', $a['rate'], $a['average_daily']);
        }

        if ($r['total']) {
            $summary .= sprintf(
                ' %d %s submitted %d daily %s across %d %s.',
                count($data['instructors']),
                Str::plural('instructor', count($data['instructors'])),
                $r['total'],
                Str::plural('report', $r['total']),
                count($data['courses']),
                Str::plural('course', count($data['courses']))
            );
        }

        if ($l['average_score'] !== null) {
            $summary .= sprintf(' Students averaged %s%% on %d assessment %s.', $l['average_score'], $l['attempts'], Str::plural('attempt', $l['attempts']));
        }

        $highlights = [];
        $perfect = $data['studentSummary']['perfect'];
        if ($perfect) {
            $highlights[] = $perfect.' '.Str::plural('student', $perfect).' attended every day on time.';
        }
        if ($a['best_day']) {
            $highlights[] = 'Best attended day: '.$a['best_day']['date']->format('l j M').' ('.$a['best_day']['rate'].'%).';
        }
        $topApproaches = collect($r['approaches'])->where('count', '>', 0)->take(3);
        if ($topApproaches->isNotEmpty()) {
            $highlights[] = 'Most used teaching approaches: '.$topApproaches->map(fn ($x) => $x['label'].' ('.$x['count'].')')->implode(', ').'.';
        }
        $lowPassRate = $l['pass_rate'] !== null && $l['pass_rate'] < 50;
        if ($l['pass_rate'] !== null && ! $lowPassRate) {
            $highlights[] = $l['pass_rate'].'% of assessment attempts were passed.';
        }
        if ($l['certificates']) {
            $highlights[] = $l['certificates'].' '.Str::plural('certificate', $l['certificates']).' issued.';
        }
        if ($f['average'] !== null) {
            $highlights[] = 'Students rated lessons '.$f['average'].' out of 5 on average ('.$f['count'].' responses).';
        }
        if ($data['revisions']['total']) {
            $highlights[] = 'Trainers sent '.$data['revisions']['total'].' revised content '.Str::plural('submission', $data['revisions']['total']).' based on this camp.';
        }

        $challenges = collect($r['themes'])->take(4)->map(fn ($theme) => sprintf(
            '%s: raised in %d %s by %d %s.',
            $theme['label'],
            $theme['reports'],
            Str::plural('report', $theme['reports']),
            $theme['instructors'],
            Str::plural('instructor', $theme['instructors'])
        ))->all();
        if ($challenges === []) {
            $challenges = collect($data['notes'])->pluck('challenges')
                ->filter(fn ($text) => $text && ! preg_match(CampReportService::NO_CHALLENGE, $text))
                ->unique()->take(3)->values()->all();
        }
        if ($lowPassRate) {
            $challenges[] = 'Only '.$l['pass_rate'].'% of assessment attempts reached the pass mark.';
        }
        $atRisk = collect($data['students'])->filter(fn ($s) => $s['rate'] !== null && $s['rate'] < CampReportService::AT_RISK_RATE)->count();
        if ($atRisk) {
            $challenges[] = $atRisk.' '.Str::plural('student', $atRisk).' attended less than '.CampReportService::AT_RISK_RATE.'% of days.';
        }
        if ($a['worst_day'] && $a['worst_day']['rate'] < 75) {
            $challenges[] = 'Lowest attendance on '.$a['worst_day']['date']->format('l j M').' ('.$a['worst_day']['rate'].'%).';
        }
        if ($data['issues']['open']) {
            $challenges[] = $data['issues']['open'].' reported '.Str::plural('issue', $data['issues']['open']).' still open.';
        }

        $recommendations = [];
        if ($atRisk) {
            $recommendations[] = 'Call the parents of students with low attendance before the next camp.';
        }
        if ($a['punctuality'] !== null && $a['punctuality'] < 85) {
            $recommendations[] = 'Work on punctuality: '.round(100 - $a['punctuality']).'% of arrivals were late.';
        }
        if ($data['issues']['open']) {
            $recommendations[] = 'Close out the open issues listed in this report.';
        }
        if ($lowPassRate) {
            $recommendations[] = 'Review assessment difficulty and re-teach the topics where most students fell short.';
        }
        $themeActions = [
            'Laptops and devices' => 'Service or replace faulty laptops and keep spare chargers before the next camp.',
            'Internet and power' => 'Arrange backup internet and power, and keep offline activities ready for outages.',
            'Logins and the online platform' => 'Set up and test student accounts before day one and show students how to submit work.',
            'Lab equipment and materials' => 'Restock lab components and replace worn equipment before the next camp.',
        ];
        foreach (collect($r['themes'])->take(3) as $theme) {
            if (isset($themeActions[$theme['label']])) {
                $recommendations[] = $themeActions[$theme['label']];
            }
        }
        if ($r['follow_ups']) {
            $recommendations[] = 'Check the '.$r['follow_ups'].' daily '.Str::plural('report', $r['follow_ups']).' flagged for follow-up.';
        }
        if ($r['total'] && $r['average_approaches'] < 2) {
            $recommendations[] = 'Encourage instructors to use more engagement approaches (rewards, project wall, choice menu, breaks).';
        }
        if ($f['average'] !== null && $f['average'] < 3.5) {
            $recommendations[] = 'Review lessons with low student ratings and update them before the next camp.';
        }
        if ($data['revisions']['pending']) {
            $recommendations[] = 'Review the '.$data['revisions']['pending'].' pending revised content '.Str::plural('submission', $data['revisions']['pending']).'.';
        }
        if ($recommendations === []) {
            $recommendations[] = 'Keep the current approach and share what worked with the next camp team.';
        }

        return [
            'summary' => $summary,
            'highlights' => implode("\n", $highlights),
            'challenges' => implode("\n", $challenges),
            'recommendations' => implode("\n", $recommendations),
        ];
    }

    private function prompt(array $data): string
    {
        $facts = [
            'camp' => $data['camp']->name,
            'dates' => $data['camp']->date_range,
            'teaching_days' => $data['period']['teaching_days'],
            'enrollment' => $data['enrollment'],
            'attendance' => [
                ...collect($data['attendance'])->except(['best_day', 'worst_day'])->all(),
                'best_day' => $data['attendance']['best_day'] ? $data['attendance']['best_day']['date']->format('D j M').' '.$data['attendance']['best_day']['rate'].'%' : null,
                'worst_day' => $data['attendance']['worst_day'] ? $data['attendance']['worst_day']['date']->format('D j M').' '.$data['attendance']['worst_day']['rate'].'%' : null,
            ],
            'students_below_'.CampReportService::AT_RISK_RATE.'pct' => collect($data['students'])->filter(fn ($s) => $s['rate'] !== null && $s['rate'] < CampReportService::AT_RISK_RATE)->count(),
            'attendance_bands' => $data['studentSummary']['bands'],
            'attendance_by_class' => $data['studentSummary']['by_class'],
            'instructors' => collect($data['instructors'])->map(fn ($i) => collect($i)->except('last_report')->all())->all(),
            'daily_reports' => collect($data['reports'])->except('approaches')->all(),
            'teaching_approaches' => $data['reports']['approaches'],
            'issues' => $data['issues'],
            'courses' => $data['courses'],
            'learning' => $data['learning'],
            'student_feedback' => $data['feedback'],
            'revised_content' => $data['revisions'],
        ];

        $notes = collect($data['notes'])->map(fn ($n) => sprintf(
            '- %s | %s | %s | Summary: %s%s%s',
            $n['date']->format('D j M'),
            $n['instructor'] ?? 'Instructor',
            $n['course'] ?? 'Course',
            Str::limit($n['summary'] ?: '-', 180),
            $n['challenges'] ? ' | Challenges: '.Str::limit($n['challenges'], 160) : '',
            $n['issues'] ? ' | Issues: '.Str::limit($n['issues'], 120) : ''
        ));

        // Large camps produce hundreds of notes; keep an even spread across the camp instead of only the first days.
        $step = max(1, (int) ceil($notes->count() / 60));
        $notes = $notes->filter(fn ($line, $i) => $i % $step === 0)->implode("\n");

        return 'You are writing the end-of-camp report for Code Academy Uganda, a coding school for children. '
            ."The readers are the school's admin and supervisors. Use clear, warm, professional British English. "
            ."Base everything only on the facts and instructor notes below. Do not invent numbers or names of students.\n\n"
            ."Return ONLY a JSON object with these keys:\n"
            ."- \"summary\": one paragraph (4-6 sentences) describing how the camp went overall.\n"
            ."- \"highlights\": array of 3-6 short points on what went well.\n"
            ."- \"challenges\": array of 2-6 short points on problems instructors raised or the data shows.\n"
            ."- \"recommendations\": array of 3-6 concrete actions for the next camp.\n\n"
            ."FACTS (JSON):\n".json_encode($facts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n"
            ."daily_reports.themes counts recurring challenges across ALL daily reports; prefer it over single notes when describing challenges.\n\n"
            ."INSTRUCTOR DAILY NOTES (evenly spread sample):\n".Str::limit($notes ?: 'No daily reports were submitted.', 9000)."\n\n"
            .'Report requested at '.now()->toDateTimeString().'.';
    }

    /**
     * @return array{summary: string, highlights: string, challenges: string, recommendations: string}|null
     */
    private function parse(string $content): ?array
    {
        $json = trim(preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content)));
        $start = strpos($json, '{');
        $end = strrpos($json, '}');
        if ($start === false || $end === false) {
            return null;
        }

        $decoded = json_decode(substr($json, $start, $end - $start + 1), true);
        if (! is_array($decoded) || blank($decoded['summary'] ?? null)) {
            return null;
        }

        $lines = fn ($value) => implode("\n", array_filter(array_map(
            fn ($item) => trim((string) $item),
            is_array($value) ? $value : explode("\n", (string) $value)
        )));

        return [
            'summary' => trim((string) $decoded['summary']),
            'highlights' => $lines($decoded['highlights'] ?? []),
            'challenges' => $lines($decoded['challenges'] ?? []),
            'recommendations' => $lines($decoded['recommendations'] ?? []),
        ];
    }
}
