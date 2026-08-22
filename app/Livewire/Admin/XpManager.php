<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\UserPoint;
use App\Models\UserProgress;
use App\Models\Course;
use App\Services\PointsService;
use App\Support\LevelSystem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    public $bulkOperation = 'add'; // add, subtract, set
    public $selectAll = false;

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
        if (!Auth::user()->hasAnyRole(['admin'])) {
            abort(403);
        }
    }

    public function render()
    {
        $query = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->with(['points', 'enrollments.course']);

        // Search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Course filter
        if ($this->courseFilter) {
            $query->whereHas('enrollments', function($q) {
                $q->where('course_id', $this->courseFilter);
            });
        }

        // Get students with XP stats
        $students = $query->paginate(20);

        // Calculate XP for time period + unify level/rank display from total XP
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

        // Sort
        $students = $this->applySorting($students);

        return view('livewire.admin.xp-manager', [
            'students' => $students,
            'courses' => Course::where('is_published', true)->orderBy('title')->get(),
            'totalStudents' => User::whereHas('roles', fn ($q) => $q->where('name', 'student'))->count(),
            'totalXp' => UserPoint::sum('total_points'),
            'avgXp' => UserPoint::avg('total_points'),
            'previewLevelInfo' => LevelSystem::info((int) ($this->editForm['total_points'] ?? 0)),
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
        $query = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        });

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->courseFilter) {
            $query->whereHas('enrollments', function($q) {
                $q->where('course_id', $this->courseFilter);
            });
        }

        $students = $query->paginate(20);
        $students = $this->applySorting($students);

        return $students->getCollection()->pluck('id')->toArray();
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
        $user = User::with('points')->findOrFail($userId);
        $this->editingUser = $user;

        $points = app(PointsService::class)->ensureUserPoints($user);
        $info = LevelSystem::info($points->total_points ?? 0);

        $this->editForm = [
            'total_points' => $points->total_points ?? 0,
            'level' => $info['level'],
            'points_to_next_level' => $info['xp_to_next_level'],
            'xp_multiplier' => $points->xp_multiplier,
            'multiplier_expires_at' => $points?->multiplier_expires_at?->format('Y-m-d\TH:i'),
            'multiplier_reason' => $points->multiplier_reason,
        ];

        $this->showEditModal = true;
    }

    public function updatedEditFormTotalPoints($value): void
    {
        $info = LevelSystem::info((int) $value);
        $this->editForm['level'] = $info['level'];
        $this->editForm['points_to_next_level'] = $info['xp_to_next_level'];
    }

    public function openDetailsModal($userId)
    {
        $user = User::with(['points', 'enrollments.course'])->findOrFail($userId);
        $this->detailsUser = $user;

        // Get complete XP history with details
        $this->xpHistory = UserProgress::where('user_id', $userId)
            ->with(['course', 'lesson'])
            ->whereNotNull('points_earned')
            ->where('points_earned', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($progress) {
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
        $this->reset('editForm');
    }

    public function saveEdit()
    {
        $this->validate([
            'editForm.total_points' => 'required|integer|min:0',
            'editForm.xp_multiplier' => 'nullable|numeric|min:0',
            'editForm.multiplier_expires_at' => 'nullable|date',
            'editForm.multiplier_reason' => 'nullable|string|max:255',
        ]);

        $points = app(PointsService::class)->ensureUserPoints($this->editingUser);
        $info = LevelSystem::info((int) $this->editForm['total_points']);

        $points->fill([
            'total_points' => (int) $this->editForm['total_points'],
            'level' => $info['level'],
            'points_to_next_level' => $info['xp_to_next_level'],
            'xp_multiplier' => $this->editForm['xp_multiplier'] ?: null,
            'multiplier_expires_at' => $this->editForm['multiplier_expires_at'] ?: null,
            'multiplier_reason' => $this->editForm['multiplier_reason'] ?: null,
        ]);
        $points->save();
        LevelSystem::sync($points);

        session()->flash(
            'message',
            'XP updated for '.$this->editingUser->name
            .' — Level '.$info['level'].' · '.$info['name']
        );
        $this->closeEditModal();
    }

    public function syncAllLevels(): void
    {
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
            'bulkPoints' => 'required|integer|min:0',
        ]);

        DB::transaction(function() {
            foreach ($this->selectedStudents as $userId) {
                $user = User::find($userId);
                if (!$user || !$user->points) continue;

                switch ($this->bulkOperation) {
                    case 'add':
                        $user->points->addPoints((int) $this->bulkPoints);
                        break;
                    case 'subtract':
                        $newTotal = max(0, (int) $user->points->total_points - (int) $this->bulkPoints);
                        $user->points->update(['total_points' => $newTotal]);
                        $user->points->syncLevel();
                        break;
                    case 'set':
                        $user->points->update(['total_points' => max(0, (int) $this->bulkPoints)]);
                        $user->points->syncLevel();
                        break;
                }

                $user->points->refresh();
            }
        });

        session()->flash('message', 'Bulk XP update applied to ' . count($this->selectedStudents) . ' students');
        $this->reset(['selectedStudents', 'bulkPoints']);
    }

    public function openCourseBulkModal($courseId = null)
    {
        $this->courseBulkCourseId = $courseId ?? $this->courseFilter;
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

        try {
            DB::beginTransaction();

            // Get all students enrolled in the course
            $students = User::whereHas('roles', function($q) {
                $q->where('name', 'student');
            })->whereHas('enrollments', function($q) {
                $q->where('course_id', $this->courseBulkCourseId)
                  ->where('status', 'approved');
            })->get();

            $count = 0;
            foreach ($students as $student) {
                // Ensure points record exists and award points
                $points = $student->points()->firstOrCreate(
                    ['user_id' => $student->id],
                    [
                        'total_points' => 0,
                        'level' => 1,
                        'points_to_next_level' => 100,
                    ]
                );

                $points->addPoints((int) $this->courseBulkPoints);

                // Log in user_progress without violating unique constraint (one row per user/course/type)
                $progress = UserProgress::firstOrCreate(
                    [
                        'user_id' => $student->id,
                        'course_id' => $this->courseBulkCourseId,
                        'type' => 'course_enrolled',
                    ],
                    [
                        'points_earned' => 0,
                        'metadata' => [],
                    ]
                );

                $newPointsEarned = ($progress->points_earned ?? 0) + $this->courseBulkPoints;
                $progress->points_earned = $newPointsEarned;

                // Merge metadata entries to keep audit trail of multiple bulk awards
                $meta = is_array($progress->metadata) ? $progress->metadata : ($progress->metadata ? json_decode($progress->metadata, true) : []);
                $meta[] = [
                    'reason' => $this->courseBulkReason ?: 'Bulk course award by admin',
                    'awarded_by' => Auth::id(),
                    'source' => 'bulk_award',
                    'awarded_at' => now()->toDateTimeString(),
                    'points' => $this->courseBulkPoints,
                ];

                $progress->metadata = $meta;
                $progress->save();

                $count++;
            }

            DB::commit();

            $course = Course::find($this->courseBulkCourseId);
            session()->flash('message', "Successfully awarded {$this->courseBulkPoints} XP to {$count} students in {$course->title}");

            $this->closeCourseBulkModal();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Course bulk XP award failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to award XP. Please try again.');
        }
    }

    public function openResetModal($type = 'all', $courseId = null)
    {
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
        $this->resetPage();
    }

    public function updatedTimeFilter()
    {
        $this->resetPage();
    }
}
