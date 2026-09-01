<?php

namespace App\Livewire\Modules;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public function render()
    {
        return view('livewire.modules.create');
    }
}
