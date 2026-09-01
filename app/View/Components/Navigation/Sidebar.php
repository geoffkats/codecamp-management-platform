<?php

namespace App\View\Components\Navigation;

use App\Models\User;
use App\Services\TrainerSubmissionQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

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
    public $isCodeClubStudent;
    public $showStudentSection;
    public $showIctStudentSection;
    public $showCodecampStudentSection;
    public $showCodeClubStudentSection;
    public $showTeacherSection;
    public $showIctTeacherSection;
    public $showCodecampTeacherSection;
    public $showCodeClubFacilitatorSection;
    public $showDualProgramSwitcher;
    public $activeProgramContext;
    public $showSupervisorSection;

    public $pendingInvitationsCount;
    public $pendingApprovalCount;
    public $pendingFeedbackCount;
    public $pendingSubmissionsCount;
    public $unreadNotificationsCount;

    /** @var array<int, array<string, mixed>> */
    public array $adminPrimaryNav = [];

    /** @var array<int, array<string, mixed>> */
    public array $adminProgramsNav = [];

    /** @var array<int, array<string, mixed>> */
    public array $adminMoreNav = [];

    /** @var array<int, array<string, mixed>> */
    public array $codecampTeacherNav = [];

    /** @var array<int, array<string, mixed>> */
    public array $ictTeacherNav = [];

    /** @var array<int, array<string, mixed>> */
    public array $supervisorNav = [];

    /** @var array<int, array<string, mixed>> */
    public array $operationsManagerNav = [];

    /** @var array<int, array<string, mixed>> */
    public array $codecampStudentNav = [];

    public array $codeclubFacilitatorNav = [];

    /** @var array<int, array<string, mixed>> */
    public array $codeclubStudentNav = [];

    /** @var array<int, array<string, mixed>> */
    public array $ictStudentNav = [];

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->loadNavigationConfig();
        
        // Cache user roles to avoid repeated hasRole() calls
        $this->isAdmin = Cache::remember("user_{$user->id}_is_admin", 3600, fn() => $user->isAdmin());
        $this->isSupervisor = Cache::remember("user_{$user->id}_is_supervisor", 3600, fn() => $user->isSupervisor());
        $this->isTeacher = Cache::remember("user_{$user->id}_is_teacher", 3600, fn() => $user->isTeacher());
        $this->isIctTeacher = Cache::remember("user_{$user->id}_is_ict_teacher", 3600, fn() => $user->isIctTeacher());
        $this->isCodecampTrainer = Cache::remember("user_{$user->id}_is_codecamp_trainer", 3600, fn() => $user->isCodecampTrainer());
        $this->isStudent = Cache::remember("user_{$user->id}_is_student", 3600, fn() => $user->isStudent());
        $this->isIctStudent = Cache::remember("user_{$user->id}_is_ict_student", 3600, fn() => $user->isIctStudent());
        $this->isCodecampStudent = Cache::remember("user_{$user->id}_is_codecamp_student", 3600, fn() => $user->isCodecampStudent());
        $this->isCodeClubStudent = Cache::remember("user_{$user->id}_is_codeclub_student", 3600, fn() => $user->isCodeClubStudent());
        $this->activeProgramContext = $user->activeProgramContext();
        $this->showDualProgramSwitcher = $user->hasDualProgramAccess();
        
        $this->showStudentSection = $this->isStudent && !$this->isAdmin && !$this->isTeacher;
        $this->showIctStudentSection = $this->showStudentSection && $this->isIctStudent;
        $this->showCodeClubStudentSection = $this->showStudentSection && $this->isCodeClubStudent && config('features.code_club', false);
        $this->showCodecampStudentSection = $this->showStudentSection && $this->isCodecampStudent && !$this->isCodeClubStudent;
        $this->showTeacherSection = $this->isTeacher && !$this->isAdmin;
        $this->showIctTeacherSection = $this->isIctTeacher && !$this->isAdmin;
        $this->showCodeClubFacilitatorSection = config('features.code_club', false)
            && $user->hasCodeClubAccess()
            && !$this->isAdmin
            && !$this->isIctTeacher
            && (!$this->isCodecampTrainer || $this->activeProgramContext === 'codeclub');
        $this->showCodecampTeacherSection = $this->showTeacherSection
            && !$this->isIctTeacher
            && (!$user->hasCodeClubAccess() || ($this->isCodecampTrainer && $this->activeProgramContext === 'codecamp'));
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

        $this->pendingSubmissionsCount = 0;
        if ($this->isAdmin || $this->isSupervisor || $this->showCodecampTeacherSection || $this->showCodeClubFacilitatorSection) {
            $this->pendingSubmissionsCount = app(TrainerSubmissionQueue::class)->cachedPendingCount($user);
        }
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

    private function loadNavigationConfig(): void
    {
        $nav = $this->resolveNavigationConfig();

        $this->adminPrimaryNav = $this->navItems($nav, 'admin.primary');
        $this->adminProgramsNav = $this->navItems($nav, 'admin.programs');
        $this->adminMoreNav = $this->navItems($nav, 'admin.more');
        $this->codecampTeacherNav = $this->navItems($nav, 'codecamp_teacher');
        $this->ictTeacherNav = $this->navItems($nav, 'ict_teacher');
        $this->supervisorNav = $this->navItems($nav, 'supervisor');
        $this->operationsManagerNav = $this->navItems($nav, 'operations_manager');
        $this->codecampStudentNav = $this->navItems($nav, 'codecamp_student');
        $this->codeclubStudentNav = $this->navItems($nav, 'codeclub_student');
        $this->ictStudentNav = $this->navItems($nav, 'ict_student');
        $this->codeclubFacilitatorNav = $this->navItems($nav, 'codeclub_facilitator');
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveNavigationConfig(): array
    {
        $config = config('navigation');

        if (is_array($config) && $config !== []) {
            return $config;
        }

        $path = config_path('navigation.php');

        if (is_readable($path)) {
            $loaded = require $path;

            return is_array($loaded) ? $loaded : [];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $nav
     * @return array<int, array<string, mixed>>
     */
    private function navItems(array $nav, string $key): array
    {
        $items = data_get($nav, $key, []);
        $items = is_array($items) ? array_values(array_filter($items, 'is_array')) : [];

        return array_values(array_filter($items, function (array $item) {
            if (($item['feature'] ?? null) === 'code_club' && ! config('features.code_club', false)) {
                return false;
            }

            if (! empty($item['roles'])) {
                $allowed = false;
                foreach ($item['roles'] as $role) {
                    if ($role === 'admin' && $this->user->isAdmin()) {
                        $allowed = true;
                    }
                    if ($role === 'supervisor' && $this->user->isSupervisor()) {
                        $allowed = true;
                    }
                }
                if (! $allowed) {
                    return false;
                }
            }

            $route = $item['route'] ?? null;

            if (! empty($item['url'])) {
                return true;
            }

            return ! $route || \Illuminate\Support\Facades\Route::has($route);
        }));
    }

    public function render()
    {
        return view('components.navigation.sidebar');
    }
}
