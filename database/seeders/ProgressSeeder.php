<?php

namespace Database\Seeders;

use App\Models\CourseEnrollment;
use App\Models\LessonProgress;
use App\Models\StudentLessonProgress;
use App\Models\UserProgress;
use App\Models\VideoProgress;
use Illuminate\Database\Seeder;

class ProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enrollments = CourseEnrollment::with(['user', 'course.lessons'])->get();

        foreach ($enrollments as $enrollment) {
            $user = $enrollment->user;
            $course = $enrollment->course;
            $lessons = $course->lessons;

            // Create course enrollment progress
            UserProgress::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'lesson_id' => null,
                'type' => 'course_enrolled',
                'points_earned' => 50,
                'completed_at' => $enrollment->enrolled_at,
            ]);

            // Create lesson progress for completed lessons
            $completedLessons = $lessons->take($enrollment->lessons_completed);
            
            foreach ($completedLessons as $lesson) {
                LessonProgress::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'lesson_id' => $lesson->id,
                    ],
                    [
                        'is_completed' => true,
                        'completed_at' => now()->subDays(rand(1, 30)),
                        'time_spent' => rand(10, 60),
                    ]
                );

                StudentLessonProgress::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'lesson_id' => $lesson->id,
                        'activity_id' => null,
                    ],
                    [
                        'status' => 'completed',
                        'progress_percentage' => 100,
                        'time_spent_minutes' => rand(15, 45),
                        'attempts' => 1,
                        'started_at' => now()->subDays(rand(1, 30)),
                        'completed_at' => now()->subDays(rand(1, 30)),
                        'last_accessed_at' => now()->subDays(rand(1, 30)),
                    ]
                );

                UserProgress::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'lesson_id' => $lesson->id,
                    'type' => 'lesson_completed',
                    'points_earned' => 25,
                    'completed_at' => now()->subDays(rand(1, 30)),
                ]);

                // Video progress if lesson has video
                if ($lesson->video_url) {
                    VideoProgress::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'lesson_id' => $lesson->id,
                            'video_url' => $lesson->video_url,
                        ],
                        [
                            'duration_seconds' => $lesson->video_duration ?? rand(300, 1800),
                            'watched_seconds' => rand(200, 1800),
                            'progress_percentage' => rand(80, 100),
                            'is_completed' => true,
                            'last_watched_at' => now()->subDays(rand(1, 30)),
                            'watch_count' => rand(1, 3),
                        ]
                    );
                }
            }

            // Create in-progress lessons
            $inProgressLessons = $lessons->skip($enrollment->lessons_completed)->take(rand(1, 2));
            
            foreach ($inProgressLessons as $lesson) {
                StudentLessonProgress::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'lesson_id' => $lesson->id,
                        'activity_id' => null,
                    ],
                    [
                        'status' => 'in_progress',
                        'progress_percentage' => rand(20, 80),
                        'time_spent_minutes' => rand(5, 30),
                        'attempts' => 1,
                        'started_at' => now()->subDays(rand(1, 7)),
                        'last_accessed_at' => now()->subHours(rand(1, 24)),
                    ]
                );

                UserProgress::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'lesson_id' => $lesson->id,
                    'type' => 'lesson_started',
                    'points_earned' => 10,
                ]);
            }
        }
    }
}

