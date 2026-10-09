<?php

namespace App\Livewire\CampRevisions;

use App\Models\CampContentRevision;
use App\Models\CodeCamp;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $status = 'all';

    public ?int $campId = null;

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user->isTeacher() || CampContentRevision::canReview($user), 403);

        if (CampContentRevision::canReview($user)) {
            $this->status = 'pending';
        }
    }

    public function setStatus(string $status): void
    {
        $this->status = array_key_exists($status, CampContentRevision::STATUSES) ? $status : 'all';
        $this->resetPage();
    }

    public function updatedCampId(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $isReviewer = CampContentRevision::canReview($user);

        $base = CampContentRevision::query()
            ->when(! $isReviewer, fn ($q) => $q->where('trainer_id', $user->id))
            ->when($this->campId, fn ($q) => $q->where('camp_id', $this->campId));

        $revisions = (clone $base)
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->with(['camp:id,name', 'course:id,title', 'trainer'])
            ->withCount(['files', 'comments'])
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->latest('submitted_at')
            ->paginate(15);

        return view('livewire.camp-revisions.index', [
            'revisions' => $revisions,
            'counts' => (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'camps' => CodeCamp::whereIn('id', (clone $base)->select('camp_id'))->orderByDesc('start_date')->get(['id', 'name']),
            'isReviewer' => $isReviewer,
            'canSubmit' => $user->isTeacher() || $user->isAdmin(),
        ]);
    }
}
