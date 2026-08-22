@props(['assignment'])

@php
    $userSubmission = $assignment->submissions->first() ?? null;
    $isSubmitted = $userSubmission && $userSubmission->submitted_at;
    $isGraded = $userSubmission && $userSubmission->graded_at;
    $isOverdue = $assignment->due_date && $assignment->due_date->isPast() && !$isSubmitted;
@endphp

<div class="border-2 {{ $isSubmitted ? 'border-green-500 dark:border-green-400' : ($isOverdue ? 'border-red-500 dark:border-red-400' : 'border-gray-200 dark:border-gray-700') }} rounded-lg p-4 hover:border-purple-500 dark:hover:border-purple-400 transition-colors">
    <div class="flex items-start justify-between mb-3">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2 py-1 rounded text-xs font-semibold text-white bg-purple-500">
                    Assignment
                </span>
                @if($isSubmitted)
                    <span class="px-2 py-1 rounded text-xs font-semibold text-white {{ $isGraded ? 'bg-green-500' : 'bg-yellow-500' }}">
                        {{ $isGraded ? 'Graded' : 'Submitted' }}
                    </span>
                @endif
                @if($isOverdue)
                    <span class="px-2 py-1 rounded text-xs font-semibold text-white bg-red-500">
                        Overdue
                    </span>
                @endif
            </div>
            <h4 class="font-semibold text-gray-900 dark:text-white mb-1">{{ $assignment->title }}</h4>
            @if($assignment->description)
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ \Illuminate\Support\Str::limit($assignment->description, 80) }}</p>
            @endif
            <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                @if($assignment->due_date)
                    <span class="{{ $isOverdue ? 'text-red-600 dark:text-red-400 font-semibold' : '' }}">
                        Due: {{ $assignment->due_date->format('M d, Y') }}
                    </span>
                @endif
                <span>Points: {{ $assignment->max_points ?? 100 }}</span>
            </div>
            @if($isGraded && $userSubmission->points_earned !== null)
                <div class="mt-2 p-2 bg-green-50 dark:bg-green-900/20 rounded">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Grade:</span>
                        <span class="font-bold text-green-600 dark:text-green-400">
                            {{ number_format($userSubmission->points_earned, 1) }} / {{ $assignment->max_points ?? 100 }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <flux:button 
        href="{{ route('assignments.show', $assignment) }}" 
        wire:navigate 
        variant="{{ $isSubmitted ? 'outline' : 'primary' }}" 
        size="sm"
        class="w-full">
        {{ $isSubmitted ? ($isGraded ? 'View Grade' : 'View Submission') : 'Submit Assignment' }}
    </flux:button>
</div>
