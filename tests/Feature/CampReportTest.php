<?php

use App\Livewire\Admin\Camps\Report;
use App\Models\CampReport;
use App\Models\CodeCamp;
use App\Models\Course;
use App\Models\DailyReport;
use App\Services\GeminiAIService;
use App\Support\RichContent;
use Livewire\Livewire;

function reportCamp(): CodeCamp
{
    $camp = CodeCamp::create([
        'name' => 'August Camp',
        'start_date' => now()->subWeeks(2)->toDateString(),
        'end_date' => now()->subDay()->toDateString(),
        'status' => 'completed',
        'created_by' => userWithRole('admin')->id,
    ]);

    DailyReport::create([
        'camp_id' => $camp->id,
        'report_date' => now()->subWeek()->toDateString(),
        'course_id' => Course::factory()->create()->id,
        'instructor_id' => userWithRole('teacher')->id,
        'status' => 'submitted',
        'summary' => 'Students built a maze game.',
        'challenges' => 'Two laptops would not charge.',
        'submitted_at' => now(),
    ]);

    return $camp;
}

function withoutGemini(): void
{
    app()->instance(GeminiAIService::class, Mockery::mock(GeminiAIService::class, [
        'isConfigured' => false,
    ]));
}

it('lets admins and supervisors open the camp report but not teachers', function () {
    withoutGemini();
    $camp = reportCamp();

    $this->actingAs(userWithRole('admin'))->get(route('admin.camps.report', $camp))
        ->assertOk()
        ->assertSee('August Camp');
    $this->actingAs(userWithRole('supervisor'))->get(route('admin.camps.report', $camp))->assertOk();
    $this->actingAs(userWithRole('teacher'))->get(route('admin.camps.report', $camp))->assertForbidden();

    expect(CampReport::where('camp_id', $camp->id)->value('source'))->toBe('compiled');
});

it('lists camp reports for admins and supervisors only', function () {
    withoutGemini();
    $camp = reportCamp();

    $this->actingAs(userWithRole('supervisor'))->get(route('admin.camp-reports.index'))
        ->assertOk()
        ->assertSee('August Camp')
        ->assertSee(route('admin.camps.report.pdf', $camp));
    $this->actingAs(userWithRole('teacher'))->get(route('admin.camp-reports.index'))->assertForbidden();
});

it('summarises attendance bands and follow-up students instead of listing everyone up front', function () {
    $rows = collect(range(1, 40))->map(fn ($i) => [
        'name' => 'Student '.$i, 'student_id' => 'S'.$i, 'class' => $i % 2 ? 'P.5' : 'P.6',
        'present' => $i <= 10 ? 1 : 5, 'late' => 0, 'absent' => $i <= 10 ? 4 : 0, 'days' => 5,
        'rate' => $i <= 10 ? 20 : 100, 'avg_score' => null,
    ])->all();

    $summary = (fn () => $this->studentSummary($rows))->call(app(\App\Services\Reports\CampReportService::class));

    expect($summary['follow_up'])->toHaveCount(10)
        ->and($summary['perfect'])->toBe(30)
        ->and(collect($summary['bands'])->firstWhere('label', 'Excellent')['count'])->toBe(30)
        ->and($summary['by_class'])->toHaveCount(2);
});

it('downloads the camp report as a PDF', function () {
    withoutGemini();
    $camp = reportCamp();

    $response = $this->actingAs(userWithRole('admin'))->get(route('admin.camps.report.pdf', $camp));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');

    $this->actingAs(userWithRole('teacher'))->get(route('admin.camps.report.pdf', $camp))->assertForbidden();
});

it('rewrites the camp feedback with AI and lets the admin edit it', function () {
    $gemini = Mockery::mock(GeminiAIService::class);
    $gemini->shouldReceive('isConfigured')->andReturn(true);
    $gemini->shouldReceive('generateContent')->andReturn([
        'success' => true,
        'content' => "```json\n".json_encode([
            'summary' => 'A strong camp with steady attendance.',
            'highlights' => ['Maze games shipped'],
            'challenges' => ['Laptop charging'],
            'recommendations' => ['Bring spare chargers'],
        ])."\n```",
    ]);
    app()->instance(GeminiAIService::class, $gemini);

    $camp = reportCamp();

    Livewire::actingAs(userWithRole('admin'))
        ->test(Report::class, ['camp' => $camp])
        ->call('generate')
        ->assertSee('A strong camp with steady attendance.')
        ->call('startEditing')
        ->set('summary', 'Edited by the admin.')
        ->call('save')
        ->assertHasNoErrors();

    $report = CampReport::where('camp_id', $camp->id)->first();
    expect($report->summary)->toBe('Edited by the admin.')
        ->and($report->source)->toBe('manual')
        ->and($report->recommendations)->toContain('Bring spare chargers');
});

it('turns Scratch code blocks and plain-text scripts in lesson content into renderable blocks', function () {
    $html = RichContent::render('<pre><code class="language-scratch">when flag clicked
move (10) steps</code></pre><pre><code class="language-python">print(1)</code></pre>');

    expect($html)->toContain('data-scratch')
        ->toContain('<pre data-source="">when flag clicked')
        ->toContain('language-python');

    expect(RichContent::render("when flag clicked\nforever\nmove 10 steps\nend"))
        ->toContain('data-scratch')
        ->toContain('move (10) steps');
});
