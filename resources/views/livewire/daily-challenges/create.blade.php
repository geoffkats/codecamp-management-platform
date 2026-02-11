<div class="flex flex-col gap-6 p-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Daily Challenge</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Create a new challenge for students to complete.</p>
        </div>
        <flux:button href="{{ route('daily-challenges.index') }}" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Challenges
        </flux:button>
    </div>

    <!-- Form -->
    <form wire:submit="save">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="p-6 space-y-6">
                <!-- Title -->
                <flux:input 
                    wire:model="title"
                    label="Challenge Title"
                    placeholder="Enter challenge title..."
                    required
                />

                <!-- Description -->
                <flux:textarea
                    wire:model="description"
                    label="Description"
                    placeholder="Describe the challenge..."
                    rows="4"
                    required
                />

                <!-- Type and Category -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:select
                        wire:model="type"
                        label="Challenge Type"
                        required
                    >
                        <option value="general">General</option>
                        <option value="coding">Coding</option>
                        <option value="quiz">Quiz</option>
                        <option value="project">Project</option>
                        <option value="reading">Reading</option>
                    </flux:select>

                    <flux:input
                        wire:model="category"
                        label="Category (Optional)"
                        placeholder="e.g., HTML, CSS, JavaScript"
                    />
                </div>

                <!-- Course Scope -->
                <flux:select
                    wire:model="course_id"
                    label="Limit to Course (Optional)"
                >
                    <option value="">General (visible to all students)</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                    @endforeach
                </flux:select>

                <!-- Difficulty and Points -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:select
                        wire:model="difficulty_level"
                        label="Difficulty Level"
                        required
                    >
                        <option value="Easy">Easy</option>
                        <option value="Medium">Medium</option>
                        <option value="Hard">Hard</option>
                    </flux:select>

                    <flux:input
                        wire:model="reward_points"
                        type="number"
                        label="Reward Points (XP)"
                        min="1"
                        max="1000"
                        required
                    />
                </div>

                <!-- Requirements -->
                <flux:textarea
                    wire:model="requirements"
                    label="Requirements (Optional)"
                    placeholder="Enter each requirement on a new line..."
                    rows="5"
                    hint="List specific tasks or criteria for completing this challenge. Each line will become a separate requirement."
                />

                <!-- Date and Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input
                        wire:model="date"
                        type="date"
                        label="Challenge Date"
                        hint="Leave blank for evergreen challenges"
                    />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <flux:checkbox wire:model="is_active" label="Active (visible to students)" />
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <flux:button href="{{ route('daily-challenges.index') }}" variant="ghost" wire:navigate>
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary" icon="check">
                    Create Challenge
                </flux:button>
            </div>
        </div>
    </form>
</div>
