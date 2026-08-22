<?php

namespace App\Livewire\Admin\Camps;

use App\Models\CampEnrollment;
use App\Models\CodeCamp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Show extends Component
{
    use WithPagination;

    public CodeCamp $camp;
    public $activeTab = 'students';

    // Inline edit fields
    public $editName = '';
    public $editDescription = '';
    public $editStartDate = '';
    public $editEndDate = '';
    public $editMaxCapacity = '';
    public $isEditing = false;

    // Add student modal
    public $showAddModal = false;
    public $studentSearch = '';
    public $studentResults = [];

    // Transfer modal
    public $showTransferModal = false;
    public $selectedStudentIds = [];
    public $transferTargetCampId = null;
    public $transferNote = '';

    // Remove modal
    public $showRemoveModal = false;
    public $removeStudentId = null;
    public $removeNote = '';

    // Student search in table
    public $searchStudents = '';

    public bool $canManageCampSettings = false;

    public function mount(CodeCamp $camp): void
    {
        $user = Auth::user();
        if (!($user->hasRole('admin') || $user->hasRole('supervisor') || $user->isTeacher() || $user->isCodecampTrainer())) {
            abort(403);
        }
        $this->canManageCampSettings = $user->hasAnyRole(['admin', 'supervisor']);
        $this->camp = $camp;
        $this->fillEditForm();
    }

    private function fillEditForm(): void
    {
        $this->editName        = $this->camp->name;
        $this->editDescription = $this->camp->description ?? '';
        $this->editStartDate   = $this->camp->start_date->format('Y-m-d');
        $this->editEndDate     = $this->camp->end_date?->format('Y-m-d') ?? '';
        $this->editMaxCapacity = $this->camp->max_capacity ?? '';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // ── Inline edit ──────────────────────────────────────────────────

    public function toggleEdit(): void
    {
        $this->isEditing = !$this->isEditing;
        if (!$this->isEditing) {
            $this->fillEditForm();
        }
    }

    public function saveCamp(): void
    {
        if (!$this->canManageCampSettings) {
            abort(403);
        }

        $this->validate([
            'editName'        => 'required|string|max:255',
            'editDescription' => 'nullable|string',
            'editStartDate'   => 'required|date',
            'editEndDate'     => 'nullable|date|after_or_equal:editStartDate',
            'editMaxCapacity' => 'nullable|integer|min:1',
        ]);

        $this->camp->update([
            'name'         => $this->editName,
            'description'  => $this->editDescription ?: null,
            'start_date'   => $this->editStartDate,
            'end_date'     => $this->editEndDate ?: null,
            'max_capacity' => $this->editMaxCapacity ?: null,
        ]);

        $this->camp->refresh();
        $this->isEditing = false;
        session()->flash('message', 'Camp updated.');
    }

    public function advanceStatus(): void
    {
        if (!$this->canManageCampSettings) {
            abort(403);
        }

        $this->camp->advanceStatus();
        $this->camp->refresh();
        session()->flash('message', 'Camp status updated to ' . ucfirst($this->camp->status) . '.');
    }

    // ── Add student modal ─────────────────────────────────────────────

    public function openAddModal(): void
    {
        $this->showAddModal  = true;
        $this->studentSearch = '';
        $this->studentResults = [];
    }

    public function closeAddModal(): void
    {
        $this->showAddModal  = false;
        $this->studentSearch = '';
        $this->studentResults = [];
    }

    public function updatedStudentSearch(): void
    {
        if (strlen($this->studentSearch) < 2) {
            $this->studentResults = [];
            return;
        }

        $alreadyEnrolledIds = $this->camp->enrollments()
            ->where('status', 'active')
            ->pluck('student_id');

        $this->studentResults = User::where('student_type', 'codecamp')
            ->where(fn($q) => $q->where('name', 'like', '%' . $this->studentSearch . '%'))
            ->whereNotIn('id', $alreadyEnrolledIds)
            ->with(['currentCampEnrollment.camp'])
            ->limit(10)
            ->get(['id', 'name'])
            ->toArray();
    }

    public function enrollStudent(int $studentId): void
    {
        $student = User::findOrFail($studentId);

        $existingInCamp = CampEnrollment::query()
            ->where('camp_id', $this->camp->id)
            ->where('student_id', $studentId)
            ->first();

        if ($existingInCamp?->status === 'active') {
            $this->closeAddModal();
            session()->flash('message', "{$student->name} is already enrolled in this camp.");

            return;
        }

        $previousActive = CampEnrollment::query()
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->where('camp_id', '!=', $this->camp->id)
            ->first();

        if ($previousActive) {
            $previousActive->update(['status' => 'transferred', 'completed_at' => now()]);
        }

        CampEnrollment::updateOrCreate(
            ['camp_id' => $this->camp->id, 'student_id' => $studentId],
            [
                'enrolled_by' => Auth::id(),
                'previous_camp_id' => $previousActive?->camp_id ?? $existingInCamp?->previous_camp_id,
                'status' => 'active',
                'enrolled_at' => now(),
                'completed_at' => null,
            ]
        );

        $this->closeAddModal();
        session()->flash('message', "{$student->name} enrolled in this camp.");
    }

    // ── Transfer students modal ───────────────────────────────────────

    public function openTransferModal(): void
    {
        $this->showTransferModal   = true;
        $this->selectedStudentIds  = [];
        $this->transferTargetCampId = null;
        $this->transferNote        = '';
    }

    public function closeTransferModal(): void
    {
        $this->showTransferModal = false;
    }

    public function transferStudents(): void
    {
        $this->validate([
            'selectedStudentIds'   => 'required|array|min:1',
            'transferTargetCampId' => 'required|exists:code_camps,id',
        ]);

        $targetCamp = CodeCamp::findOrFail($this->transferTargetCampId);

        foreach ($this->selectedStudentIds as $studentId) {
            // Mark current enrollment as transferred
            CampEnrollment::where('camp_id', $this->camp->id)
                ->where('student_id', $studentId)
                ->where('status', 'active')
                ->update(['status' => 'transferred', 'completed_at' => now()]);

            // Create new enrollment in target camp (upsert to avoid duplicates)
            CampEnrollment::updateOrCreate(
                ['camp_id' => $targetCamp->id, 'student_id' => $studentId],
                [
                    'enrolled_by'      => Auth::id(),
                    'previous_camp_id' => $this->camp->id,
                    'status'           => 'active',
                    'enrolled_at'      => now(),
                    'completed_at'     => null,
                    'notes'            => $this->transferNote ?: null,
                ]
            );
        }

        $count = count($this->selectedStudentIds);
        $this->closeTransferModal();
        session()->flash('message', "{$count} student(s) transferred to {$targetCamp->name}.");
    }

    // ── Remove student ────────────────────────────────────────────────

    public function confirmRemove(int $studentId): void
    {
        $this->showRemoveModal = true;
        $this->removeStudentId = $studentId;
        $this->removeNote      = '';
    }

    public function closeRemoveModal(): void
    {
        $this->showRemoveModal = false;
        $this->removeStudentId = null;
    }

    public function removeStudent(): void
    {
        CampEnrollment::where('camp_id', $this->camp->id)
            ->where('student_id', $this->removeStudentId)
            ->update(['status' => 'dropped', 'completed_at' => now(), 'notes' => $this->removeNote ?: null]);

        $this->closeRemoveModal();
        session()->flash('message', 'Student removed from this camp.');
    }

    public function render()
    {
        $activeStudents = CampEnrollment::where('camp_id', $this->camp->id)
            ->where('status', 'active')
            ->when($this->searchStudents, fn($q) => $q->whereHas('student', fn($sq) =>
                $sq->where('name', 'like', '%' . $this->searchStudents . '%')
            ))
            ->with(['student.studentProfile', 'enrolledBy', 'previousCamp'])
            ->latest('enrolled_at')
            ->paginate(20);

        $historyEnrollments = CampEnrollment::where('camp_id', $this->camp->id)
            ->whereIn('status', ['transferred', 'completed', 'dropped'])
            ->with(['student', 'enrolledBy', 'previousCamp'])
            ->latest('completed_at')
            ->get();

        $otherCamps = CodeCamp::where('id', '!=', $this->camp->id)
            ->whereIn('status', ['upcoming', 'active'])
            ->orderBy('start_date')
            ->get(['id', 'name']);

        $stats = [
            'active'      => CampEnrollment::where('camp_id', $this->camp->id)->where('status', 'active')->count(),
            'transferred' => CampEnrollment::where('camp_id', $this->camp->id)->where('status', 'transferred')->count(),
            'completed'   => CampEnrollment::where('camp_id', $this->camp->id)->where('status', 'completed')->count(),
            'dropped'     => CampEnrollment::where('camp_id', $this->camp->id)->where('status', 'dropped')->count(),
        ];

        $transferDestinations = CampEnrollment::where('previous_camp_id', $this->camp->id)
            ->whereIn('student_id', $historyEnrollments->pluck('student_id'))
            ->with('camp:id,name')
            ->get()
            ->keyBy(fn ($e) => $e->student_id . ':' . $e->previous_camp_id);

        return view('livewire.admin.camps.show', [
            'activeStudents'       => $activeStudents,
            'historyEnrollments'   => $historyEnrollments,
            'otherCamps'           => $otherCamps,
            'stats'                => $stats,
            'transferDestinations' => $transferDestinations,
        ]);
    }
}
