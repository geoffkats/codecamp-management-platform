<div class="flex flex-col gap-6 p-6 max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('submissions.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    @if($type === 'assessment')
                        {{ $submission->assessment->title ?? 'Assessment' }}
                    @else
                        {{ $submission->assignment->title ?? 'Assignment' }}
                    @endif
                </h1>
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                @if($type === 'assessment')
                    {{ $submission->assessment->course->title ?? 'N/A' }}
                @else
                    {{ $submission->assignment->course->title ?? 'N/A' }}
                @endif
            </p>
        </div>
        
        @if(auth()->user()->can('grade_submissions'))
            @if($type === 'assessment' && $submission->assessment)
                <a href="{{ route('grades.grade', $submission) }}"
                   class="px-6 py-3 {{ $isGraded ? 'bg-amber-600 hover:bg-amber-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white rounded-lg font-medium transition-colors">
                    {{ $isGraded ? 'Edit Grade' : 'Grade Submission' }}
                </a>
            @elseif($type === 'assignment' && $submission->assignment)
                <a href="{{ route('grades.grade', $submission) }}"
                   class="px-6 py-3 {{ $isGraded ? 'bg-amber-600 hover:bg-amber-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white rounded-lg font-medium transition-colors">
                    {{ $isGraded ? 'Edit Grade' : 'Grade Submission' }}
                </a>
            @endif
        @endif
    </div>

    {{-- Status Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg {{ $isGraded ? 'bg-green-100 dark:bg-green-900/30' : 'bg-orange-100 dark:bg-orange-900/30' }} flex items-center justify-center">
                    <svg class="w-6 h-6 {{ $isGraded ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                    <p class="text-lg font-bold {{ $isGraded ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                        {{ $isGraded ? 'Graded' : 'Pending Grading' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Submitted</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                        @if($type === 'assessment')
                            {{ $submission->completed_at ? $submission->completed_at->format('M d, Y') : 'N/A' }}
                        @else
                            {{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y') : 'N/A' }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if($type === 'assignment' && $submission->assignment->due_date)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg {{ $isOverdue ? 'bg-red-100 dark:bg-red-900/30' : 'bg-gray-100 dark:bg-gray-700' }} flex items-center justify-center">
                        <svg class="w-6 h-6 {{ $isOverdue ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Due Date</p>
                        <p class="text-lg font-bold {{ $isOverdue ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $submission->assignment->due_date->format('M d, Y') }}
                        </p>
                    </div>
                </div>
            </div>
        @elseif($type === 'assessment')
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Time Spent</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $submission->time_spent ?? 0 }} min
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Grade Display --}}
    @if($isGraded && $percentage !== null)
        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/40 rounded-xl shadow-lg border border-green-200 dark:border-green-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-700 dark:text-green-300 mb-1">Grade</p>
                    <div class="flex items-baseline gap-3">
                        <span class="text-4xl font-bold {{ $percentage >= 70 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ number_format($percentage, 1) }}%
                        </span>
                        @if($type === 'assignment')
                            <span class="text-xl text-gray-600 dark:text-gray-400">
                                ({{ $submission->points_earned }}/{{ $submission->assignment->max_points }})
                            </span>
                        @endif
                    </div>
                    @if($type === 'assignment' && $submission->grader)
                        <p class="text-sm text-green-600 dark:text-green-400 mt-2">
                            Graded by {{ $submission->grader->name }} on {{ $submission->graded_at->format('M d, Y') }}
                        </p>
                    @elseif($type === 'assessment')
                        @php
                            $answers = $submission->answers ?? [];
                            $gradedAt = isset($answers['graded_at']) ? \Carbon\Carbon::parse($answers['graded_at']) : $submission->updated_at;
                        @endphp
                        <p class="text-sm text-green-600 dark:text-green-400 mt-2">
                            Graded on {{ $gradedAt->format('M d, Y') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Submission Content --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Submission Details</h2>
            
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Student</p>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-gray-900 dark:text-white font-medium">{{ $submission->user->name }}</span>
                </div>
            </div>

            @if($type === 'assessment')
                @php
                    $submissionText = $submission->submissionText();
                    $submissionFiles = $submission->submissionFiles();
                @endphp

                @if($submissionText)
                    <div class="mb-6">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Submission Text</p>
                        <div class="p-6 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                            <div class="max-w-none prose prose-sm dark:prose-invert prose-headings:font-semibold prose-p:mb-4 prose-p:leading-7 prose-p:text-gray-800 dark:prose-p:text-gray-200 prose-strong:text-gray-900 dark:prose-strong:text-gray-100 prose-ul:my-4 prose-ol:my-4 prose-li:my-2 prose-pre:bg-gray-100 dark:prose-pre:bg-gray-800 prose-code:text-sm">
                                <div class="whitespace-pre-wrap break-words text-gray-900 dark:text-gray-100 leading-7">
                                    @php
                                        // Convert plain text to formatted HTML with proper line breaks and spacing
                                        $formattedText = $submissionText;
                                        // Split into paragraphs (double line breaks)
                                        $paragraphs = preg_split('/\n\s*\n/', $formattedText);
                                        $formattedText = '';
                                        foreach ($paragraphs as $para) {
                                            $para = trim($para);
                                            if (!empty($para)) {
                                                // Check if it's a list item
                                                if (preg_match('/^[-*•]\s+/', $para) || preg_match('/^\d+[\.\)]\s+/', $para)) {
                                                    $formattedText .= '<p class="mb-2 ml-4">' . nl2br(e($para)) . '</p>';
                                                } else {
                                                    $formattedText .= '<p class="mb-4">' . nl2br(e($para)) . '</p>';
                                                }
                                            }
                                        }
                                    @endphp
                                    {!! $formattedText ?: '<p>' . nl2br(e($submissionText)) . '</p>' !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!empty($submissionFiles))
                    <div class="mb-6">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Attachments</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($submissionFiles as $file)
                                @php
                                    $filePath = is_array($file) ? ($file['path'] ?? '') : (string) $file;
                                    $fileName = is_array($file) ? ($file['name'] ?? basename($filePath)) : basename($filePath);
                                    $isImage = preg_match('/\.(jpe?g|png|gif|webp|bmp)$/i', $fileName);
                                @endphp
                                @if($filePath === '') @continue @endif
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden bg-gray-50 dark:bg-gray-900/50">
                                    @if($isImage)
                                        <a href="{{ Storage::disk('public')->url($filePath) }}" target="_blank">
                                            <img src="{{ Storage::disk('public')->url($filePath) }}" alt="{{ $fileName }}" class="w-full max-h-48 object-contain bg-white dark:bg-gray-900">
                                        </a>
                                    @endif
                                    <a href="{{ \App\Support\SubmissionFile::downloadUrl($filePath, $fileName) }}"
                                       class="flex items-center gap-3 p-4 hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors">
                                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $fileName }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $isImage ? 'Click to view full size' : 'Click to download' }}</p>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @php
                    $feedback = $submission->graderFeedback();
                @endphp
            @else
                @if($submission->content)
                    <div class="mb-6">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Submission Text</p>
                        <div class="p-6 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                            <div class="max-w-none prose prose-sm dark:prose-invert prose-headings:font-semibold prose-p:mb-4 prose-p:leading-7 prose-p:text-gray-800 dark:prose-p:text-gray-200 prose-strong:text-gray-900 dark:prose-strong:text-gray-100 prose-ul:my-4 prose-ol:my-4 prose-li:my-2 prose-pre:bg-gray-100 dark:prose-pre:bg-gray-800 prose-code:text-sm">
                                <div class="whitespace-pre-wrap break-words text-gray-900 dark:text-gray-100 leading-7">
                                    @php
                                        // Convert plain text to formatted HTML with proper line breaks and spacing
                                        $formattedText = $submission->content;
                                        // Split into paragraphs (double line breaks)
                                        $paragraphs = preg_split('/\n\s*\n/', $formattedText);
                                        $formattedText = '';
                                        foreach ($paragraphs as $para) {
                                            $para = trim($para);
                                            if (!empty($para)) {
                                                // Check if it's a list item
                                                if (preg_match('/^[-*•]\s+/', $para) || preg_match('/^\d+[\.\)]\s+/', $para)) {
                                                    $formattedText .= '<p class="mb-2 ml-4">' . nl2br(e($para)) . '</p>';
                                                } else {
                                                    $formattedText .= '<p class="mb-4">' . nl2br(e($para)) . '</p>';
                                                }
                                            }
                                        }
                                    @endphp
                                    {!! $formattedText ?: '<p>' . nl2br(e($submission->content)) . '</p>' !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!empty($submission->attachments) && is_array($submission->attachments))
                    <div class="mb-6">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Attachments</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($submission->attachments as $file)
                                @php
                                    $filePath = is_array($file) ? ($file['path'] ?? '') : (string) $file;
                                    $fileName = is_array($file) ? ($file['name'] ?? basename($filePath)) : basename($filePath);
                                @endphp
                                @if($filePath === '') @continue @endif
                                <a href="{{ \App\Support\SubmissionFile::downloadUrl($filePath, $fileName) }}"
                                   class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $fileName }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Click to download</p>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @php
                    $feedback = $submission->feedback;
                @endphp
            @endif
        </div>
    </div>

    {{-- Feedback --}}
    @if($feedback)
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl shadow-lg border border-blue-200 dark:border-blue-800 p-6">
            <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-4">Instructor Feedback</h3>
            <div class="max-w-none prose prose-sm dark:prose-invert prose-p:text-blue-900 dark:prose-p:text-blue-100 prose-p:mb-4 prose-p:leading-7 prose-strong:text-blue-900 dark:prose-strong:text-blue-100">
                <div class="whitespace-pre-wrap break-words leading-7">
                    @php
                        // Convert plain text to formatted HTML with proper line breaks and spacing
                        $formattedFeedback = $feedback;
                        // Split into paragraphs (double line breaks)
                        $paragraphs = preg_split('/\n\s*\n/', $formattedFeedback);
                        $formattedFeedback = '';
                        foreach ($paragraphs as $para) {
                            $para = trim($para);
                            if (!empty($para)) {
                                $formattedFeedback .= '<p class="mb-4">' . nl2br(e($para)) . '</p>';
                            }
                        }
                    @endphp
                    {!! $formattedFeedback ?: '<p>' . nl2br(e($feedback)) . '</p>' !!}
                </div>
            </div>
        </div>
    @endif

    {{-- Assignment/Assessment Info --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
            @if($type === 'assessment')
                Assessment Details
            @else
                Assignment Details
            @endif
        </h3>
        <div class="space-y-3">
            <div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Description</p>
                <p class="text-gray-900 dark:text-white mt-1">
                        @if($type === 'assessment')
                            {{ $submission->assessment->description ?? 'N/A' }}
                        @else
                            {{ $submission->assignment->description ?? 'N/A' }}
                        @endif
                </p>
            </div>
            @if($type === 'assignment' && $submission->assignment && $submission->assignment->instructions)
                <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Instructions</p>
                    <div class="prose dark:prose-invert max-w-none mt-1 text-gray-900 dark:text-white">
                        {!! nl2br(e($submission->assignment->instructions)) !!}
                    </div>
                </div>
            @endif
            <div class="grid grid-cols-2 gap-4 pt-2">
                <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Max Points</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                        @if($type === 'assessment')
                            {{ $submission->assessment->max_points ?? 100 }}
                        @else
                            {{ $submission->assignment->max_points ?? 100 }}
                        @endif
                    </p>
                </div>
                @if($type === 'assignment' && $submission->assignment->due_date)
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Due Date</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $submission->assignment->due_date->format('M d, Y g:i A') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
