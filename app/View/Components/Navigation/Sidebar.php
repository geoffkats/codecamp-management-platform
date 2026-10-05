<?php

namespace App\View\Components\Navigation;

use App\Models\User;
use App\Services\TrainerSubmissionQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
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

    /**
     * Sections shown to this user, in order, each with resolved items.
     *
     * @var array<int, array{key: string, label: string, icon: string, active: bool, badge: int, items: array<int, array<string, mixed>>}>
     */
    public array $sections = [];

    public string $roleLabel = 'Member';

    public function __construct(User $user)
    {
        $this->user = $user;

        foreach (self::visibility($user) as $flag => $value) {
            $this->{$flag} = $value;
        }
        $this->showDualProgramSwitcher = $user->hasDualProgramAccess();
        
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

        $this->roleLabel = match (true) {
            (bool) $this->isAdmin => 'Administrator',
            (bool) $this->isSupervisor => 'Supervisor',
            (bool) $this->isIctTeacher => 'ICT Teacher',
            (bool) $this->showCodeClubFacilitatorSection => 'Club Facilitator',
            (bool) $this->isTeacher => 'Teacher',
            $user->hasRole('operations_manager') => 'Operations',
            (bool) $this->isStudent => 'Student',
            default => 'Member',
        };

        $this->sections = $this->buildSections();
    }

    /**
     * Role flags and which menu sections apply. Role checks are cached because the sidebar renders on every page.
     *
     * @return array<string, mixed>
     */
    public static function visibility(User $user): array
    {
        $is = fn (string $key, \Closure $check) => Cache::remember("user_{$user->id}_{$key}", 3600, $check);

        $v = [
            'isAdmin' => $is('is_admin', fn () => $user->isAdmin()),
            'isSupervisor' => $is('is_supervisor', fn () => $user->isSupervisor()),
            'isTeacher' => $is('is_teacher', fn () => $user->isTeacher()),
            'isIctTeacher' => $is('is_ict_teacher', fn () => $user->isIctTeacher()),
            'isCodecampTrainer' => $is('is_codecamp_trainer', fn () => $user->isCodecampTrainer()),
            'isStudent' => $is('is_student', fn () => $user->isStudent()),
            'isIctStudent' => $is('is_ict_student', fn () => $user->isIctStudent()),
            'isCodecampStudent' => $is('is_codecamp_student', fn () => $user->isCodecampStudent()),
            'isCodeClubStudent' => $is('is_codeclub_student', fn () => $user->isCodeClubStudent()),
            'activeProgramContext' => $user->activeProgramContext(),
        ];

        $v['showStudentSection'] = $v['isStudent'] && ! $v['isAdmin'] && ! $v['isTeacher'];
        $v['showIctStudentSection'] = $v['showStudentSection'] && $v['isIctStudent'];
        $v['showCodeClubStudentSection'] = $v['showStudentSection'] && $v['isCodeClubStudent'] && config('features.code_club', false);
        $v['showCodecampStudentSection'] = $v['showStudentSection'] && $v['isCodecampStudent'] && ! $v['isCodeClubStudent'];
        $v['showTeacherSection'] = $v['isTeacher'] && ! $v['isAdmin'];
        $v['showIctTeacherSection'] = $v['isIctTeacher'] && ! $v['isAdmin'];
        $v['showCodeClubFacilitatorSection'] = config('features.code_club', false)
            && $user->hasCodeClubAccess()
            && ! $v['isAdmin']
            && ! $v['isIctTeacher']
            && (! $v['isCodecampTrainer'] || $v['activeProgramContext'] === 'codeclub');
        $v['showCodecampTeacherSection'] = $v['showTeacherSection']
            && ! $v['isIctTeacher']
            && (! $user->hasCodeClubAccess() || ($v['isCodecampTrainer'] && $v['activeProgramContext'] === 'codecamp'));
        $v['showSupervisorSection'] = $v['isSupervisor'] && ! $v['isAdmin'];

        return $v;
    }

    /**
     * The config/navigation.php role keys whose menus this user gets, in display order.
     *
     * @return array<int, string>
     */
    public static function roleKeysFor(User $user): array
    {
        $v = self::visibility($user);
        $roleKeys = [];

        if ($v['showStudentSection'] && $user->studentProfile) {
            $roleKeys[] = match (true) {
                (bool) $v['showIctStudentSection'] => 'ict_student',
                (bool) $v['showCodeClubStudentSection'] => 'codeclub_student',
                default => 'codecamp_student',
            };
        }

        if ($v['showIctTeacherSection']) {
            $roleKeys[] = 'ict_teacher';
        } elseif ($v['showCodeClubFacilitatorSection']) {
            $roleKeys[] = 'codeclub_facilitator';
        } elseif ($v['showCodecampTeacherSection']) {
            $roleKeys[] = 'codecamp_teacher';
        }

        if ($v['isAdmin']) {
            $roleKeys[] = 'admin';
        }

        if (! $v['isAdmin'] && $user->hasRole('operations_manager')) {
            $roleKeys[] = 'operations_manager';
        }

        if ($v['showSupervisorSection']) {
            $roleKeys[] = 'supervisor';
        }

        return $roleKeys;
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
     * @return array<int, array<string, mixed>>
     */
    private function buildSections(): array
    {
        $nav = $this->resolveNavigationConfig();
        $roleKeys = self::roleKeysFor($this->user);

        $badges = [
            'pending_approvals' => (int) $this->pendingApprovalCount,
            'pending_feedback' => (int) $this->pendingFeedbackCount,
            'pending_submissions' => (int) $this->pendingSubmissionsCount,
        ];

        $seenHrefs = [];
        $sections = [];

        foreach ($roleKeys as $roleKey) {
            foreach ((array) ($nav[$roleKey] ?? []) as $section) {
                if (! is_array($section) || empty($section['items'])) {
                    continue;
                }

                $items = [];
                foreach ($section['items'] as $item) {
                    $resolved = is_array($item) ? $this->resolveItem($item, $badges) : null;
                    if (! $resolved || isset($seenHrefs[$resolved['href']])) {
                        continue;
                    }
                    $seenHrefs[$resolved['href']] = true;
                    $items[] = $resolved;
                }

                if ($items === []) {
                    continue;
                }

                $label = (string) ($section['label'] ?? 'Menu');
                $key = Str::slug($label);

                if (isset($sections[$key])) {
                    $sections[$key]['items'] = array_merge($sections[$key]['items'], $items);
                } else {
                    $sections[$key] = [
                        'key' => $key,
                        'label' => __($label),
                        'icon' => (string) ($section['icon'] ?? 'squares-2x2'),
                        'items' => $items,
                    ];
                }
            }
        }

        return array_values(array_map(function (array $section) {
            $sectionWord = Str::lower($section['label']);
            $section['items'] = array_map(fn (array $item) => [...$item, 'keywords' => $item['keywords'].' '.$sectionWord], $section['items']);
            $section['active'] = collect($section['items'])->contains('active', true);
            $section['badge'] = (int) collect($section['items'])->sum('badge');

            return $section;
        }, $sections));
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, int>  $badges
     * @return array<string, mixed>|null
     */
    private function resolveItem(array $item, array $badges): ?array
    {
        if (($item['feature'] ?? null) === 'code_club' && ! config('features.code_club', false)) {
            return null;
        }

        if (! empty($item['roles'])) {
            $allowed = (in_array('admin', $item['roles'], true) && $this->isAdmin)
                || (in_array('supervisor', $item['roles'], true) && $this->isSupervisor);
            if (! $allowed) {
                return null;
            }
        }

        $route = $item['route'] ?? null;
        $href = ($route && Route::has($route))
            ? route($route)
            : (! empty($item['url']) ? url($item['url']) : null);
        $label = (string) ($item['label'] ?? '');

        if (! $href || $label === '') {
            return null;
        }

        $match = $item['match'] ?? $route;

        return [
            'label' => __($label),
            'href' => $href,
            'icon' => (string) ($item['icon'] ?? 'link'),
            'active' => $match ? request()->routeIs($match) : false,
            'badge' => ! empty($item['badge']) ? (int) ($badges[$item['badge']] ?? 0) : 0,
            'keywords' => Str::lower($label.' '.($item['keywords'] ?? '')),
        ];
    }

    public function render()
    {
        return view('components.navigation.sidebar');
    }
}
