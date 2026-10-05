@php
    $isAssignment = $assessment_type === 'assignment';
    $backUrl = $isAssignment ? route('assignments.index') : route('assessments.manage');
    $inputClass = 'w-full rounded-xl border-0 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white dark:ring-gray-700';
    $labelClass = 'mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400';
    $cardClass = 'rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800 sm:p-6';
@endphp

<div class="{{ $this->embedded ? 'bg-gray-50 dark:bg-gray-950' : 'mx-auto max-w-5xl p-4 sm:p-6' }}">

    @if($this->embedded)
        <div class="sticky top-0 z-10 flex items-center gap-3 border-b border-gray-200 bg-white px-6 py-3 dark:border-gray-700 dark:bg-gray-900">
            <button wire:click="cancelEmbedded" type="button"
                    class="flex items-center gap-1.5 text-sm font-medium text-gray-500 transition-colors hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-400">
                <flux:icon name="chevron-left" variant="micro" class="size-4" /> Back to Lesson
            </button>
            <div class="h-4 w-px bg-gray-200 dark:bg-gray-700"></div>
            <span class="text-sm font-bold text-gray-800 dark:text-white">New Quiz / Assessment</span>
        </div>
    @else
        <div class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-6 text-white shadow-lg">
            <div class="pointer-events-none absolute -right-10 -top-16 size-48 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute right-24 -bottom-20 size-40 rounded-full bg-white/10"></div>
            <div class="relative">
                <a href="{{ $backUrl }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-widest text-orange-100 hover:text-white">
                    <flux:icon name="arrow-left" variant="micro" class="size-3.5" /> {{ $isAssignment ? 'Assignments' : 'Assessments' }}
                </a>
                <h1 class="mt-1 text-2xl font-extrabold sm:text-3xl">{{ $isAssignment ? 'New assignment' : 'New assessment' }}</h1>
                <p class="mt-1 text-sm text-orange-100">
                    {{ $isAssignment ? 'Set the brief, due date and what students should hand in.' : 'Pick a type, set the rules, then add questions from the bank or write new ones.' }}
                </p>
            </div>
        </div>
    @endif

    <form wire:submit="save" class="{{ $this->embedded ? 'space-y-5 p-6' : 'space-y-5' }}">

        {{-- Type --}}
        <section class="{{ $cardClass }}">
            <h2 class="text-base font-extrabold text-gray-900 dark:text-white">What are you making?</h2>
            <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">{{ $assessmentTypes[$assessment_type]['description'] ?? '' }}</p>
            <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
                @foreach($assessmentTypes as $value => $meta)
                    @php $active = $assessment_type === $value; @endphp
                    <button type="button" wire:click="$set('assessment_type', '{{ $value }}')" wire:key="type-{{ $value }}"
                            @class([
                                'group flex items-center gap-2.5 rounded-xl p-3 text-left ring-1 transition',
                                'bg-orange-50 ring-2 ring-orange-500 dark:bg-orange-900/20' => $active,
                                'bg-white ring-gray-200 hover:ring-orange-300 dark:bg-gray-900 dark:ring-gray-700' => ! $active,
                            ])>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg {{ $meta['tile'] }}">
                            <flux:icon :name="$meta['icon']" class="size-5" />
                        </span>
                        <span class="min-w-0 text-sm font-bold {{ $active ? 'text-orange-700 dark:text-orange-300' : 'text-gray-800 dark:text-gray-200' }}">{{ $meta['label'] }}</span>
                    </button>
                @endforeach
            </div>
            @error('assessment_type') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
        </section>

        {{-- Basics --}}
        <section class="{{ $cardClass }} space-y-4">
            <h2 class="text-base font-extrabold text-gray-900 dark:text-white">Basics</h2>

            @if(!$this->embedded)
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}">Course <span class="text-rose-500">*</span></label>
                        <select wire:model.live="course_id" class="{{ $inputClass }}" required>
                            <option value="">Select a course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                        @error('course_id') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Lesson</label>
                        <select wire:model="lesson_id" class="{{ $inputClass }}" @disabled(! $course_id || $lessons->isEmpty())>
                            <option value="">{{ $course_id && $lessons->isEmpty() ? 'No lessons yet — course level' : 'Course level (not tied to a lesson)' }}</option>
                            @foreach($lessons as $lesson)
                                <option value="{{ $lesson->id }}">{{ $lesson->title }}</option>
                            @endforeach
                        </select>
                        @error('lesson_id') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            <div>
                <label class="{{ $labelClass }}">Title <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="title" class="{{ $inputClass }} text-base font-semibold" placeholder="{{ $isAssignment ? 'e.g. Build your first web page' : 'e.g. Module 1 Quiz' }}" required @if($this->embedded) autofocus @endif>
                @error('title') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">{{ $isAssignment ? 'Brief summary' : 'Description' }}</label>
                <textarea wire:model="description" rows="3" class="{{ $inputClass }}"
                          placeholder="{{ $isAssignment ? 'Short overview shown to students before they submit' : 'Optional — shown to students before they start' }}"></textarea>
                @error('description') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            @if(in_array($assessment_type, ['pre_project_test', 'post_project_test']))
                <div>
                    <label class="{{ $labelClass }}">Project platform</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['' => 'Not set', 'scratch' => 'Scratch 3', 'other' => 'Other / Custom'] as $value => $label)
                            <button type="button" wire:click="$set('project_platform', {{ $value === '' ? 'null' : "'{$value}'" }})"
                                    @class([
                                        'rounded-full px-3.5 py-1.5 text-xs font-bold ring-1 transition',
                                        'bg-orange-500 text-white ring-orange-500' => (string) $project_platform === $value,
                                        'bg-white text-gray-600 ring-gray-200 hover:ring-orange-300 dark:bg-gray-900 dark:text-gray-300 dark:ring-gray-700' => (string) $project_platform !== $value,
                                    ])>{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        @if($isAssignment)
            {{-- Assignment details --}}
            <section class="{{ $cardClass }} space-y-4">
                <h2 class="text-base font-extrabold text-gray-900 dark:text-white">Assignment details</h2>

                <div>
                    <label class="{{ $labelClass }}">Instructions for students</label>
                    <textarea wire:model="assignment_instructions" rows="5" class="{{ $inputClass }}" placeholder="What should students do? Include steps, rubric notes or links."></textarea>
                    @error('assignment_instructions') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}">Due date</label>
                        <input type="date" wire:model="assignment_due_date" class="{{ $inputClass }}">
                        @error('assignment_due_date') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Max points</label>
                        <input type="number" wire:model="assignment_max_points" min="1" max="1000" class="{{ $inputClass }}" required>
                        @error('assignment_max_points') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Students can hand in</label>
                    <div class="grid gap-2.5 sm:grid-cols-2">
                        @foreach(['assignment_allow_text' => ['Text response', 'Type their answer in the browser', 'pencil-square'], 'assignment_allow_files' => ['File upload', 'PDF, Word, images, ZIP (10MB max)', 'paper-clip']] as $field => [$label, $hint, $icon])
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl p-3 ring-1 ring-gray-200 transition has-[:checked]:bg-orange-50 has-[:checked]:ring-2 has-[:checked]:ring-orange-500 dark:ring-gray-700 dark:has-[:checked]:bg-orange-900/20">
                                <input type="checkbox" wire:model="{{ $field }}" class="mt-0.5 size-4 rounded accent-orange-500">
                                <span>
                                    <span class="flex items-center gap-1.5 text-sm font-bold text-gray-900 dark:text-white"><flux:icon :name="$icon" variant="micro" class="size-4 text-orange-500" />{{ $label }}</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('assignment_allow_files') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">Brief files (optional)</label>
                    <label class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-gray-200 px-4 py-6 text-center transition hover:border-orange-300 hover:bg-orange-50/50 dark:border-gray-700 dark:hover:bg-orange-900/10">
                        <flux:icon name="cloud-arrow-up" class="size-7 text-orange-400" />
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Click to attach worksheets or reference images</span>
                        <span class="text-xs text-gray-400">Max 10MB each</span>
                        <input type="file" wire:model="assignmentBriefFiles" multiple accept=".pdf,.doc,.docx,.txt,.zip,.jpg,.jpeg,.png" class="hidden">
                    </label>
                    <div wire:loading wire:target="assignmentBriefFiles" class="mt-2 text-xs font-semibold text-orange-600">Uploading…</div>
                    @error('assignmentBriefFiles.*') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror

                    @if(!empty($assignmentBriefFiles))
                        <ul class="mt-3 space-y-2">
                            @foreach($assignmentBriefFiles as $index => $file)
                                <li class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-3 py-2 text-sm dark:bg-gray-800">
                                    <span class="flex min-w-0 items-center gap-2 text-gray-700 dark:text-gray-300"><flux:icon name="document" variant="micro" class="size-4 shrink-0 text-gray-400" /><span class="truncate">{{ $file->getClientOriginalName() }}</span></span>
                                    <button type="button" wire:click="removeAssignmentBriefFile({{ $index }})" class="text-xs font-bold text-rose-600 hover:text-rose-700">Remove</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        @endif

        {{-- Rules --}}
        <section class="{{ $cardClass }} space-y-5">
            <h2 class="text-base font-extrabold text-gray-900 dark:text-white">Rules</h2>

            <div class="grid grid-cols-2 gap-3 {{ $isAssignment ? 'sm:grid-cols-3' : 'sm:grid-cols-4' }}">
                @php
                    $numbers = ['max_attempts' => ['Attempts', 'arrow-path', 1, null, ''], 'passing_score' => ['Pass mark %', 'check-badge', 0, 100, ''], 'xp_reward' => ['XP reward', 'star', 0, null, '']];
                    if (! $isAssignment) {
                        $numbers = ['max_attempts' => $numbers['max_attempts'], 'time_limit_minutes' => ['Time limit (min)', 'clock', 1, null, 'None']] + array_slice($numbers, 1, null, true);
                    }
                @endphp
                @foreach($numbers as $field => [$label, $icon, $min, $max, $placeholder])
                    <div class="rounded-xl bg-gray-50 p-3 ring-1 ring-gray-100 focus-within:ring-2 focus-within:ring-orange-500 dark:bg-gray-800 dark:ring-gray-700">
                        <span class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400"><flux:icon :name="$icon" variant="micro" class="size-3.5 text-orange-500" />{{ $label }}</span>
                        <input type="number" wire:model="{{ $field }}" min="{{ $min }}" @if($max) max="{{ $max }}" @endif placeholder="{{ $placeholder }}"
                               class="mt-1 w-full border-0 bg-transparent p-0 text-2xl font-extrabold text-gray-900 placeholder:text-gray-300 focus:ring-0 dark:text-white">
                        @error($field) <p class="text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>

            @unless($isAssignment)
                <div class="rounded-xl bg-orange-50/70 p-4 ring-1 ring-orange-100 dark:bg-orange-900/10 dark:ring-orange-900/40">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-3">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-orange-500 text-white"><flux:icon name="arrows-right-left" class="size-5" /></span>
                            <div>
                                <p class="text-sm font-extrabold text-gray-900 dark:text-white">Random question pool</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Add more questions than students answer — e.g. 20 in the pool, 10 per attempt. Every attempt draws a different random set in random order.</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2 rounded-xl bg-white px-3 py-2 ring-1 ring-orange-200 dark:bg-gray-900 dark:ring-orange-900/50">
                            <span class="text-xs font-semibold text-gray-500">Questions per attempt</span>
                            <input type="number" wire:model="questions_per_attempt" min="1" placeholder="All"
                                   class="w-16 rounded-lg border-0 bg-gray-100 px-2 py-1 text-center text-sm font-extrabold text-gray-900 placeholder:font-semibold placeholder:text-gray-400 focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>
                    @error('questions_per_attempt') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach([
                        'is_randomized' => ['Shuffle question order', 'Each student sees questions in a different order', 'queue-list'],
                        'shuffle_options' => ['Shuffle answers', 'A, B, C, D appear in a different order', 'arrows-up-down'],
                        'show_results_immediately' => ['Show score right away', 'Students see their result on submit', 'bolt'],
                        'show_correct_answers' => ['Show correct answers', 'Reveal the answers after submitting', 'eye'],
                        'allow_review' => ['Allow review', 'Students can look back at their answers', 'document-magnifying-glass'],
                        'is_required' => ['Required', 'Must be completed to finish the lesson', 'flag'],
                    ] as $field => [$label, $hint, $icon])
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl p-3 ring-1 ring-gray-200 transition has-[:checked]:bg-orange-50 has-[:checked]:ring-orange-300 dark:ring-gray-700 dark:has-[:checked]:bg-orange-900/20">
                            <input type="checkbox" wire:model="{{ $field }}" class="mt-0.5 size-4 rounded accent-orange-500">
                            <span>
                                <span class="flex items-center gap-1.5 text-sm font-bold text-gray-900 dark:text-white"><flux:icon :name="$icon" variant="micro" class="size-4 text-orange-500" />{{ $label }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @else
                <label class="flex w-fit cursor-pointer items-start gap-3 rounded-xl p-3 ring-1 ring-gray-200 transition has-[:checked]:bg-orange-50 has-[:checked]:ring-orange-300 dark:ring-gray-700">
                    <input type="checkbox" wire:model="is_required" class="mt-0.5 size-4 rounded accent-orange-500">
                    <span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Required</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Must be submitted to finish the lesson</span>
                    </span>
                </label>
            @endunless
        </section>

        {{-- Actions --}}
        <div class="sticky bottom-4 z-10 flex items-center justify-between gap-3 rounded-2xl bg-white/90 p-3 shadow-lg ring-1 ring-gray-100 backdrop-blur dark:bg-gray-900/90 dark:ring-gray-800">
            <p class="hidden pl-2 text-xs text-gray-500 sm:block">
                {{ $isAssignment ? 'Students can submit as soon as it is published.' : 'Next: add questions from the Question Bank or write new ones.' }}
            </p>
            <div class="ml-auto flex items-center gap-2">
                @if($this->embedded)
                    <button type="button" wire:click="cancelEmbedded" class="rounded-xl px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</button>
                @else
                    <a href="{{ $backUrl }}" wire:navigate class="rounded-xl px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
                @endif
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:shadow-md disabled:opacity-60">
                    <flux:icon :name="$isAssignment ? 'check' : 'arrow-right'" variant="micro" class="size-4" />
                    {{ $isAssignment ? 'Create assignment' : 'Create & add questions' }}
                </button>
            </div>
        </div>
    </form>
</div>
