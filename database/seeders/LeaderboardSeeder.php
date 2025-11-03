<?php

namespace Database\Seeders;

use App\Models\Leaderboard;
use App\Models\UserPoint;
use App\Models\Course;
use Illuminate\Database\Seeder;

class LeaderboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Overall leaderboard
        $userPoints = UserPoint::with('user')
            ->orderByDesc('total_points')
            ->limit(50)
            ->get();

        foreach ($userPoints as $index => $userPoint) {
            Leaderboard::updateOrCreate(
                [
                    'type' => 'overall',
                    'user_id' => $userPoint->user_id,
                    'period_start' => now()->startOfMonth(),
                    'period_end' => now()->endOfMonth(),
                ],
                [
                    'points' => $userPoint->total_points,
                    'rank' => $index + 1,
                    'last_updated' => now(),
                ]
            );
        }

        // Course-specific leaderboards
        $courses = Course::where('is_published', true)->take(3)->get();

        foreach ($courses as $course) {
            $coursePoints = UserPoint::whereHas('user.enrollments', function ($q) use ($course) {
                $q->where('course_id', $course->id);
            })
            ->orderByDesc('total_points')
            ->limit(20)
            ->get();

            foreach ($coursePoints as $index => $userPoint) {
                Leaderboard::updateOrCreate(
                    [
                        'type' => 'course',
                        'course_id' => $course->id,
                        'user_id' => $userPoint->user_id,
                        'period_start' => now()->startOfMonth(),
                        'period_end' => now()->endOfMonth(),
                    ],
                    [
                        'points' => $userPoint->total_points,
                        'rank' => $index + 1,
                        'last_updated' => now(),
                    ]
                );
            }
        }
    }
}

