<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\User;
use Illuminate\Database\Seeder;

class DiscussionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::where('is_published', true)->get();
        $students = User::whereHas('roles', function ($q) {
            $q->where('name', 'student');
        })->get();
        $teachers = User::whereHas('roles', function ($q) {
            $q->where('name', 'teacher');
        })->get();

        if ($courses->isEmpty() || $students->isEmpty()) {
            $this->command->warn('Need courses and students to create discussions.');
            return;
        }

        foreach ($courses as $course) {
            // Create course-wide discussions
            for ($i = 0; $i < 3; $i++) {
                $student = $students->random();
                
                $discussion = Discussion::create([
                    'course_id' => $course->id,
                    'lesson_id' => null,
                    'user_id' => $student->id,
                    'title' => 'Question about ' . $course->title . ' - ' . ($i + 1),
                    'content' => 'I have a question about this course. Can someone help me understand ' . $course->title . ' better?',
                    'is_pinned' => $i === 0,
                    'status' => 'active',
                    'views_count' => rand(10, 100),
                    'replies_count' => 0,
                ]);

                // Add some replies
                $replyCount = rand(2, 5);
                for ($j = 0; $j < $replyCount; $j++) {
                    $replyUser = (rand(0, 1) && !$teachers->isEmpty()) 
                        ? $teachers->random() 
                        : $students->random();
                    
                    $isTeacher = $replyUser->roles()->where('name', 'teacher')->exists();
                    
                    DiscussionReply::create([
                        'discussion_id' => $discussion->id,
                        'user_id' => $replyUser->id,
                        'parent_id' => null,
                        'content' => 'Great question! Here is my answer and some helpful information...',
                        'is_solution' => $j === 0 && $isTeacher,
                        'likes_count' => rand(0, 10),
                    ]);
                }

                $discussion->update([
                    'replies_count' => $replyCount,
                    'last_reply_at' => now(),
                    'last_reply_by' => DiscussionReply::where('discussion_id', $discussion->id)
                        ->latest()
                        ->first()
                        ?->user_id,
                ]);
            }

            // Create lesson-specific discussions
            foreach ($course->lessons->take(2) as $lesson) {
                $student = $students->random();
                
                $discussion = Discussion::create([
                    'course_id' => $course->id,
                    'lesson_id' => $lesson->id,
                    'user_id' => $student->id,
                    'title' => 'Help with ' . $lesson->title,
                    'content' => 'I\'m having trouble understanding ' . $lesson->title . '. Can someone explain this concept?',
                    'status' => 'active',
                    'views_count' => rand(5, 50),
                    'replies_count' => rand(1, 3),
                ]);
            }
        }
    }
}
