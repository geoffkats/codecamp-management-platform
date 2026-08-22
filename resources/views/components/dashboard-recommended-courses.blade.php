<div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg shadow-xl overflow-hidden border-2 border-green-200 dark:border-green-700 p-8">
    <!-- Header Section -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-green-900 dark:text-green-100">🎯 Recommended For You</h2>
        <p class="text-md text-green-700 dark:text-green-300 mt-2">Explore these popular courses based on your learning interests</p>
    </div>
    
    <!-- Courses Grid -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        @forelse($courses ?? [] as $course)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden flex flex-col border border-gray-200 dark:border-gray-700 group">
                <!-- Course Image/Thumbnail -->
                <div class="h-40 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center overflow-hidden relative">
                    @if($course->featured_image)
                        <img src="{{ asset('storage/' . $course->featured_image) }}" 
                             alt="{{ $course->title }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="text-5xl font-bold text-white">{{ substr($course->title, 0, 1) }}</div>
                    @endif
                </div>
                
                <!-- Course Content -->
                <div class="p-5 flex flex-col flex-grow">
                    <!-- Title -->
                    <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-2 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                        {{ $course->title }}
                    </h3>
                    
                    <!-- Instructor -->
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 flex items-center">
                        👨‍🏫 {{ $course->instructor->name ?? 'N/A' }}
                    </p>
                    
                    <!-- Description (truncated) -->
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                        {{ Str::limit($course->description, 80) }}
                    </p>
                    
                    <!-- Stats Row -->
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <span class="flex items-center gap-1">👥 {{ $course->enrollments_count }} students</span>
                        <span class="flex items-center gap-1">📚 {{ $course->lessons_count }} lessons</span>
                    </div>
                    
                    <!-- Difficulty & Enrollment -->
                    <div class="flex items-center justify-between mb-4">
                        @php
                            $difficultyColor = match($course->difficulty_level ?? 'beginner') {
                                'beginner' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                'intermediate' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                'advanced' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300',
                            };
                        @endphp
                        <span class="inline-block px-3 py-1 text-xs font-bold rounded-full {{ $difficultyColor }}">
                            {{ ucfirst($course->difficulty_level ?? 'beginner') }}
                        </span>
                    </div>
                    
                    <!-- Enrollment Status / Invite CTA -->
                    <div class="mt-auto">
                        <div class="w-full text-center px-4 py-3 rounded-lg border border-blue-200 bg-blue-50 text-blue-900 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-100 font-semibold">
                            🔒 Invite required — ask an admin for access
                        </div>
                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                            Not enrolled yet. Enrollment for this course is invite-only. Please request an invite from the admin or your instructor.
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16">
                <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-gray-600 dark:text-gray-400 text-lg font-medium mb-2">No recommendations at the moment</p>
                <p class="text-gray-500 dark:text-gray-500 mb-6">Check back soon for personalized course suggestions</p>
                <a href="{{ route('enrollments.index') }}" class="inline-block rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                    My Courses →
                </a>
            </div>
        @endforelse
    </div>
</div>
