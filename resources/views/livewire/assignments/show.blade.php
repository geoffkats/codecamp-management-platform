<div class="flex flex-col gap-6 p-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <span class="px-3 py-1 rounded-full text-xs font-semibold text-white bg-purple-500">
                    📄 Assignment
                </span>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $assignment->title }}</h1>
                @if($isOverdue && !$isSubmitted)
                    <flux:badge variant="danger" size="lg">Overdue</flux:badge>
                @elseif($isSubmitted)
                    <flux:badge variant="{{ $isGraded ? 'success' : 'warning' }}" size="lg">
                        {{ $isGraded ? 'Graded' : 'Submitted' }}
                    </flux:badge>
                @endif
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                <a href="{{ route('courses.show', $assignment->course) }}" class="hover:text-blue-600 dark:hover:text-blue-400" wire:navigate>
                    {{ $assignment->course->title }}
                </a>
                @if($assignment->lesson)
                    <span class="mx-2">•</span>
                    <a href="{{ route('lessons.show', $assignment->lesson) }}" class="hover:text-blue-600 dark:hover:text-blue-400" wire:navigate>
                        {{ $assignment->lesson->title }}
                    </a>
                @endif
            </p>
        </div>
        @if(Auth::user()->hasAnyRole(['teacher', 'admin']))
            <div class="flex items-center gap-3">
                <flux:button href="{{ route('assignments.edit', $assignment) }}" variant="ghost" wire:navigate>
                    Edit Assignment
                </flux:button>
            </div>
        @endif
    </div>

    <!-- Stats for Teachers -->
    @if($submissionStats)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Submissions</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $submissionStats['total'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Graded</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $submissionStats['graded'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Pending</p>
                <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ $submissionStats['pending'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Avg Score</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($submissionStats['average_score'], 1) }}%</p>
            </div>
        </div>
    @endif

    <!-- Assignment Details -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Due Date</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                    @if($assignment->due_date)
                        {{ $assignment->due_date->format('M d, Y g:i A') }}
                        @if($assignment->due_date->isPast())
                            <span class="text-red-600 dark:text-red-400">({{ $assignment->due_date->diffForHumans() }})</span>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">({{ $assignment->due_date->diffForHumans() }})</span>
                        @endif
                    @else
                        No due date
                    @endif
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Points</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ number_format($assignment->max_points ?? 100) }} points</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Created By</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $assignment->creator->name ?? 'Unknown' }}</p>
            </div>
        </div>

        @if($assignment->description)
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Description</h2>
                <div class="prose dark:prose-invert max-w-none">
                    {!! $assignment->description !!}
                </div>
            </div>
        @endif

        @if($assignment->instructions)
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Instructions</h2>
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                    <div class="prose dark:prose-invert max-w-none">
                        {!! $assignment->instructions !!}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Submission Section (Students) -->
    @if(Auth::user()->hasRole('student'))
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Your Submission</h2>

            @if($isSubmitted)
                <!-- View Submission -->
                <div class="mb-6">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Submitted On</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $submission->submitted_at->format('M d, Y g:i A') }}
                                </p>
                            </div>
                            @if($isGraded)
                                <div class="text-right">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Grade</p>
                                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                        {{ number_format($submission->points_earned ?? 0, 1) }} / {{ number_format($assignment->max_points ?? 100, 1) }}
                                    </p>
                                    <flux:badge variant="success" class="mt-2">
                                        {{ number_format((($submission->points_earned ?? 0) / ($assignment->max_points ?? 100)) * 100, 1) }}%
                                    </flux:badge>
                                </div>
                            @endif
                        </div>

                        @if($submission->content)
                            <div class="mb-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Your Text Submission:</h3>
                                <div class="bg-white dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                    <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ $submission->content }}</p>
                                </div>
                            </div>
                        @endif

                        @if($submission->attachments && count($submission->attachments) > 0)
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Uploaded Files:</h3>
                                <div class="space-y-2">
                                    @foreach($submission->attachments as $file)
                                        <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                                            <div class="flex items-center gap-3">
                                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ basename($file) }}</span>
                                            </div>
                                            <a href="{{ asset('storage/' . $file) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                                Download
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($feedback)
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Teacher Feedback:</h3>
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                    <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ $feedback }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- Submit Assignment Form -->
                <form wire:submit="submit">
                    <div class="space-y-6">
                        <div>
                            <flux:field label="Text Submission (Optional if uploading files)">
                                <flux:textarea 
                                    wire:model="submissionText" 
                                    placeholder="Type your assignment submission here..."
                                    rows="10"
                                    class="min-h-[200px]"
                                />
                                <flux:error name="submissionText" />
                            </flux:field>
                        </div>

                        <div>
                            <flux:field label="Upload Files (Optional)">
                                <flux:input 
                                    type="file" 
                                    wire:model="submissionFiles" 
                                    multiple
                                    accept=".pdf,.doc,.docx,.txt,.zip,.rar"
                                />
                                <flux:error name="submissionFiles.*" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Maximum 10MB per file. Supported formats: PDF, DOC, DOCX, TXT, ZIP, RAR
                                </p>
                            </flux:field>
                        </div>

                        @if($assignment->due_date)
                            <div class="bg-{{ $isOverdue ? 'red' : 'yellow' }}-50 dark:bg-{{ $isOverdue ? 'red' : 'yellow' }}-900/20 border border-{{ $isOverdue ? 'red' : 'yellow' }}-200 dark:border-{{ $isOverdue ? 'red' : 'yellow' }}-800 rounded-lg p-4">
                                <div class="flex items-center gap-3">
                                    <svg class="h-5 w-5 text-{{ $isOverdue ? 'red' : 'yellow' }}-600 dark:text-{{ $isOverdue ? 'red' : 'yellow' }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-sm text-{{ $isOverdue ? 'red' : 'yellow' }}-800 dark:text-{{ $isOverdue ? 'red' : 'yellow' }}-200">
                                        @if($isOverdue)
                                            This assignment is overdue. Late submissions may receive reduced points.
                                        @else
                                            Due in {{ $assignment->due_date->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-3">
                            <flux:button type="button" href="{{ route('assignments.index') }}" variant="ghost" wire:navigate>
                                Cancel
                            </flux:button>
                            <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>Submit Assignment</span>
                                <span wire:loading>Submitting...</span>
                            </flux:button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    @endif

    <!-- Submissions List (Teachers) -->
    @if(Auth::user()->hasAnyRole(['teacher', 'admin']))
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Student Submissions</h2>
                <flux:button href="{{ route('submissions.index', ['assignment' => $assignment->id]) }}" variant="ghost" wire:navigate>
                    View All
                </flux:button>
            </div>

            @php
                $submissions = $assignment->submissions()->with('user')->latest()->paginate(10);
            @endphp

            @if($submissions->count() > 0)
                <div class="space-y-4">
                    @foreach($submissions as $submission)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $submission->user->name }}</h3>
                                        @if($submission->graded_at)
                                            <flux:badge variant="success" size="sm">Graded</flux:badge>
                                        @else
                                            <flux:badge variant="warning" size="sm">Pending</flux:badge>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Submitted {{ $submission->submitted_at->diffForHumans() }}
                                    </p>
                                    @if($submission->points_earned !== null)
                                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                                            Score: {{ number_format($submission->points_earned, 1) }} / {{ number_format($assignment->max_points ?? 100, 1) }}
                                        </p>
                                    @endif
                                </div>
                                @if($submission->graded_at)
                                    <flux:button href="{{ route('submissions.show', $submission) }}" variant="ghost" size="sm" wire:navigate>
                                        View
                                    </flux:button>
                                @else
                                    <flux:button href="{{ route('grades.grade', $submission) }}" variant="primary" size="sm" wire:navigate>
                                        Grade
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    {{ $submissions->links() }}
                </div>
            @else
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p>No submissions yet</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 flex items-center gap-3">
            <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('message') }}</p>
        </div>
    @endif
</div>
