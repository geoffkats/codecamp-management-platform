<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Support\LessonRestyler;
use App\Support\RichContent;
use Illuminate\Support\Facades\Storage;

function restyleLessonFor(string $content, array $attributes = []): Lesson
{
    $course = Course::factory()->create(['instructor_id' => userWithRole('admin')->id]);

    return Lesson::create(array_merge([
        'course_id' => $course->id,
        'title' => 'Day 1: Meet mBot',
        'content' => $content,
        'order' => 1,
    ], $attributes));
}

it('boxes known sections, blockquotes and labelled paragraphs without losing text', function () {
    $html = '<h2>What is mBot?</h2><p>mBot is a robot.</p><p></p>'
        .'<h2>Safety</h2><ul><li>Keep fingers away.</li></ul>'
        .'<h3>Try this</h3><ol><li>Drive a square.</li></ol>'
        .'<h2>Batteries</h2><blockquote><p><strong>Tip:</strong> Charge the batteries.</p></blockquote>'
        .'<p>Note: the robot is aluminium.</p>'
        .'<p><strong>Processor</strong></p><p>The CPU thinks.</p>'
        .'<table><tr><th>Port</th></tr><tr><td>M1</td></tr></table>';

    $restyler = new LessonRestyler;
    $output = $restyler->restyle($html, ['badge' => 'Day 1', 'title' => 'Meet mBot', 'meta' => 'Robotics']);

    expect($output)
        ->toContain('data-block="hero"')
        ->toContain('data-block="warn"')
        ->toContain('data-block="try"')
        ->toContain('data-block="tip"')
        ->toContain('data-block="key"')
        ->toContain('<h3>Processor</h3>')
        ->toContain('border-bottom:3px solid #f97316')
        ->toContain('background:#1e3a8a;color:#ffffff;text-align:left')
        ->not->toContain('<p></p>')
        ->not->toContain('Tip:')
        ->not->toContain('Note:');

    foreach (['mBot is a robot.', 'Keep fingers away.', 'Drive a square.', 'Charge the batteries.', 'the robot is aluminium.', 'The CPU thinks.', 'M1'] as $text) {
        expect($output)->toContain($text);
    }

    expect($restyler->stats())->toMatchArray(['banner' => 1, 'tables' => 1])
        ->and(RichContent::render($output))->toContain('data-block="warn"');
});

it('restyles lessons, skips styled ones and can restore the originals', function () {
    Storage::fake('local');
    $original = '<h2>Safety</h2><ul><li>Keep fingers away.</li></ul>';
    $lesson = restyleLessonFor($original);
    $styled = restyleLessonFor('<div data-block="tip" style="color:#000;"><p>Done</p></div>', ['title' => 'Already styled']);

    $this->artisan('lessons:restyle', ['--lesson' => [$lesson->id, $styled->id], '--dry-run' => true])->assertSuccessful();
    expect($lesson->fresh()->content)->toBe($original);

    $this->artisan('lessons:restyle', ['--lesson' => [$lesson->id, $styled->id]])->assertSuccessful();

    $content = $lesson->fresh()->content;
    expect($content)
        ->toContain('data-block="hero"')
        ->toContain('>Day 1</span>')
        ->toContain('>Meet mBot</h1>')
        ->toContain('data-block="warn"')
        ->and($styled->fresh()->content)->toBe('<div data-block="tip" style="color:#000;"><p>Done</p></div>');

    $runs = Storage::disk('local')->directories('lesson-restyle-backups');
    expect($runs)->toHaveCount(1);

    $this->artisan('lessons:restyle', ['--restore' => basename($runs[0])])->assertSuccessful();
    expect($lesson->fresh()->content)->toBe($original);
});
