<?php

namespace App\Livewire\Questions;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('New Question')]
class Create extends Component
{
    public function render()
    {
        return view('livewire.questions.create');
    }
}
