<?php

namespace App\Livewire\Lessons;

use App\Models\Lesson;
use Livewire\Component;
use Livewire\WithPagination;

class LessonList extends Component
{
    use WithPagination;

    public $courseId;
    public $search = '';
    public $filterType = '';
    public $filterDifficulty = '';

    public function mount($courseId = null)
    {
        $this->courseId = $courseId;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Lesson::with(['course', 'module'])
            ->when($this->courseId, function ($q) {
                $q->where('course_id', $this->courseId);
            })
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('summary', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterType, function ($q) {
                $q->where('lesson_type', $this->filterType);
            })
            ->when($this->filterDifficulty, function ($q) {
                $q->where('difficulty_level', $this->filterDifficulty);
            });

        return view('livewire.lessons.lesson-list', [
            'lessons' => $query->orderBy('order_index')->paginate(12)
        ]);
    }
}

