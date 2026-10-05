@php
    $typeStyles = \App\Livewire\Questions\QuestionEditor::TYPE_STYLES;
    $currentType = $questionFormData['question_type'];
    $isPage = $context === 'page';
    $card = 'rounded-2xl bg-white dark:bg-gray-900 ring-1 ring-gray-100 dark:ring-gray-800 shadow-sm p-5';
    $label = 'block text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-2';
    $panel = 'rounded-2xl bg-gray-50 dark:bg-gray-800/50 ring-1 ring-gray-100 dark:ring-gray-800 p-5';
    $addBtn = 'inline-flex items-center gap-1.5 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-700 transition hover:bg-orange-100 dark:bg-orange-900/30 dark:text-orange-300';
    $removeBtn = 'rounded-lg p-1.5 text-gray-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-900/20';
    $difficultyStyles = ['easy' => 'bg-emerald-500', 'medium' => 'bg-amber-500', 'hard' => 'bg-rose-500'];
    $tagPreview = collect(preg_split('/[,\n]/', $tagInput) ?: [])->map(fn ($t) => trim($t))->filter()->unique()->take(12);
@endphp

<div>
    <form wire:submit.prevent="save" class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_19rem]">

        {{-- ===================== Main column ===================== --}}
        <div class="space-y-5 min-w-0">
            @if($usageCount > 1)
                <div class="flex items-start gap-3 rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-200 dark:ring-amber-800">
                    <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-amber-500" />
                    <p>Used in <strong>{{ $usageCount }} assessments</strong>. Your changes apply to all of them. Attempts already started keep the version they were given.</p>
                </div>
            @endif

            {{-- Type picker --}}
            <div class="{{ $card }}">
                <span class="{{ $label }}">Question type</span>
                <div class="grid grid-cols-2 sm:grid-cols-3 {{ $isPage ? 'xl:grid-cols-4' : 'md:grid-cols-4' }} gap-2">
                    @foreach($allowedTypes as $value => $typeLabel)
                        @php $style = $typeStyles[$value] ?? ['icon' => 'question-mark-circle', 'tile' => 'bg-gray-100 text-gray-600']; @endphp
                        <button type="button" wire:click="$set('questionFormData.question_type', '{{ $value }}')" wire:key="type-{{ $value }}" @class([
                            'flex items-center gap-2.5 rounded-xl p-2.5 text-left transition',
                            'bg-orange-50 ring-2 ring-orange-500 dark:bg-orange-900/20' => $currentType === $value,
                            'ring-1 ring-gray-200 hover:ring-orange-300 hover:bg-orange-50/50 dark:ring-gray-700 dark:hover:bg-gray-800' => $currentType !== $value,
                        ])>
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $style['tile'] }}">
                                <flux:icon :name="$style['icon']" variant="mini" class="size-4" />
                            </span>
                            <span class="text-xs font-semibold leading-tight text-gray-800 dark:text-gray-100">{{ \Illuminate\Support\Str::before($typeLabel, ' (') }}</span>
                        </button>
                    @endforeach
                </div>
                @error('questionFormData.question_type')
                    <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Question text + image --}}
            <div class="{{ $card }} space-y-4">
                <div>
                    <label class="{{ $label }}" for="question-text">Question</label>
                    <textarea id="question-text" wire:model.live.debounce.600ms="questionFormData.question_text" rows="4" placeholder="Type your question…"
                              class="w-full rounded-xl border-0 bg-gray-50 px-4 py-3 text-base font-medium text-gray-900 ring-1 ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white dark:ring-gray-700"></textarea>
                    @error('questionFormData.question_text')
                        <p class="mt-1 text-sm font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-gray-500">
                        Code on its own lines (keep the indentation) shows as a code panel, and Scratch scripts like
                        <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">when green flag clicked</code> show as real blocks.
                        To be explicit, wrap them in <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">```python</code> or <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">```scratch</code> … <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">```</code>.
                    </p>
                    @if(\App\Support\QuestionText::hasRichParts($questionFormData['question_text'] ?? ''))
                        <div class="mt-3 rounded-xl bg-gray-50 p-4 ring-1 ring-gray-200 dark:bg-gray-800/60 dark:ring-gray-700">
                            <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-gray-400">Student preview</p>
                            <x-question-text :text="$questionFormData['question_text'] ?? ''" class="text-[15px] font-medium text-gray-900 dark:text-white" />
                        </div>
                    @endif
                    @if($currentType === 'fill_blank')
                        <p class="mt-1.5 text-xs text-gray-500">Mark each gap with <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">{blank}</code> or <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">_____</code>.</p>
                    @endif
                </div>

                @if($questionFormData['image_url'] || $questionImage)
                    <div class="relative inline-block">
                        <img src="{{ $questionImage ? $questionImage->temporaryUrl() : Storage::disk('public')->url($questionFormData['image_url']) }}" alt="Question image" class="max-h-56 rounded-xl ring-1 ring-gray-200 dark:ring-gray-700">
                        <button type="button" wire:click="removeQuestionImage" class="absolute -right-2 -top-2 rounded-full bg-white p-1 text-gray-500 shadow ring-1 ring-gray-200 hover:text-rose-600" aria-label="Remove image">
                            <flux:icon name="x-mark" variant="mini" class="size-4" />
                        </button>
                    </div>
                @else
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-dashed border-gray-200 px-4 py-3 text-sm text-gray-500 transition hover:border-orange-300 hover:bg-orange-50/40 dark:border-gray-700">
                        <flux:icon name="photo" class="size-5 text-orange-400" />
                        <span><span class="font-semibold text-orange-600">Add an image</span> (optional, max 5MB)</span>
                        <input type="file" wire:model="questionImage" accept="image/*" class="sr-only">
                    </label>
                @endif
                <div wire:loading wire:target="questionImage" class="text-xs text-gray-500">Uploading…</div>
                @error('questionImage') <p class="text-sm font-medium text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Answers: choice types --}}
            @if(in_array($currentType, ['multiple_choice', 'multiple_select', 'true_false', 'choice']))
                <div class="{{ $card }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="{{ $label }} !mb-0">Answers</span>
                        @if($currentType !== 'true_false' || count($questionOptions) < 2)
                            <button type="button" wire:click="addQuestionOption" class="{{ $addBtn }}">
                                <flux:icon name="plus" variant="micro" class="size-3.5" /> Add answer
                            </button>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        {{ $currentType === 'multiple_select' ? 'Students can pick several. Mark every correct answer.' : 'Students pick one. Click the circle next to the correct answer.' }}
                    </p>

                    <div class="space-y-2.5">
                        @forelse($questionOptions as $index => $option)
                            @php $correct = (bool) ($option['is_correct'] ?? false); @endphp
                            <div wire:key="option-{{ $option['id'] ?? 'new' }}-{{ $index }}" @class([
                                'rounded-xl p-3 ring-1 transition',
                                'bg-emerald-50/70 ring-emerald-300 dark:bg-emerald-900/20 dark:ring-emerald-700' => $correct,
                                'bg-white ring-gray-200 dark:bg-gray-800 dark:ring-gray-700' => ! $correct,
                            ])>
                                <div class="flex items-center gap-3">
                                    <button type="button"
                                            wire:click="{{ $currentType === 'multiple_select' ? 'toggleCorrectOption' : 'markCorrectOption' }}({{ $index }})"
                                            title="{{ $correct ? 'Correct answer' : 'Mark as correct' }}"
                                            @class([
                                                'flex size-8 shrink-0 items-center justify-center text-sm font-bold transition',
                                                'rounded-lg' => $currentType === 'multiple_select',
                                                'rounded-full' => $currentType !== 'multiple_select',
                                                'bg-emerald-500 text-white shadow-sm' => $correct,
                                                'bg-gray-100 text-gray-500 hover:bg-emerald-100 hover:text-emerald-700 dark:bg-gray-700 dark:text-gray-300' => ! $correct,
                                            ])>
                                        @if($correct)
                                            <flux:icon name="check" variant="mini" class="size-4" />
                                        @else
                                            {{ chr(65 + $index) }}
                                        @endif
                                    </button>
                                    <input wire:model="questionOptions.{{ $index }}.option_text" placeholder="Answer {{ chr(65 + $index) }}"
                                           class="min-w-0 flex-1 border-0 bg-transparent px-1 py-1.5 text-sm font-medium text-gray-900 placeholder:text-gray-400 focus:ring-0 dark:text-white">
                                    <label class="cursor-pointer rounded-lg p-1.5 text-gray-400 transition hover:bg-orange-50 hover:text-orange-600" title="Add image">
                                        <flux:icon name="photo" variant="mini" class="size-4" />
                                        <input type="file" wire:model="tempOptionImages.{{ $index }}" accept="image/*" class="sr-only">
                                    </label>
                                    <button type="button" wire:click="removeQuestionOption({{ $index }})" class="{{ $removeBtn }}" title="Remove">
                                        <flux:icon name="trash" variant="mini" class="size-4" />
                                    </button>
                                </div>
                                @if(isset($tempOptionImages[$index]) && $tempOptionImages[$index])
                                    <img src="{{ $tempOptionImages[$index]->temporaryUrl() }}" alt="" class="mt-2 ml-11 max-h-32 rounded-lg ring-1 ring-gray-200">
                                @elseif(!empty($option['image_url']))
                                    <div class="relative mt-2 ml-11 inline-block">
                                        <img src="{{ Storage::disk('public')->url($option['image_url']) }}" alt="" class="max-h-32 rounded-lg ring-1 ring-gray-200">
                                        <button type="button" wire:click="removeOptionImage({{ $index }})" class="absolute -right-2 -top-2 rounded-full bg-white p-0.5 text-gray-500 shadow ring-1 ring-gray-200 hover:text-rose-600" aria-label="Remove image">
                                            <flux:icon name="x-mark" variant="micro" class="size-3.5" />
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <button type="button" wire:click="addQuestionOption" class="w-full rounded-xl border-2 border-dashed border-gray-200 py-6 text-sm font-semibold text-gray-500 hover:border-orange-300 hover:text-orange-600 dark:border-gray-700">
                                + Add the first answer
                            </button>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Answers: short answer --}}
            @if($currentType === 'short_answer')
                <div class="{{ $card }} space-y-4">
                    <div>
                        <span class="{{ $label }} !mb-1">Answer key</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Leave empty if a teacher should mark it by hand.</p>
                    </div>
                    <flux:input wire:model="shortAnswer.correct_answer" label="Correct answer" placeholder="e.g. HyperText Markup Language" />
                    <flux:textarea wire:model="shortAnswer.alternatives" label="Also accept (one per line)" rows="3" placeholder="HTML" />
                    <flux:switch wire:model="shortAnswer.case_sensitive" label="Case sensitive" />
                </div>
            @endif

            {{-- Answers: matching --}}
            @if($currentType === 'matching')
                <div class="{{ $card }}">
                    <div class="flex items-center justify-between mb-4">
                        <span class="{{ $label }} !mb-0">Pairs to match</span>
                        <button type="button" wire:click="addMatchingPair" class="{{ $addBtn }}"><flux:icon name="plus" variant="micro" class="size-3.5" /> Add pair</button>
                    </div>
                    <div class="space-y-2.5">
                        @forelse($matchingPairs as $index => $pair)
                            <div class="flex items-center gap-2" wire:key="pair-{{ $index }}">
                                <input wire:model="matchingPairs.{{ $index }}.left_item" placeholder="e.g. HTML"
                                       class="min-w-0 flex-1 rounded-xl border-0 bg-gray-50 px-3 py-2 text-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:ring-gray-700 dark:text-white">
                                <flux:icon name="arrows-right-left" variant="mini" class="size-4 shrink-0 text-cyan-500" />
                                <input wire:model="matchingPairs.{{ $index }}.right_item" placeholder="e.g. Structure of a web page"
                                       class="min-w-0 flex-1 rounded-xl border-0 bg-gray-50 px-3 py-2 text-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:ring-gray-700 dark:text-white">
                                <button type="button" wire:click="removeMatchingPair({{ $index }})" class="{{ $removeBtn }}"><flux:icon name="trash" variant="mini" class="size-4" /></button>
                            </div>
                        @empty
                            <button type="button" wire:click="addMatchingPair" class="w-full rounded-xl border-2 border-dashed border-gray-200 py-6 text-sm font-semibold text-gray-500 hover:border-orange-300 hover:text-orange-600 dark:border-gray-700">+ Add the first pair</button>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Answers: ordering --}}
            @if($currentType === 'ordering')
                <div class="{{ $card }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="{{ $label }} !mb-0">Correct order</span>
                        <button type="button" wire:click="addOrderingItem" class="{{ $addBtn }}"><flux:icon name="plus" variant="micro" class="size-3.5" /> Add step</button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Students see these shuffled. The number sets each step's correct place.</p>
                    <div class="space-y-2.5">
                        @forelse($orderingItems as $index => $item)
                            <div class="flex items-center gap-2" wire:key="ordering-{{ $index }}">
                                <input type="number" min="1" wire:model="orderingItems.{{ $index }}.correct_order"
                                       class="w-14 rounded-xl border-0 bg-pink-50 px-2 py-2 text-center text-sm font-bold text-pink-700 ring-1 ring-pink-200 focus:ring-2 focus:ring-orange-500 dark:bg-pink-900/20 dark:text-pink-300 dark:ring-pink-800">
                                <input wire:model="orderingItems.{{ $index }}.item_text" placeholder="Step text"
                                       class="min-w-0 flex-1 rounded-xl border-0 bg-gray-50 px-3 py-2 text-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:ring-gray-700 dark:text-white">
                                <button type="button" wire:click="removeOrderingItem({{ $index }})" class="{{ $removeBtn }}"><flux:icon name="trash" variant="mini" class="size-4" /></button>
                            </div>
                        @empty
                            <button type="button" wire:click="addOrderingItem" class="w-full rounded-xl border-2 border-dashed border-gray-200 py-6 text-sm font-semibold text-gray-500 hover:border-orange-300 hover:text-orange-600 dark:border-gray-700">+ Add the first step</button>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Answers: fill in the blank --}}
            @if($currentType === 'fill_blank')
                <div class="{{ $card }}">
                    <div class="flex items-center justify-between mb-4">
                        <span class="{{ $label }} !mb-0">Blanks</span>
                        <button type="button" wire:click="addFillBlank" class="{{ $addBtn }}"><flux:icon name="plus" variant="micro" class="size-3.5" /> Add blank</button>
                    </div>
                    <div class="space-y-3">
                        @forelse($fillBlankSettings['blanks'] ?? [] as $index => $blank)
                            <div class="{{ $panel }} !p-4" wire:key="blank-{{ $index }}">
                                <div class="flex items-center gap-2">
                                    <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-xs font-bold text-teal-700 dark:bg-teal-900/40 dark:text-teal-300">{{ $index + 1 }}</span>
                                    <input wire:model="fillBlankSettings.blanks.{{ $index }}.correct_answer" placeholder="Correct answer"
                                           class="min-w-0 flex-1 rounded-xl border-0 bg-white px-3 py-2 text-sm font-medium ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500 dark:bg-gray-900 dark:ring-gray-700 dark:text-white">
                                    <button type="button" wire:click="removeFillBlank({{ $index }})" class="{{ $removeBtn }}"><flux:icon name="trash" variant="mini" class="size-4" /></button>
                                </div>
                                <div class="mt-3 ml-9 space-y-2">
                                    @foreach($blank['alternative_answers'] ?? [] as $altIndex => $altAnswer)
                                        <div class="flex items-center gap-2" wire:key="blank-{{ $index }}-alt-{{ $altIndex }}">
                                            <input wire:model="fillBlankSettings.blanks.{{ $index }}.alternative_answers.{{ $altIndex }}" placeholder="Also accept"
                                                   class="min-w-0 flex-1 rounded-lg border-0 bg-white px-3 py-1.5 text-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500 dark:bg-gray-900 dark:ring-gray-700 dark:text-white">
                                            <button type="button" wire:click="removeAlternativeAnswer({{ $index }}, {{ $altIndex }})" class="{{ $removeBtn }}"><flux:icon name="x-mark" variant="mini" class="size-4" /></button>
                                        </div>
                                    @endforeach
                                    <div class="flex items-center gap-4">
                                        <button type="button" wire:click="addAlternativeAnswer({{ $index }})" class="text-xs font-semibold text-orange-600 hover:text-orange-700">+ Also accept…</button>
                                        <flux:checkbox wire:model="fillBlankSettings.blanks.{{ $index }}.case_sensitive" label="Case sensitive" />
                                    </div>
                                </div>
                            </div>
                        @empty
                            <button type="button" wire:click="addFillBlank" class="w-full rounded-xl border-2 border-dashed border-gray-200 py-6 text-sm font-semibold text-gray-500 hover:border-orange-300 hover:text-orange-600 dark:border-gray-700">+ Add the first blank</button>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Rating --}}
            @if($currentType === 'rating')
                <div class="{{ $card }}">
                    <span class="{{ $label }}">Rating scale</span>
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input type="number" wire:model.live="ratingScaleSettings.min" label="From" min="0" />
                        <flux:input type="number" wire:model.live="ratingScaleSettings.max" label="To" min="1" />
                    </div>
                    @php $min = (int) ($ratingScaleSettings['min'] ?? 1); $max = (int) ($ratingScaleSettings['max'] ?? 5); @endphp
                    @if($max > $min && $max - $min <= 10)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach(range($min, $max) as $point)
                                <span class="flex size-9 items-center justify-center rounded-full bg-yellow-100 text-sm font-bold text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">{{ $point }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Code submission --}}
            @if($currentType === 'code_submission')
                <div class="{{ $card }} space-y-4">
                    <span class="{{ $label }} !mb-0">Code settings</span>
                    <flux:select wire:model="codeSubmissionSettings.language" label="Language">
                        @foreach(['javascript' => 'JavaScript', 'python' => 'Python', 'java' => 'Java', 'cpp' => 'C++', 'c' => 'C', 'php' => 'PHP', 'ruby' => 'Ruby', 'go' => 'Go', 'rust' => 'Rust', 'typescript' => 'TypeScript'] as $value => $languageLabel)
                            <option value="{{ $value }}">{{ $languageLabel }}</option>
                        @endforeach
                    </flux:select>
                    <div>
                        <label class="{{ $label }}">Starter code</label>
                        <textarea wire:model="codeSubmissionSettings.template" rows="6" placeholder="// Starting code for students"
                                  class="w-full rounded-xl border-0 bg-gray-900 px-4 py-3 font-mono text-sm text-emerald-300 ring-1 ring-gray-800 placeholder:text-gray-500 focus:ring-2 focus:ring-orange-500"></textarea>
                    </div>
                    <flux:textarea wire:model="codeSubmissionSettings.expected_output" label="Expected output (optional)" rows="3" />
                </div>
            @endif

            {{-- File upload --}}
            @if($currentType === 'file_upload')
                <div class="{{ $card }} space-y-4">
                    <span class="{{ $label }} !mb-0">Upload rules</span>
                    <flux:input wire:model="questionFormData.settings.allowed_types" label="Allowed file types" placeholder="pdf, docx, png, zip" />
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input type="number" wire:model="questionFormData.settings.max_size" label="Max size (MB)" min="1" max="100" />
                        <flux:input type="number" wire:model="questionFormData.settings.max_files" label="Max files" min="1" />
                    </div>
                </div>
            @endif

            {{-- Rubric --}}
            @if($currentType === 'rubric_criteria')
                <div class="{{ $card }}">
                    <div class="flex items-center justify-between mb-4">
                        <span class="{{ $label }} !mb-0">Rubric</span>
                        <button type="button" wire:click="addRubricCriterion" class="{{ $addBtn }}"><flux:icon name="plus" variant="micro" class="size-3.5" /> Add criterion</button>
                    </div>
                    <div class="space-y-3">
                        @forelse($rubricCriteria as $index => $criterion)
                            <div class="{{ $panel }} !p-4 space-y-3" wire:key="criterion-{{ $index }}">
                                <div class="flex items-center gap-2">
                                    <input wire:model="rubricCriteria.{{ $index }}.name" placeholder="Criterion, e.g. Code quality"
                                           class="min-w-0 flex-1 rounded-xl border-0 bg-white px-3 py-2 text-sm font-semibold ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500 dark:bg-gray-900 dark:ring-gray-700 dark:text-white">
                                    <input type="number" min="0" wire:model="rubricCriteria.{{ $index }}.max_points" placeholder="Pts"
                                           class="w-20 rounded-xl border-0 bg-white px-3 py-2 text-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500 dark:bg-gray-900 dark:ring-gray-700 dark:text-white">
                                    <button type="button" wire:click="removeRubricCriterion({{ $index }})" class="{{ $removeBtn }}"><flux:icon name="trash" variant="mini" class="size-4" /></button>
                                </div>
                                <textarea wire:model="rubricCriteria.{{ $index }}.description" rows="2" placeholder="What does this criterion assess?"
                                          class="w-full rounded-xl border-0 bg-white px-3 py-2 text-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500 dark:bg-gray-900 dark:ring-gray-700 dark:text-white"></textarea>
                                <div class="space-y-1.5">
                                    @foreach($criterion['performance_levels'] ?? [] as $levelIndex => $level)
                                        <div class="grid grid-cols-12 gap-2">
                                            <input wire:model="rubricCriteria.{{ $index }}.performance_levels.{{ $levelIndex }}.level" placeholder="Level" class="col-span-4 rounded-lg border-0 bg-white px-2.5 py-1.5 text-xs font-semibold ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700 dark:text-white">
                                            <input type="number" wire:model="rubricCriteria.{{ $index }}.performance_levels.{{ $levelIndex }}.points" placeholder="%" class="col-span-2 rounded-lg border-0 bg-white px-2.5 py-1.5 text-xs ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700 dark:text-white">
                                            <input wire:model="rubricCriteria.{{ $index }}.performance_levels.{{ $levelIndex }}.description" placeholder="Description" class="col-span-6 rounded-lg border-0 bg-white px-2.5 py-1.5 text-xs ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700 dark:text-white">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <button type="button" wire:click="addRubricCriterion" class="w-full rounded-xl border-2 border-dashed border-gray-200 py-6 text-sm font-semibold text-gray-500 hover:border-orange-300 hover:text-orange-600 dark:border-gray-700">+ Add the first criterion</button>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Word limits --}}
            @if(in_array($currentType, ['essay', 'short_answer']))
                <div class="{{ $card }}">
                    <span class="{{ $label }}">Word limits (optional)</span>
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input type="number" wire:model="questionFormData.settings.min_words" placeholder="Minimum" min="0" />
                        <flux:input type="number" wire:model="questionFormData.settings.max_words" placeholder="Maximum" min="1" />
                    </div>
                </div>
            @endif

            {{-- Explanation --}}
            <div class="{{ $card }}">
                <label class="{{ $label }}" for="question-explanation">Explanation shown after answering (optional)</label>
                <textarea id="question-explanation" wire:model="questionFormData.explanation" rows="2" placeholder="Why is this the right answer?"
                          class="w-full rounded-xl border-0 bg-gray-50 px-4 py-3 text-sm text-gray-900 ring-1 ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white dark:ring-gray-700"></textarea>
            </div>
        </div>

        {{-- ===================== Settings column ===================== --}}
        <div class="space-y-5 lg:sticky lg:top-0 lg:self-start">
            <div class="{{ $card }} space-y-5">
                <div>
                    <label class="{{ $label }}" for="question-points">Points</label>
                    <input id="question-points" type="number" min="0" step="0.5" wire:model="questionFormData.points"
                           class="w-full rounded-xl border-0 bg-gray-50 px-4 py-2.5 text-lg font-extrabold text-gray-900 ring-1 ring-gray-200 focus:bg-white focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                    @error('questionFormData.points') <p class="mt-1 text-sm font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <span class="{{ $label }}">Difficulty</span>
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
                        @foreach($difficulties as $value => $difficultyLabel)
                            <button type="button" wire:click="$set('questionFormData.difficulty', '{{ $value }}')" @class([
                                'flex items-center justify-center gap-1.5 rounded-lg py-1.5 text-xs font-bold transition',
                                'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' => $questionFormData['difficulty'] === $value,
                                'text-gray-500 hover:text-gray-800 dark:text-gray-400' => $questionFormData['difficulty'] !== $value,
                            ])>
                                <span class="size-2 rounded-full {{ $difficultyStyles[$value] }}"></span>{{ $difficultyLabel }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <span class="{{ $label }}">Status</span>
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
                        @foreach($statuses as $value => $statusLabel)
                            <button type="button" wire:click="$set('questionFormData.status', '{{ $value }}')" @class([
                                'rounded-lg py-1.5 text-xs font-bold transition',
                                'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' => $questionFormData['status'] === $value,
                                'text-gray-500 hover:text-gray-800 dark:text-gray-400' => $questionFormData['status'] !== $value,
                            ])>{{ $statusLabel }}</button>
                        @endforeach
                    </div>
                    <p class="mt-1.5 text-[11px] text-gray-400">Only active questions show up when adding from the bank.</p>
                </div>

                <div>
                    <label class="{{ $label }}" for="question-tags">Tags</label>
                    <input id="question-tags" wire:model.live.debounce.400ms="tagInput" placeholder="html, loops, variables"
                           class="w-full rounded-xl border-0 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 ring-1 ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                    @if($tagPreview->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach($tagPreview as $tagName)
                                <span class="rounded-lg bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-700 dark:bg-orange-900/20 dark:text-orange-300">#{{ $tagName }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-1.5 text-[11px] text-gray-400">Separate with commas.</p>
                    @endif
                </div>
            </div>

            {{-- Placements --}}
            <div class="{{ $card }}">
                <div class="flex items-center justify-between mb-3">
                    <span class="{{ $label }} !mb-0">Belongs to</span>
                    <button type="button" wire:click="addPlacement" class="text-xs font-semibold text-orange-600 hover:text-orange-700">+ Course</button>
                </div>
                @error('placements') <p class="mb-2 text-sm font-medium text-rose-600">{{ $message }}</p> @enderror
                <div class="space-y-3">
                    @forelse($placements as $index => $placement)
                        <div class="rounded-xl bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-gray-800 dark:ring-gray-700 space-y-2" wire:key="placement-{{ $index }}">
                            <div class="flex items-center gap-2">
                                <flux:icon name="academic-cap" variant="mini" class="size-4 shrink-0 text-orange-500" />
                                <select wire:model.live="placements.{{ $index }}.course_id"
                                        class="min-w-0 flex-1 rounded-lg border-0 bg-white py-1.5 pl-2 pr-7 text-xs font-semibold ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500 dark:bg-gray-900 dark:text-white dark:ring-gray-700">
                                    <option value="">Choose course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="removePlacement({{ $index }})" class="{{ $removeBtn }} !p-1"><flux:icon name="x-mark" variant="micro" class="size-3.5" /></button>
                            </div>
                            @if(!empty($placement['course_id']))
                                <select wire:model="placements.{{ $index }}.course_module_id"
                                        class="w-full rounded-lg border-0 bg-white py-1.5 pl-2 pr-7 text-xs ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500 dark:bg-gray-900 dark:text-white dark:ring-gray-700">
                                    <option value="">Whole course</option>
                                    @foreach($modulesByCourse[$placement['course_id']] ?? [] as $module)
                                        <option value="{{ $module->id }}">{{ $module->title }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-gray-400">Not in any course yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-2">
                @if($isPage)
                    <a href="{{ route('questions.index') }}" wire:navigate class="flex-1 rounded-xl px-4 py-3 text-center text-sm font-semibold text-gray-600 ring-1 ring-gray-200 transition hover:bg-gray-50 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-800">Cancel</a>
                @else
                    <button type="button" wire:click="$dispatch('question-editor-cancelled')" class="flex-1 rounded-xl px-4 py-3 text-sm font-semibold text-gray-600 ring-1 ring-gray-200 transition hover:bg-gray-50 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-800">Cancel</button>
                @endif
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="flex-[2] inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-3 text-sm font-bold text-white shadow-md transition hover:bg-orange-600 disabled:opacity-60">
                    <flux:icon name="check" variant="mini" class="size-5" wire:loading.remove wire:target="save" />
                    <span wire:loading.remove wire:target="save">Save question</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </div>
    </form>
</div>
