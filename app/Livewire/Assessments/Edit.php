<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    use WithPagination, WithFileUploads;

    public Assessment $assessment;
    public $title;
    public $description;
    public $assessment_type;
    public $max_attempts = 1;
    public $time_limit_minutes;
    public $passing_score = 70;
    public $xp_reward = 50;
    public $is_required = false;
    public $show_results_immediately = true;
    public $is_randomized = false;
    public $shuffle_options = false;
    public $show_correct_answers = true;
    public $allow_review = true;
    
    // Question editor state
    public $showQuestionModal = false;
    public $editingQuestionId = null;
    public $questionFormData = [
        'question_text' => '',
        'question_type' => 'multiple_choice',
        'points' => 10,
        'order' => 0,
        'explanation' => '',
        'image_url' => '',
        'settings' => [],
    ];
    public $questionOptions = [];
    
    // File uploads
    public $questionImage = null;
    public $optionImages = []; // Array to track option images by index
    public $tempOptionImages = []; // Temporary storage for option image uploads
    
    // Student submissions view
    public $showSubmissions = false;
    public $selectedAttempt = null;
    
    // Question type specific data
    public $rubricCriteria = []; // For rubric_criteria question type
    public $matchingPairs = []; // For matching question type
    public $orderingItems = []; // For ordering question type
    public $ratingScaleSettings = []; // For rating question type
    public $fillBlankSettings = []; // For fill_blank question type
    public $codeSubmissionSettings = []; // For code_submission question type
    
    protected $rules = [
        'title' => 'required|string|max:255',
        'assessment_type' => 'required|string',
        'max_attempts' => 'nullable|integer|min:1',
        'time_limit_minutes' => 'nullable|integer|min:1',
        'passing_score' => 'nullable|integer|min:0|max:100',
        'xp_reward' => 'nullable|integer|min:0',
    ];

    public function mount(Assessment $assessment, $attempt = null)
    {
        // Check authorization
        if (!$assessment->course) {
            abort(404);
        }
        
        $user = Auth::user();
        $hasGlobalEdit = $user->hasPermission('edit_courses') || $user->isAdmin();

        if ($user->isTeacher() && !$hasGlobalEdit && $assessment->course->instructor_id !== $user->id) {
            abort(403, 'You can only edit assessments for your own courses.');
        }

        $this->assessment = $assessment->load('questions.options');
        $this->title = $assessment->title;
        $this->description = $assessment->description;
        $this->assessment_type = $assessment->assessment_type;
        $this->max_attempts = $assessment->max_attempts;
        $this->time_limit_minutes = $assessment->time_limit_minutes;
        $this->passing_score = $assessment->passing_score;
        $this->xp_reward = $assessment->xp_reward;
        $this->is_required = $assessment->is_required;
        $this->show_results_immediately = $assessment->show_results_immediately;
        $this->is_randomized = $assessment->is_randomized ?? false;
        $this->shuffle_options = $assessment->shuffle_options ?? false;
        $this->show_correct_answers = $assessment->show_correct_answers ?? true;
        $this->allow_review = $assessment->allow_review ?? true;
        
        // Auto-open attempt if provided
        if ($attempt) {
            $this->startGrading($attempt);
        }
    }

    public function updateAssessment()
    {
        $this->validate();

        $this->assessment->update([
            'title' => $this->title,
            'description' => $this->description,
            'assessment_type' => $this->assessment_type,
            'max_attempts' => $this->max_attempts,
            'time_limit_minutes' => $this->time_limit_minutes,
            'passing_score' => $this->passing_score,
            'xp_reward' => $this->xp_reward,
            'is_required' => $this->is_required,
            'show_results_immediately' => $this->show_results_immediately,
            'is_randomized' => $this->is_randomized,
            'shuffle_options' => $this->shuffle_options,
            'show_correct_answers' => $this->show_correct_answers,
            'allow_review' => $this->allow_review,
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        session()->flash('message', 'Assessment updated and approved successfully!');
    }

    public function openQuestionModal($questionId = null)
    {
        if ($questionId) {
            $question = Question::with('options')->find($questionId);
            $this->editingQuestionId = $questionId;
            $settings = $question->settings ?? [];
            if (!is_array($settings)) {
                $settings = json_decode($settings, true) ?? [];
            }
            $this->questionFormData = [
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'points' => $question->points,
                'order' => $question->order,
                'explanation' => $question->explanation,
                'image_url' => $question->image_url,
                'settings' => $settings,
            ];
            $this->questionOptions = $question->options->map(function ($option) {
                return [
                    'id' => $option->id,
                    'option_text' => $option->option_text,
                    'is_correct' => $option->is_correct,
                    'order' => $option->order,
                    'image_url' => $option->image_url,
                ];
            })->toArray();
            
            // Load type-specific data from settings
            $settings = $question->settings ?? [];
            if (!is_array($settings)) {
                $settings = json_decode($settings, true) ?? [];
            }
            
            $this->rubricCriteria = $settings['rubric_criteria'] ?? [];
            $this->matchingPairs = $settings['matching_pairs'] ?? [];
            $this->orderingItems = $settings['ordering_items'] ?? [];
            $this->ratingScaleSettings = $settings['rating_scale'] ?? ['min' => 1, 'max' => 5, 'labels' => []];
            $this->fillBlankSettings = $settings['fill_blank'] ?? ['blanks' => []];
            $this->codeSubmissionSettings = $settings['code_submission'] ?? ['language' => 'javascript', 'template' => ''];
        } else {
            $this->resetQuestionForm();
        }
        
        // Reset file uploads
        $this->questionImage = null;
        $this->tempOptionImages = [];
        
        $this->showQuestionModal = true;
    }

    public function closeQuestionModal()
    {
        $this->showQuestionModal = false;
        $this->editingQuestionId = null;
        $this->resetQuestionForm();
    }

    public function resetQuestionForm()
    {
        $nextOrder = $this->assessment->questions()->max('order') + 1 ?? 1;
        $this->questionFormData = [
            'question_text' => '',
            'question_type' => $this->getDefaultQuestionType(),
            'points' => 10,
            'order' => $nextOrder,
            'explanation' => '',
            'image_url' => '',
            'settings' => [],
        ];
        $this->questionOptions = [];
        $this->questionImage = null;
        $this->tempOptionImages = [];
        
        // Reset type-specific data
        $this->rubricCriteria = [];
        $this->matchingPairs = [];
        $this->orderingItems = [];
        $this->ratingScaleSettings = ['min' => 1, 'max' => 5, 'labels' => []];
        $this->fillBlankSettings = ['blanks' => []];
        $this->codeSubmissionSettings = ['language' => 'javascript', 'template' => ''];
    }
    
    public function updatedQuestionFormDataQuestionType()
    {
        // Clear options when changing question type (some types don't need options)
        if (!in_array($this->questionFormData['question_type'], ['multiple_choice', 'true_false', 'choice', 'matching'])) {
            $this->questionOptions = [];
        }
        
        // Reset type-specific data when switching types
        $this->rubricCriteria = [];
        $this->matchingPairs = [];
        $this->orderingItems = [];
        $this->ratingScaleSettings = ['min' => 1, 'max' => 5, 'labels' => []];
        $this->fillBlankSettings = ['blanks' => []];
        $this->codeSubmissionSettings = ['language' => 'javascript', 'template' => ''];
    }
    
    // Rubric Criteria Methods
    public function addRubricCriterion()
    {
        $this->rubricCriteria[] = [
            'name' => '',
            'description' => '',
            'max_points' => 0,
            'weight' => 1,
            'performance_levels' => [
                ['level' => 'Excellent', 'points' => 100, 'description' => ''],
                ['level' => 'Good', 'points' => 75, 'description' => ''],
                ['level' => 'Satisfactory', 'points' => 50, 'description' => ''],
                ['level' => 'Needs Improvement', 'points' => 25, 'description' => ''],
            ],
        ];
    }
    
    public function removeRubricCriterion($index)
    {
        unset($this->rubricCriteria[$index]);
        $this->rubricCriteria = array_values($this->rubricCriteria);
    }
    
    // Matching Pairs Methods
    public function addMatchingPair()
    {
        $this->matchingPairs[] = [
            'left_item' => '',
            'right_item' => '',
        ];
    }
    
    public function removeMatchingPair($index)
    {
        unset($this->matchingPairs[$index]);
        $this->matchingPairs = array_values($this->matchingPairs);
    }
    
    // Ordering Items Methods
    public function addOrderingItem()
    {
        $this->orderingItems[] = [
            'item_text' => '',
            'correct_order' => count($this->orderingItems) + 1,
        ];
    }
    
    public function removeOrderingItem($index)
    {
        unset($this->orderingItems[$index]);
        $this->orderingItems = array_values($this->orderingItems);
        // Reorder
        foreach ($this->orderingItems as $i => $item) {
            $this->orderingItems[$i]['correct_order'] = $i + 1;
        }
    }
    
    // Fill Blank Methods
    public function addFillBlank()
    {
        $this->fillBlankSettings['blanks'][] = [
            'position' => '',
            'correct_answer' => '',
            'case_sensitive' => false,
            'alternative_answers' => [],
        ];
    }
    
    public function removeFillBlank($index)
    {
        unset($this->fillBlankSettings['blanks'][$index]);
        $this->fillBlankSettings['blanks'] = array_values($this->fillBlankSettings['blanks']);
    }
    
    public function addAlternativeAnswer($blankIndex)
    {
        if (!isset($this->fillBlankSettings['blanks'][$blankIndex]['alternative_answers'])) {
            $this->fillBlankSettings['blanks'][$blankIndex]['alternative_answers'] = [];
        }
        $this->fillBlankSettings['blanks'][$blankIndex]['alternative_answers'][] = '';
    }
    
    public function removeQuestionImage()
    {
        if ($this->questionFormData['image_url'] && Storage::disk('public')->exists($this->questionFormData['image_url'])) {
            Storage::disk('public')->delete($this->questionFormData['image_url']);
        }
        $this->questionFormData['image_url'] = '';
        $this->questionImage = null;
    }
    
    public function removeOptionImage($index)
    {
        if (isset($this->questionOptions[$index]['image_url']) && $this->questionOptions[$index]['image_url']) {
            if (Storage::disk('public')->exists($this->questionOptions[$index]['image_url'])) {
                Storage::disk('public')->delete($this->questionOptions[$index]['image_url']);
            }
            $this->questionOptions[$index]['image_url'] = '';
        }
        unset($this->tempOptionImages[$index]);
    }

    public function getDefaultQuestionType()
    {
        // Return default question type based on assessment type
        return match($this->assessment_type) {
            'quiz' => 'multiple_choice',
            'assignment' => 'essay',
            'pre_project_test' => 'multiple_choice',
            'post_project_test' => 'multiple_choice',
            'unit_survey' => 'rating',
            'rubric_assessment' => 'rubric_criteria',
            'peer_review' => 'rating',
            'self_assessment' => 'rating',
            default => 'multiple_choice',
        };
    }

    public function getAvailableQuestionTypes()
    {
        // Return available question types based on assessment type
        return match($this->assessment_type) {
            'quiz' => [
                'multiple_choice' => 'Multiple Choice',
                'true_false' => 'True/False',
                'short_answer' => 'Short Answer',
                'essay' => 'Essay',
                'matching' => 'Matching',
                'fill_blank' => 'Fill in the Blank',
                'ordering' => 'Ordering',
            ],
            'assignment' => [
                'essay' => 'Essay Question',
                'file_upload' => 'File Upload',
                'code_submission' => 'Code Submission',
                'rubric_criteria' => 'Rubric Criteria',
            ],
            'pre_project_test' => [
                'multiple_choice' => 'Multiple Choice',
                'true_false' => 'True/False',
                'short_answer' => 'Short Answer',
                'essay' => 'Essay',
            ],
            'post_project_test' => [
                'multiple_choice' => 'Multiple Choice',
                'true_false' => 'True/False',
                'short_answer' => 'Short Answer',
                'essay' => 'Essay',
                'matching' => 'Matching',
                'fill_blank' => 'Fill in the Blank',
            ],
            'unit_survey' => [
                'rating' => 'Rating Scale',
                'choice' => 'Single Choice',
                'multiple_choice' => 'Multiple Choice',
                'short_answer' => 'Text Response',
            ],
            'rubric_assessment' => [
                'rubric_criteria' => 'Rubric Criteria',
                'rating' => 'Rating Scale',
            ],
            'peer_review' => [
                'rating' => 'Rating',
                'choice' => 'Choice',
                'multiple_choice' => 'Multiple Choice',
                'short_answer' => 'Text Feedback',
            ],
            'self_assessment' => [
                'rating' => 'Self-Rating',
                'choice' => 'Single Choice',
                'short_answer' => 'Text Response',
                'essay' => 'Reflection',
            ],
            default => [
                'multiple_choice' => 'Multiple Choice',
                'essay' => 'Essay',
            ],
        };
    }

    public function addQuestionOption()
    {
        $this->questionOptions[] = [
            'option_text' => '',
            'is_correct' => false,
            'order' => count($this->questionOptions) + 1,
        ];
    }

    public function removeQuestionOption($index)
    {
        unset($this->questionOptions[$index]);
        $this->questionOptions = array_values($this->questionOptions);
        // Reorder
        foreach ($this->questionOptions as $i => $option) {
            $this->questionOptions[$i]['order'] = $i + 1;
        }
    }

    public function saveQuestion()
    {
        $this->validate([
            'questionFormData.question_text' => 'required|string',
            'questionFormData.question_type' => 'required|string',
            'questionFormData.points' => 'required|integer|min:0',
            'questionImage' => 'nullable|image|max:5120', // 5MB max
        ]);

        // Handle question image upload
        $questionImagePath = $this->questionFormData['image_url'];
        if ($this->questionImage) {
            // Delete old image if exists
            if ($this->questionFormData['image_url'] && Storage::disk('public')->exists($this->questionFormData['image_url'])) {
                Storage::disk('public')->delete($this->questionFormData['image_url']);
            }
            $questionImagePath = $this->questionImage->store('assessments/questions', 'public');
        }

        // Validate based on question type
        switch ($this->questionFormData['question_type']) {
            case 'multiple_choice':
            case 'choice':
            case 'true_false':
                if (empty($this->questionOptions) || count(array_filter($this->questionOptions, fn($o) => !empty($o['option_text']))) === 0) {
                    session()->flash('error', 'Please add at least one option for this question type.');
                    return;
                }
                break;
            case 'matching':
                if (empty($this->matchingPairs) || count(array_filter($this->matchingPairs, fn($p) => !empty($p['left_item']) && !empty($p['right_item']))) === 0) {
                    session()->flash('error', 'Please add at least one matching pair.');
                    return;
                }
                break;
            case 'ordering':
                if (empty($this->orderingItems) || count($this->orderingItems) < 2 || count(array_filter($this->orderingItems, fn($i) => !empty($i['item_text']))) < 2) {
                    session()->flash('error', 'Please add at least two items to order.');
                    return;
                }
                break;
            case 'rubric_criteria':
                if (empty($this->rubricCriteria) || count(array_filter($this->rubricCriteria, fn($c) => !empty($c['name']))) === 0) {
                    session()->flash('error', 'Please add at least one rubric criterion.');
                    return;
                }
                break;
            case 'fill_blank':
                if (empty($this->fillBlankSettings['blanks']) || count(array_filter($this->fillBlankSettings['blanks'], fn($b) => !empty($b['correct_answer']))) === 0) {
                    session()->flash('error', 'Please add at least one blank with a correct answer.');
                    return;
                }
                break;
        }

        // Build settings based on question type
        $settings = [];
        
        switch ($this->questionFormData['question_type']) {
            case 'rubric_criteria':
                $settings['rubric_criteria'] = $this->rubricCriteria;
                break;
            case 'matching':
                $settings['matching_pairs'] = $this->matchingPairs;
                break;
            case 'ordering':
                $settings['ordering_items'] = $this->orderingItems;
                break;
            case 'rating':
                $settings['rating_scale'] = $this->ratingScaleSettings;
                break;
            case 'fill_blank':
                $settings['fill_blank'] = $this->fillBlankSettings;
                break;
            case 'code_submission':
                $settings['code_submission'] = $this->codeSubmissionSettings;
                break;
            case 'file_upload':
                $settings['allowed_types'] = $this->questionFormData['settings']['allowed_types'] ?? null;
                $settings['max_size'] = $this->questionFormData['settings']['max_size'] ?? null;
                $settings['max_files'] = $this->questionFormData['settings']['max_files'] ?? null;
                break;
            case 'essay':
            case 'short_answer':
                $settings['min_words'] = $this->questionFormData['settings']['min_words'] ?? null;
                $settings['max_words'] = $this->questionFormData['settings']['max_words'] ?? null;
                break;
        }

        $questionData = [
            'assessment_id' => $this->assessment->id,
            'question_text' => $this->questionFormData['question_text'],
            'question_type' => $this->questionFormData['question_type'],
            'points' => $this->questionFormData['points'],
            'order' => $this->questionFormData['order'],
            'explanation' => $this->questionFormData['explanation'] ?? null,
            'image_url' => $questionImagePath,
            'settings' => !empty($settings) ? $settings : null,
        ];

        if ($this->editingQuestionId) {
            $question = Question::find($this->editingQuestionId);
            $question->update($questionData);
        } else {
            $question = Question::create($questionData);
        }

        // Save options for question types that use them
        if (in_array($this->questionFormData['question_type'], ['multiple_choice', 'true_false', 'choice'])) {
            // Delete old options if editing
            if ($this->editingQuestionId) {
                // Delete old option images
                foreach ($question->options as $oldOption) {
                    if ($oldOption->image_url && Storage::disk('public')->exists($oldOption->image_url)) {
                        Storage::disk('public')->delete($oldOption->image_url);
                    }
                }
                $question->options()->delete();
            }

            foreach ($this->questionOptions as $index => $optionData) {
                if (!empty($optionData['option_text'])) {
                    // Handle option image upload
                    $optionImagePath = $optionData['image_url'] ?? null;
                    if (isset($this->tempOptionImages[$index]) && $this->tempOptionImages[$index]) {
                        $optionImagePath = $this->tempOptionImages[$index]->store('assessments/options', 'public');
                    }

                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $optionData['option_text'],
                        'is_correct' => $optionData['is_correct'] ?? false,
                        'order' => $optionData['order'] ?? 0,
                        'image_url' => $optionImagePath,
                    ]);
                }
            }
        }
        
        // Save matching pairs as options
        if ($this->questionFormData['question_type'] === 'matching') {
            if ($this->editingQuestionId) {
                $question->options()->delete();
            }
            
            foreach ($this->matchingPairs as $index => $pair) {
                if (!empty($pair['left_item']) && !empty($pair['right_item'])) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $pair['left_item'] . '|' . $pair['right_item'], // Store as delimiter-separated
                        'is_correct' => true, // All matching pairs are correct
                        'order' => $index + 1,
                    ]);
                }
            }
        }
        
        // Save ordering items as options
        if ($this->questionFormData['question_type'] === 'ordering') {
            if ($this->editingQuestionId) {
                $question->options()->delete();
            }
            
            foreach ($this->orderingItems as $index => $item) {
                if (!empty($item['item_text'])) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $item['item_text'],
                        'is_correct' => true,
                        'order' => $item['correct_order'] ?? ($index + 1),
                    ]);
                }
            }
        }

        // Refresh assessment
        $this->assessment->refresh();
        $this->assessment->load('questions.options');
        
        // Reset file uploads
        $this->questionImage = null;
        $this->tempOptionImages = [];
        
        $this->closeQuestionModal();
        session()->flash('message', 'Question saved successfully!');
    }

    public function deleteQuestion($questionId)
    {
        Question::find($questionId)->delete();
        $this->assessment->refresh();
        $this->assessment->load('questions.options');
        session()->flash('message', 'Question deleted successfully!');
    }

    public function reorderQuestions($questionIds)
    {
        foreach ($questionIds as $index => $questionId) {
            Question::where('id', $questionId)->update(['order' => $index + 1]);
        }
        $this->assessment->refresh();
        $this->assessment->load('questions.options');
    }

    public $gradingAttempt = false;
    public $attemptScore = 0;
    public $attemptFeedback = '';
    public $attemptMaxScore = 100;

    public function viewAttempt($attemptId)
    {
        $attempt = AssessmentAttempt::with(['user', 'assessment.questions.options'])
            ->find($attemptId);
        
        // Verify authorization - teacher can only view attempts from their scope
        if ($attempt && Auth::user()->isIctTeacher()) {
            $schoolId = Auth::user()->ictSchoolId();
            if (!$schoolId || $attempt->student_type !== 'ict' || (int) $attempt->school_id !== (int) $schoolId) {
                abort(403, 'You can only view attempts from your school.');
            }
        } elseif ($attempt && Auth::user()->isTeacher()) {
            if ($attempt->student_type !== 'codecamp') {
                abort(403, 'You can only view attempts from your own courses.');
            }

            if (!$attempt->assessment->course || $attempt->assessment->course->instructor_id !== Auth::id()) {
                abort(403, 'You can only view attempts from your own courses.');
            }
        }
        
        $this->selectedAttempt = $attempt;
        
        // Initialize grading fields if assessment type is assignment
        if ($attempt && $attempt->assessment->assessment_type === 'assignment') {
            $this->attemptScore = $attempt->score ?? 0;
            $this->attemptMaxScore = $attempt->assessment->max_points ?? 100;
            $answers = $attempt->answers ?? [];
            $this->attemptFeedback = $answers['feedback'] ?? '';
        }
    }

    public function startGrading($attemptId)
    {
        $this->viewAttempt($attemptId);
        $this->gradingAttempt = true;
    }

    public function closeAttemptView()
    {
        $this->selectedAttempt = null;
        $this->gradingAttempt = false;
        $this->attemptScore = 0;
        $this->attemptFeedback = '';
    }

    public function saveAttemptGrade()
    {
        if (!$this->selectedAttempt) {
            return;
        }

        $this->validate([
            'attemptScore' => 'required|numeric|min:0|max:' . $this->attemptMaxScore,
            'attemptFeedback' => 'nullable|string',
        ]);

        $attempt = $this->selectedAttempt;
        $assessment = $attempt->assessment;
        
        // Calculate percentage
        $percentage = ($this->attemptScore / $this->attemptMaxScore) * 100;
        $passed = $percentage >= ($assessment->passing_score ?? 70);
        
        // Update attempt with grade
        $answers = $attempt->answers ?? [];
        $answers['feedback'] = $this->attemptFeedback;
        $answers['graded_at'] = now()->toIso8601String();
        $answers['graded_by'] = Auth::id();
        
        $attempt->update([
            'score' => round($percentage, 2),
            'is_passed' => $passed,
            'answers' => $answers,
            'auto_scored' => false,
            'is_locked' => true,
            'teacher_id' => Auth::id(),
        ]);

        // Create Grade record
        \App\Models\Grade::updateOrCreate(
            [
                'user_id' => $attempt->user_id,
                'course_id' => $assessment->course_id,
                'gradeable_type' => Assessment::class,
                'gradeable_id' => $attempt->assessment_id,
            ],
            [
                'score' => $this->attemptScore,
                'max_score' => $this->attemptMaxScore,
                'percentage' => round($percentage, 2),
                'letter_grade' => $this->calculateLetterGrade($percentage),
                'feedback' => json_encode(['feedback' => $this->attemptFeedback]),
                'graded_by' => Auth::id(),
                'graded_at' => now(),
                'is_final' => true,
            ]
        );

        // Award XP if passed
        if ($passed && $assessment->xp_reward) {
            $user = $attempt->user;
            if (!$user->points) {
                \App\Models\UserPoint::create([
                    'user_id' => $user->id,
                    'total_points' => 0,
                    'level' => 1,
                ]);
            }
            $user->points->increment('total_points', $assessment->xp_reward);
        }

        // Send notification
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyAssessmentResult(
                $attempt->user,
                $assessment,
                $passed,
                round($percentage, 1)
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send grade notification', ['error' => $e->getMessage()]);
        }

        session()->flash('message', 'Grade saved successfully!');
        $this->closeAttemptView();
        $this->assessment->refresh();
    }

    private function calculateLetterGrade($percentage)
    {
        if ($percentage >= 97) return 'A+';
        if ($percentage >= 93) return 'A';
        if ($percentage >= 90) return 'A-';
        if ($percentage >= 87) return 'B+';
        if ($percentage >= 83) return 'B';
        if ($percentage >= 80) return 'B-';
        if ($percentage >= 77) return 'C+';
        if ($percentage >= 73) return 'C';
        if ($percentage >= 70) return 'C-';
        if ($percentage >= 67) return 'D+';
        if ($percentage >= 63) return 'D';
        if ($percentage >= 60) return 'D-';
        return 'F';
    }

    public function render()
    {
        $questions = $this->assessment->questions()->orderBy('order')->get();
        $totalPoints = $questions->sum('points');
        
        // Get all attempts for teachers/admins - scoped by visibility
        $attempts = AssessmentAttempt::with('user')
            ->visibleTo(Auth::user())
            ->where('assessment_id', $this->assessment->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->paginate(10);
        
        return view('livewire.assessments.edit', [
            'questions' => $questions,
            'totalPoints' => $totalPoints,
            'availableQuestionTypes' => $this->getAvailableQuestionTypes(),
            'attempts' => $attempts,
        ]);
    }
}
