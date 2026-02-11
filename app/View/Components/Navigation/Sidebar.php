<?php

namespace App\View\Components\Navigation;

use Illuminate\View\Component;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class Sidebar extends Component
{
    public $user;
    public $isAdmin;
    public $isSupervisor;
    public $isTeacher;
    public $isIctTeacher;
    public $isCodecampTrainer;
    public $isStudent;
    public $isIctStudent;
    public $isCodecampStudent;
    public $showStudentSection;
    public $showIctStudentSection;
    public $showCodecampStudentSection;
    public $showTeacherSection;
    public $showIctTeacherSection;
    public $showCodecampTeacherSection;
    public $showSupervisorSection;
    public $pendingInvitationsCount;
    public $pendingApprovalCount;
    public $pendingFeedbackCount;
    public $unreadNotificationsCount;

    public function __construct(User $user)
    {
        $this->user = $user;
        
        // Cache user roles to avoid repeated hasRole() calls
        $this->isAdmin = Cache::remember("user_{$user->id}_is_admin", 3600, fn() => $user->isAdmin());
        $this->isSupervisor = Cache::remember("user_{$user->id}_is_supervisor", 3600, fn() => $user->isSupervisor());
        $this->isTeacher = Cache::remember("user_{$user->id}_is_teacher", 3600, fn() => $user->isTeacher());
        $this->isIctTeacher = Cache::remember("user_{$user->id}_is_ict_teacher", 3600, fn() => $user->isIctTeacher());
        $this->isCodecampTrainer = Cache::remember("user_{$user->id}_is_codecamp_trainer", 3600, fn() => $user->isCodecampTrainer());
        $this->isStudent = Cache::remember("user_{$user->id}_is_student", 3600, fn() => $user->isStudent());
        $this->isIctStudent = Cache::remember("user_{$user->id}_is_ict_student", 3600, fn() => $user->isIctStudent());
        $this->isCodecampStudent = Cache::remember("user_{$user->id}_is_codecamp_student", 3600, fn() => $user->isCodecampStudent());
        
        $this->showStudentSection = $this->isStudent && !$this->isAdmin && !$this->isTeacher;
        $this->showIctStudentSection = $this->showStudentSection && $this->isIctStudent;
        $this->showCodecampStudentSection = $this->showStudentSection && !$this->isIctStudent;
        $this->showTeacherSection = $this->isTeacher && !$this->isAdmin;
        $this->showIctTeacherSection = $this->isIctTeacher && !$this->isAdmin;
        $this->showCodecampTeacherSection = $this->showTeacherSection && !$this->isIctTeacher;
        $this->showSupervisorSection = $this->isSupervisor && !$this->isAdmin;
        
        // Cache expensive counts with 5-minute TTL
        $this->pendingInvitationsCount = Cache::remember(
            "user_{$user->id}_pending_invitations",
            300,
            fn() => $this->getPendingInvitationsCount()
        );
        
        $this->pendingApprovalCount = Cache::remember(
            "pending_approval_count",
            300,
            fn() => $this->getPendingApprovalCount()
        );
        
        $this->pendingFeedbackCount = Cache::remember(
            "user_{$user->id}_pending_feedback",
            300,
            fn() => $this->getPendingFeedbackCount()
        );
        
        $this->unreadNotificationsCount = Cache::remember(
            "user_{$user->id}_unread_notifications",
            60,
            fn() => $this->getUnreadNotificationsCount()
        );
    }

    private function getPendingInvitationsCount()
    {
        return \App\Models\CourseInvitation::where('user_id', $this->user->id)
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->count();
    }

    private function getPendingApprovalCount()
    {
        if (!$this->isSupervisor && !$this->isAdmin) {
            return 0;
        }

        return \App\Models\Course::where('approval_status', 'pending')->count() +
               \App\Models\Lesson::where('approval_status', 'pending')->count() +
               \App\Models\Assessment::where('approval_status', 'pending')->count();
    }

    private function getPendingFeedbackCount()
    {
        return \App\Models\TeacherFeedback::where('status', 'pending')->count();
    }

    private function getUnreadNotificationsCount()
    {
        return \App\Models\Notification::where('user_id', $this->user->id)
            ->where('read_at', null)
            ->count();
    }

    public function render()
    {
        return view('components.navigation.sidebar');
    }
}
