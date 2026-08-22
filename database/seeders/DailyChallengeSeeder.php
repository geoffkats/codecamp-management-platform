<?php

namespace Database\Seeders;

use App\Models\DailyChallenge;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DailyChallengeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $challenges = [
            [
                'title' => 'Complete 3 Lessons Today',
                'description' => 'Complete at least 3 lessons to earn bonus points!',
                'type' => 'lesson_completion',
                'requirements' => ['count' => 3],
                'reward_points' => 100,
                'difficulty_level' => 'easy',
                'category' => 'learning',
            ],
            [
                'title' => 'Perfect Quiz Score',
                'description' => 'Achieve 100% on any quiz today',
                'type' => 'quiz_score',
                'requirements' => ['score' => 100],
                'reward_points' => 150,
                'difficulty_level' => 'medium',
                'category' => 'assessment',
            ],
            [
                'title' => 'Study for 30 Minutes',
                'description' => 'Spend at least 30 minutes learning today',
                'type' => 'study_time',
                'requirements' => ['minutes' => 30],
                'reward_points' => 75,
                'difficulty_level' => 'easy',
                'category' => 'time',
            ],
            [
                'title' => 'Course Progress Boost',
                'description' => 'Complete 20% of any course today',
                'type' => 'course_progress',
                'requirements' => ['percentage' => 20],
                'reward_points' => 200,
                'difficulty_level' => 'hard',
                'category' => 'progress',
            ],
            [
                'title' => 'Forum Participation',
                'description' => 'Post 3 helpful replies or discussions in your course forum (min. 40 characters each; one reply per thread counts).',
                'type' => 'forum_participation',
                'requirements' => ['posts' => 3, 'mode' => 'both', 'min_characters' => 40],
                'reward_points' => 50,
                'difficulty_level' => 'easy',
                'category' => 'social',
            ],
        ];

        // Create challenges for today and next 7 days
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today()->addDays($i);
            
            foreach ($challenges as $challengeData) {
                DailyChallenge::create([
                    'title' => $challengeData['title'],
                    'description' => $challengeData['description'],
                    'type' => $challengeData['type'],
                    'requirements' => $challengeData['requirements'],
                    'reward_points' => $challengeData['reward_points'],
                    'date' => $date,
                    'is_active' => true,
                    'difficulty_level' => $challengeData['difficulty_level'],
                    'category' => $challengeData['category'],
                ]);
            }
        }
    }
}
