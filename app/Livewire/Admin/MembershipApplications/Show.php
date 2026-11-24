<?php

namespace App\Livewire\Admin\MembershipApplications;

use App\Models\RegistrationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public RegistrationRequest $application;

    public function mount(RegistrationRequest $application): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isAdmin() && ! $user->isSupervisor() && ! $user->hasPermission('manage_users'))) {
            abort(403, 'Unauthorized - Admin or Supervisor access required');
        }

        abort_unless($application->isMembership(), 404);

        $this->application = $application->load('payments');
    }

    public function updateStatus(string $status): void
    {
        if (! in_array($status, ['new', 'contacted', 'scheduled', 'closed'], true)) {
            return;
        }

        $this->application->update(['status' => $status]);
        $this->application->refresh();
        session()->flash('message', 'Membership status updated.');
    }

    public function markPaidOffline(): void
    {
        if ($this->application->isPaid()) {
            return;
        }

        $this->application->update([
            'payment_status' => 'paid',
            'meta' => array_merge($this->application->meta ?? [], [
                'paid_offline_by' => Auth::user()->name,
                'paid_offline_at' => now()->toDateTimeString(),
            ]),
        ]);
        $this->application->refresh()->load('payments');
        session()->flash('message', 'Application fee marked as paid.');
    }

    public function deleteApplication(): void
    {
        $meta = $this->application->meta ?? [];

        foreach (['term_report_1', 'term_report_2', 'term_report_3'] as $field) {
            $path = $meta[$field] ?? null;
            if (filled($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $directory = 'membership-reports/'.$this->application->id;
        if (Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->deleteDirectory($directory);
        }

        $name = $this->application->full_name;
        $this->application->delete();

        session()->flash('message', 'Membership application for '.$name.' was deleted.');

        $this->redirect(route('admin.membership-applications.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.membership-applications.show');
    }
}
