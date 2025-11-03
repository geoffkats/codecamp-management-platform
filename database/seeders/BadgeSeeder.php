<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => 'First Steps',
                'slug' => 'first-steps',
                'description' => 'Complete your first lesson',
                'icon' => 'star',
                'color' => '#3B82F6',
                'criteria' => [
                    'type' => 'lesson_completion',
                    'count' => 1,
                ],
                'points_reward' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Quick Learner',
                'slug' => 'quick-learner',
                'description' => 'Complete 5 lessons',
                'icon' => 'bolt',
                'color' => '#10B981',
                'criteria' => [
                    'type' => 'lesson_completion',
                    'count' => 5,
                ],
                'points_reward' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Course Master',
                'slug' => 'course-master',
                'description' => 'Complete your first course',
                'icon' => 'trophy',
                'color' => '#F59E0B',
                'criteria' => [
                    'type' => 'course_completion',
                    'count' => 1,
                ],
                'points_reward' => 200,
                'is_active' => true,
            ],
            [
                'name' => 'Quiz Champion',
                'slug' => 'quiz-champion',
                'description' => 'Score 100% on 5 quizzes',
                'icon' => 'academic-cap',
                'color' => '#8B5CF6',
                'criteria' => [
                    'type' => 'perfect_quiz_score',
                    'count' => 5,
                ],
                'points_reward' => 150,
                'is_active' => true,
            ],
            [
                'name' => 'Streak Starter',
                'slug' => 'streak-starter',
                'description' => 'Maintain a 7-day learning streak',
                'icon' => 'fire',
                'color' => '#EF4444',
                'criteria' => [
                    'type' => 'learning_streak',
                    'days' => 7,
                ],
                'points_reward' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Level 10',
                'slug' => 'level-10',
                'description' => 'Reach level 10',
                'icon' => 'chart-bar',
                'color' => '#6366F1',
                'criteria' => [
                    'type' => 'level',
                    'level' => 10,
                ],
                'points_reward' => 300,
                'is_active' => true,
            ],
            [
                'name' => 'Perfect Score',
                'slug' => 'perfect-score',
                'description' => 'Achieve 100% on any assessment',
                'icon' => 'star',
                'color' => '#FCD34D',
                'criteria' => [
                    'type' => 'perfect_score',
                    'count' => 1,
                ],
                'points_reward' => 75,
                'is_active' => true,
            ],
            [
                'name' => 'Discussion Starter',
                'slug' => 'discussion-starter',
                'description' => 'Post 10 discussion replies',
                'icon' => 'chat-bubble-left-right',
                'color' => '#06B6D4',
                'criteria' => [
                    'type' => 'discussion_posts',
                    'count' => 10,
                ],
                'points_reward' => 50,
                'is_active' => true,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['slug' => $badge['slug']],
                $badge
            );
        }
    }
}

