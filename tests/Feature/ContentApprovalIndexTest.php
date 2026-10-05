<?php

use App\Livewire\ContentApprovals\Index;
use App\Models\ContentApproval;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Notification;
use Livewire\Livewire;

function pendingLessonApproval(string $title = 'Loops with Scratch'): array
{
    $trainer = userWithRole('codecamp_trainer');
    $course = Course::factory()->create(['title' => 'Scratch Game Design', 'instructor_id' => $trainer->id]);
    $module = CourseModule::factory()->create(['course_id' => $course->id]);
    $lesson = Lesson::create(['course_id' => $course->id, 'module_id' => $module->id, 'title' => $title, 'order_index' => 1, 'approval_status' => 'pending', 'is_published' => false]);
    $approval = ContentApproval::create([
        'approvable_type' => Lesson::class,
        'approvable_id' => $lesson->id,
        'status' => 'pending',
        'submitted_by' => $trainer->id,
        'submitted_at' => now()->subDays(4),
        'category' => 'lesson',
    ]);

    return [$approval, $lesson, $trainer];
}

it('shows waiting items with their course and trainer', function () {
    [, , $trainer] = pendingLessonApproval();

    $this->actingAs(userWithRole('supervisor'))
        ->get(route('content-approvals.index'))
        ->assertOk()
        ->assertSee('Loops with Scratch')
        ->assertSee('Scratch Game Design')
        ->assertSee($trainer->name)
        ->assertSee('Waiting 4 days');
});

it('approves a waiting lesson, publishes it and tells the trainer', function () {
    [$approval, $lesson, $trainer] = pendingLessonApproval();

    Livewire::actingAs(userWithRole('supervisor'))
        ->test(Index::class)
        ->call('approveContent', $approval->id);

    expect($approval->fresh()->status)->toBe('approved')
        ->and($lesson->fresh()->approval_status)->toBe('approved')
        ->and($lesson->fresh()->is_published)->toBeTrue()
        ->and(Notification::where('user_id', $trainer->id)->where('title', 'Content Approved')->exists())->toBeTrue();
});

it('will not re-approve an item that was already sent back', function () {
    [$approval] = pendingLessonApproval();
    $approval->update(['status' => 'rejected']);

    expect(fn () => Livewire::actingAs(userWithRole('supervisor'))
        ->test(Index::class)
        ->call('approveContent', $approval->id))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    expect($approval->fresh()->status)->toBe('rejected');
});

it('keeps trainers out', function () {
    $this->actingAs(userWithRole('codecamp_trainer'))
        ->get(route('content-approvals.index'))
        ->assertForbidden();
});
