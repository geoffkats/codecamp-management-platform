<?php

namespace App\Livewire\Admin;

use App\Models\RegistrationRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class RegistrationRequests extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filterType = 'all';
    public $filterStatus = 'all';
    public $selectedRequestId = null;
    public $showDetailsModal = false;

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isSupervisor() && !$user->hasPermission('manage_users'))) {
            abort(403, 'Unauthorized - Admin or Supervisor access required');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function viewRequest(int $requestId): void
    {
        $this->selectedRequestId = $requestId;
        $this->showDetailsModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailsModal = false;
        $this->selectedRequestId = null;
    }

    public function updateStatus(int $requestId, string $status): void
    {
        $allowed = ['new', 'contacted', 'scheduled', 'closed'];
        if (!in_array($status, $allowed, true)) {
            return;
        }

        $request = RegistrationRequest::findOrFail($requestId);
        $request->update(['status' => $status]);

        session()->flash('message', 'Registration request status updated.');
    }

    public function getSelectedRequestProperty(): ?RegistrationRequest
    {
        if (!$this->selectedRequestId) {
            return null;
        }

        return RegistrationRequest::find($this->selectedRequestId);
    }

    public function render()
    {
        $query = RegistrationRequest::query();

        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->search) {
            $search = '%' . $this->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhere('organization_name', 'like', $search)
                    ->orWhere('role_title', 'like', $search)
                    ->orWhere('program_interest', 'like', $search)
                    ->orWhere('course_interest', 'like', $search);
            });
        }

        $requests = $query->orderByDesc('created_at')->paginate(15);

        $stats = [
            'total' => RegistrationRequest::count(),
            'new' => RegistrationRequest::where('status', 'new')->count(),
            'contacted' => RegistrationRequest::where('status', 'contacted')->count(),
            'scheduled' => RegistrationRequest::where('status', 'scheduled')->count(),
            'closed' => RegistrationRequest::where('status', 'closed')->count(),
        ];

        return view('livewire.admin.registration-requests', [
            'requests' => $requests,
            'stats' => $stats,
        ]);
    }
}
