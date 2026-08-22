@props(['user'])

@php
    // Show getting started if user registered recently (within 7 days)
    $showGettingStarted = $user->created_at->diffInDays() <= 7;
@endphp

@if($showGettingStarted)
<div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mb-6">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100">
                🎯 Getting Started with CodeCamp
            </h3>
            <div class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-4">
                <div class="text-sm text-blue-800 dark:text-blue-200 flex items-start gap-2">
                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-blue-600 text-white text-xs font-bold flex-shrink-0">1</span>
                    <span><strong>Browse Courses</strong> - Find courses in your interest area</span>
                </div>
                <div class="text-sm text-blue-800 dark:text-blue-200 flex items-start gap-2">
                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-blue-600 text-white text-xs font-bold flex-shrink-0">2</span>
                    <span><strong>Join a Course</strong> - Enroll to start learning</span>
                </div>
                <div class="text-sm text-blue-800 dark:text-blue-200 flex items-start gap-2">
                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-blue-600 text-white text-xs font-bold flex-shrink-0">3</span>
                    <span><strong>Complete Lessons</strong> - Work through course material</span>
                </div>
                <div class="text-sm text-blue-800 dark:text-blue-200 flex items-start gap-2">
                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-blue-600 text-white text-xs font-bold flex-shrink-0">4</span>
                    <span><strong>Earn Badges</strong> - Unlock achievements & XP</span>
                </div>
            </div>
            <div class="mt-3 flex gap-2">
                <a href="{{ route('enrollments.index') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
                    My Courses →
                </a>
                <button type="button" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline" wire:click="dismissGettingStarted">
                    Dismiss
                </button>
            </div>
        </div>
    </div>
</div>
@endif
