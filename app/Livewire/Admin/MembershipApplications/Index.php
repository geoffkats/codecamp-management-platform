<?php

namespace App\Livewire\Admin\MembershipApplications;

use App\Models\RegistrationRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterStatus = 'all';

    #[Url]
    public string $filterPayment = 'all';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isAdmin() && ! $user->isSupervisor() && ! $user->hasPermission('manage_users'))) {
            abort(403, 'Unauthorized - Admin or Supervisor access required');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPayment(): void
    {
        $this->resetPage();
    }

    public function updateStatus(int $requestId, string $status): void
    {
        if (! in_array($status, ['new', 'contacted', 'scheduled', 'closed'], true)) {
            return;
        }

        RegistrationRequest::where('type', 'membership')->findOrFail($requestId)->update(['status' => $status]);
        session()->flash('message', 'Membership status updated.');
    }

    public function render()
    {
        $query = RegistrationRequest::query()->where('type', 'membership');

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterPayment === 'paid') {
            $query->where('payment_status', 'paid');
        } elseif ($this->filterPayment === 'unpaid') {
            $query->where(function ($q) {
                $q->whereNull('payment_status')->orWhere('payment_status', '!=', 'paid');
            });
        }

        if ($this->search !== '') {
            $search = '%'.$this->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhere('organization_name', 'like', $search)
                    ->orWhere('meta', 'like', $search);
            });
        }

        $applications = $query->orderByDesc('created_at')->paginate(20);

        $stats = [
            'total' => RegistrationRequest::where('type', 'membership')->count(),
            'new' => RegistrationRequest::where('type', 'membership')->where('status', 'new')->count(),
            'unpaid' => RegistrationRequest::where('type', 'membership')->where(function ($q) {
                $q->whereNull('payment_status')->orWhere('payment_status', '!=', 'paid');
            })->where('status', '!=', 'closed')->count(),
            'closed' => RegistrationRequest::where('type', 'membership')->where('status', 'closed')->count(),
        ];

        return view('livewire.admin.membership-applications.index', [
            'applications' => $applications,
            'stats' => $stats,
        ]);
    }
}
