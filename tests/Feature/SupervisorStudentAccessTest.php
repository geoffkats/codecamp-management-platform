<?php

use App\Models\StudentProfile;

it('lets a supervisor open the student list and a student profile', function () {
    $supervisor = userWithRole('supervisor');
    $student = userWithRole('student');
    $profile = StudentProfile::create([
        'user_id' => $student->id,
        'program_type' => 'codecamp',
        'full_name' => 'Amina Nakato',
        'parent_guardian_name' => 'Parent',
        'parent_guardian_contact' => '0700000000',
    ]);

    $this->actingAs($supervisor)->get(route('students.index'))->assertOk();
    $this->actingAs($supervisor)->get(route('students.show', $profile))->assertOk()->assertSee('Amina Nakato');
});

it('still keeps students out of the student management pages', function () {
    $this->actingAs(userWithRole('student'))->get(route('students.index'))->assertForbidden();
});
