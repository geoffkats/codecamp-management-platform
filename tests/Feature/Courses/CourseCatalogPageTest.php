<?php

use App\Livewire\Courses\Index;
use App\Models\Course;
use Livewire\Livewire;

it('lets admins see draft and pending courses in the catalogue', function () {
    $live = Course::factory()->create(['title' => 'Live Course X1', 'is_published' => true, 'approval_status' => 'approved']);
    $draft = Course::factory()->create(['title' => 'Draft Course X2', 'is_published' => false, 'approval_status' => 'draft']);

    Livewire::actingAs(userWithRole('admin'))
        ->test(Index::class)
        ->assertSee($live->title)
        ->assertSee($draft->title)
        ->call('setStatus', 'draft')
        ->assertSee($draft->title)
        ->assertDontSee($live->title)
        ->call('setView', 'list')
        ->assertSet('viewMode', 'list')
        ->assertSee($draft->title);
});

it('still limits teachers to their own courses', function () {
    $teacher = userWithRole('teacher');
    Course::factory()->create(['title' => 'Mine X3', 'instructor_id' => $teacher->id, 'is_published' => false]);
    Course::factory()->create(['title' => 'Someone Else Draft X4', 'is_published' => false]);

    Livewire::actingAs($teacher)
        ->test(Index::class)
        ->assertSee('Mine X3')
        ->assertDontSee('Someone Else Draft X4');
});
