<?php

namespace App\Livewire\Questions;

use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Edit Question')]
class Edit extends Component
{
    public Question $question;

    public function mount(Question $question): void
    {
        abort_unless($question->isVisibleTo(Auth::user()), 403);
        $this->question = $question;
    }

    public function render()
    {
        return view('livewire.questions.edit');
    }
}
