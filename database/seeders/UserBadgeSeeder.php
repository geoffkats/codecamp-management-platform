<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Please run UserSeeder first.');
            return;
        }

        $badges = Badge::where('is_active', true)->get();

        if ($badges->isEmpty()) {
            $this->command->warn('No badges found. Please run BadgeSeeder first.');
            return;
        }

        $badgeAssignmentCount = 0;

        foreach ($students as $student) {
            // Each student earns 2-5 random badges
            $earnedBadges = $badges->random(rand(2, min(5, $badges->count())));

            foreach ($earnedBadges as $badge) {
                // Skip if student already has this badge
                if ($student->badges()->where('badges.id', $badge->id)->exists()) {
                    continue;
                }

                // Attach badge with earned date
                $student->badges()->attach($badge->id, [
                    'earned_at' => now()->subDays(rand(1, 60)),
                    'created_at' => now()->subDays(rand(1, 60)),
                    'updated_at' => now()->subDays(rand(1, 60)),
                ]);

                // Award points if badge has points reward
                if ($badge->points_reward > 0) {
                    $userPoint = $student->userPoint;
                    if ($userPoint) {
                        $userPoint->increment('total_points', $badge->points_reward);
                    }
                }

                $badgeAssignmentCount++;
            }
        }

        $this->command->info("Assigned {$badgeAssignmentCount} badges to students.");
    }
}

