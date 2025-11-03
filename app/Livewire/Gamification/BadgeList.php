<?php

namespace App\Livewire\Gamification;

use App\Models\Badge;
use Livewire\Component;
use Livewire\WithPagination;

class BadgeList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterActive = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Badge::when($this->search, function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%')
              ->orWhere('description', 'like', '%' . $this->search . '%');
        })
        ->when($this->filterActive !== '', function ($q) {
            $q->where('is_active', $this->filterActive);
        });

        return view('livewire.gamification.badge-list', [
            'badges' => $query->paginate(12)
        ]);
    }
}
