<div class="max-w-4xl mx-auto p-6 space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Assessment</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Configure assessment settings and requirements</p>
            </div>
            <a href="{{ route('assessments.index') }}" wire:navigate
               class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Basic Information --}}
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Basic Information</h2>

                {{-- Course Selection --}}
                <div>
                    <flux:field>
                        <flux:label>Course</flux:label>
                        <flux:select wire:model.live="course_id" placeholder="Select a course" required>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="course_id" />
                        <flux:description>Select the course for this assessment</flux:description>
                    </flux:field>
                </div>

                {{-- Lesson Selection (Optional) --}}
                @if($course_id && $lessons->count() > 0)
                    <div>
                        <flux:field>
                            <flux:label>Lesson (Optional)</flux:label>
                            <flux:select wire:model="lesson_id" placeholder="Select a lesson (optional)">
                                <option value="">No specific lesson</option>
                                @foreach($lessons as $lesson)
                                    <option value="{{ $lesson->id }}">{{ $lesson->title }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="lesson_id" />
                            <flux:description>Link this assessment to a specific lesson</flux:description>
                        </flux:field>
                    </div>
                @endif

                {{-- Title --}}
                <div>
                    <flux:field>
                        <flux:label>Assessment Title</flux:label>
                        <flux:input wire:model="title" placeholder="Enter assessment title" required />
                        <flux:error name="title" />
                    </flux:field>
                </div>

                {{-- Assessment Type --}}
                <div>
                    <flux:field>
                        <flux:label>Assessment Type</flux:label>
                        <flux:select wire:model.live="assessment_type" required>
                            @foreach($assessmentTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="assessment_type" />
                        <flux:description>
                            @if($assessment_type === 'quiz')
                                Traditional quiz with questions and multiple choice answers
                            @elseif($assessment_type === 'assignment')
                                Student assignment with file uploads and written responses
                            @elseif($assessment_type === 'pre_project_test')
                                Baseline knowledge test before starting a project
                            @elseif($assessment_type === 'post_project_test')
                                Knowledge assessment after completing a project
                            @elseif($assessment_type === 'unit_survey')
                                Survey with rating scales and open-ended questions
                            @elseif($assessment_type === 'rubric_assessment')
                                Criteria-based assessment with weighted scoring
                            @elseif($assessment_type === 'peer_review')
                                Students evaluate each other's work
                            @elseif($assessment_type === 'self_assessment')
                                Students reflect on their own work and progress
                            @else
                                Select an assessment type to see description
                            @endif
                        </flux:description>
                    </flux:field>
                </div>

                {{-- Description --}}
                <div>
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description" rows="4" placeholder="Enter assessment description" />
                        <flux:error name="description" />
                    </flux:field>
                </div>
            </div>

            {{-- Rubric Criteria (for rubric assessments) --}}
            @if($assessment_type === 'rubric_assessment')
                <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Rubric Criteria</h2>
                        <flux:button wire:click="addRubricCriterion" variant="ghost" size="sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Criterion
                        </flux:button>
                    </div>

                    @foreach($rubric_criteria as $index => $criterion)
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Criterion {{ $index + 1 }}</h3>
                                <flux:button wire:click="removeRubricCriterion({{ $index }})" variant="ghost" size="sm" class="text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </flux:button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <flux:field>
                                    <flux:label>Name</flux:label>
                                    <flux:input wire:model="rubric_criteria.{{ $index }}.name" placeholder="Criterion name" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Max Points</flux:label>
                                    <flux:input type="number" wire:model="rubric_criteria.{{ $index }}.max_points" min="0" />
                                </flux:field>
                            </div>
                            <flux:field>
                                <flux:label>Description</flux:label>
                                <flux:textarea wire:model="rubric_criteria.{{ $index }}.description" rows="2" />
                            </flux:field>
                        </div>
                    @endforeach

                    @if(count($rubric_criteria) === 0)
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                            No criteria added yet. Click "Add Criterion" to start building your rubric.
                        </p>
                    @endif
                </div>
            @endif

            {{-- Assessment Settings --}}
            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Settings</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Max Attempts --}}
                    <div>
                        <flux:field>
                            <flux:label>Maximum Attempts</flux:label>
                            <flux:input type="number" wire:model="max_attempts" min="1" required />
                            <flux:error name="max_attempts" />
                            <flux:description>Number of times students can take this assessment</flux:description>
                        </flux:field>
                    </div>

                    {{-- Time Limit --}}
                    <div>
                        <flux:field>
                            <flux:label>Time Limit (minutes)</flux:label>
                            <flux:input type="number" wire:model="time_limit_minutes" min="1" placeholder="Optional" />
                            <flux:error name="time_limit_minutes" />
                            <flux:description>Leave empty for no time limit</flux:description>
                        </flux:field>
                    </div>

                    {{-- Passing Score --}}
                    <div>
                        <flux:field>
                            <flux:label>Passing Score (%)</flux:label>
                            <flux:input type="number" wire:model="passing_score" min="0" max="100" required />
                            <flux:error name="passing_score" />
                        </flux:field>
                    </div>

                    {{-- XP Reward --}}
                    <div>
                        <flux:field>
                            <flux:label>XP Reward</flux:label>
                            <flux:input type="number" wire:model="xp_reward" min="0" required />
                            <flux:error name="xp_reward" />
                            <flux:description>Points awarded upon passing</flux:description>
                        </flux:field>
                    </div>
                </div>

                {{-- Checkboxes --}}
                <div class="space-y-3">
                    <flux:checkbox wire:model="is_required">Required Assessment</flux:checkbox>
                    <flux:checkbox wire:model="show_results_immediately">Show Results Immediately</flux:checkbox>
                    <flux:checkbox wire:model="is_randomized">Randomize Questions</flux:checkbox>
                    <flux:checkbox wire:model="shuffle_options">Shuffle Answer Options</flux:checkbox>
                    <flux:checkbox wire:model="show_correct_answers">Show Correct Answers</flux:checkbox>
                    <flux:checkbox wire:model="allow_review">Allow Review</flux:checkbox>
                    <flux:checkbox wire:model="is_locked">Lock Assessment</flux:checkbox>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-6">
                <flux:button href="{{ route('assessments.index') }}" wire:navigate variant="ghost">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Create Assessment
                </flux:button>
            </div>
        </form>
    </div>
</div>

@if(session()->has('message'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
         class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('message') }}
    </div>
@endif
