<?php

namespace App\Support;

use App\Models\UserPoint;

/**
 * Single source of truth for XP → level number + rank title.
 *
 * Level number: +1 every XP_PER_LEVEL points (keeps progressing forever).
 * Rank title: always derived from total XP, never from the level index
 * (avoids "Level 49 · Beginner" when titles only existed for levels 1–9).
 */
class LevelSystem
{
    public const XP_PER_LEVEL = 100;

    /**
     * Rank bands by total XP.
     * Keys are intentionally stable display ordered.
     *
     * @var array<int, array{min: int, max: int, name: string, color: string, hex: string}>
     */
    public const RANKS = [
        1 => ['min' => 0,     'max' => 200,   'name' => 'Beginner',  'color' => 'gray',   'hex' => '#9CA3AF'],
        2 => ['min' => 201,   'max' => 500,   'name' => 'Explorer',  'color' => 'blue',   'hex' => '#3B82F6'],
        3 => ['min' => 501,   'max' => 1000,  'name' => 'Coder',     'color' => 'teal',   'hex' => '#14B8A6'],
        4 => ['min' => 1001,  'max' => 2000,  'name' => 'Builder',   'color' => 'green',  'hex' => '#22C55E'],
        5 => ['min' => 2001,  'max' => 3500,  'name' => 'Developer', 'color' => 'yellow', 'hex' => '#EAB308'],
        6 => ['min' => 3501,  'max' => 5000,  'name' => 'Pro',       'color' => 'orange', 'hex' => '#F97316'],
        7 => ['min' => 5001,  'max' => 7500,  'name' => 'Expert',    'color' => 'red',    'hex' => '#EF4444'],
        8 => ['min' => 7501,  'max' => 10000, 'name' => 'Master',    'color' => 'purple', 'hex' => '#8B5CF6'],
        9 => ['min' => 10001, 'max' => PHP_INT_MAX, 'name' => 'Legend', 'color' => 'gold', 'hex' => '#D97706'],
    ];

    public static function levelForXp(int|float|null $xp): int
    {
        $xp = max(0, (int) $xp);

        return max(1, (int) floor($xp / self::XP_PER_LEVEL) + 1);
    }

    /**
     * @return array{min: int, max: int, name: string, color: string, hex: string, tier: int}
     */
    public static function rankForXp(int|float|null $xp): array
    {
        $xp = max(0, (int) $xp);
        $matched = self::RANKS[1];
        $tier = 1;

        foreach (self::RANKS as $rankTier => $rank) {
            if ($xp >= $rank['min']) {
                $matched = $rank;
                $tier = $rankTier;
            }
        }

        return $matched + ['tier' => $tier];
    }

    public static function rankName(int|float|null $xp): string
    {
        return self::rankForXp($xp)['name'];
    }

    public static function xpIntoCurrentLevel(int|float|null $xp): int
    {
        return max(0, (int) $xp) % self::XP_PER_LEVEL;
    }

    public static function xpToNextLevel(int|float|null $xp): int
    {
        $into = self::xpIntoCurrentLevel($xp);

        return self::XP_PER_LEVEL - $into;
    }

    /**
     * Progress through the current named rank (0–100).
     */
    public static function rankProgress(int|float|null $xp): array
    {
        $xp = max(0, (int) $xp);
        $rank = self::rankForXp($xp);
        $next = self::RANKS[$rank['tier'] + 1] ?? null;

        if (! $next) {
            return [
                'xp_in_rank' => max(0, $xp - $rank['min']),
                'xp_needed' => 0,
                'progress' => 100,
                'next_name' => null,
                'is_max' => true,
            ];
        }

        $span = max(1, $next['min'] - $rank['min']);
        $into = max(0, $xp - $rank['min']);

        return [
            'xp_in_rank' => $into,
            'xp_needed' => $span,
            'progress' => (int) min(100, round(($into / $span) * 100)),
            'next_name' => $next['name'],
            'is_max' => false,
        ];
    }

    /**
     * Full snapshot used by UI surfaces.
     *
     * @return array{
     *   xp: int,
     *   level: int,
     *   name: string,
     *   color: string,
     *   hex: string,
     *   tier: int,
     *   xp_in_level: int,
     *   xp_to_next_level: int,
     *   level_progress: int,
     *   xp_in_rank: int,
     *   xp_needed: int,
     *   progress: int,
     *   next_name: ?string,
     *   is_max: bool
     * }
     */
    public static function info(int|float|null $xp): array
    {
        $xp = max(0, (int) $xp);
        $rank = self::rankForXp($xp);
        $rankProgress = self::rankProgress($xp);
        $xpInLevel = self::xpIntoCurrentLevel($xp);

        return [
            'xp' => $xp,
            'level' => self::levelForXp($xp),
            'name' => $rank['name'],
            'color' => $rank['color'],
            'hex' => $rank['hex'],
            'tier' => $rank['tier'],
            'xp_in_level' => $xpInLevel,
            'xp_to_next_level' => self::XP_PER_LEVEL - $xpInLevel,
            'level_progress' => (int) min(100, round(($xpInLevel / self::XP_PER_LEVEL) * 100)),
            'xp_in_rank' => $rankProgress['xp_in_rank'],
            'xp_needed' => $rankProgress['xp_needed'],
            'progress' => $rankProgress['progress'],
            'next_name' => $rankProgress['next_name'],
            'is_max' => $rankProgress['is_max'],
        ];
    }

    public static function sync(UserPoint $points): UserPoint
    {
        $info = self::info($points->total_points ?? 0);

        $points->level = $info['level'];
        $points->points_to_next_level = $info['xp_to_next_level'];
        $points->save();

        return $points;
    }
}
