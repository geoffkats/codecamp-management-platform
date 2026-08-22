<div class="flex flex-col gap-6 p-6">
    {{-- Lesson Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('courses.learn', $course) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            <a href="{{ route('courses.show', $course) }}" class="hover:underline">{{ $course->title }}</a>
                            <span class="mx-2">•</span>
                            <span>Module {{ $lesson->module->order_index }}</span>
                        </p>
                    </div>
                </div>
                @if($lesson->description)
                    <p class="text-gray-600 dark:text-gray-400">{{ $lesson->description }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($isLessonCompleted)
                    <flux:badge variant="success">Completed</flux:badge>
                @else
                    <flux:button 
                        wire:click="openCompletionModal" 
                        variant="primary"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        :disabled="!$canComplete">
                        <span wire:loading.remove wire:target="completeLesson,openCompletionModal">
                            @if($canComplete)
                                Mark as Complete
                            @else
                                Complete Required Items First
                            @endif
                        </span>
                        <span wire:loading wire:target="completeLesson">
                            <span class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </span>
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Missing Requirements Warning --}}
        @if(!$isLessonCompleted && !empty($completionStatus['missing'] ?? []))
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mt-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-2">
                            Complete Required Items First
                        </h4>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-3">
                            You must complete the following before marking this lesson as complete:
                        </p>
                        <ul class="space-y-2">
                            @foreach($completionStatus['missing'] ?? [] as $missing)
                                <li class="flex items-center gap-2 text-sm text-yellow-800 dark:text-yellow-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ $missing['title'] ?? $missing['message'] ?? 'Complete requirement' }}</span>
                                    @if(isset($missing['type_label']))
                                        <span class="text-xs text-yellow-600 dark:text-yellow-400">
                                            ({{ $missing['type_label'] }})
                                        </span>
                                    @endif
                                    @if(isset($missing['route']) && isset($missing['id']))
                                        <a href="{{ route($missing['route'], $missing['id']) }}" wire:navigate class="ml-auto text-xs text-yellow-700 dark:text-yellow-300 underline hover:text-yellow-900 dark:hover:text-yellow-100">
                                            Go →
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Video Progress for Video Lessons --}}
        @if($lesson->content_type === 'video' && $videoProgress > 0)
            <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="text-gray-700 dark:text-gray-300">Video Progress</span>
                    <span class="font-semibold">{{ round($videoProgress) }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                         style="width: {{ $videoProgress }}%"></div>
                </div>
            </div>
        @endif
    </div>

    {{-- Lesson Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content Area --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Video Player --}}
            @if(($lesson->video_url || $lesson->lesson_type === 'video') && $lesson->video_url)
                <div class="bg-black rounded-xl shadow-lg overflow-hidden">
                    <div class="aspect-video">
                        <video 
                            id="lesson-video"
                            class="w-full h-full"
                            controls
                            preload="metadata"
                            @play="$wire.dispatch('video-started')"
                            @timeupdate="handleVideoProgress()"
                            @ended="handleVideoEnded()"
                        >
                            <source src="{{ $lesson->video_url }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const video = document.getElementById('lesson-video');
                        if (video) {
                            // Resume from last watched position
                            @if($videoWatchedSeconds > 0)
                                video.currentTime = {{ $videoWatchedSeconds }};
                            @endif

                            let updateInterval;

                            function handleVideoProgress() {
                                const currentTime = video.currentTime;
                                const duration = video.duration;
                                
                                if (duration > 0) {
                                    clearTimeout(updateInterval);
                                    updateInterval = setTimeout(() => {
                                        @this.updateVideoProgress(
                                            Math.floor(currentTime),
                                            Math.floor(duration),
                                            false
                                        );
                                    }, 1000); // Update every second
                                }
                            }

                            function handleVideoEnded() {
                                @this.updateVideoProgress(
                                    Math.floor(video.duration),
                                    Math.floor(video.duration),
                                    true
                                );
                                
                                // Auto-mark lesson as complete if video finished
                                setTimeout(() => {
                                    @if(!$isLessonCompleted)
                                        @this.completeLesson();
                                    @endif
                                }, 500);
                            }

                            video.addEventListener('timeupdate', handleVideoProgress);
                            video.addEventListener('ended', handleVideoEnded);
                        }
                    });
                </script>
            @endif

            {{-- Text Content --}}
            @if($lesson->content)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Lesson Content</h2>
                    @php
                        $hasHtml = strip_tags($lesson->content) !== $lesson->content;
                    @endphp
                    <div class="prose prose-lg dark:prose-invert max-w-none 
                                prose-headings:font-bold prose-headings:text-gray-900 dark:prose-headings:text-white
                                prose-p:text-gray-700 dark:prose-p:text-gray-300 prose-p:leading-relaxed prose-p:mb-4
                                prose-ul:text-gray-700 dark:prose-ul:text-gray-300 prose-ul:my-4
                                prose-ol:text-gray-700 dark:prose-ol:text-gray-300 prose-ol:my-4
                                prose-li:text-gray-700 dark:prose-li:text-gray-300 prose-li:my-2
                                prose-strong:text-gray-900 dark:prose-strong:text-white prose-strong:font-semibold
                                prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-a:no-underline hover:prose-a:underline
                                prose-code:text-pink-600 dark:prose-code:text-pink-400 prose-code:bg-gray-100 dark:prose-code:bg-gray-800 prose-code:px-1 prose-code:py-0.5 prose-code:rounded
                                prose-pre:bg-gray-900 dark:prose-pre:bg-gray-800 prose-pre:text-gray-100
                                prose-blockquote:border-l-blue-500 prose-blockquote:pl-4 prose-blockquote:italic
                                prose-img:rounded-lg prose-img:shadow-lg prose-img:my-4
                                prose-table:w-full prose-table:border-collapse prose-table:my-4
                                prose-th:bg-gray-100 dark:prose-th:bg-gray-700 prose-th:font-semibold prose-th:p-2
                                prose-td:border prose-td:border-gray-200 dark:prose-td:border-gray-600 prose-td:p-2
                                prose-hr:border-gray-200 dark:prose-hr:border-gray-700
                                {{ $hasHtml ? '' : 'whitespace-pre-wrap' }} break-words">
                        @if($hasHtml)
                            {{-- Display HTML content with proper styling --}}
                            {!! $lesson->content !!}
                        @else
                            {{-- Display plain text with line breaks preserved --}}
                            {!! nl2br(e($lesson->content)) !!}
                        @endif
                    </div>
                </div>
            @endif

            {{-- Attachments --}}
            @if($lesson->attachments && count(json_decode($lesson->attachments, true) ?? []) > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Attachments</h2>
                    <div class="space-y-2">
                        @foreach(json_decode($lesson->attachments, true) as $attachment)
                            <a href="{{ asset('storage/' . $attachment['path']) }}" target="_blank" 
                               class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $attachment['name'] ?? 'Download' }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Lesson Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Lesson Details</h3>
                <div class="space-y-3">
                    @if($lesson->duration_minutes)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Duration</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $lesson->duration_minutes }} minutes</span>
                        </div>
                    @endif
                    @if($lesson->points_reward)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Points</span>
                            <span class="font-semibold text-yellow-600 dark:text-yellow-400">{{ $lesson->points_reward }} XP</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Type</span>
                        <flux:badge size="sm" variant="primary">{{ ucfirst($lesson->content_type) }}</flux:badge>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Navigation</h3>
                <div class="space-y-3">
                    @if($previousLesson)
                        <a href="{{ route('lessons.view', $previousLesson) }}" wire:navigate
                           class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Previous</p>
                                <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $previousLesson->title }}</p>
                            </div>
                        </a>
                    @endif
                    @if($nextLesson)
                        <a href="{{ route('lessons.view', $nextLesson) }}" wire:navigate
                           class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <div class="flex-1 min-w-0 text-right">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Next</p>
                                <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $nextLesson->title }}</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <div class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm font-semibold text-green-800 dark:text-green-200">You've completed all lessons in this module!</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quizzes --}}
            @if($lesson->quizzes && $lesson->quizzes->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Quizzes</h3>
                    <div class="space-y-2">
                        @foreach($lesson->quizzes as $quiz)
                            <a href="{{ route('quizzes.take', $quiz) }}" wire:navigate
                               class="block p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $quiz->title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $quiz->questions->count() }} questions</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Assessments --}}
            @if($lesson->assessments && $lesson->assessments->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span>📝</span>
                        <span>Assessments</span>
                    </h3>
                    <div class="space-y-4">
                        @foreach($lesson->assessments as $assessment)
                            @php
                                $typeInfo = [
                                    'quiz' => ['label' => 'Quiz', 'color' => 'bg-blue-500', 'icon' => '📝', 'desc' => 'Question-based assessment'],
                                    'assignment' => ['label' => 'Assignment', 'color' => 'bg-purple-500', 'icon' => '📄', 'desc' => 'File or text submission'],
                                    'unit_survey' => ['label' => 'Survey', 'color' => 'bg-green-500', 'icon' => '📊', 'desc' => 'Feedback collection'],
                                    'rubric_assessment' => ['label' => 'Rubric', 'color' => 'bg-orange-500', 'icon' => '📋', 'desc' => 'Criteria-based evaluation'],
                                    'peer_review' => ['label' => 'Peer Review', 'color' => 'bg-pink-500', 'icon' => '👥', 'desc' => 'Evaluate peers'],
                                    'self_assessment' => ['label' => 'Self-Assessment', 'color' => 'bg-indigo-500', 'icon' => '🔍', 'desc' => 'Reflect on learning'],
                                    'pre_project_test' => ['label' => 'Pre-Project Test', 'color' => 'bg-yellow-500', 'icon' => '⏮️', 'desc' => 'Baseline evaluation'],
                                    'post_project_test' => ['label' => 'Post-Project Test', 'color' => 'bg-yellow-600', 'icon' => '⏭️', 'desc' => 'Post-project evaluation'],
                                ];
                                $info = $typeInfo[$assessment->assessment_type] ?? ['label' => ucfirst(str_replace('_', ' ', $assessment->assessment_type)), 'color' => 'bg-gray-500', 'icon' => '📝', 'desc' => ''];
                                $userAttempts = $assessment->attempts ?? collect();
                                $bestAttempt = $userAttempts->sortByDesc('percentage_score')->first();
                                $attemptCount = $userAttempts->count();
                                $canTake = $assessment->max_attempts == 0 || $attemptCount < $assessment->max_attempts;
                            @endphp
                            <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="px-2 py-1 rounded text-xs font-semibold text-white {{ $info['color'] }}">
                                                {{ $info['icon'] }} {{ $info['label'] }}
                                            </span>
                                            @if($assessment->is_required)
                                                <span class="px-2 py-1 rounded text-xs font-semibold text-white bg-red-500">
                                                    Required
                                                </span>
                                            @endif
                                        </div>
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-1">{{ $assessment->title }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $info['desc'] }}</p>
                                        @if($assessment->description)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ \Illuminate\Support\Str::limit($assessment->description, 80) }}</p>
                                        @endif
                                        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                                            @if($assessment->time_limit_minutes)
                                                <span>⏱️ {{ $assessment->time_limit_minutes }} min</span>
                                            @endif
                                            @if($assessment->questions && $assessment->questions->count() > 0)
                                                <span>❓ {{ $assessment->questions->count() }} questions</span>
                                            @endif
                                            <span>⭐ {{ $assessment->xp_reward }} XP</span>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($attemptCount > 0 && $bestAttempt)
                                    <div class="mb-3 p-2 bg-gray-50 dark:bg-gray-900 rounded text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Best Score:</span>
                                            @php
                                                $maxScore = ($assessment->questions && $assessment->questions->count() > 0) 
                                                    ? $assessment->questions->sum('points') 
                                                    : 100;
                                                $attemptScore = $bestAttempt->score ?? 0;
                                                $percentage = min($maxScore > 0 ? ($attemptScore / $maxScore) * 100 : 0, 100);
                                            @endphp
                                            <span class="font-bold {{ $bestAttempt->is_passed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ number_format($percentage, 1) }}%
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                            {{ $attemptCount }} attempt(s)
                                            @if($canTake && $assessment->max_attempts > 0)
                                                • {{ $assessment->max_attempts - $attemptCount }} remaining
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="flex items-center gap-2">
                                    <flux:button 
                                        href="{{ route('assessments.show', $assessment) }}" 
                                        wire:navigate 
                                        variant="outline" 
                                        size="sm"
                                        class="flex-1">
                                        View Details
                                    </flux:button>
                                    @if($canTake)
                                        <flux:button 
                                            href="{{ route('assessments.take', $assessment) }}" 
                                            wire:navigate 
                                            variant="primary" 
                                            size="sm"
                                            class="flex-1">
                                            {{ $attemptCount > 0 ? 'Retake' : 'Start' }}
                                        </flux:button>
                                    @else
                                        <flux:badge variant="danger" size="sm">Max Attempts Reached</flux:badge>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Assignments --}}
            @if($lesson->assignments && $lesson->assignments->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span>📄</span>
                        <span>Assignments</span>
                    </h3>
                    <div class="space-y-4">
                        @foreach($lesson->assignments as $assignment)
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
                                                📄 Assignment
                                            </span>
                                            @if($isSubmitted)
                                                <span class="px-2 py-1 rounded text-xs font-semibold text-white {{ $isGraded ? 'bg-green-500' : 'bg-yellow-500' }}">
                                                    {{ $isGraded ? '✓ Graded' : 'Submitted' }}
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
                                                    📅 Due: {{ $assignment->due_date->format('M d, Y') }}
                                                </span>
                                            @endif
                                            <span>⭐ {{ $assignment->max_points ?? 100 }} points</span>
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
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Completion Confirmation Modal --}}
@if($showCompletionModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click="showCompletionModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-6" wire:click.stop>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Complete Lesson?</h3>
            
            @if(!empty($completionStatus['missing'] ?? []))
                <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200 font-semibold mb-2">
                        ⚠️ Some requirements are not met:
                    </p>
                    <ul class="text-xs text-yellow-700 dark:text-yellow-300 space-y-1">
                        @foreach($completionStatus['missing'] ?? [] as $missing)
                            <li>• {{ $missing['title'] ?? $missing['message'] ?? 'Complete requirement' }}</li>
                        @endforeach
                    </ul>
                </div>
            @else
                <p class="text-gray-700 dark:text-gray-300 mb-4">
                    Are you sure you want to mark this lesson as complete? This action cannot be undone.
                </p>
            @endif
            
            <div class="flex items-center justify-end gap-3">
                <flux:button 
                    wire:click="showCompletionModal = false" 
                    variant="ghost">
                    Cancel
                </flux:button>
                <flux:button 
                    wire:click="confirmCompleteLesson" 
                    variant="primary"
                    wire:loading.attr="disabled"
                    :disabled="!$canComplete">
                    <span wire:loading.remove wire:target="confirmCompleteLesson">
                        Yes, Complete Lesson
                    </span>
                    <span wire:loading wire:target="confirmCompleteLesson">
                        Processing...
                    </span>
                </flux:button>
            </div>
        </div>
    </div>
@endif

{{-- Success/Error Messages --}}
@if(session()->has('message'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
         class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        {{ session('message') }}
    </div>
@endif

@if(session()->has('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)" 
         class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        {{ session('error') }}
    </div>
@endif

