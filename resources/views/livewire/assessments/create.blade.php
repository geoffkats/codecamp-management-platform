<div class="{{ $this->embedded ? 'bg-white dark:bg-gray-900' : 'max-w-4xl mx-auto p-6' }}">

    {{-- Embedded header --}}
    @if($this->embedded)
    <div class="flex items-center gap-3 px-6 py-3 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10 bg-white dark:bg-gray-900">
        <button wire:click="cancelEmbedded" type="button"
                class="flex items-center gap-1.5 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Lesson
        </button>
        <div class="h-4 w-px bg-gray-200 dark:bg-gray-700"></div>
        <span class="text-sm font-bold text-gray-800 dark:text-white">New Quiz / Assessment</span>
    </div>
    @endif

    <div class="{{ $this->embedded ? 'p-6 space-y-6' : 'space-y-6' }}">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            @if(!$this->embedded)
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $assessment_type === 'assignment' ? 'Create New Assignment' : 'Create New Assessment' }}
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $assessment_type === 'assignment'
                            ? 'Set instructions, due date, and optional brief attachments for students'
                            : 'Configure assessment settings and requirements' }}
                    </p>
                </div>
                <a href="{{ $course_id ? route('curriculum.builder', $course_id) : ($assessment_type === 'assignment' ? route('assignments.index') : route('assessments.index')) }}" wire:navigate
                   class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Fill in the basics. You can add questions right after creating it.</p>
            @endif

            <form wire:submit="save" class="space-y-5">
                {{-- Course/Lesson selectors — only show when NOT embedded (they're pre-filled from the builder) --}}
                @if(!$this->embedded)
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Lesson</h2>
                    <div>
                        <flux:field>
                            <flux:label>Course</flux:label>
                            <flux:select wire:model.live="course_id" placeholder="Select a course" required>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="course_id" />
                        </flux:field>
                    </div>
                    @if($course_id && $lessons->count() > 0)
                    <div>
                        <flux:field>
                            <flux:label>Lesson (optional)</flux:label>
                            <flux:select wire:model="lesson_id" placeholder="Course-level assessment (no lesson)">
                                <option value="">Course-level — not tied to a lesson</option>
                                @foreach($lessons as $lesson)
                                    <option value="{{ $lesson->id }}">{{ $lesson->title }}</option>
                                @endforeach
                            </flux:select>
                            <flux:description>Leave blank for a course-wide quiz or test.</flux:description>
                            <flux:error name="lesson_id" />
                        </flux:field>
                    </div>
                    @elseif($course_id)
                    <p class="text-sm text-gray-500 dark:text-gray-400">This course has no lessons yet. The assessment will be saved at course level.</p>
                    @endif
                </div>
                @endif

                {{-- Title --}}
                <div>
                    <flux:field>
                        <flux:label>Assessment Title <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="title" placeholder="e.g., Module 1 Quiz" required autofocus="{{ $this->embedded ? 'true' : 'false' }}" />
                        <flux:error name="title" />
                    </flux:field>
                </div>

                {{-- Assessment Type --}}
                <div>
                    <flux:field>
                        <flux:label>Assessment Type <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model.live="assessment_type" required>
                            @foreach($assessmentTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="assessment_type" />
                        <flux:description>
                            @if($assessment_type === 'quiz') Traditional quiz with multiple-choice questions
                            @elseif($assessment_type === 'assignment') Student assignment with file uploads
                            @elseif($assessment_type === 'pre_project_test') Baseline test before a project
                            @elseif($assessment_type === 'post_project_test') Knowledge test after a project
                            @elseif($assessment_type === 'unit_survey') Survey with ratings and open questions
                            @elseif($assessment_type === 'rubric_assessment') Criteria-based grading
                            @elseif($assessment_type === 'peer_review') Students review each other's work
                            @elseif($assessment_type === 'self_assessment') Students reflect on their own work
                            @endif
                        </flux:description>
                    </flux:field>
                </div>

                {{-- Project Platform --}}
                @if(in_array($assessment_type, ['pre_project_test', 'post_project_test']))
                <div>
                    <flux:field>
                        <flux:label>Project Platform</flux:label>
                        <flux:select wire:model="project_platform">
                            <option value="">Select platform...</option>
                            <option value="scratch">Scratch 3</option>
                            <option value="other">Other / Custom</option>
                        </flux:select>
                        <flux:error name="project_platform" />
                    </flux:field>
                </div>
                @endif

                {{-- Description --}}
                <div>
                    <flux:field>
                        <flux:label>{{ $assessment_type === 'assignment' ? 'Brief Summary' : 'Description' }}</flux:label>
                        <flux:textarea wire:model="description" rows="3" placeholder="{{ $assessment_type === 'assignment' ? 'Short overview shown to students before they submit' : 'Optional description for students' }}" />
                        <flux:error name="description" />
                    </flux:field>
                </div>

                @if($assessment_type === 'assignment')
                <div class="border border-purple-200 dark:border-purple-800 rounded-xl p-5 space-y-5 bg-purple-50/50 dark:bg-purple-900/10">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Assignment Details</h2>

                    <flux:field>
                        <flux:label>Instructions for Students</flux:label>
                        <flux:textarea wire:model="assignment_instructions" rows="5" placeholder="What should students do? Include steps, rubric notes, or links." />
                        <flux:error name="assignment_instructions" />
                    </flux:field>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Due Date (optional)</flux:label>
                            <flux:input type="date" wire:model="assignment_due_date" />
                            <flux:error name="assignment_due_date" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Max Points</flux:label>
                            <flux:input type="number" wire:model="assignment_max_points" min="1" max="1000" required />
                            <flux:error name="assignment_max_points" />
                        </flux:field>
                    </div>

                    <div class="flex flex-wrap gap-x-5 gap-y-2">
                        <flux:checkbox wire:model="assignment_allow_text">Allow text response</flux:checkbox>
                        <flux:checkbox wire:model="assignment_allow_files">Allow file uploads</flux:checkbox>
                    </div>
                    <flux:error name="assignment_allow_files" />

                    <flux:field>
                        <flux:label>Attach Brief Files (optional)</flux:label>
                        <flux:input type="file" wire:model="assignmentBriefFiles" multiple accept=".pdf,.doc,.docx,.txt,.zip,.jpg,.jpeg,.png" />
                        <flux:description>Worksheets, briefs, or reference images students can download (max 10MB each).</flux:description>
                        <flux:error name="assignmentBriefFiles.*" />
                    </flux:field>

                    @if(!empty($assignmentBriefFiles))
                        <ul class="space-y-2">
                            @foreach($assignmentBriefFiles as $index => $file)
                                <li class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm">
                                    <span class="truncate text-gray-700 dark:text-gray-300">{{ $file->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="removeAssignmentBriefFile({{ $index }})" class="text-red-600 hover:text-red-700 text-xs font-semibold">Remove</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                @endif

                {{-- Settings (compact grid) --}}
                @if($assessment_type !== 'assignment')
                <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Settings</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <flux:field>
                            <flux:label>Max Attempts</flux:label>
                            <flux:input type="number" wire:model="max_attempts" min="1" required />
                            <flux:error name="max_attempts" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Time Limit (min)</flux:label>
                            <flux:input type="number" wire:model="time_limit_minutes" min="1" placeholder="None" />
                            <flux:error name="time_limit_minutes" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Passing Score (%)</flux:label>
                            <flux:input type="number" wire:model="passing_score" min="0" max="100" required />
                            <flux:error name="passing_score" />
                        </flux:field>
                        <flux:field>
                            <flux:label>XP Reward</flux:label>
                            <flux:input type="number" wire:model="xp_reward" min="0" required />
                            <flux:error name="xp_reward" />
                        </flux:field>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2">
                        <flux:checkbox wire:model="is_required">Required</flux:checkbox>
                        <flux:checkbox wire:model="show_results_immediately">Show Results Immediately</flux:checkbox>
                        <flux:checkbox wire:model="is_randomized">Randomize Questions</flux:checkbox>
                        <flux:checkbox wire:model="shuffle_options">Shuffle Answers</flux:checkbox>
                        <flux:checkbox wire:model="show_correct_answers">Show Correct Answers</flux:checkbox>
                        <flux:checkbox wire:model="allow_review">Allow Review</flux:checkbox>
                    </div>
                </div>
                @else
                <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <flux:field>
                            <flux:label>Max Attempts</flux:label>
                            <flux:input type="number" wire:model="max_attempts" min="1" required />
                            <flux:error name="max_attempts" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Passing Score (%)</flux:label>
                            <flux:input type="number" wire:model="passing_score" min="0" max="100" required />
                            <flux:error name="passing_score" />
                        </flux:field>
                        <flux:field>
                            <flux:label>XP Reward</flux:label>
                            <flux:input type="number" wire:model="xp_reward" min="0" required />
                            <flux:error name="xp_reward" />
                        </flux:field>
                    </div>
                    <div class="mt-3">
                        <flux:checkbox wire:model="is_required">Required</flux:checkbox>
                    </div>
                </div>
                @endif

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    @if($this->embedded)
                        <button type="button" wire:click="cancelEmbedded"
                                class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 text-sm font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors shadow-sm">
                            Create &amp; Add Questions
                        </button>
                    @else
                        <flux:button href="{{ $assessment_type === 'assignment' ? route('assignments.index') : route('assessments.index') }}" wire:navigate variant="ghost">Cancel</flux:button>
                        <flux:button type="submit" variant="primary">{{ $assessment_type === 'assignment' ? 'Create Assignment' : 'Create Assessment' }}</flux:button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@if(session()->has('message') && !$this->embedded)
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('message') }}
    </div>
@endif
