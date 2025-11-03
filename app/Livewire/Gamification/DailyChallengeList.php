<?php

namespace App\Livewire\Gamification;

use App\Models\DailyChallenge;
use Livewire\Component;
use Livewire\WithPagination;

class DailyChallengeList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterActive = '';
    public $filterDifficulty = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = DailyChallenge::when($this->search, function ($q) {
            $q->where('title', 'like', '%' . $this->search . '%')
              ->orWhere('description', 'like', '%' . $this->search . '%');
        })
        ->when($this->filterActive !== '', function ($q) {
            $q->where('is_active', $this->filterActive);
        })
        ->when($this->filterDifficulty, function ($q) {
            $q->where('difficulty_level', $this->filterDifficulty);
        })
        ->orderByDesc('date');

        return view('livewire.gamification.daily-challenge-list', [
            'challenges' => $query->paginate(12)
        ]);
    }
}

