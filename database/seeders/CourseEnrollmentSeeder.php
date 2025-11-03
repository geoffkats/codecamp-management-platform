<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseEnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })->get();

        $courses = Course::where('is_published', true)->get();

        if ($students->isEmpty() || $courses->isEmpty()) {
            $this->command->warn('No students or published courses found. Please run UserSeeder and CourseSeeder first.');
            return;
        }

        foreach ($students as $student) {
            // Each student enrolls in 2-4 random courses
            $enrolledCourses = $courses->random(rand(2, min(4, $courses->count())));

            foreach ($enrolledCourses as $course) {
                $enrollment = CourseEnrollment::updateOrCreate(
                    [
                        'user_id' => $student->id,
                        'course_id' => $course->id,
                    ],
                    [
                        'enrolled_at' => now()->subDays(rand(1, 30)),
                        'progress_percentage' => rand(0, 100),
                        'lessons_completed' => rand(0, 10),
                        'quizzes_completed' => rand(0, 5),
                        'average_quiz_score' => rand(60, 95),
                    ]
                );

                // Randomly complete some enrollments
                if (rand(0, 100) > 70 && $enrollment->progress_percentage >= 100) {
                    $enrollment->update([
                        'completed_at' => now()->subDays(rand(1, 10)),
                    ]);
                }
            }
        }
    }
}

