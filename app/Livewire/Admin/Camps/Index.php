<?php

namespace App\Livewire\Admin\Camps;

use App\Models\CampEnrollment;
use App\Models\CodeCamp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $filterStatus = '';
    public $showCreateForm = false;

    // Create form fields
    public $name = '';
    public $description = '';
    public $startDate = '';
    public $endDate = '';
    public $maxCapacity = '';

    public bool $canCreateCamps = false;

    public function mount(): void
    {
        $user = Auth::user();
        if (!($user->hasRole('admin') || $user->hasRole('supervisor') || $user->isTeacher() || $user->isCodecampTrainer())) {
            abort(403);
        }
        $this->canCreateCamps = $user->hasAnyRole(['admin', 'supervisor']);
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function toggleCreateForm(): void
    {
        $this->showCreateForm = !$this->showCreateForm;
        if (!$this->showCreateForm) {
            $this->resetCreateForm();
        }
    }

    public function createCamp(): void
    {
        if (!$this->canCreateCamps) {
            abort(403);
        }

        $this->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'startDate'   => 'required|date',
            'endDate'     => 'nullable|date|after_or_equal:startDate',
            'maxCapacity' => 'nullable|integer|min:1',
        ]);

        CodeCamp::create([
            'name'         => $this->name,
            'slug'         => Str::slug($this->name),
            'description'  => $this->description ?: null,
            'start_date'   => $this->startDate,
            'end_date'     => $this->endDate ?: null,
            'max_capacity' => $this->maxCapacity ?: null,
            'status'       => 'upcoming',
            'created_by'   => Auth::id(),
        ]);

        $this->resetCreateForm();
        $this->showCreateForm = false;
        session()->flash('message', "Camp \"{$this->name}\" created successfully.");
    }

    private function resetCreateForm(): void
    {
        $this->name        = '';
        $this->description = '';
        $this->startDate   = '';
        $this->endDate     = '';
        $this->maxCapacity = '';
        $this->resetValidation();
    }

    public function render()
    {
        $query = CodeCamp::withCount(['enrollments as total_students' => fn($q) => $q->where('status', 'active')])
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('start_date');

        $camps = $query->paginate(15);

        $stats = [
            'total'              => CodeCamp::count(),
            'active'             => CodeCamp::active()->count(),
            'total_students'     => CampEnrollment::where('status', 'active')->count(),
            'completed'          => CodeCamp::completed()->count(),
        ];

        return view('livewire.admin.camps.index', [
            'camps' => $camps,
            'stats' => $stats,
        ]);
    }
}
