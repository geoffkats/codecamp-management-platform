<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="flex flex-col gap-6 p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('curriculum.builder', $assessment->course) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Assessment</h1>
                </div>
                <p class="text-gray-600 dark:text-gray-400">{{ $assessment->course->title }}</p>
            </div>
            <div class="flex gap-3">
                <flux:button href="{{ route('assessments.show', $assessment) }}" variant="outline" wire:navigate>
                    View Assessment
                </flux:button>
            </div>
        </div>

        @if(session()->has('message'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 flex items-center gap-3">
                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        @if(session()->has('error'))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 flex items-center gap-3">
                <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Assessment Details Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Assessment Details</h2>
            </div>
            <form wire:submit.prevent="updateAssessment" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Title *</flux:label>
                        <flux:input wire:model="title" />
                        @error('title') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Assessment Type</flux:label>
                        <flux:select wire:model="assessment_type" disabled>
                            <option value="{{ $assessment_type }}">{{ ucfirst(str_replace('_', ' ', $assessment_type)) }}</option>
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description" rows="4" />
                    </flux:field>

                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>Max Attempts</flux:label>
                            <flux:input type="number" wire:model="max_attempts" min="1" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Time Limit (minutes)</flux:label>
                            <flux:input type="number" wire:model="time_limit_minutes" min="1" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Passing Score (%)</flux:label>
                            <flux:input type="number" wire:model="passing_score" min="0" max="100" />
                        </flux:field>

                        <flux:field>
                            <flux:label>XP Reward</flux:label>
                            <flux:input type="number" wire:model="xp_reward" min="0" />
                        </flux:field>
                    </div>

                    <div class="space-y-4">
                        <flux:field>
                            <flux:checkbox wire:model="is_required" label="Required Assessment" />
                        </flux:field>

                        <flux:field>
                            <flux:checkbox wire:model="show_results_immediately" label="Show Results Immediately" />
                        </flux:field>

                        <flux:field>
                            <flux:checkbox wire:model="is_randomized" label="Randomize Questions" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Questions will appear in random order</p>
                        </flux:field>

                        <flux:field>
                            <flux:checkbox wire:model="shuffle_options" label="Shuffle Answer Options" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Answer options will be randomly shuffled</p>
                        </flux:field>

                        <flux:field>
                            <flux:checkbox wire:model="show_correct_answers" label="Show Correct Answers" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Display correct answers after submission</p>
                        </flux:field>

                        <flux:field>
                            <flux:checkbox wire:model="allow_review" label="Allow Review" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Students can review their answers</p>
                        </flux:field>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button type="submit" variant="primary">Save Assessment</flux:button>
                </div>
            </form>
        </div>

        {{-- Questions Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Questions</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $questions->count() }} question(s) • {{ $totalPoints }} total points
                    </p>
                </div>
                <flux:button wire:click="openQuestionModal" variant="primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Question
                </flux:button>
            </div>

            <div class="p-6">
                @if($questions->count() > 0)
                    <div class="space-y-4">
                        @foreach($questions as $question)
                            <div class="border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6 bg-gray-50 dark:bg-gray-900/50 hover:border-blue-400 dark:hover:border-blue-600 transition-colors">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-3">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                #{{ $question->order }}
                                            </span>
                                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 capitalize">
                                                {{ str_replace('_', ' ', $question->question_type) }}
                                            </span>
                                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                {{ $question->points }} pts
                                            </span>
                                        </div>
                                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">
                                            {{ $question->question_text }}
                                        </h3>
                                        @if($question->image_url)
                                            <div class="mt-4">
                                                <img src="{{ Storage::disk('public')->url($question->image_url) }}" 
                                                     alt="Question image" 
                                                     class="max-w-md rounded-lg border border-gray-200 dark:border-gray-700">
                                            </div>
                                        @endif
                                        @if($question->options->count() > 0)
                                            <div class="mt-4 space-y-3">
                                                @foreach($question->options as $option)
                                                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border {{ $option->is_correct ? 'border-green-300 dark:border-green-700' : 'border-gray-200 dark:border-gray-700' }}">
                                                        @if(in_array($question->question_type, ['multiple_choice', 'true_false', 'choice']))
                                                            <input type="radio" disabled {{ $option->is_correct ? 'checked' : '' }} class="w-4 h-4 mt-1">
                                                        @endif
                                                        <div class="flex-1">
                                                            <span class="text-gray-900 dark:text-white font-medium {{ $option->is_correct ? 'text-green-600 dark:text-green-400' : '' }}">
                                                                {{ $option->option_text }}
                                                            </span>
                                                            @if($option->image_url)
                                                                <div class="mt-2">
                                                                    <img src="{{ Storage::disk('public')->url($option->image_url) }}" 
                                                                         alt="{{ $option->image_alt_text ?? 'Option image' }}" 
                                                                         class="max-w-xs rounded border border-gray-200 dark:border-gray-700">
                                                                </div>
                                                            @endif
                                                        </div>
                                                        @if($option->is_correct)
                                                            <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">✓ Correct</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($question->explanation)
                                            <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                                <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Explanation:</strong> {{ $question->explanation }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex gap-2">
                                        <flux:button wire:click="openQuestionModal({{ $question->id }})" variant="ghost" class="text-sm">
                                            Edit
                                        </flux:button>
                                        <flux:button wire:click="deleteQuestion({{ $question->id }})" variant="danger" class="text-sm" wire:confirm="Are you sure?">
                                            Delete
                                        </flux:button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Questions Yet</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Start by adding your first question</p>
                        <flux:button wire:click="openQuestionModal" variant="primary">
                            Add First Question
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Student Submissions Section (for teachers/admins) --}}
        @if(auth()->user()->isAdmin() || auth()->user()->isTeacher())
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Student Submissions</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            View and review student attempts
                        </p>
                    </div>
                    <flux:button wire:click="$toggle('showSubmissions')" variant="outline">
                        {{ $showSubmissions ? 'Hide' : 'Show' }} Submissions
                    </flux:button>
                </div>
                @if($showSubmissions && $attempts)
                    <div class="p-6">
                        @if($attempts->count() > 0)
                            <div class="space-y-4">
                                @foreach($attempts as $attempt)
                                    <div class="border-2 border-gray-200 dark:border-gray-700 rounded-xl p-5 bg-gray-50 dark:bg-gray-900/50 hover:border-blue-400 dark:hover:border-blue-600 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">
                                                        {{ $attempt->user->name }}
                                                    </h3>
                                                    <span class="px-3 py-1 rounded-full text-xs font-medium {{ $attempt->is_passed ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                                        {{ $attempt->is_passed ? 'Passed' : 'Failed' }}
                                                    </span>
                                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                        Score: {{ number_format($attempt->score ?? 0, 1) }}%
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                                    <span>Completed: {{ $attempt->completed_at ? $attempt->completed_at->format('M d, Y H:i') : 'N/A' }}</span>
                                                    <span>Time Spent: {{ $attempt->time_spent ?? 0 }} minutes</span>
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <flux:button wire:click="viewAttempt({{ $attempt->id }})" variant="primary" class="text-sm">
                                                    View Details
                                                </flux:button>
                                                @if($assessment->assessment_type === 'assignment' && $attempt->score === null)
                                                    <flux:button wire:click="startGrading({{ $attempt->id }})" variant="success" class="text-sm">
                                                        Grade
                                                    </flux:button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-6">
                                {{ $attempts->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Submissions Yet</h3>
                                <p class="text-gray-600 dark:text-gray-400">Students haven't completed this assessment yet</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Question Editor Modal --}}
    @if($showQuestionModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:ignore.self>
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeQuestionModal"></div>
            
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-2xl transition-all w-full max-w-4xl max-h-[90vh] flex flex-col">
                    {{-- Modal Header --}}
                    <div class="bg-gradient-to-r from-purple-600 via-pink-600 to-red-600 px-8 py-6">
                        <div class="flex items-center justify-between">
<div>
                                <h3 class="text-2xl font-bold text-white">
                                    {{ $editingQuestionId ? 'Edit' : 'Add' }} Question
                                </h3>
                                <p class="text-white/80 text-sm mt-1">Create a question for this {{ str_replace('_', ' ', $assessment_type) }} assessment</p>
                            </div>
                            <button type="button" wire:click="closeQuestionModal" class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Modal Body --}}
                    <div class="px-8 py-6 flex-1 overflow-y-auto">
                        <form wire:submit.prevent="saveQuestion" class="space-y-6">
                            {{-- Question Text --}}
                            <flux:field>
                                <flux:label class="text-base font-semibold">Question Text *</flux:label>
                                <flux:textarea wire:model="questionFormData.question_text" rows="3" placeholder="Enter your question here..." />
                                @error('questionFormData.question_text') 
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                                @enderror
                            </flux:field>

                            {{-- Question Type --}}
                            <flux:field>
                                <flux:label class="text-base font-semibold">Question Type *</flux:label>
                                <flux:select wire:model.live="questionFormData.question_type">
                                    @foreach($availableQuestionTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </flux:select>
                            </flux:field>

                            {{-- Points and Order --}}
                            <div class="grid grid-cols-2 gap-4">
                                <flux:field>
                                    <flux:label class="text-base font-semibold">Points *</flux:label>
                                    <flux:input type="number" wire:model="questionFormData.points" min="0" />
                                </flux:field>
                                <flux:field>
                                    <flux:label class="text-base font-semibold">Order</flux:label>
                                    <flux:input type="number" wire:model="questionFormData.order" min="0" />
                                </flux:field>
                            </div>

                            {{-- Options for multiple choice, true/false, choice only (matching handled separately) --}}
                            @if(in_array($questionFormData['question_type'], ['multiple_choice', 'true_false', 'choice']))
                                <div class="border-2 border-blue-200 dark:border-blue-800 rounded-xl p-6 bg-blue-50/50 dark:bg-blue-900/10">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-900 dark:text-white">Answer Options</h4>
                                        <flux:button type="button" wire:click="addQuestionOption" variant="outline" class="text-sm">
                                            + Add Option
                                        </flux:button>
                                    </div>
                                    @if(!empty($questionOptions))
                                        <div class="space-y-4">
                                            @foreach($questionOptions as $index => $option)
                                                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-700">
                                                    <div class="flex items-start gap-3 mb-3">
                                                        <input type="checkbox" wire:model="questionOptions.{{ $index }}.is_correct" class="w-5 h-5 text-blue-600 rounded mt-1">
                                                        <flux:input wire:model="questionOptions.{{ $index }}.option_text" placeholder="Option text..." class="flex-1" />
                                                        <button type="button" wire:click="removeQuestionOption({{ $index }})" class="text-red-500 hover:text-red-700 p-2">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    {{-- Option Image --}}
                                                    @if(isset($option['image_url']) && $option['image_url'])
                                                        <div class="relative inline-block mt-2">
                                                            <img src="{{ Storage::disk('public')->url($option['image_url']) }}" 
                                                                 alt="Option image" 
                                                                 class="max-w-xs rounded-lg border border-gray-200 dark:border-gray-700">
                                                            <button type="button" 
                                                                    wire:click="removeOptionImage({{ $index }})" 
                                                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    @endif
                                                    @if(isset($tempOptionImages[$index]) && $tempOptionImages[$index])
                                                        <div class="relative inline-block mt-2">
                                                            <img src="{{ $tempOptionImages[$index]->temporaryUrl() }}" 
                                                                 alt="Option preview" 
                                                                 class="max-w-xs rounded-lg border border-gray-200 dark:border-gray-700">
                                                        </div>
                                                    @endif
                                                    <div class="mt-2">
                                                        <flux:field>
                                                            <flux:input type="file" 
                                                                       wire:model="tempOptionImages.{{ $index }}" 
                                                                       accept="image/*" 
                                                                       class="text-sm" />
                                                            <flux:description class="text-xs">Upload image for this option (optional)</flux:description>
                                                        </flux:field>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center py-4">Click "+ Add Option" to add answer choices</p>
                                    @endif
                                </div>
                            @endif

                            {{-- File Upload Question Type --}}
                            @if($questionFormData['question_type'] === 'file_upload')
                                <div class="border-2 border-green-200 dark:border-green-800 rounded-xl p-6 bg-green-50/50 dark:bg-green-900/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">File Upload Settings</h4>
                                    <div class="grid grid-cols-1 gap-4">
                                        <flux:field>
                                            <flux:label>Allowed File Types (comma separated)</flux:label>
                                            <flux:input wire:model="questionFormData.settings.allowed_types" 
                                                       placeholder="pdf,doc,docx,jpg,png,jpeg" />
                                            <flux:description>Specify allowed file extensions (e.g., pdf,doc,docx)</flux:description>
                                        </flux:field>
                                        <div class="grid grid-cols-2 gap-4">
                                            <flux:field>
                                                <flux:label>Max File Size (MB)</flux:label>
                                                <flux:input type="number" wire:model="questionFormData.settings.max_size" min="1" max="100" />
                                            </flux:field>
                                            <flux:field>
                                                <flux:label>Max Files Allowed</flux:label>
                                                <flux:input type="number" wire:model="questionFormData.settings.max_files" min="1" />
                                            </flux:field>
                                        </div>
                                    </div>
                                    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <p class="text-sm text-blue-800 dark:text-blue-300">
                                            <strong>Note:</strong> Students will see a file upload interface when answering this question. They can upload files based on your settings above.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            {{-- Code Submission Question Type --}}
                            @if($questionFormData['question_type'] === 'code_submission')
                                <div class="border-2 border-indigo-200 dark:border-indigo-800 rounded-xl p-6 bg-indigo-50/50 dark:bg-indigo-900/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Code Submission Settings</h4>
                                    <div class="grid grid-cols-1 gap-4">
                                        <flux:field>
                                            <flux:label>Programming Language</flux:label>
                                            <flux:select wire:model="codeSubmissionSettings.language">
                                                <option value="javascript">JavaScript</option>
                                                <option value="python">Python</option>
                                                <option value="java">Java</option>
                                                <option value="cpp">C++</option>
                                                <option value="c">C</option>
                                                <option value="php">PHP</option>
                                                <option value="ruby">Ruby</option>
                                                <option value="go">Go</option>
                                                <option value="rust">Rust</option>
                                                <option value="typescript">TypeScript</option>
                                            </flux:select>
                                        </flux:field>
                                        <flux:field>
                                            <flux:label>Code Template (Optional)</flux:label>
                                            <flux:textarea wire:model="codeSubmissionSettings.template" rows="6" 
                                                          placeholder="// Enter starting code template for students...&#10;function solution() {&#10;    // Your code here&#10;}" />
                                            <flux:description>Students will see this template when they start coding</flux:description>
                                        </flux:field>
                                        <flux:field>
                                            <flux:label>Expected Output (Optional)</flux:label>
                                            <flux:textarea wire:model="codeSubmissionSettings.expected_output" rows="3" 
                                                          placeholder="Enter expected output if applicable..." />
                                        </flux:field>
                                    </div>
                                </div>
                            @endif

                            {{-- Rubric Criteria Question Type --}}
                            @if($questionFormData['question_type'] === 'rubric_criteria')
                                <div class="border-2 border-purple-200 dark:border-purple-800 rounded-xl p-6 bg-purple-50/50 dark:bg-purple-900/10">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-900 dark:text-white">Rubric Criteria</h4>
                                        <flux:button type="button" wire:click="addRubricCriterion" variant="outline" class="text-sm">
                                            + Add Criterion
                                        </flux:button>
                                    </div>
                                    @if(!empty($rubricCriteria))
                                        <div class="space-y-4">
                                            @foreach($rubricCriteria as $index => $criterion)
                                                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-700">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <h5 class="font-semibold text-gray-900 dark:text-white">Criterion {{ $index + 1 }}</h5>
                                                        <button type="button" wire:click="removeRubricCriterion({{ $index }})" class="text-red-500 hover:text-red-700 p-2">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                                        <flux:field>
                                                            <flux:label>Criterion Name *</flux:label>
                                                            <flux:input wire:model="rubricCriteria.{{ $index }}.name" placeholder="e.g., Code Quality" />
                                                        </flux:field>
                                                        <flux:field>
                                                            <flux:label>Max Points</flux:label>
                                                            <flux:input type="number" wire:model="rubricCriteria.{{ $index }}.max_points" min="0" />
                                                        </flux:field>
                                                    </div>
                                                    <flux:field>
                                                        <flux:label>Description</flux:label>
                                                        <flux:textarea wire:model="rubricCriteria.{{ $index }}.description" rows="2" />
                                                    </flux:field>
                                                    <div class="mt-4">
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Performance Levels</label>
                                                        <div class="space-y-2">
                                                            @foreach($criterion['performance_levels'] ?? [] as $levelIndex => $level)
                                                                <div class="grid grid-cols-12 gap-2 items-center">
                                                                    <div class="col-span-3">
                                                                        <flux:input wire:model="rubricCriteria.{{ $index }}.performance_levels.{{ $levelIndex }}.level" 
                                                                                   placeholder="Level name" class="text-sm" />
                                                                    </div>
                                                                    <div class="col-span-2">
                                                                        <flux:input type="number" wire:model="rubricCriteria.{{ $index }}.performance_levels.{{ $levelIndex }}.points" 
                                                                                   placeholder="Points" min="0" class="text-sm" />
                                                                    </div>
                                                                    <div class="col-span-7">
                                                                        <flux:input wire:model="rubricCriteria.{{ $index }}.performance_levels.{{ $levelIndex }}.description" 
                                                                                   placeholder="Level description" class="text-sm" />
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center py-4">Click "+ Add Criterion" to create rubric criteria</p>
                                    @endif
                                </div>
                            @endif

                            {{-- Matching Question Type --}}
                            @if($questionFormData['question_type'] === 'matching')
                                <div class="border-2 border-cyan-200 dark:border-cyan-800 rounded-xl p-6 bg-cyan-50/50 dark:bg-cyan-900/10">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-900 dark:text-white">Matching Pairs</h4>
                                        <flux:button type="button" wire:click="addMatchingPair" variant="outline" class="text-sm">
                                            + Add Pair
                                        </flux:button>
                                    </div>
                                    @if(!empty($matchingPairs))
                                        <div class="space-y-4">
                                            @foreach($matchingPairs as $index => $pair)
                                                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-700">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <h5 class="font-semibold text-gray-900 dark:text-white">Pair {{ $index + 1 }}</h5>
                                                        <button type="button" wire:click="removeMatchingPair({{ $index }})" class="text-red-500 hover:text-red-700 p-2">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <flux:field>
                                                            <flux:label>Left Item *</flux:label>
                                                            <flux:input wire:model="matchingPairs.{{ $index }}.left_item" placeholder="e.g., HTML" />
                                                        </flux:field>
                                                        <flux:field>
                                                            <flux:label>Right Item (Match) *</flux:label>
                                                            <flux:input wire:model="matchingPairs.{{ $index }}.right_item" placeholder="e.g., HyperText Markup Language" />
                                                        </flux:field>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center py-4">Click "+ Add Pair" to create matching pairs</p>
                                    @endif
                                </div>
                            @endif

                            {{-- Ordering Question Type --}}
                            @if($questionFormData['question_type'] === 'ordering')
                                <div class="border-2 border-pink-200 dark:border-pink-800 rounded-xl p-6 bg-pink-50/50 dark:bg-pink-900/10">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-900 dark:text-white">Ordering Items</h4>
                                        <flux:button type="button" wire:click="addOrderingItem" variant="outline" class="text-sm">
                                            + Add Item
                                        </flux:button>
                                    </div>
                                    @if(!empty($orderingItems))
                                        <div class="space-y-4">
                                            @foreach($orderingItems as $index => $item)
                                                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-700">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <div class="flex items-center gap-3">
                                                            <span class="px-3 py-1 rounded text-sm font-medium bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-400">
                                                                Order: {{ $item['correct_order'] ?? ($index + 1) }}
                                                            </span>
                                                            <h5 class="font-semibold text-gray-900 dark:text-white">Item {{ $index + 1 }}</h5>
                                                        </div>
                                                        <button type="button" wire:click="removeOrderingItem({{ $index }})" class="text-red-500 hover:text-red-700 p-2">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <flux:field>
                                                            <flux:label>Item Text *</flux:label>
                                                            <flux:input wire:model="orderingItems.{{ $index }}.item_text" placeholder="e.g., Step 1: Planning" />
                                                        </flux:field>
                                                        <flux:field>
                                                            <flux:label>Correct Order Position</flux:label>
                                                            <flux:input type="number" wire:model="orderingItems.{{ $index }}.correct_order" min="1" />
                                                            <flux:description>Set the correct position (1, 2, 3, etc.)</flux:description>
                                                        </flux:field>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center py-4">Click "+ Add Item" to create items to order</p>
                                    @endif
                                </div>
                            @endif

                            {{-- Rating Scale Question Type --}}
                            @if($questionFormData['question_type'] === 'rating')
                                <div class="border-2 border-yellow-200 dark:border-yellow-800 rounded-xl p-6 bg-yellow-50/50 dark:bg-yellow-900/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Rating Scale Settings</h4>
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <flux:field>
                                            <flux:label>Minimum Value</flux:label>
                                            <flux:input type="number" wire:model.live="ratingScaleSettings.min" min="0" />
                                        </flux:field>
                                        <flux:field>
                                            <flux:label>Maximum Value</flux:label>
                                            <flux:input type="number" wire:model.live="ratingScaleSettings.max" min="1" />
                                        </flux:field>
                                    </div>
                                    <flux:field>
                                        <flux:label>Scale Labels (Optional)</flux:label>
                                        <flux:textarea wire:model="ratingScaleSettings.labels" rows="3" 
                                                      placeholder="Enter labels separated by commas, e.g., Poor, Fair, Good, Very Good, Excellent" />
                                        <flux:description>Optional: Provide labels for each rating point</flux:description>
                                    </flux:field>
                                </div>
                            @endif

                            {{-- Fill in the Blank Question Type --}}
                            @if($questionFormData['question_type'] === 'fill_blank')
                                <div class="border-2 border-teal-200 dark:border-teal-800 rounded-xl p-6 bg-teal-50/50 dark:bg-teal-900/10">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-900 dark:text-white">Fill in the Blank</h4>
                                        <flux:button type="button" wire:click="addFillBlank" variant="outline" class="text-sm">
                                            + Add Blank
                                        </flux:button>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        Use <strong>{{ '{blank}' }}</strong> or <strong>_____</strong> in your question text to mark where blanks should appear.
                                    </p>
                                    @if(!empty($fillBlankSettings['blanks']))
                                        <div class="space-y-4">
                                            @foreach($fillBlankSettings['blanks'] as $index => $blank)
                                                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-700">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <h5 class="font-semibold text-gray-900 dark:text-white">Blank {{ $index + 1 }}</h5>
                                                        <button type="button" wire:click="removeFillBlank({{ $index }})" class="text-red-500 hover:text-red-700 p-2">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                                        <flux:field>
                                                            <flux:label>Correct Answer *</flux:label>
                                                            <flux:input wire:model="fillBlankSettings.blanks.{{ $index }}.correct_answer" placeholder="Correct answer" />
                                                        </flux:field>
                                                        <flux:field>
                                                            <flux:label>Position in Question</flux:label>
                                                            <flux:input wire:model="fillBlankSettings.blanks.{{ $index }}.position" placeholder="e.g., 1 (first blank)" />
                                                        </flux:field>
                                                    </div>
                                                    <div class="mb-3">
                                                        <flux:field>
                                                            <flux:checkbox wire:model="fillBlankSettings.blanks.{{ $index }}.case_sensitive" label="Case Sensitive" />
                                                        </flux:field>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Alternative Answers (Optional)</label>
                                                        <div class="space-y-2">
                                                            @foreach($blank['alternative_answers'] ?? [] as $altIndex => $altAnswer)
                                                                <div class="flex gap-2">
                                                                    <flux:input wire:model="fillBlankSettings.blanks.{{ $index }}.alternative_answers.{{ $altIndex }}" 
                                                                               placeholder="Alternative answer" class="flex-1" />
                                                                    <button type="button" wire:click="$wire.fillBlankSettings.blanks.{{ $index }}.alternative_answers.splice({{ $altIndex }}, 1)" 
                                                                            class="text-red-500 hover:text-red-700 p-2">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                            <flux:button type="button" wire:click="addAlternativeAnswer({{ $index }})" variant="ghost" class="text-sm">
                                                                + Add Alternative
                                                            </flux:button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center py-4">Click "+ Add Blank" to define correct answers</p>
                                    @endif
                                </div>
                            @endif

                            {{-- True/False Question Type --}}
                            @if($questionFormData['question_type'] === 'true_false')
                                <div class="border-2 border-blue-200 dark:border-blue-800 rounded-xl p-6 bg-blue-50/50 dark:bg-blue-900/10">
                                    <p class="text-sm text-blue-800 dark:text-blue-300 mb-4">
                                        <strong>Note:</strong> Add two options below: "True" and "False", and mark the correct one.
                                    </p>
                                    @if(empty($questionOptions) || count($questionOptions) < 2)
                                        <flux:button type="button" wire:click="addQuestionOption" variant="outline" class="text-sm mb-2">
                                            + Add Option
                                        </flux:button>
                                    @endif
                                </div>
                            @endif

                            {{-- Other question type specific fields --}}
                            @if($questionFormData['question_type'] === 'essay' || $questionFormData['question_type'] === 'short_answer')
                                <div class="border-2 border-purple-200 dark:border-purple-800 rounded-xl p-6 bg-purple-50/50 dark:bg-purple-900/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Text Response Settings</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <flux:field>
                                            <flux:label>Minimum Word Count</flux:label>
                                            <flux:input type="number" wire:model="questionFormData.settings.min_words" min="0" />
                                        </flux:field>
                                        <flux:field>
                                            <flux:label>Maximum Word Count</flux:label>
                                            <flux:input type="number" wire:model="questionFormData.settings.max_words" min="1" />
                                        </flux:field>
                                    </div>
                                </div>
                            @endif

                            {{-- Explanation --}}
                            <flux:field>
                                <flux:label class="text-base font-semibold">Explanation (Optional)</flux:label>
                                <flux:textarea wire:model="questionFormData.explanation" rows="2" placeholder="Explain the correct answer..." />
                            </flux:field>

                            {{-- Question Image Upload --}}
                            <div class="border-2 border-blue-200 dark:border-blue-800 rounded-xl p-6 bg-blue-50/50 dark:bg-blue-900/10">
                                <flux:label class="text-base font-semibold mb-3 block">Question Image (Optional)</flux:label>
                                @if($questionFormData['image_url'] || $questionImage)
                                    <div class="relative inline-block mb-3">
                                        <img src="{{ $questionImage ? $questionImage->temporaryUrl() : Storage::disk('public')->url($questionFormData['image_url']) }}" 
                                             alt="Question preview" 
                                             class="max-w-md rounded-lg border-2 border-gray-200 dark:border-gray-700">
                                        <button type="button" 
                                                wire:click="removeQuestionImage" 
                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                                <flux:field>
                                    <flux:input type="file" wire:model="questionImage" accept="image/*" />
                                    <flux:description>Upload an image for this question (max 5MB)</flux:description>
                                </flux:field>
                            </div>

                            {{-- Modal Footer --}}
                            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 mt-8">
                                <flux:button type="button" wire:click="closeQuestionModal" variant="ghost" class="px-6 py-2.5">
                                    Cancel
                                </flux:button>
                                <flux:button type="submit" variant="primary" class="px-6 py-2.5">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Question
                                </flux:button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Student Attempt Detail Modal --}}
    @if($selectedAttempt)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:ignore.self>
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeAttemptView"></div>
            
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-2xl transition-all w-full max-w-5xl max-h-[90vh] flex flex-col">
                    {{-- Modal Header --}}
                    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 px-8 py-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-white">
                                    Submission by {{ $selectedAttempt->user->name }}
                                </h3>
                                <p class="text-white/80 text-sm mt-1">
                                    Score: {{ number_format($selectedAttempt->score ?? 0, 1) }}% 
                                    @if($selectedAttempt->is_passed ?? false)
                                        • Passed ✓
                                    @else
                                        • Failed
                                    @endif
                                </p>
                            </div>
                            <button type="button" wire:click="closeAttemptView" class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Modal Body --}}
                    <div class="px-8 py-6 flex-1 overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Score</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($selectedAttempt->score ?? 0, 1) }}%</p>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Time Spent</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $selectedAttempt->time_spent ?? 0 }} min</p>
                            </div>
                            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Completed</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $selectedAttempt->completed_at?->format('M d, Y H:i') ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            @php
                                $answers = $selectedAttempt->answers ?? [];
                                $questions = $selectedAttempt->assessment->questions ?? collect();
                            @endphp
                            @foreach($questions as $question)
                                <div class="border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                    Question #{{ $question->order }}
                                                </span>
                                                <span class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 capitalize">
                                                    {{ str_replace('_', ' ', $question->question_type) }}
                                                </span>
                                                <span class="px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                                                    {{ $question->points }} pts
                                                </span>
                                            </div>
                                            <h4 class="font-bold text-gray-900 dark:text-white mb-2">{{ $question->question_text }}</h4>
                                            @if($question->image_url)
                                                <img src="{{ Storage::disk('public')->url($question->image_url) }}" 
                                                     alt="Question image" 
                                                     class="max-w-md rounded-lg border border-gray-200 dark:border-gray-700 mt-3">
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Student Answer --}}
                                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Student Answer:</p>
                                        @if($question->question_type === 'file_upload')
                                            @php
                                                $answerData = $answers[$question->id] ?? null;
                                                $uploadedFiles = is_array($answerData) ? ($answerData['files'] ?? []) : [];
                                            @endphp
                                            @if(!empty($uploadedFiles))
                                                <div class="space-y-2">
                                                    @foreach($uploadedFiles as $filePath)
                                                        <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            </svg>
                                                            <div class="flex-1">
                                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ basename($filePath) }}</p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $filePath }}</p>
                                                            </div>
                                                            <a href="{{ Storage::disk('public')->url($filePath) }}" 
                                                               target="_blank" 
                                                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-gray-600 dark:text-gray-400 italic">No files uploaded</p>
                                            @endif
                                        @elseif(in_array($question->question_type, ['essay', 'short_answer', 'text', 'reflection']))
                                            <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ $answers[$question->id] ?? 'No answer provided' }}</p>
                                        @elseif($question->question_type === 'multiple_choice' || $question->question_type === 'choice')
                                            @php
                                                $selectedOptionIds = is_array($answers[$question->id] ?? null) ? $answers[$question->id] : [$answers[$question->id] ?? null];
                                                $selectedOptions = $question->options->whereIn('id', $selectedOptionIds)->filter();
                                            @endphp
                                            @if($selectedOptions->count() > 0)
                                                @foreach($selectedOptions as $option)
                                                    <div class="flex items-start gap-2 p-3 bg-white dark:bg-gray-800 rounded-lg border {{ $option->is_correct ? 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                                        <input type="checkbox" checked disabled class="w-4 h-4 mt-1">
                                                        <div class="flex-1">
                                                            <span class="text-gray-900 dark:text-white">{{ $option->option_text }}</span>
                                                            @if($option->image_url)
                                                                <img src="{{ Storage::disk('public')->url($option->image_url) }}" 
                                                                     alt="Option image" 
                                                                     class="max-w-xs rounded border border-gray-200 dark:border-gray-700 mt-2">
                                                            @endif
                                                        </div>
                                                        @if($option->is_correct)
                                                            <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Correct</span>
                                                        @else
                                                            <span class="px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Incorrect</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-gray-600 dark:text-gray-400 italic">No answer selected</p>
                                            @endif
                                        @else
                                            <p class="text-gray-900 dark:text-white">{{ $answers[$question->id] ?? 'No answer provided' }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Grading Section for Assignment-Type Assessments --}}
                        @if($selectedAttempt->assessment->assessment_type === 'assignment')
                            <div class="mt-8 p-6 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl border-2 border-indigo-200 dark:border-indigo-800">
                                <div class="flex items-center justify-between mb-6">
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">Grade Submission</h4>
                                    @if(!$gradingAttempt && $selectedAttempt->score === null)
                                        <button wire:click="startGrading({{ $selectedAttempt->id }})" 
                                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                                            Start Grading
                                        </button>
                                    @endif
                                </div>

                                @if($gradingAttempt || $selectedAttempt->score !== null)
                                    <div class="space-y-6">
                                        @php
                                            $answers = $selectedAttempt->answers ?? [];
                                            $submissionText = $answers['text'] ?? '';
                                            $submissionFiles = $answers['files'] ?? [];
                                        @endphp

                                        {{-- Submission Text --}}
                                        @if($submissionText)
                                            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Submission Text:</p>
                                                <div class="max-w-none prose prose-sm dark:prose-invert prose-headings:font-semibold prose-p:mb-4 prose-p:leading-7 prose-p:text-gray-800 dark:prose-p:text-gray-200 prose-strong:text-gray-900 dark:prose-strong:text-gray-100 prose-ul:my-4 prose-ol:my-4 prose-li:my-2 prose-pre:bg-gray-100 dark:prose-pre:bg-gray-800 prose-code:text-sm">
                                                    <div class="whitespace-pre-wrap break-words text-gray-900 dark:text-gray-100 leading-7">
                                                        @php
                                                            // Convert plain text to formatted HTML with proper line breaks and spacing
                                                            $formattedText = $submissionText;
                                                            // Split into paragraphs (double line breaks)
                                                            $paragraphs = preg_split('/\n\s*\n/', $formattedText);
                                                            $formattedText = '';
                                                            foreach ($paragraphs as $para) {
                                                                $para = trim($para);
                                                                if (!empty($para)) {
                                                                    // Check if it's a list item
                                                                    if (preg_match('/^[-*•]\s+/', $para) || preg_match('/^\d+[\.\)]\s+/', $para)) {
                                                                        $formattedText .= '<p class="mb-2 ml-4">' . nl2br(e($para)) . '</p>';
                                                                    } else {
                                                                        $formattedText .= '<p class="mb-4">' . nl2br(e($para)) . '</p>';
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        {!! $formattedText ?: '<p>' . nl2br(e($submissionText)) . '</p>' !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Submission Files --}}
                                        @if(!empty($submissionFiles))
                                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Uploaded Files:</p>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    @foreach($submissionFiles as $filePath)
                                                        <a href="{{ Storage::disk('public')->url($filePath) }}" target="_blank"
                                                           class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                                                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            </svg>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ basename($filePath) }}</p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">Click to download</p>
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Grading Form --}}
                                        @if($gradingAttempt || $selectedAttempt->score !== null)
                                            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 space-y-6">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                            Score (out of {{ $attemptMaxScore }})
                                                        </label>
                                                        <input type="number" 
                                                               wire:model.live="attemptScore" 
                                                               min="0" 
                                                               max="{{ $attemptMaxScore }}" 
                                                               step="0.5"
                                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                                               {{ $selectedAttempt->score !== null && !$gradingAttempt ? 'readonly' : '' }}>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                            Percentage
                                                        </label>
                                                        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                                                {{ $attemptMaxScore > 0 ? number_format(($attemptScore / $attemptMaxScore) * 100, 1) : 0 }}%
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                        Feedback
                                                    </label>
                                                    <textarea wire:model="attemptFeedback" 
                                                              rows="6"
                                                              placeholder="Provide detailed feedback to the student..."
                                                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                                              {{ $selectedAttempt->score !== null && !$gradingAttempt ? 'readonly' : '' }}></textarea>
                                                </div>

                                                @if($gradingAttempt)
                                                    <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                                        <button wire:click="saveAttemptGrade" 
                                                                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                                                            Save Grade
                                                        </button>
                                                        <button wire:click="closeAttemptView" 
                                                                class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                @else
                                                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                                            Graded on: {{ $selectedAttempt->updated_at->format('M d, Y H:i') }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
