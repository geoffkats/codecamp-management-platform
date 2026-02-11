<?php

namespace App\Livewire\Certificates;

use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Certificate $certificate;

    public function mount(Certificate $certificate): void
    {
        $user = Auth::user();

        if (!$user || ((int) $certificate->user_id !== (int) $user->id && !$user->hasAnyRole(['admin']))) {
            abort(403);
        }

        $this->certificate = $certificate->load(['user', 'course']);
    }

    public function render()
    {
        return view('livewire.certificates.show', [
            'certificate' => $this->certificate,
        ]);
    }
}
