<?php

use App\Livewire\Dashboard\InstructorDashboard;
use App\Models\Course;
use App\Models\StudentProfile;
use Livewire\Livewire;

it('shows a trainer their courses, class progress and only links they can open', function () {
    $trainer = userWithRole('codecamp_trainer');
    $trainer->update(['name' => 'Grace Namuli']);
    $course = Course::factory()->create(['title' => 'COMPUTER ESSENTIALS', 'instructor_id' => $trainer->id, 'is_published' => true]);
    $campStudent = function () {
        $student = userWithRole('student');
        StudentProfile::create([
            'user_id' => $student->id,
            'program_type' => 'codecamp',
            'full_name' => $student->name,
            'parent_guardian_name' => 'Parent',
            'parent_guardian_contact' => '0700000000',
        ]);

        return $student->id;
    };
    $course->enrollments()->create(['user_id' => $campStudent(), 'enrolled_at' => now(), 'progress_percentage' => 40]);
    $course->enrollments()->create(['user_id' => $campStudent(), 'enrolled_at' => now(), 'progress_percentage' => 100, 'completed_at' => now()]);

    Livewire::actingAs($trainer)->test(InstructorDashboard::class)
        ->assertSee(', Grace')
        ->assertSee('Computer Essentials')
        ->assertSee('70%')
        ->assertSee('50%')
        ->assertSee('Nothing to mark')
        ->assertSee(route('attendance.code'))
        ->assertDontSee(route('attendance.dashboard'));
});

it('renders the trainer dashboard from the dashboard route', function () {
    $this->actingAs(userWithRole('codecamp_trainer'))->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Quick actions')
        ->assertSee('No courses yet');
});
