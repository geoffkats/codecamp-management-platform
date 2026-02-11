<div class="flex flex-col gap-6 p-6">
        <!-- Quick Actions Header -->
        <x-dashboard-quick-actions :user="$user" />

        <!-- Getting Started Guide (for new users) -->
        <x-dashboard-getting-started :user="$user" />

        <!-- Welcome Header -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Your Learning Dashboard
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Track your progress, earn badges, and climb the leaderboard
                </p>
            </div>
            <div class="flex items-center gap-4">
                @php
                    $categoryLabel = match($user->studentProfile?->student_category ?? 'codecamp') {
                        'school_club' => 'School Club',
                        'ict_school' => 'ICT School',
                        default => 'Codecamp',
                    };
                @endphp
                <flux:badge variant="secondary" size="lg">
                    {{ $categoryLabel }}
                </flux:badge>
                <flux:badge variant="primary" size="lg">
                    Level {{ $stats['level'] }}
                </flux:badge>
                <flux:badge variant="ghost" size="lg">
                    {{ number_format($stats['totalPoints']) }} XP
                </flux:badge>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow-lg overflow-hidden">
                <div class="flex items-center justify-between p-6">
                    <div>
                        <p class="text-sm font-medium text-blue-100">Active Courses</p>
                        <p class="mt-2 text-3xl font-bold">{{ $stats['enrollments'] }}</p>
                    </div>
                    <div class="rounded-full bg-blue-400/20 p-3">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg overflow-hidden">
                <div class="flex items-center justify-between p-6">
                    <div>
                        <p class="text-sm font-medium text-green-100">Completed</p>
                        <p class="mt-2 text-3xl font-bold">{{ $stats['completedCourses'] }}</p>
                    </div>
                    <div class="rounded-full bg-green-400/20 p-3">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg shadow-lg overflow-hidden">
                <div class="flex items-center justify-between p-6">
                    <div>
                        <p class="text-sm font-medium text-purple-100">Lessons Completed</p>
                        <p class="mt-2 text-3xl font-bold">{{ $stats['completedLessons'] }}</p>
                    </div>
                    <div class="rounded-full bg-purple-400/20 p-3">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-lg shadow-lg overflow-hidden">
                <div class="flex items-center justify-between p-6">
                    <div>
                        <p class="text-sm font-medium text-yellow-100">Badges Earned</p>
                        <p class="mt-2 text-3xl font-bold">{{ $stats['totalBadges'] }}</p>
                    </div>
                    <div class="rounded-full bg-yellow-400/20 p-3">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left Column - Active Courses -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-semibold">My Courses</h2>
                        <flux:button href="{{ route('courses.index') }}" variant="ghost" size="sm" wire:navigate>
                            View All
                        </flux:button>
                    </div>
                    <div class="p-6">
                        @if($activeEnrollments->count() > 0)
                            <div class="space-y-4">
                                @foreach($activeEnrollments as $enrollment)
                                    <div class="flex items-center gap-4 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                        @if($enrollment->course->featured_image)
                                            <img src="{{ asset('storage/' . $enrollment->course->featured_image) }}" 
                                                 alt="{{ $enrollment->course->title }}"
                                                 class="h-16 w-16 rounded-lg object-cover">
                                        @else
                                            <div class="h-16 w-16 rounded-lg bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                                <span class="text-2xl font-bold text-white">{{ substr($enrollment->course->title, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                                {{ $enrollment->course->title }}
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                {{ $enrollment->course->instructor->name }}
                                            </p>
                                            <div class="mt-2">
                                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                    <span>Progress</span>
                                                    <span>{{ number_format($enrollment->progress_percentage ?? 0, 1) }}%</span>
                                                </div>
                                                <div class="h-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                    <div class="h-full bg-blue-500 rounded-full transition-all duration-300" 
                                                         style="width: {{ $enrollment->progress_percentage ?? 0 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <flux:button href="{{ route('courses.show', $enrollment->course) }}" variant="ghost" size="sm" wire:navigate>
                                            Continue
                                        </flux:button>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                {{ $activeEnrollments->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No courses yet</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by enrolling in a course</p>
                                <div class="mt-6">
                                    <flux:button href="{{ route('courses.index') }}" variant="primary" wire:navigate>
                                        Browse Courses
                                    </flux:button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - Sidebar -->
            <div class="space-y-6">
                <!-- Daily Challenges -->
                @if($dailyChallenges->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-xl font-semibold">Daily Challenges</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            @foreach($dailyChallenges as $challenge)
                                <div class="flex items-start gap-4 rounded-lg border border-gray-200 dark:border-gray-700 p-4 {{ $challenge->is_completed ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                    <div class="flex-shrink-0">
                                        @if($challenge->is_completed)
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500">
                                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        @else
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500">
                                                <span class="text-xl">🔥</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $challenge->title }}</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $challenge->description }}</p>
                                        @if($challenge->course)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Course: {{ $challenge->course->title }}
                                            </p>
                                        @endif
                                        <div class="mt-2 flex items-center gap-2">
                                            <flux:badge size="sm" variant="{{ $challenge->is_completed ? 'success' : 'primary' }}">
                                                {{ $challenge->reward_points ?? 100 }} XP
                                            </flux:badge>
                                            @if(!$challenge->is_completed)
                                                <flux:button href="{{ route('daily-challenges.show', $challenge) }}" variant="ghost" size="sm" wire:navigate>
                                                    Start
                                                </flux:button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Leaderboard Position -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-semibold">Leaderboard</h2>
                    </div>
                    <div class="p-6">
                        <div class="text-center">
                            <div class="text-4xl font-bold text-gray-900 dark:text-white">#{{ $leaderboardPosition['rank'] }}</div>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">of {{ $leaderboardPosition['total'] }} learners</p>
                            <div class="mt-4">
                                <div class="h-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full" 
                                         style="width: {{ min($leaderboardPosition['percentage'], 100) }}%"></div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Top {{ $leaderboardPosition['percentage'] }}%</p>
                            </div>
                            <div class="mt-6">
                                <flux:button href="{{ route('leaderboards.index') }}" variant="ghost" size="sm" wire:navigate>
                                    View Full Leaderboard
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Learning Streak -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-semibold">Learning Streak</h2>
                    </div>
                    <div class="p-6 text-center">
                        <div class="text-4xl font-bold text-orange-500">{{ $learningStreak['current'] }}</div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Day streak 🔥</p>
                        @if($learningStreak['lastActivity'])
                            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                                Last active: {{ \Carbon\Carbon::parse($learningStreak['lastActivity'])->diffForHumans() }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Recent Badges -->
                @if($recentBadges->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-xl font-semibold">Recent Badges</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4">
                                @foreach($recentBadges as $badge)
                                    @php
                                        $earnedDate = $badge->pivot->earned_at ?? now();
                                    @endphp
                                    <div class="text-center group">
                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 shadow-lg transform transition-transform duration-300 group-hover:scale-110">
                                            <div class="text-white">
                                                <x-badge-icon :icon="$badge->icon ?? 'trophy'" class="w-10 h-10" />
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs font-semibold text-gray-900 dark:text-white">{{ $badge->name }}</p>
                                        @if($earnedDate)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ \Carbon\Carbon::parse($earnedDate)->diffForHumans() }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                <flux:button href="{{ route('badges.index') }}" variant="ghost" size="sm" class="w-full" wire:navigate>
                                    View All Badges
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Upcoming Deadlines -->
        @if(count($upcomingDeadlines['assignments']) > 0 || count($upcomingDeadlines['quizzes']) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-semibold">📅 Upcoming Deadlines</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach($upcomingDeadlines['assignments'] as $assignment)
                            <div class="flex items-center gap-4 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition">
                                <div class="flex-shrink-0">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/20">
                                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $assignment->title }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $assignment->course->title }}</p>
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                                        Due: {{ $assignment->due_date->diffForHumans() }}
                                    </p>
                                </div>
                                <flux:button href="{{ route('assignments.show', $assignment) }}" variant="ghost" size="sm" wire:navigate>
                                    View
                                </flux:button>
                            </div>
                        @endforeach
                        @foreach($upcomingDeadlines['quizzes'] as $quiz)
                            <div class="flex items-center gap-4 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition">
                                <div class="flex-shrink-0">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/20">
                                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $quiz->title ?? 'Quiz' }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $quiz->lesson->course->title ?? 'N/A' }}</p>
                                </div>
                                <flux:button href="{{ route('assessments.show', $quiz) }}" variant="ghost" size="sm" wire:navigate>
                                    View
                                </flux:button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Additional Dashboard Sections -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Recent Certificates -->
            @if($recentCertificates->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-semibold">Recent Certificates</h2>
                        <flux:button href="{{ route('certificates.index') }}" variant="ghost" size="sm" wire:navigate>
                            View All
                        </flux:button>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($recentCertificates as $certificate)
                                <div class="flex items-center gap-4 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <div class="flex-shrink-0">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-yellow-400 to-orange-500">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ $certificate->title }}</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $certificate->course->title ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Issued {{ $certificate->issued_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    @if($certificate->is_verified)
                                        <flux:badge variant="primary" size="sm">Verified</flux:badge>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recent Submissions -->
            @if($recentSubmissions->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-semibold">Recent Submissions</h2>
                        <flux:button href="{{ route('submissions.index') }}" variant="ghost" size="sm" wire:navigate>
                            View All
                        </flux:button>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($recentSubmissions as $submission)
                                <div class="flex items-center gap-4 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <div class="flex-shrink-0">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/20">
                                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ $submission->assignment->title }}</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $submission->assignment->course->title ?? 'N/A' }}</p>
                                        <div class="mt-1 flex items-center gap-2">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                Submitted {{ $submission->submitted_at->diffForHumans() }}
                                            </span>
                                            @if($submission->graded_at)
                                                <flux:badge size="xs" variant="primary">
                                                    Graded
                                                </flux:badge>
                                            @else
                                                <flux:badge size="xs" variant="warning">
                                                    Pending
                                                </flux:badge>
                                            @endif
                                        </div>
                                    </div>
                                    <flux:button href="{{ route('submissions.show', $submission) }}" variant="ghost" size="sm" wire:navigate>
                                        View
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold">Quick Actions</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <flux:button href="{{ route('courses.index') }}" variant="ghost" class="flex flex-col items-center gap-2 py-4" wire:navigate>
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span class="text-sm font-medium">Browse Courses</span>
                    </flux:button>
                    <flux:button href="{{ route('badges.index') }}" variant="ghost" class="flex flex-col items-center gap-2 py-4" wire:navigate>
                        <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        <span class="text-sm font-medium">View Badges</span>
                    </flux:button>
                    <flux:button href="{{ route('certificates.index') }}" variant="ghost" class="flex flex-col items-center gap-2 py-4" wire:navigate>
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        <span class="text-sm font-medium">Certificates</span>
                    </flux:button>
                    <flux:button href="{{ route('leaderboards.index') }}" variant="ghost" class="flex flex-col items-center gap-2 py-4" wire:navigate>
                        <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span class="text-sm font-medium">Leaderboard</span>
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Help & Tips Section -->
        <x-dashboard-help-tips />

        <!-- Recommended Courses (if not all courses are enrolled) -->
        @if($recommendedCourses && $recommendedCourses->count() > 0)
            <x-dashboard-recommended-courses :courses="$recommendedCourses" />
        @endif
    </div>

