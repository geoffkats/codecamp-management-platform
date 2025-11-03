<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use Livewire\Component;
use Livewire\WithPagination;

class AssessmentList extends Component
{
    use WithPagination;

    public $courseId;
    public $lessonId;
    public $search = '';
    public $filterType = '';

    public function mount($courseId = null, $lessonId = null)
    {
        $this->courseId = $courseId;
        $this->lessonId = $lessonId;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Assessment::with(['course', 'lesson'])
            ->when($this->courseId, function ($q) {
                $q->where('course_id', $this->courseId);
            })
            ->when($this->lessonId, function ($q) {
                $q->where('lesson_id', $this->lessonId);
            })
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterType, function ($q) {
                $q->where('assessment_type', $this->filterType);
            });

        return view('livewire.assessments.assessment-list', [
            'assessments' => $query->paginate(12)
        ]);
    }
}

