@props(['user'])

<div class="bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-900 dark:to-purple-900 rounded-lg shadow-xl p-6 text-white mb-6">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
            <h2 class="text-2xl font-bold">Welcome back, {{ $user->name }}! 👋</h2>
            <p class="mt-2 text-blue-100">Ready to continue your learning journey?</p>
        </div>
        
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4 w-full md:w-auto">
            <a href="{{ route('courses.index') }}" 
               class="flex flex-col items-center justify-center p-4 bg-white/10 hover:bg-white/20 rounded-lg transition text-sm font-medium">
                <svg class="h-6 w-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Browse Courses
            </a>
            
            <a href="{{ route('progress.student') }}" 
               class="flex flex-col items-center justify-center p-4 bg-white/10 hover:bg-white/20 rounded-lg transition text-sm font-medium">
                <svg class="h-6 w-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                View Progress
            </a>
            
            <a href="{{ route('badges.index') }}" 
               class="flex flex-col items-center justify-center p-4 bg-white/10 hover:bg-white/20 rounded-lg transition text-sm font-medium">
                <svg class="h-6 w-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
                Badges & XP
            </a>
            
            <a href="{{ route('leaderboards.index') }}" 
               class="flex flex-col items-center justify-center p-4 bg-white/10 hover:bg-white/20 rounded-lg transition text-sm font-medium">
                <svg class="h-6 w-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Leaderboards
            </a>
        </div>
    </div>
</div>
