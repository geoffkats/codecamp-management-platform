<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Support\LessonRestyler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RestyleLessons extends Command
{
    protected $signature = 'lessons:restyle
        {--course=* : Only lessons of these course ids}
        {--lesson=* : Only these lesson ids}
        {--dry-run : Show what would change without saving}
        {--no-banner : Do not add the lesson banner at the top}
        {--restore= : Put back the original content saved by an earlier run (the run id it printed)}';

    protected $description = 'Give existing lessons the styled look (banner, coloured boxes, section headings, tables) without changing their text';

    private const BACKUP_DIR = 'lesson-restyle-backups';

    public function handle(): int
    {
        if ($this->option('restore')) {
            return $this->restore((string) $this->option('restore'));
        }

        $dryRun = (bool) $this->option('dry-run');
        $runId = now()->format('Ymd-His');

        $ids = Lesson::query()
            ->when($this->option('course'), fn ($q, $courses) => $q->whereIn('course_id', $courses))
            ->when($this->option('lesson'), fn ($q, $lessons) => $q->whereIn('id', $lessons))
            ->orderBy('course_id')->orderBy('order')->orderBy('id')
            ->pluck('id');

        if ($ids->isEmpty()) {
            $this->warn('No lessons matched.');

            return self::SUCCESS;
        }

        $positions = $this->positionsInCourse();
        $rows = [];
        $changed = 0;

        foreach ($ids as $id) {
            $lesson = Lesson::with(['course:id,title', 'module:id,title'])->find($id);
            $content = (string) $lesson->content;

            if (trim(strip_tags($content, '<img><iframe>')) === '') {
                $rows[] = [$lesson->id, Str::limit($lesson->title, 45), 'skipped: empty', ''];

                continue;
            }
            if (LessonRestyler::isStyled($content)) {
                $rows[] = [$lesson->id, Str::limit($lesson->title, 45), 'skipped: already styled', ''];

                continue;
            }
            if (strip_tags($content) === $content) {
                $rows[] = [$lesson->id, Str::limit($lesson->title, 45), 'skipped: plain text', ''];

                continue;
            }

            $badge = 'Lesson '.($positions[$lesson->id] ?? 1);
            $title = trim($lesson->title);
            if (preg_match('/^((?:lesson|day|week|session|class|module)\s*\d+)\s*[:.\-–—]\s*(.+)$/iu', $title, $match)) {
                [, $badge, $title] = $match;
            }

            $restyler = new LessonRestyler;
            $styled = $restyler->restyle($content, $this->option('no-banner') ? null : [
                'badge' => $badge,
                'title' => $title,
                'tagline' => Str::limit(trim(html_entity_decode(strip_tags((string) $lesson->summary))), 180),
                'meta' => collect([
                    $lesson->course?->title,
                    $lesson->module?->title,
                    $lesson->duration_minutes ? $lesson->duration_minutes.' minutes' : null,
                ])->filter()->map(fn ($part) => trim($part))->implode(' · '),
            ]);

            $stats = collect($restyler->stats())->filter()->map(fn ($count, $key) => str_replace('_', ' ', $key).': '.$count)->implode(', ');

            if (! $dryRun) {
                Storage::disk('local')->put(self::BACKUP_DIR."/{$runId}/{$lesson->id}.html", $content);
                $lesson->content = $styled;
                $lesson->save();
            }

            $changed++;
            $rows[] = [$lesson->id, Str::limit($lesson->title, 45), $dryRun ? 'would restyle' : 'restyled', $stats];
        }

        $this->table(['Lesson', 'Title', 'Result', 'Changes'], $rows);

        if ($dryRun) {
            $this->info("[dry run] {$changed} lesson(s) would be restyled. Nothing was saved.");
        } else {
            $this->info("{$changed} lesson(s) restyled. Originals saved under storage/app/private/".self::BACKUP_DIR."/{$runId}.");
            $this->line("To undo this run: php artisan lessons:restyle --restore={$runId}");
        }

        return self::SUCCESS;
    }

    private function restore(string $runId): int
    {
        $files = Storage::disk('local')->files(self::BACKUP_DIR.'/'.basename($runId));
        if ($files === []) {
            $this->error("No backups found for run {$runId}.");

            return self::FAILURE;
        }

        $restored = 0;
        foreach ($files as $file) {
            $lesson = Lesson::find((int) pathinfo($file, PATHINFO_FILENAME));
            if (! $lesson) {
                continue;
            }
            $lesson->content = Storage::disk('local')->get($file);
            $lesson->save();
            $restored++;
        }

        $this->info("Restored {$restored} lesson(s) from run {$runId}.");

        return self::SUCCESS;
    }

    /**
     * @return array<int, int> lesson id => 1-based position within its course
     */
    private function positionsInCourse(): array
    {
        $positions = [];
        Lesson::query()
            ->orderBy('course_id')->orderBy('order')->orderBy('order_index')->orderBy('id')
            ->get(['id', 'course_id'])
            ->groupBy('course_id')
            ->each(function ($lessons) use (&$positions) {
                foreach ($lessons->values() as $index => $lesson) {
                    $positions[$lesson->id] = $index + 1;
                }
            });

        return $positions;
    }
}
