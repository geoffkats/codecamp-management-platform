<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\UserPoint;
use App\Models\UserProgress;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Services\PointsService;
use App\Support\LevelSystem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class XpManager extends Component
{
    use WithPagination;

    public $search = '';
    public $courseFilter = '';
    public $timeFilter = 'all'; // all, today, week, month
    public $sortBy = 'total_points';
    public $sortDirection = 'desc';
    
    // Bulk operations
    public $selectedStudents = [];
    public $bulkPoints = 0;
    public $bulkOperation = 'add'; // add, subtract
    public $bulkCourseId = null;
    public $selectAll = false;

    public $awardCourseId = null;
    public $awardPoints = 0;
    public $awardReason = '';
    public $awardOperation = 'add';
    public $awardMessage = '';

    public bool $canManageAllXp = false;

    private $managedCoursesCache = null;

    // Course bulk award
    public $showCourseBulkModal = false;
    public $courseBulkCourseId = null;
    public $courseBulkPoints = 0;
    public $courseBulkReason = '';
    
    // Edit modal
    public $showEditModal = false;
    public $editingUser = null;
    public $editForm = [
        'total_points' => 0,
        'level' => 1,
        'points_to_next_level' => null,
        'xp_multiplier' => null,
        'multiplier_expires_at' => null,
        'multiplier_reason' => null,
    ];
    
    // Details/Audit modal
    public $showDetailsModal = false;
    public $detailsUser = null;
    public $xpHistory = [];
    
    // Reset confirmation
    public $showResetModal = false;
    public $resetType = 'all'; // all, course, week
    public $resetCourseId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'courseFilter' => ['except' => ''],
        'timeFilter' => ['except' => 'all'],
    ];

    public function mount()
    {
        $user = Auth::user();

        abort_unless($user && $user->can('award_course_xp'), 403);

        $this->canManageAllXp = $user->isAdmin() || $user->isSupervisor();

        if ($this->courseFilter && ! $this->canAccessCourse((int) $this->courseFilter)) {
            $this->courseFilter = '';
        }

        if ($this->courseFilter) {
            $this->bulkCourseId = $this->courseFilter;
        }
    }

    public function render()
    {
        $query = $this->studentQuery()->with(['points', 'enrollments.course']);

        $students = $query->paginate(20);

        foreach ($students as $student) {
            $student->period_xp = $this->calculatePeriodXp($student->id);
            $student->course_xp = $this->courseFilter
                ? $this->calculateCourseXp($student->id, $this->courseFilter)
                : 0;
            $info = LevelSystem::info($student->points?->total_points ?? 0);
            $student->level_number = $info['level'];
            $student->rank_name = $info['name'];
            $student->rank_color = $info['color'];
        }

        $students = $this->applySorting($students);

        $editingEnrollments = collect();
        if ($this->editingUser) {
            $editingEnrollments = CourseEnrollment::query()
                ->where('user_id', $this->editingUser->id)
                ->when(! $this->canManageAllXp, fn ($q) => $q->whereIn('course_id', $this->managedCourseIds()))
                ->with('course')
                ->orderByRaw('CASE WHEN completed_at IS NULL THEN 0 ELSE 1 END')
                ->orderByDesc('enrolled_at')
                ->get();
        }

        $studentIdsSubquery = $this->studentQuery()->select('users.id');

        return view('livewire.admin.xp-manager', [
            'students' => $students,
            'courses' => $this->managedCourses(),
            'editingEnrollments' => $editingEnrollments,
            'totalStudents' => $this->studentQuery()->count(),
            'totalXp' => UserPoint::query()->whereIn('user_id', $this->studentQuery()->select('users.id'))->sum('total_points'),
            'avgXp' => UserPoint::query()->whereIn('user_id', $this->studentQuery()->select('users.id'))->avg('total_points'),
        ]);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedStudents = $this->getCurrentPageStudentIds();
        } else {
            $this->selectedStudents = [];
        }
    }

    private function getCurrentPageStudentIds(): array
    {
        $students = $this->applySorting($this->studentQuery()->with('points')->paginate(20));

        return $students->getCollection()->pluck('id')->toArray();
    }

    private function studentQuery()
    {
        $query = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'student'))
            ->where('id', '!=', Auth::id());

        $managedIds = $this->managedCourseIds();

        if (! $this->canManageAllXp) {
            if ($managedIds === []) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereHas('enrollments', fn ($q) => $q->whereIn('course_id', $managedIds));
            }
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->courseFilter) {
            $courseId = (int) $this->courseFilter;
            if ($this->canAccessCourse($courseId)) {
                $query->whereHas('enrollments', fn ($q) => $q->where('course_id', $courseId));
            } else {
                $query->whereRaw('0 = 1');
            }
        }

        return $query;
    }

    private function managedCourses()
    {
        if ($this->managedCoursesCache !== null) {
            return $this->managedCoursesCache;
        }

        $query = Course::query()->orderBy('title');

        if ($this->canManageAllXp) {
            return $this->managedCoursesCache = $query->where('is_published', true)->get();
        }

        return $this->managedCoursesCache = $query->accessibleBy(Auth::user())->get();
    }

    /**
     * @return array<int>
     */
    private function managedCourseIds(): array
    {
        return $this->managedCourses()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function canAccessCourse(int $courseId): bool
    {
        return in_array($courseId, $this->managedCourseIds(), true);
    }

    private function assertCanAccessCourse(int $courseId): void
    {
        abort_unless($this->canAccessCourse($courseId), 403);
    }

    private function assertCanManageStudent(int $userId): void
    {
        $query = User::query()
            ->where('id', $userId)
            ->whereHas('roles', fn ($q) => $q->where('name', 'student'));

        if (! $this->canManageAllXp) {
            $ids = $this->managedCourseIds();
            abort_unless($ids !== [], 403);
            $query->whereHas('enrollments', fn ($q) => $q->whereIn('course_id', $ids));
        }

        abort_unless($query->exists(), 403);
    }

    private function assertCanAwardToStudentCourse(int $userId, int $courseId): void
    {
        abort_unless($this->canAccessCourse($courseId), 403);

        $enrolled = CourseEnrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();

        abort_unless($enrolled, 403, 'This student is not in that course.');
    }

    private function calculatePeriodXp($userId)
    {
        $query = UserProgress::where('user_id', $userId);

        switch ($this->timeFilter) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
        }

        return $query->sum('points_earned') ?? 0;
    }

    private function calculateCourseXp($userId, $courseId)
    {
        return UserProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->sum('points_earned') ?? 0;
    }

    private function applySorting($students)
    {
        $collection = $students->getCollection();

        $sorted = $collection->sortBy(function($student) {
            switch ($this->sortBy) {
                case 'name':
                    return strtolower($student->name);
                case 'total_points':
                    return $student->points->total_points ?? 0;
                case 'level':
                    return LevelSystem::levelForXp($student->points->total_points ?? 0);
                case 'period_xp':
                    return $student->period_xp;
                case 'course_xp':
                    return $student->course_xp;
                default:
                    return $student->points->total_points ?? 0;
            }
        }, SORT_REGULAR, $this->sortDirection === 'desc');

        return $students->setCollection($sorted->values());
    }

    private function recalculateLevel(UserPoint $points): void
    {
        $points->syncLevel();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function openEditModal($userId)
    {
        $this->assertCanManageStudent((int) $userId);

        $user = User::with(['points', 'enrollments.course'])->findOrFail($userId);
        $this->editingUser = $user;

        $points = app(PointsService::class)->ensureUserPoints($user);

        $this->editForm = [
            'total_points' => $points->total_points ?? 0,
            'level' => LevelSystem::levelForXp($points->total_points ?? 0),
            'points_to_next_level' => null,
            'xp_multiplier' => $points->xp_multiplier,
            'multiplier_expires_at' => $points?->multiplier_expires_at?->format('Y-m-d\TH:i'),
            'multiplier_reason' => $points->multiplier_reason,
        ];

        $currentCourseId = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->currentClass()
            ->value('course_id');

        $filterCourseId = $this->courseFilter
            ? (int) $this->courseFilter
            : null;

        $enrolledIds = $user->enrollments
            ->pluck('course_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $this->canAccessCourse($id))
            ->values()
            ->all();

        if ($filterCourseId && in_array($filterCourseId, $enrolledIds, true)) {
            $this->awardCourseId = $filterCourseId;
        } elseif ($currentCourseId) {
            $this->awardCourseId = (int) $currentCourseId;
        } else {
            $this->awardCourseId = $enrolledIds[0] ?? null;
        }

        $this->awardPoints = 0;
        $this->awardReason = '';
        $this->awardOperation = 'add';
        $this->awardMessage = '';
        $this->showDetailsModal = false;
        $this->showEditModal = true;
    }

    public function openDetailsModal($userId)
    {
        $this->assertCanManageStudent((int) $userId);

        $user = User::with(['points', 'enrollments.course'])->findOrFail($userId);
        $this->detailsUser = $user;

        $this->xpHistory = UserProgress::where('user_id', $userId)
            ->with(['course', 'lesson'])
            ->whereNotNull('points_earned')
            ->where('points_earned', '!=', 0)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($progress) {
                return [
                    'id' => $progress->id,
                    'type' => $progress->type,
                    'points' => $progress->points_earned,
                    'course' => $progress->course?->title,
                    'lesson' => $progress->lesson?->title,
                    'date' => $progress->created_at,
                    'metadata' => $progress->metadata,
                ];
            });

        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->detailsUser = null;
        $this->reset('xpHistory');
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingUser = null;
        $this->reset(['editForm', 'awardCourseId', 'awardPoints', 'awardReason', 'awardOperation', 'awardMessage']);
    }

    public function awardStudentXp()
    {
        if (! $this->editingUser) {
            return;
        }

        $this->validate([
            'awardCourseId' => [
                'required',
                'integer',
                Rule::exists('course_enrollments', 'course_id')->where('user_id', $this->editingUser->id),
            ],
            'awardPoints' => 'required|integer|min:1|max:10000',
            'awardOperation' => 'required|in:add,subtract',
            'awardReason' => 'nullable|string|max:255',
        ], [
            'awardCourseId.required' => 'Choose the course this XP belongs to.',
            'awardCourseId.exists' => 'This student is not enrolled in that course.',
        ]);

        $this->assertCanAwardToStudentCourse((int) $this->editingUser->id, (int) $this->awardCourseId);

        $amount = $this->awardOperation === 'subtract'
            ? -(int) $this->awardPoints
            : (int) $this->awardPoints;

        $applied = app(PointsService::class)->awardAdminCourseXp(
            (int) $this->editingUser->id,
            (int) $this->awardCourseId,
            $amount,
            $this->awardReason ?: null,
            Auth::id(),
            $this->canManageAllXp ? 'xp_manager_student' : 'instructor_award'
        );

        if ($applied === 0) {
            $this->awardMessage = 'No XP to remove for this student.';
            return;
        }

        $course = Course::find($this->awardCourseId);
        $verb = $applied >= 0 ? 'Awarded' : 'Removed';

        $this->awardMessage = $verb.' '.number_format(abs($applied)).' XP in '.($course?->title ?? 'the selected course').'.';
        $this->editingUser = User::with(['points', 'enrollments.course'])->find($this->editingUser->id);
        $this->awardPoints = 0;
        $this->awardReason = '';
    }

    public function saveEdit()
    {
        abort_unless($this->canManageAllXp, 403);

        $this->validate([
            'editForm.xp_multiplier' => 'nullable|numeric|min:0',
            'editForm.multiplier_expires_at' => 'nullable|date',
            'editForm.multiplier_reason' => 'nullable|string|max:255',
        ]);

        $points = app(PointsService::class)->ensureUserPoints($this->editingUser);

        $points->fill([
            'xp_multiplier' => $this->editForm['xp_multiplier'] ?: null,
            'multiplier_expires_at' => $this->editForm['multiplier_expires_at'] ?: null,
            'multiplier_reason' => $this->editForm['multiplier_reason'] ?: null,
        ]);
        $points->save();
        LevelSystem::sync($points);

        session()->flash('message', 'XP multiplier updated for '.$this->editingUser->name);
        $this->closeEditModal();
    }

    public function syncAllLevels(): void
    {
        abort_unless($this->canManageAllXp, 403);

        $count = app(PointsService::class)->syncAllLevels();
        session()->flash('message', "Synced levels and ranks for {$count} students from their total XP.");
    }

    public function bulkUpdateXp()
    {
        if (empty($this->selectedStudents)) {
            session()->flash('error', 'No students selected');
            return;
        }

        $this->validate([
            'bulkCourseId' => 'required|exists:courses,id',
            'bulkPoints' => 'required|integer|min:1|max:10000',
            'bulkOperation' => 'required|in:add,subtract',
        ], [
            'bulkCourseId.required' => 'Choose the course this XP belongs to.',
        ]);

        $this->assertCanAccessCourse((int) $this->bulkCourseId);

        $amount = $this->bulkOperation === 'subtract'
            ? -(int) $this->bulkPoints
            : (int) $this->bulkPoints;

        $awarded = 0;
        $skipped = 0;
        $service = app(PointsService::class);

        foreach ($this->selectedStudents as $userId) {
            $enrolled = CourseEnrollment::query()
                ->where('user_id', $userId)
                ->where('course_id', $this->bulkCourseId)
                ->exists();

            if (! $enrolled) {
                $skipped++;
                continue;
            }

            $service->awardAdminCourseXp(
                (int) $userId,
                (int) $this->bulkCourseId,
                $amount,
                'Bulk award by '.($this->canManageAllXp ? 'admin' : 'instructor'),
                Auth::id(),
                $this->canManageAllXp ? 'xp_manager_bulk' : 'instructor_bulk'
            );
            $awarded++;
        }

        $course = Course::find($this->bulkCourseId);
        $message = "Applied {$this->bulkPoints} XP in ".($course?->title ?? 'the course')." for {$awarded} student(s).";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} not enrolled in that course.";
        }

        session()->flash('message', $message);
        $this->reset(['selectedStudents', 'bulkPoints', 'selectAll']);
    }

    public function openCourseBulkModal($courseId = null)
    {
        $target = $courseId ?? $this->courseFilter;
        if ($target && ! $this->canAccessCourse((int) $target)) {
            abort(403);
        }

        $this->courseBulkCourseId = $target;
        $this->courseBulkPoints = 0;
        $this->courseBulkReason = '';
        $this->showCourseBulkModal = true;
    }

    public function closeCourseBulkModal()
    {
        $this->showCourseBulkModal = false;
        $this->reset(['courseBulkCourseId', 'courseBulkPoints', 'courseBulkReason']);
    }

    public function awardCourseXp()
    {
        $this->validate([
            'courseBulkCourseId' => 'required|exists:courses,id',
            'courseBulkPoints' => 'required|integer|min:1|max:10000',
            'courseBulkReason' => 'nullable|string|max:255',
        ]);

        $this->assertCanAccessCourse((int) $this->courseBulkCourseId);

        $studentIds = CourseEnrollment::query()
            ->currentClass(null, (int) $this->courseBulkCourseId)
            ->pluck('user_id')
            ->unique()
            ->all();

        $count = 0;
        $service = app(PointsService::class);

        foreach ($studentIds as $userId) {
            $service->awardAdminCourseXp(
                (int) $userId,
                (int) $this->courseBulkCourseId,
                (int) $this->courseBulkPoints,
                $this->courseBulkReason ?: 'Bulk course award by '.($this->canManageAllXp ? 'admin' : 'instructor'),
                Auth::id(),
                $this->canManageAllXp ? 'xp_manager_course' : 'instructor_course'
            );
            $count++;
        }

        $course = Course::find($this->courseBulkCourseId);
        session()->flash(
            'message',
            "Awarded {$this->courseBulkPoints} XP to {$count} student(s) currently in ".($course?->title ?? 'the course').'.'
        );

        $this->closeCourseBulkModal();
    }

    public function openResetModal($type = 'all', $courseId = null)
    {
        abort_unless($this->canManageAllXp, 403);

        $this->resetType = $type;
        $this->resetCourseId = $courseId;
        $this->showResetModal = true;
    }

    public function closeResetModal()
    {
        $this->showResetModal = false;
        $this->reset(['resetType', 'resetCourseId']);
    }

    public function confirmReset()
    {
        abort_unless($this->canManageAllXp, 403);

        try {
            DB::beginTransaction();
            
            switch ($this->resetType) {
                case 'all':
                    // Reset all student XP
                    UserPoint::where('user_id', '>', 0)->update([
                        'total_points' => 0,
                        'level' => 1,
                        'points_to_next_level' => 100,
                    ]);
                    // Use delete() instead of truncate() to work within transaction
                    UserProgress::query()->delete();
                    session()->flash('message', 'All student XP has been reset');
                    break;

                case 'week':
                    // Remove this week's progress
                    $weekStart = now()->startOfWeek();
                    $weekEnd = now()->endOfWeek();

                    $progressToDelete = UserProgress::whereBetween('created_at', [$weekStart, $weekEnd])->get();
                    $pointsToRemove = $progressToDelete->groupBy('user_id')->map(fn ($items) => $items->sum('points_earned'));

                    foreach ($pointsToRemove as $userId => $points) {
                        $record = UserPoint::where('user_id', $userId)->first();
                        if (! $record) {
                            continue;
                        }
                        $record->update([
                            'total_points' => max(0, (int) $record->total_points - (int) $points),
                        ]);
                        $record->syncLevel();
                    }

                    UserProgress::whereBetween('created_at', [$weekStart, $weekEnd])->delete();
                    session()->flash('message', 'This week\'s XP has been reset');
                    break;

                case 'course':
                    if ($this->resetCourseId) {
                        $progressToDelete = UserProgress::where('course_id', $this->resetCourseId)->get();
                        $pointsToRemove = $progressToDelete->groupBy('user_id')->map(fn ($items) => $items->sum('points_earned'));

                        foreach ($pointsToRemove as $userId => $points) {
                            $record = UserPoint::where('user_id', $userId)->first();
                            if (! $record) {
                                continue;
                            }
                            $record->update([
                                'total_points' => max(0, (int) $record->total_points - (int) $points),
                            ]);
                            $record->syncLevel();
                        }

                        UserProgress::where('course_id', $this->resetCourseId)->delete();
                        $course = Course::find($this->resetCourseId);
                        session()->flash('message', 'XP reset for course: '.$course->title);
                    }
                    break;
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('XP Reset failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to reset XP. Please try again.');
        }

        $this->closeResetModal();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCourseFilter()
    {
        $this->bulkCourseId = $this->courseFilter ?: null;
        $this->resetPage();
    }

    public function updatedTimeFilter()
    {
        $this->resetPage();
    }
}
