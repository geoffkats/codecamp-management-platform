<?php

use App\Livewire\Students\ManageStudents;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\StudentProfile;
use Illuminate\Support\Str;
use Livewire\Livewire;

// student_profiles is MyISAM, so its rows survive the test transaction; keep student IDs unique.
function managedStudent(string $name, string $program = 'codecamp'): StudentProfile
{
    $user = userWithRole('student');

    return StudentProfile::forceCreate([
        'user_id' => $user->id,
        'student_id' => 'MS-'.strtoupper(Str::random(8)),
        'full_name' => $name,
        'parent_guardian_name' => 'Parent',
        'parent_guardian_contact' => '0700000000',
        'program_type' => $program,
        'is_active' => true,
    ]);
}

it('renders the students page with program tabs and the student list', function () {
    $student = managedStudent('Zawadi '.Str::random(5));

    $this->actingAs(userWithRole('admin'))
        ->get(route('students.index', ['search' => $student->full_name]))
        ->assertOk()
        ->assertSee('All programs')
        ->assertSee($student->full_name)
        ->assertSee($student->student_id);
});

it('shows the bulk bar for selected students and assigns them to a course', function () {
    $a = managedStudent('Bulk '.Str::random(5));
    $b = managedStudent('Bulk '.Str::random(5));
    $course = Course::factory()->create();

    Livewire::actingAs(userWithRole('admin'))
        ->test(ManageStudents::class)
        ->set('selected', [(string) $a->id, (string) $b->id])
        ->assertSee('2')
        ->assertSee('Assign course')
        ->assertSee('Export CSV')
        ->call('openAssignModal')
        ->set('selectedCourseId', $course->id)
        ->call('assignSelectedToCourse')
        ->assertHasNoErrors()
        ->assertSet('selected', []);

    expect(CourseEnrollment::where('course_id', $course->id)->pluck('user_id')->sort()->values()->all())
        ->toBe(collect([$a->user_id, $b->user_id])->sort()->values()->all());
});

it('groups the admin sidebar into searchable sections', function () {
    $html = $this->actingAs(userWithRole('admin'))->get(route('students.index'))->assertOk()->getContent();

    expect($html)
        ->toContain('Search menu')
        ->toContain('People')
        ->toContain('Teaching')
        ->toContain('Question Bank')
        ->toContain('aria-current="page"');
});
