<?php

namespace Database\Seeders;

use App\Models\ContentApproval;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContentApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = User::whereHas('roles', function ($query) {
            $query->where('name', 'teacher');
        })->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No teachers found. Please run UserSeeder first.');
            return;
        }

        // Create pending approvals for courses
        $pendingCourses = Course::where('approval_status', 'pending')->get();
        foreach ($pendingCourses as $course) {
            ContentApproval::updateOrCreate(
                [
                    'approvable_type' => Course::class,
                    'approvable_id' => $course->id,
                ],
                [
                    'status' => 'pending',
                    'submitted_by' => $course->instructor_id,
                    'submitted_at' => $course->submitted_for_approval_at ?? now(),
                    'priority' => ['low', 'normal', 'high'][rand(0, 2)],
                    'category' => 'course',
                ]
            );
        }

        // Create some sample pending approvals for modules
        $pendingModules = CourseModule::where('approval_status', 'pending')->limit(5)->get();
        foreach ($pendingModules as $module) {
            ContentApproval::updateOrCreate(
                [
                    'approvable_type' => CourseModule::class,
                    'approvable_id' => $module->id,
                ],
                [
                    'status' => 'pending',
                    'submitted_by' => $module->course->instructor_id,
                    'submitted_at' => $module->submitted_for_approval_at ?? now(),
                    'priority' => 'normal',
                    'category' => 'module',
                ]
            );
        }

        // Create some sample pending approvals for lessons
        $pendingLessons = Lesson::where('approval_status', 'pending')->limit(10)->get();
        foreach ($pendingLessons as $lesson) {
            ContentApproval::updateOrCreate(
                [
                    'approvable_type' => Lesson::class,
                    'approvable_id' => $lesson->id,
                ],
                [
                    'status' => 'pending',
                    'submitted_by' => $lesson->course->instructor_id,
                    'submitted_at' => $lesson->submitted_for_approval_at ?? now(),
                    'priority' => 'normal',
                    'category' => 'lesson',
                ]
            );
        }
    }
}

