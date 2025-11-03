<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $notificationTypes = [
            'success' => [
                'title' => 'Welcome to Your New Course!',
                'message' => 'You have successfully enrolled in {course_name}. Start learning now!',
            ],
            'achievement' => [
                'title' => 'Badge Earned! 🏆',
                'message' => 'Congratulations! You have earned the {badge_name} badge.',
            ],
            'success' => [
                'title' => 'Quiz Completed',
                'message' => 'You have completed {quiz_name} with a score of {score}%.',
            ],
            'info' => [
                'title' => 'Assignment Graded',
                'message' => 'Your assignment "{assignment_name}" has been graded. Check your score!',
            ],
            'achievement' => [
                'title' => 'Course Completed! 🎉',
                'message' => 'Congratulations! You have completed {course_name}. You can now download your certificate.',
            ],
            'reminder' => [
                'title' => 'New Daily Challenge Available',
                'message' => 'A new daily challenge is available. Complete it to earn bonus points!',
            ],
            'system' => [
                'title' => 'System Update',
                'message' => 'New features have been added to the platform. Check them out!',
            ],
        ];

        $notificationCount = 0;

        foreach ($users as $user) {
            // Create 3-8 notifications per user
            $numberOfNotifications = rand(3, 8);

            for ($i = 0; $i < $numberOfNotifications; $i++) {
                // Map to valid notification types
                $typeKeys = array_keys($notificationTypes);
                $typeIndex = array_rand($typeKeys);
                $type = $typeKeys[$typeIndex];
                $template = $notificationTypes[$type];
                
                // Create notifications from different time periods
                $createdAt = now()->subDays(rand(0, 30))->subHours(rand(0, 23));
                
                Notification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $template['title'],
                    'message' => $this->replacePlaceholders($template['message']),
                    'is_read' => rand(0, 100) > 40, // 60% read rate
                    'read_at' => rand(0, 100) > 40 ? $createdAt->addHours(rand(1, 24)) : null,
                    'data' => [
                        'type' => $type,
                        'action_url' => '/dashboard',
                    ],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $notificationCount++;
            }
        }

        $this->command->info("Created {$notificationCount} notifications for users.");
    }

    private function replacePlaceholders(string $message): string
    {
        $replacements = [
            '{course_name}' => ['Introduction to Web Development', 'Advanced JavaScript', 'React Mastery', 'Python Basics'][rand(0, 3)],
            '{badge_name}' => ['Quick Learner', 'Quiz Champion', 'Course Master'][rand(0, 2)],
            '{quiz_name}' => ['HTML Fundamentals Quiz', 'CSS Basics Quiz', 'JavaScript Quiz'][rand(0, 2)],
            '{assignment_name}' => ['Project Assignment', 'Final Assignment', 'Weekly Assignment'][rand(0, 2)],
            '{score}' => rand(65, 100),
        ];

        foreach ($replacements as $placeholder => $replacement) {
            $message = str_replace($placeholder, $replacement, $message);
        }

        return $message;
    }
}

