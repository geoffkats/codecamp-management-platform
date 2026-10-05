<?php

namespace App\Livewire\ContentApprovals;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\ContentApproval;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    private const TYPES = [
        'course' => Course::class,
        'module' => CourseModule::class,
        'lesson' => Lesson::class,
        'assessment' => Assessment::class,
        'assignment' => Assignment::class,
    ];

    private const STATUSES = ['pending', 'approved', 'rejected', 'all'];

    public string $filterStatus = 'pending';

    public string $filterType = 'all';

    public string $search = '';

    protected $queryString = [
        'filterStatus' => ['except' => 'pending'],
        'filterType' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        abort_unless(Gate::allows('review_content'), 403, 'You do not have permission to review content.');

        if (! in_array($this->filterStatus, self::STATUSES, true)) {
            $this->filterStatus = 'pending';
        }
    }

    public function filterByStatus(string $status): void
    {
        $this->filterStatus = in_array($status, self::STATUSES, true) ? $status : 'pending';
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function approveContent(int $approvalId): void
    {
        abort_unless(Gate::allows('review_content'), 403);

        $approval = ContentApproval::where('status', 'pending')->findOrFail($approvalId);
        $approval->approve(Auth::user());

        session()->flash('message', 'Approved: '.($approval->approvable->title ?? 'content').'. The trainer has been told.');
    }

    public function approveAll(): void
    {
        abort_unless(Gate::allows('review_content'), 403);

        $pending = $this->filteredQuery()->where('status', 'pending')->get();

        foreach ($pending as $approval) {
            $approval->approve(Auth::user());
        }

        session()->flash('message', 'Approved '.$pending->count().' '.str('item')->plural($pending->count()).'.');
    }

    private function filteredQuery()
    {
        return ContentApproval::query()
            ->when(isset(self::TYPES[$this->filterType]), fn ($q) => $q->where('approvable_type', self::TYPES[$this->filterType]))
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($q) use ($term) {
                    $q->whereHasMorph('approvable', array_values(self::TYPES), fn ($m) => $m->where('title', 'like', $term))
                        ->orWhereHas('submitter', fn ($u) => $u->where('name', 'like', $term));
                });
            });
    }

    public function render()
    {
        $approvals = $this->filteredQuery()
            ->with([
                'submitter:id,name',
                'reviewer:id,name',
                'approvable' => fn (MorphTo $morph) => $morph->morphWith([
                    Lesson::class => ['course:id,title'],
                    CourseModule::class => ['course:id,title'],
                    Assessment::class => ['course:id,title'],
                    Assignment::class => ['course:id,title'],
                ]),
            ])
            ->when($this->filterStatus !== 'all', fn ($q) => $q->where('status', $this->filterStatus))
            ->when(
                $this->filterStatus === 'pending',
                fn ($q) => $q->orderBy('submitted_at'),
                fn ($q) => $q->orderByDesc('reviewed_at')->orderByDesc('submitted_at')
            )
            ->paginate(15);

        $counts = $this->filteredQuery()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $oldestPending = ContentApproval::where('status', 'pending')->min('submitted_at');

        return view('livewire.content-approvals.index', [
            'approvals' => $approvals,
            'counts' => $counts,
            'oldestPending' => $oldestPending ? \Illuminate\Support\Carbon::parse($oldestPending) : null,
            'reviewedThisWeek' => ContentApproval::whereIn('status', ['approved', 'rejected'])
                ->where('reviewed_at', '>=', now()->startOfWeek())
                ->count(),
        ]);
    }
}
