<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public User $user;
    
    // Password reset modal
    public $showResetModal = false;
    public $newPassword = null;

    public function mount(User $user)
    {
        $this->user = $user->load(['roles', 'points', 'badges', 'courses', 'enrollments.course']);
        
        // Authorization check
        if (Auth::id() !== $user->id && !Auth::user()->hasAnyRole(['admin'])) {
            abort(403);
        }
    }

    public function render()
    {
        // Get user statistics
        $stats = [
            'courses_created' => $this->user->courses()->count(),
            'courses_enrolled' => $this->user->enrollments()->count(),
            'courses_completed' => $this->user->enrollments()->whereNotNull('completed_at')->count(),
            'badges_earned' => $this->user->badges()->count(),
            'total_points' => $this->user->points->total_points ?? 0,
            'level' => $this->user->points->level ?? 1,
            'lessons_completed' => $this->user->enrollments()->sum('lessons_completed'),
            'average_score' => $this->user->enrollments()->avg('average_quiz_score') ?? 0,
        ];

        // Get recent activity
        $recentActivity = [
            'recent_courses' => $this->user->courses()->latest()->take(5)->get(),
            'recent_enrollments' => $this->user->enrollments()->with('course')->latest()->take(5)->get(),
            'recent_badges' => $this->user->badges()->latest('user_badges.earned_at')->take(5)->get(),
        ];

        // Get leaderboard position
        $leaderboardPosition = \App\Models\UserPoint::where('total_points', '>', $stats['total_points'])->count() + 1;
        $totalUsers = \App\Models\UserPoint::where('total_points', '>', 0)->count();

        return view('livewire.users.show', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'leaderboardPosition' => $leaderboardPosition,
            'totalUsers' => $totalUsers,
        ]);
    }

    public function openResetModal()
    {
        $this->showResetModal = true;
        $this->newPassword = null;
    }

    public function closeResetModal()
    {
        $this->showResetModal = false;
        $this->newPassword = null;
    }

    public function confirmResetPassword()
    {
        // Generate an easier-to-type password (10 characters: letters and numbers only)
        // Format: 3 letters + 2 numbers + 3 letters + 2 numbers (easier to type and remember)
        $newPassword = strtolower(\Illuminate\Support\Str::random(3)) . 
                       rand(10, 99) . 
                       strtolower(\Illuminate\Support\Str::random(3)) . 
                       rand(10, 99);
        
        // Update user's password
        $this->user->update([
            'password' => Hash::make($newPassword)
        ]);
        
        // Store the new password to display in modal
        $this->newPassword = $newPassword;
        
        // Refresh user data
        $this->user->refresh();
    }
}
