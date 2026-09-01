<?php

namespace App\Livewire\Lessons;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public function render()
    {
        return view('livewire.lessons.create');
    }
}
