<div class="max-w-4xl mx-auto p-6 space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Assignment</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Set up assignment details and requirements</p>
            </div>
            <a href="{{ route('assignments.index') }}" wire:navigate
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
                        </flux:field>
                    </div>
                @endif

                {{-- Title --}}
                <div>
                    <flux:field>
                        <flux:label>Assignment Title</flux:label>
                        <flux:input wire:model="title" placeholder="Enter assignment title" required />
                        <flux:error name="title" />
                    </flux:field>
                </div>

                {{-- Description --}}
                <div>
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description" rows="4" placeholder="Enter assignment description" required />
                        <flux:error name="description" />
                    </flux:field>
                </div>

                {{-- Instructions --}}
                <div>
                    <flux:field>
                        <flux:label>Instructions</flux:label>
                        <flux:textarea wire:model="instructions" rows="6" placeholder="Enter detailed instructions for students" />
                        <flux:error name="instructions" />
                        <flux:description>Provide step-by-step instructions and requirements</flux:description>
                    </flux:field>
                </div>
            </div>

            {{-- Assignment Settings --}}
            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Settings</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Due Date --}}
                    <div>
                        <flux:field>
                            <flux:label>Due Date</flux:label>
                            <flux:input type="datetime-local" wire:model="due_date" />
                            <flux:error name="due_date" />
                            <flux:description>Set a deadline for submissions</flux:description>
                        </flux:field>
                    </div>

                    {{-- Max Points --}}
                    <div>
                        <flux:field>
                            <flux:label>Maximum Points</flux:label>
                            <flux:input type="number" wire:model="max_points" min="1" max="1000" required />
                            <flux:error name="max_points" />
                        </flux:field>
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select wire:model="status" required>
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="archived">Archived</option>
                        </flux:select>
                        <flux:error name="status" />
                        <flux:description>Active assignments are visible to students</flux:description>
                    </flux:field>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-6">
                <flux:button href="{{ route('assignments.index') }}" wire:navigate variant="ghost">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Create Assignment
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
