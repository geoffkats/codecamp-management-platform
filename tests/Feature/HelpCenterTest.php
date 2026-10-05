<?php

use App\Support\Help\HelpCenter;

it('lets staff open the manual and its guides', function () {
    $trainer = userWithRole('codecamp_trainer');

    $this->actingAs($trainer)->get(route('help.index'))
        ->assertOk()
        ->assertSee("What's new", false)
        ->assertSee('Building a quiz or assessment');

    $this->actingAs($trainer)->get(route('help.panel'))->assertOk()->assertSee('Help &amp; What', false);
    $this->actingAs($trainer)->get(route('help.show', 'building-assessments'))->assertOk();
});

it('keeps students out of the staff manual', function () {
    $student = userWithRole('student');

    $this->actingAs($student)->get(route('help.index'))->assertForbidden();
    $this->actingAs($student)->get(route('help.panel'))->assertForbidden();
});

it('only shows guides written for the user role', function () {
    $help = app(HelpCenter::class);
    $supervisor = userWithRole('supervisor');
    $admin = userWithRole('admin');

    expect($help->guides($supervisor)->pluck('slug'))->toContain('grading-and-results')
        ->not->toContain('question-bank');

    expect($help->guides($admin)->pluck('slug'))->toContain('question-bank', 'grading-and-results');

    $this->actingAs($supervisor)->get(route('help.show', 'question-bank'))->assertNotFound();
});

it('lists the newest updates first with a key for the unseen dot', function () {
    $help = app(HelpCenter::class);
    $admin = userWithRole('admin');
    $updates = $help->updates($admin);

    expect($updates)->not->toBeEmpty()
        ->and($help->latestUpdateKey($admin))->toBe($updates->first()['date'].'/'.$updates->first()['slug'])
        ->and($updates->pluck('date')->all())->toBe($updates->pluck('date')->sortDesc()->values()->all());
});

it('only links manual images that exist', function () {
    $files = array_merge(glob(resource_path('docs/manual/guides/*.md')), glob(resource_path('docs/manual/whats-new/*.md')));
    $missing = [];

    foreach ($files as $file) {
        $text = file_get_contents($file);
        preg_match_all('/\]\(manual:([^)\s]+)\)|^image:\s*(\S+)/m', $text, $m);
        foreach (array_filter(array_merge($m[1], $m[2])) as $image) {
            if (! file_exists(public_path('docs/manual/'.$image))) {
                $missing[] = basename($file).' → '.$image;
            }
        }
    }

    expect($missing)->toBe([]);
});

it('shows the help tab in the app layout for staff only', function () {
    $this->actingAs(userWithRole('admin'))->get(route('dashboard'))->assertSee('Open help and what', false);
    $this->actingAs(userWithRole('student'))->get(route('dashboard'))->assertDontSee('Open help and what', false);
});
