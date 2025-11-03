<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="flex flex-col gap-6 p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create New Course</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Build your course step by step</p>
            </div>
            <flux:button href="{{ route('courses.index') }}" variant="ghost" wire:navigate>
                Cancel
            </flux:button>
        </div>

        {{-- Flash Message --}}
        @if(session()->has('message'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 flex items-center gap-3">
                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        {{-- Form --}}
        <form wire:submit.prevent="saveDraft" class="space-y-6">
            {{-- Basic Information Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Basic Information</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Enter the core details of your course</p>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Course Title *
                        </label>
                        <flux:input wire:model.live="title" placeholder="e.g., Introduction to Web Development" />
                        @error('title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter a clear and descriptive title for your course</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            URL Slug
                        </label>
                        <flux:input wire:model="slug" placeholder="auto-generated-from-title" />
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Short Description *
                        </label>
                        <flux:textarea wire:model="short_description" rows="3" placeholder="A brief one-line description that appears in course listings" />
                        @error('short_description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Full Description *
                        </label>
                        <flux:textarea wire:model="description" rows="6" placeholder="Detailed course description..." />
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Course Details Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Course Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Difficulty Level *
                            </label>
                            <flux:field>
                                <flux:select wire:model="difficulty_level">
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                </flux:select>
                            </flux:field>
                            @error('difficulty_level')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Estimated Duration (hours) *
                            </label>
                            <flux:input type="number" wire:model="estimated_duration" min="1" />
                            @error('estimated_duration')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Price (USD) *
                            </label>
                            <flux:input type="number" wire:model="price" step="0.01" min="0" />
                            @error('price')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Category *
                            </label>
                            <flux:input wire:model="category" placeholder="e.g., Web Development, Data Science" />
                            @error('category')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tags Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Tags</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Add tags to help students find your course</p>
                </div>
                <div class="p-6">
                    <div class="flex gap-2">
                        <flux:input wire:model="tagInput" placeholder="Add a tag and press Enter" wire:keydown.enter.prevent="addTag" class="flex-1" />
                        <flux:button type="button" wire:click="addTag" variant="primary">Add</flux:button>
                    </div>
                    @if(count($tags) > 0)
                        <div class="flex flex-wrap gap-2 mt-4">
                            @foreach($tags as $index => $tag)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ $tag }}
                                    <button type="button" wire:click="removeTag({{ $index }})" class="ml-2 hover:text-red-600 dark:hover:text-red-400">×</button>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Requirements Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Requirements</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">List what students need before taking this course</p>
                </div>
                <div class="p-6">
                    <div class="flex gap-2">
                        <flux:input wire:model="requirementInput" placeholder="Add a requirement" wire:keydown.enter.prevent="addRequirement" class="flex-1" />
                        <flux:button type="button" wire:click="addRequirement" variant="primary">Add</flux:button>
                    </div>
                    @if(count($requirements) > 0)
                        <ul class="mt-4 space-y-2">
                            @foreach($requirements as $index => $requirement)
                                <li class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-900 dark:text-white">{{ $requirement }}</span>
                                    <button type="button" wire:click="removeRequirement({{ $index }})" class="text-red-500 hover:text-red-700 dark:hover:text-red-400">Remove</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- What You'll Learn Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">What You'll Learn</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">List the key learning outcomes</p>
                </div>
                <div class="p-6">
                    <div class="flex gap-2">
                        <flux:input wire:model="learnInput" placeholder="Add a learning outcome" wire:keydown.enter.prevent="addLearningOutcome" class="flex-1" />
                        <flux:button type="button" wire:click="addLearningOutcome" variant="primary">Add</flux:button>
                    </div>
                    @if(count($what_you_learn) > 0)
                        <ul class="mt-4 space-y-2">
                            @foreach($what_you_learn as $index => $outcome)
                                <li class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-900 dark:text-white">{{ $outcome }}</span>
                                    <button type="button" wire:click="removeLearningOutcome({{ $index }})" class="text-red-500 hover:text-red-700 dark:hover:text-red-400">Remove</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Options Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Options</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center">
                        <flux:checkbox wire:model="is_featured" label="Feature this course" />
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-4 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <flux:button type="button" wire:click="saveDraft" variant="outline">
                    Save as Draft
                </flux:button>
                <flux:button type="button" wire:click="submitForApproval" variant="primary">
                    Submit for Approval
                </flux:button>
            </div>
        </form>
    </div>
</div>
