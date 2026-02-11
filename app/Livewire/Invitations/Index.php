<?php

namespace App\Livewire\Invitations;

use App\Models\CourseInvitation;
use App\Models\CourseEnrollment;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $filter = 'pending'; // 'pending', 'accepted', 'declined', 'expired', 'all'

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function acceptInvitation($invitationId)
    {
        $invitation = CourseInvitation::where('user_id', Auth::id())
            ->findOrFail($invitationId);

        // Check if already enrolled
        $existingEnrollment = CourseEnrollment::where('user_id', Auth::id())
            ->where('course_id', $invitation->course_id)
            ->first();

        if ($existingEnrollment) {
            session()->flash('error', 'You are already enrolled in this course.');
            // Mark invitation as accepted anyway
            $invitation->update(['status' => 'accepted', 'responded_at' => now()]);
            return;
        }

        // Check if invitation is expired
        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);
            session()->flash('error', 'This invitation has expired.');
            return;
        }

        // Check if course has max students limit
        $course = $invitation->course;
        if ($course->max_students) {
            $currentEnrollments = CourseEnrollment::where('course_id', $course->id)->count();
            if ($currentEnrollments >= $course->max_students) {
                session()->flash('error', 'This course has reached maximum enrollment capacity.');
                return;
            }
        }

        // Accept invitation
        $invitation->accept();

        // Get course for later use
        $course = $invitation->course;

        // Create enrollment
        CourseEnrollment::create([
            'user_id' => Auth::id(),
            'course_id' => $invitation->course_id,
            'enrolled_at' => now(),
            'progress_percentage' => 0,
        ]);

        // Award enrollment points (safe from duplicates)
        $pointsService = app(\App\Services\PointsService::class);
        $pointsService->awardEnrollmentPoints(Auth::id(), $invitation->course_id);

        session()->flash('message', 'Invitation accepted! You are now enrolled in "' . $course->title . '"');
        $this->dispatch('invitation-accepted');
    }

    public function declineInvitation($invitationId)
    {
        $invitation = CourseInvitation::where('user_id', Auth::id())
            ->findOrFail($invitationId);

        if ($invitation->status !== 'pending') {
            session()->flash('error', 'This invitation has already been responded to.');
            return;
        }

        $invitation->decline();

        session()->flash('message', 'Invitation declined.');
    }

    public function render()
    {
        $query = CourseInvitation::where('user_id', Auth::id())
            ->with(['course', 'inviter']);

        // Filter by status
        if ($this->filter !== 'all') {
            if ($this->filter === 'pending') {
                $query->where('status', 'pending')
                      ->where(function($q) {
                          $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                      });
            } elseif ($this->filter === 'accepted') {
                $query->where('status', 'accepted');
            } elseif ($this->filter === 'declined') {
                $query->where('status', 'declined');
            } elseif ($this->filter === 'expired') {
                $query->where(function($q) {
                    $q->where('status', 'expired')
                      ->orWhere(function($query) {
                          $query->where('status', 'pending')
                                ->whereNotNull('expires_at')
                                ->where('expires_at', '<=', now());
                      });
                });
            }
        }

        $invitations = $query->orderByDesc('invited_at')
            ->paginate(10);

        // Calculate stats
        $stats = [
            'pending' => CourseInvitation::where('user_id', Auth::id())
                ->where('status', 'pending')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->count(),
            'accepted' => CourseInvitation::where('user_id', Auth::id())
                ->where('status', 'accepted')
                ->count(),
            'declined' => CourseInvitation::where('user_id', Auth::id())
                ->where('status', 'declined')
                ->count(),
            'expired' => CourseInvitation::where('user_id', Auth::id())
                ->where('status', 'pending')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count(),
        ];

        return view('livewire.invitations.index', [
            'invitations' => $invitations,
            'stats' => $stats,
        ]);
    }
}
