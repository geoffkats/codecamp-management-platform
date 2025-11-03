<div class="max-w-5xl mx-auto p-8 space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between pb-6 border-b {{ $approval->status === 'approved' ? 'border-green-300 dark:border-green-700' : 'border-gray-200 dark:border-gray-700' }}">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('content-approvals.index') }}" wire:navigate 
                   class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Content Review</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $contentType }} • Approval Request</p>
                </div>
            </div>
        </div>
        @if($approval->status === 'approved')
            <div class="flex items-center gap-2 px-4 py-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="px-3 py-1 text-sm font-semibold text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/40 rounded-md">
                    {{ ucfirst($approval->status) }}
                </span>
            </div>
        @else
            <flux:badge variant="{{ $approval->status === 'pending' ? 'warning' : 'danger' }}" size="lg">
                {{ ucfirst($approval->status) }}
            </flux:badge>
        @endif
    </div>

    {{-- Submission Info --}}
    <div class="bg-white dark:bg-gray-800 border {{ $approval->status === 'approved' ? 'border-green-200 dark:border-green-800' : 'border-gray-200 dark:border-gray-700' }} rounded-lg overflow-hidden">
        <div class="px-6 py-4 {{ $approval->status === 'approved' ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700' }} border-b">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Submission Details</h2>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Content Type</dt>
                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $contentType }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Submitted By</dt>
                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $approval->submitter->name ?? 'Unknown' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Submitted Date</dt>
                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $approval->submitted_at?->format('M d, Y \a\t g:i A') }}</dd>
                </div>
                @if($approval->priority)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Priority</dt>
                        <dd>
                            <flux:badge variant="{{ $approval->priority === 'high' ? 'danger' : ($approval->priority === 'medium' ? 'warning' : 'ghost') }}" size="sm">
                                {{ ucfirst($approval->priority) }}
                            </flux:badge>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Content Preview --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Content Preview</h2>
                @if($approvable)
                    @if($approvable instanceof \App\Models\Course)
                        <a href="{{ route('courses.show', $approvable) }}" target="_blank" 
                           class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-1">
                            View Full Course
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    @elseif($approvable instanceof \App\Models\Lesson)
                        <a href="{{ route('lessons.show', $approvable) }}" target="_blank" 
                           class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-1">
                            View Full Lesson
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    @endif
                @endif
            </div>
        </div>
        
        @if($approvable)
            <div class="p-6">
                @if(isset($approvable->title))
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ $approvable->title }}</h3>
                @endif
                
                @if(isset($approvable->description))
                    <div class="prose prose-sm dark:prose-invert max-w-none mb-6">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $approvable->description }}</p>
                    </div>
                @endif

                @if(isset($approvable->short_description))
                    <p class="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">{{ $approvable->short_description }}</p>
                @endif

                @if($approvable instanceof \App\Models\Course)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Instructor</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $approvable->instructor->name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Category</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $approvable->category ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Difficulty</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $approvable->difficulty_level ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Status</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $approvable->is_published ? 'Published' : 'Draft' }}</dd>
                            </div>
                        </dl>
                    </div>
                @endif

                @if($approvable instanceof \App\Models\Lesson)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Lesson Type</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $approvable->lesson_type ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Duration</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $approvable->duration_minutes ?? 'N/A' }} minutes</dd>
                            </div>
                        </dl>
                    </div>
                @endif
            </div>
        @else
            <div class="p-6">
                <p class="text-gray-500 dark:text-gray-400 italic">Content not found or has been deleted.</p>
            </div>
        @endif
    </div>

    {{-- Submissions Needing Grading --}}
    @if($submissionCount > 0)
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Submissions Awaiting Grading</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $submissionCount }} {{ Str::plural('submission', $submissionCount) }} need grading</p>
                    </div>
                    @if($submissionCount > 0)
                        <flux:badge variant="warning" size="sm">{{ $submissionCount }} Pending</flux:badge>
                    @endif
                </div>
            </div>
            
            <div class="p-6">
                <div class="space-y-3">
                    @foreach($pendingSubmissions as $submission)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $submission->assignment->title }}
                                    </h4>
                                    <flux:badge size="xs" variant="ghost">
                                        {{ $submission->assignment->course->title ?? 'N/A' }}
                                    </flux:badge>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-gray-600 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ $submission->user->name }}
                                    </span>
                                    @if($submission->submitted_at)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $submission->submitted_at->diffForHumans() }}
                                        </span>
                                    @endif
                                    @if($submission->assignment->max_points)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                            </svg>
                                            {{ $submission->assignment->max_points }} points
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-4 flex items-center gap-2">
                                <a href="{{ route('grades.grade', $submission) }}" 
                                   wire:navigate
                                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                    </svg>
                                    Grade
                                </a>
                                <a href="{{ route('submissions.show', $submission) }}" 
                                   target="_blank"
                                   class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors"
                                   title="View Submission">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($submissionCount > 5)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                            Showing {{ min(5, count($pendingSubmissions)) }} of {{ $submissionCount }} submissions
                        </p>
                        <a href="{{ route('submissions.index') }}" 
                           wire:navigate
                           class="mt-2 block text-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                            View All Submissions →
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Review Form --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Review & Decision</h2>
        </div>
        
        <div class="p-6 space-y-6">
            <div>
                <flux:field>
                    <flux:label class="text-sm font-medium text-gray-700 dark:text-gray-300">Review Notes</flux:label>
                    <flux:description class="text-xs text-gray-500 dark:text-gray-400">Optional notes that will be shared with the submitter</flux:description>
                    <flux:textarea 
                        wire:model="notes"
                        rows="4"
                        placeholder="Add any feedback or notes for the submitter..." 
                        class="mt-2" />
                </flux:field>
            </div>

            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                <flux:field>
                    <flux:label class="text-sm font-medium text-gray-700 dark:text-gray-300">Rejection Reason</flux:label>
                    <flux:description class="text-xs text-gray-500 dark:text-gray-400">Required if you plan to reject this submission</flux:description>
                    <flux:textarea 
                        wire:model="rejectionReason"
                        rows="4"
                        placeholder="Provide a clear and detailed reason for rejection..." 
                        class="mt-2" />
                </flux:field>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
        <a href="{{ route('content-approvals.index') }}" wire:navigate 
           class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
            Cancel
        </a>
        <div class="flex items-center gap-3">
            <flux:button 
                wire:click="reject"
                variant="danger"
                :disabled="empty($rejectionReason)">
                Reject
            </flux:button>
            <flux:button 
                wire:click="approve"
                variant="primary">
                Approve
            </flux:button>
        </div>
    </div>
</div>

@if(session()->has('message'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-6 bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800 shadow-xl rounded-lg px-4 py-3 z-50 max-w-md">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ session('message') }}</p>
        </div>
    </div>
@endif

@if(session()->has('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-6 bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800 shadow-xl rounded-lg px-4 py-3 z-50 max-w-md">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ session('error') }}</p>
        </div>
    </div>
@endif
