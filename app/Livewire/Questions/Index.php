<?php

namespace App\Livewire\Questions;

use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filterType = 'all';
    public $filterQuiz = 'all';
    public $filterAssessment = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => 'all'],
        'filterQuiz' => ['except' => 'all'],
        'filterAssessment' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Question::query()->with(['quiz', 'assessment', 'options']);

        // Search
        if ($this->search) {
            $query->where('question_text', 'like', '%' . $this->search . '%');
        }

        // Filter by type
        if ($this->filterType !== 'all') {
            $query->where('question_type', $this->filterType);
        }

        // Filter by quiz
        if ($this->filterQuiz !== 'all') {
            $query->where('quiz_id', $this->filterQuiz);
        }

        // Filter by assessment
        if ($this->filterAssessment !== 'all') {
            $query->where('assessment_id', $this->filterAssessment);
        }

        $questions = $query->orderBy('created_at', 'desc')->paginate(20);

        $questionTypes = [
            'multiple_choice' => 'Multiple Choice',
            'multiple_select' => 'Multiple Select',
            'true_false' => 'True/False',
            'short_answer' => 'Short Answer',
            'essay' => 'Essay',
        ];

        $quizzes = \App\Models\Quiz::orderBy('title')->get();
        $assessments = \App\Models\Assessment::orderBy('title')->get();

        return view('livewire.questions.index', [
            'questions' => $questions,
            'questionTypes' => $questionTypes,
            'quizzes' => $quizzes,
            'assessments' => $assessments,
        ]);
    }
}
