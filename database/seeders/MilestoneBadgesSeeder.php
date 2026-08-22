<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class MilestoneBadgesSeeder extends Seeder
{
    /**
     * Milestone badges tied to real student actions.
     * Criteria types used:
     *   first_lesson, streak_days, challenges_done, course_complete,
     *   night_owl, speed_demon, kudos_given, kudos_received,
     *   weekly_competition_winner
     */
    public function run(): void
    {
        $badges = [
            // ── Lesson milestone ──────────────────────────────────────────────
            [
                'name'          => 'First Step',
                'slug'          => 'first-step',
                'description'   => 'Complete your very first lesson.',
                'icon'          => '🚀',
                'color'         => '#F97316',
                'criteria'      => ['type' => 'lesson_count', 'count' => 1],
                'points_reward' => 50,
            ],
            [
                'name'          => 'Quick Learner',
                'slug'          => 'quick-learner',
                'description'   => 'Complete 10 lessons.',
                'icon'          => '📚',
                'color'         => '#3B82F6',
                'criteria'      => ['type' => 'lesson_count', 'count' => 10],
                'points_reward' => 100,
            ],
            [
                'name'          => 'Knowledge Seeker',
                'slug'          => 'knowledge-seeker',
                'description'   => 'Complete 25 lessons.',
                'icon'          => '🧠',
                'color'         => '#8B5CF6',
                'criteria'      => ['type' => 'lesson_count', 'count' => 25],
                'points_reward' => 200,
            ],

            // ── Streak badges ─────────────────────────────────────────────────
            [
                'name'          => 'Hot Streak',
                'slug'          => 'hot-streak',
                'description'   => 'Study 5 days in a row.',
                'icon'          => '🔥',
                'color'         => '#EF4444',
                'criteria'      => ['type' => 'streak_days', 'days' => 5],
                'points_reward' => 150,
            ],
            [
                'name'          => 'Streak Master',
                'slug'          => 'streak-master',
                'description'   => 'Study 14 days in a row.',
                'icon'          => '⚡',
                'color'         => '#F59E0B',
                'criteria'      => ['type' => 'streak_days', 'days' => 14],
                'points_reward' => 300,
            ],

            // ── Challenge badges ──────────────────────────────────────────────
            [
                'name'          => 'Challenge Accepted',
                'slug'          => 'challenge-accepted',
                'description'   => 'Complete your first daily challenge.',
                'icon'          => '🎯',
                'color'         => '#10B981',
                'criteria'      => ['type' => 'challenges_done', 'count' => 1],
                'points_reward' => 50,
            ],
            [
                'name'          => 'Challenge Champ',
                'slug'          => 'challenge-champ',
                'description'   => 'Complete 10 daily challenges.',
                'icon'          => '💪',
                'color'         => '#059669',
                'criteria'      => ['type' => 'challenges_done', 'count' => 10],
                'points_reward' => 200,
            ],

            // ── Course completion ─────────────────────────────────────────────
            [
                'name'          => 'Course Complete',
                'slug'          => 'course-complete',
                'description'   => 'Finish all lessons in a course.',
                'icon'          => '🎓',
                'color'         => '#6366F1',
                'criteria'      => ['type' => 'course_complete', 'count' => 1],
                'points_reward' => 500,
            ],

            // ── Time-based badges ─────────────────────────────────────────────
            [
                'name'          => 'Night Owl',
                'slug'          => 'night-owl',
                'description'   => 'Study after 10 PM.',
                'icon'          => '🦉',
                'color'         => '#1E1B4B',
                'criteria'      => ['type' => 'night_owl'],
                'points_reward' => 75,
            ],
            [
                'name'          => 'Speed Demon',
                'slug'          => 'speed-demon',
                'description'   => 'Complete 3 lessons in a single day.',
                'icon'          => '⚡',
                'color'         => '#DC2626',
                'criteria'      => ['type' => 'speed_demon', 'lessons_per_day' => 3],
                'points_reward' => 150,
            ],
            [
                'name'          => 'Early Bird',
                'slug'          => 'early-bird',
                'description'   => 'Study before 8 AM.',
                'icon'          => '🌅',
                'color'         => '#F59E0B',
                'criteria'      => ['type' => 'early_bird'],
                'points_reward' => 75,
            ],

            // ── Social badges ─────────────────────────────────────────────────
            [
                'name'          => 'Kind Soul',
                'slug'          => 'kind-soul',
                'description'   => 'Give kudos to 5 different classmates.',
                'icon'          => '👏',
                'color'         => '#EC4899',
                'criteria'      => ['type' => 'kudos_given', 'count' => 5],
                'points_reward' => 50,
            ],
            [
                'name'          => 'Class Favourite',
                'slug'          => 'class-favourite',
                'description'   => 'Receive kudos from 10 different classmates.',
                'icon'          => '🌟',
                'color'         => '#F59E0B',
                'criteria'      => ['type' => 'kudos_received', 'count' => 10],
                'points_reward' => 100,
            ],

            // ── Competition badge ─────────────────────────────────────────────
            [
                'name'          => 'Week Winner',
                'slug'          => 'week-winner',
                'description'   => 'Win a weekly challenge competition.',
                'icon'          => '🏆',
                'color'         => '#D97706',
                'criteria'      => ['type' => 'weekly_competition_winner'],
                'points_reward' => 500,
            ],
        ];

        foreach ($badges as $data) {
            Badge::updateOrCreate(
                ['slug' => $data['slug']],
                $data + ['is_active' => true]
            );
        }

        $this->command->info('Milestone badges seeded: ' . count($badges) . ' badges.');
    }
}
