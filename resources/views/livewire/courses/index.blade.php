<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="flex flex-col gap-6 p-6">
        {{-- Header Section --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Courses</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Browse and manage all available courses
                </p>
            </div>
            @can('create', \App\Models\Course::class)
                <flux:button href="{{ route('courses.create') }}" variant="primary" wire:navigate>
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Course
                </flux:button>
            @endcan
        </div>

        {{-- Filters Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div class="lg:col-span-2">
                        <flux:input
                            wire:model.live.debounce.300ms="search"
                            label="Search Courses"
                            placeholder="Search by title or description..."
                        />
                    </div>

                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select wire:model.live="filterStatus">
                            @foreach($statusOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Difficulty</flux:label>
                        <flux:select wire:model.live="filterDifficulty">
                            @foreach($difficultyOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    @if(count($categoryOptions) > 1)
                        <flux:field>
                            <flux:label>Category</flux:label>
                            <flux:select wire:model.live="filterCategory">
                                @foreach($categoryOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    @endif
                </div>
            </div>
        </div>

        {{-- Flash Message --}}
        @if (session()->has('message'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 flex items-center gap-3">
                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        {{-- Courses Grid --}}
        @if($courses->count() > 0)
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($courses as $course)
                    <div class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden flex flex-col hover:shadow-2xl transition-all duration-300 border border-gray-200 dark:border-gray-700">
                        {{-- Course Image/Thumbnail --}}
                        @if($course->featured_image)
                            <div class="relative h-48 w-full overflow-hidden">
                                <img src="{{ asset('storage/' . $course->featured_image) }}" 
                                     alt="{{ $course->title }}" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                            </div>
                        @else
                            <div class="h-48 w-full bg-gradient-to-br from-blue-500 via-purple-600 to-pink-600 flex items-center justify-center relative overflow-hidden">
                                <div class="absolute inset-0 bg-black/20"></div>
                                <span class="relative z-10 text-6xl font-bold text-white opacity-90">{{ substr($course->title, 0, 1) }}</span>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                            </div>
                        @endif

                        {{-- Course Content --}}
                        <div class="flex flex-col gap-4 p-6 flex-1">
                            {{-- Title and Description --}}
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white line-clamp-2 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ $course->title }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                    {{ $course->short_description ?? Str::limit(strip_tags($course->description), 100) }}
                                </p>
                            </div>

                            {{-- Course Meta Info --}}
                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-5 h-5 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <span class="font-medium">{{ $course->instructor->name }}</span>
                                </div>
                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <span>{{ ucfirst($course->difficulty_level) }}</span>
                                </div>
                                @if($course->estimated_duration)
                                    <span class="text-gray-300 dark:text-gray-600">•</span>
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ $course->estimated_duration }} min</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Badges --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->is_published ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                                    {{ $course->is_published ? 'Published' : 'Draft' }}
                                </span>
                                @if($course->approval_status)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->approval_status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ($course->approval_status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400') }}">
                                        {{ ucfirst($course->approval_status) }}
                                    </span>
                                @endif
                                @if($course->is_featured)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                                        ⭐ Featured
                                    </span>
                                @endif
                            </div>

                            {{-- Stats and Actions --}}
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <span class="font-medium">{{ $course->enrollments_count }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <span class="font-medium">{{ $course->lessons_count }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <flux:button href="{{ route('courses.show', $course) }}" variant="ghost" size="sm" wire:navigate class="hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                        View
                                    </flux:button>
                                    @can('update', $course)
                                        <flux:button href="{{ route('courses.edit', $course) }}" variant="ghost" size="sm" wire:navigate class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            Edit
                                        </flux:button>
                                    @endcan
                                    @can('delete', $course)
                                        <flux:button 
                                            wire:click="delete({{ $course->id }})" 
                                            variant="ghost" 
                                            size="sm" 
                                            wire:confirm="Are you sure you want to delete this course? This action cannot be undone."
                                            class="hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400"
                                        >
                                            Delete
                                        </flux:button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $courses->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-12 text-center">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30 flex items-center justify-center">
                        <svg class="w-10 h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No courses found</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        @if($this->search || $this->filterStatus !== 'all' || $this->filterDifficulty !== 'all' || $this->filterCategory !== 'all')
                            Try adjusting your filters to find more courses
                        @else
                            Get started by creating your first course
                        @endif
                    </p>
                    @can('create', \App\Models\Course::class)
                        <flux:button href="{{ route('courses.create') }}" variant="primary" wire:navigate>
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Create Course
                        </flux:button>
                    @endcan
                </div>
            </div>
        @endif
    </div>
</div>
