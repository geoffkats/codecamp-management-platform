<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="flex flex-col gap-6 p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Course</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $course->title }}</p>
            </div>
            <div class="flex gap-2">
                <flux:button href="{{ route('courses.show', $course) }}" variant="ghost" wire:navigate>
                    Cancel
                </flux:button>
                <flux:button href="{{ route('curriculum.builder', $course) }}" variant="outline" wire:navigate>
                    Curriculum Builder
                </flux:button>
            </div>
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
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Course Title *
                        </label>
                        <flux:input wire:model.live="title" />
                        @error('title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            URL Slug
                        </label>
                        <flux:input wire:model="slug" />
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Short Description *
                        </label>
                        <flux:textarea wire:model="short_description" rows="3" />
                        @error('short_description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Full Description *
                        </label>
                        <flux:textarea wire:model="description" rows="6" />
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
                            <flux:input wire:model="category" />
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
                </div>
                <div class="p-6">
                    <div class="flex gap-2">
                        <flux:input wire:model="tagInput" placeholder="Add a tag" wire:keydown.enter.prevent="addTag" class="flex-1" />
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
                </div>
                <div class="p-6">
                    <div class="flex gap-2">
                        <flux:input wire:model="requirementInput" placeholder="Add requirement" wire:keydown.enter.prevent="addRequirement" class="flex-1" />
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
                </div>
                <div class="p-6">
                    <div class="flex gap-2">
                        <flux:input wire:model="learnInput" placeholder="Add learning outcome" wire:keydown.enter.prevent="addLearningOutcome" class="flex-1" />
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
                <div class="p-6 space-y-4">
                    {{-- Approval Status --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="h-5 w-5 {{ $course->approval_status === 'approved' ? 'text-green-600 dark:text-green-400' : ($course->approval_status === 'pending' ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-400') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                    Approval Status
                                </h3>
                                @if($course->approval_status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Approved
                                    </span>
                                @elseif($course->approval_status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                        Pending Review
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        Draft
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                @if($course->approval_status === 'approved')
                                    This course has been reviewed and approved for use.
                                    @if($course->approved_at)
                                        <span class="text-xs">Approved {{ $course->approved_at->diffForHumans() }}</span>
                                    @endif
                                @elseif($course->approval_status === 'pending')
                                    This course is awaiting review by a supervisor.
                                @else
                                    This course is in draft mode and has not been submitted for review.
                                @endif
                            </p>
                        </div>
                        @if($course->approval_status !== 'approved' && !auth()->user()->hasAnyRole(['admin', 'supervisor']))
                            <flux:button 
                                type="button" 
                                wire:click="submitForApproval" 
                                variant="primary"
                                class="ml-4">
                                Submit for Approval
                            </flux:button>
                        @endif
                    </div>

                    {{-- Publication Status --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="h-5 w-5 {{ $is_published ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                    Publication Status
                                </h3>
                                @if($is_published)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        Unpublished
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $is_published ? 'This course is visible and students can enroll.' : 'This course is hidden from students.' }}
                            </p>
                        </div>
                        <flux:button 
                            type="button" 
                            wire:click="togglePublish" 
                            variant="{{ $is_published ? 'outline' : 'primary' }}"
                            class="ml-4">
                            {{ $is_published ? 'Unpublish' : 'Publish' }}
                        </flux:button>
                    </div>

                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <label class="block text-sm font-bold text-gray-900 dark:text-white mb-3">
                            <svg class="h-5 w-5 inline-block mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Enrollment Type
                        </label>
                        <flux:field>
                            <flux:select wire:model="enrollment_type">
                                <option value="open">Open Enrollment - Anyone can enroll</option>
                                <option value="approval_required">Approval Required - Students must request to enroll</option>
                                <option value="invite_only">Invite Only - Only invited students can enroll</option>
                            </flux:select>
                        </flux:field>
                        @error('enrollment_type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                            @if($enrollment_type === 'open')
                                Students can freely enroll in this course.
                            @elseif($enrollment_type === 'approval_required')
                                Students can request enrollment, but you must approve their request.
                            @else
                                Only students you specifically invite can access this course.
                            @endif
                        </p>
                    </div>

                    @if($enrollment_type !== 'open')
                        <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                            <label class="block text-sm font-bold text-gray-900 dark:text-white mb-2">
                                Maximum Students (Optional)
                            </label>
                            <flux:input type="number" wire:model="max_students" min="1" placeholder="No limit" />
                            @error('max_students')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                Leave empty for unlimited enrollment
                            </p>
                        </div>
                    @endif

                    <div class="flex items-center">
                        <flux:checkbox wire:model="is_featured" label="Feature this course on the homepage" />
                    </div>
                </div>
            </div>

            {{-- Enrolled Students Card --}}
            @if($course->enrollments()->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Enrolled Students ({{ $course->enrollments()->count() }})
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($enrollments as $enrollment)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold text-sm">
                                        {{ substr($enrollment->user->name, 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $enrollment->user->name }}</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $enrollment->user->email }}</p>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                            Enrolled {{ $enrollment->enrolled_at->diffForHumans() }}
                                            @if($enrollment->progress_percentage > 0)
                                                • {{ number_format($enrollment->progress_percentage, 1) }}% complete
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <flux:button 
                                    type="button" 
                                    wire:click="unenrollStudent({{ $enrollment->id }})" 
                                    variant="danger" 
                                    size="sm"
                                    wire:confirm="Are you sure you want to unenroll {{ $enrollment->user->name }}? They will lose access to this course."
                                    class="ml-4">
                                    Unenroll
                                </flux:button>
                            </div>
                        @empty
                            <p class="text-sm text-gray-600 dark:text-gray-400 py-4">No students enrolled yet.</p>
                        @endforelse
                    </div>

                    @if($enrollments->hasPages())
                        <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                            {{ $enrollments->links() }}
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-4 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <flux:button type="button" wire:click="saveDraft" variant="primary">
                    Save Changes
                </flux:button>
                @if($course->enrollments()->count() === 0)
                    <flux:button type="button" wire:click="deleteCourse" variant="danger" wire:confirm="Are you sure you want to delete this course?">
                        Delete Course
                    </flux:button>
                @endif
            </div>
        </form>
    </div>
</div>
