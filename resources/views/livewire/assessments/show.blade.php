<div class="max-w-6xl mx-auto p-6 space-y-6">
    {{-- Assessment Header --}}
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-xl shadow-lg p-8 text-white">
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('courses.show', $assessment->course) }}" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold">{{ $assessment->title }}</h1>
                </div>
                @if($assessment->description)
                    <p class="text-blue-100 mb-4">{{ $assessment->description }}</p>
                @endif
                <div class="flex items-center gap-6 text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>{{ $assessment->course->title }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @php
                            $typeColors = [
                                'quiz' => 'bg-blue-500',
                                'assignment' => 'bg-purple-500',
                                'unit_survey' => 'bg-green-500',
                                'rubric_assessment' => 'bg-orange-500',
                                'peer_review' => 'bg-pink-500',
                                'self_assessment' => 'bg-indigo-500',
                                'pre_project_test' => 'bg-yellow-500',
                                'post_project_test' => 'bg-yellow-600',
                            ];
                            $typeLabels = [
                                'quiz' => 'Quiz',
                                'assignment' => 'Assignment',
                                'unit_survey' => 'Survey',
                                'rubric_assessment' => 'Rubric',
                                'peer_review' => 'Peer Review',
                                'self_assessment' => 'Self-Assessment',
                                'pre_project_test' => 'Pre-Project Test',
                                'post_project_test' => 'Post-Project Test',
                            ];
                            $color = $typeColors[$assessment->assessment_type] ?? 'bg-gray-500';
                            $label = $typeLabels[$assessment->assessment_type] ?? ucfirst(str_replace('_', ' ', $assessment->assessment_type));
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold text-white {{ $color }}">
                            {{ $label }}
                        </span>
                    </div>
                    @if($assessment->time_limit_minutes)
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $assessment->time_limit_minutes }} minutes</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- User Progress --}}
        @if($hasTaken)
            <div class="mt-6 bg-white/20 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-blue-100">Your Best Score</span>
                    @if($bestScore === null)
                        <span class="text-2xl font-bold text-yellow-300">Pending</span>
                    @else
                        <span class="text-2xl font-bold">{{ number_format($bestScore, 1) }}%</span>
                    @endif
                </div>
                @if($bestScore !== null)
                    <div class="w-full bg-white/20 rounded-full h-2">
                        <div class="h-2 bg-white rounded-full transition-all duration-300" 
                             style="width: {{ min($bestScore, 100) }}%"></div>
                    </div>
                @else
                    <div class="w-full bg-white/20 rounded-full h-2">
                        <div class="h-2 bg-yellow-300 rounded-full" style="width: 100%"></div>
                    </div>
                @endif
                @if($attemptsRemaining !== 'unlimited')
                    <p class="text-xs text-blue-100 mt-2">{{ $attemptsRemaining }} attempts remaining</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Statistics (for instructors/admins) --}}
    @if(Auth::user()->hasAnyRole(['teacher', 'admin']))
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Attempts</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total_attempts'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">Average Score</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['average_score'] }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">Pass Rate</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['pass_rate'] }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">Questions</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $assessment->questions ? $assessment->questions->count() : 0 }}</p>
            </div>
        </div>
    @endif

    {{-- Assessment Details --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Assessment Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Type</p>
                @php
                    $typeInfo = [
                        'quiz' => ['label' => 'Quiz', 'desc' => 'Question-based assessment with automatic grading', 'icon' => '📝'],
                        'assignment' => ['label' => 'Assignment', 'desc' => 'File or text submission for manual grading', 'icon' => '📄'],
                        'unit_survey' => ['label' => 'Unit Survey', 'desc' => 'Collect feedback with ratings and open-ended questions', 'icon' => '📊'],
                        'rubric_assessment' => ['label' => 'Rubric Assessment', 'desc' => 'Criteria-based evaluation with weighted scoring', 'icon' => '📋'],
                        'peer_review' => ['label' => 'Peer Review', 'desc' => 'Students evaluate each other\'s work', 'icon' => '👥'],
                        'self_assessment' => ['label' => 'Self-Assessment', 'desc' => 'Reflect on your own learning and progress', 'icon' => '🔍'],
                        'pre_project_test' => ['label' => 'Pre-Project Test', 'desc' => 'Baseline knowledge evaluation before a project', 'icon' => '⏮️'],
                        'post_project_test' => ['label' => 'Post-Project Test', 'desc' => 'Knowledge measurement after a project', 'icon' => '⏭️'],
                    ];
                    $info = $typeInfo[$assessment->assessment_type] ?? ['label' => ucfirst(str_replace('_', ' ', $assessment->assessment_type)), 'desc' => '', 'icon' => '📝'];
                @endphp
                <div class="flex items-center gap-2">
                    <span class="text-xl">{{ $info['icon'] }}</span>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $info['label'] }}</p>
                        @if($info['desc'])
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $info['desc'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Max Attempts</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                    {{ $assessment->max_attempts > 0 ? $assessment->max_attempts : 'Unlimited' }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Passing Score</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $assessment->passing_score }}%</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">XP Reward</p>
                <p class="font-semibold text-yellow-600 dark:text-yellow-400">{{ $assessment->xp_reward }} XP</p>
            </div>
            @if($assessment->is_required)
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Required</p>
                    <flux:badge variant="warning">Required</flux:badge>
                </div>
            @endif
        </div>
    </div>

    {{-- Rubric Criteria (if rubric assessment) --}}
    @if($assessment->assessment_type === 'rubric_assessment' && $assessment->rubric_criteria)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Rubric Criteria</h2>
            <div class="space-y-4">
                @foreach($assessment->rubric_criteria as $index => $criterion)
                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $criterion['name'] ?? 'Criterion ' . ($index + 1) }}</h3>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $criterion['max_points'] ?? 0 }} points</span>
                        </div>
                        @if(isset($criterion['description']))
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $criterion['description'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Your Attempts --}}
    @if($hasTaken)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Your Attempts</h2>
            <div class="space-y-3">
                @foreach($userAttempts as $attempt)
                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    Attempt #{{ $attempt->attempt_number ?? $loop->iteration }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $attempt->completed_at ? $attempt->completed_at->format('M d, Y H:i') : 'Incomplete' }}
                                </p>
                            </div>
                            <div class="text-right">
                                @php
                                    $isPendingGrading = $assessment->assessment_type === 'assignment' && $attempt->score === null;
                                    $maxScore = ($assessment->questions && $assessment->questions->count() > 0) 
                                        ? $assessment->questions->sum('points') 
                                        : 100;
                                    $attemptScore = $attempt->score ?? 0;
                                    $percentage = $maxScore > 0 ? ($attemptScore / $maxScore) * 100 : 0;
                                @endphp
                                @if($isPendingGrading)
                                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                        Pending
                                    </p>
                                    <flux:badge variant="warning" size="sm">Pending Review</flux:badge>
                                @else
                                    <p class="text-2xl font-bold {{ $attempt->is_passed ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($percentage, 1) }}%
                                    </p>
                                    @if($attempt->is_passed)
                                        <flux:badge variant="success" size="sm">Passed</flux:badge>
                                    @else
                                        <flux:badge variant="danger" size="sm">Failed</flux:badge>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3">
        @if(Auth::user()->hasAnyRole(['teacher', 'admin']))
            <flux:button href="{{ route('assessments.edit', $assessment) }}" wire:navigate variant="ghost">
                Edit Assessment
            </flux:button>
        @endif
        
        @if($attemptsRemaining === 'unlimited' || $attemptsRemaining > 0)
            <flux:button href="{{ route('assessments.take', $assessment) }}" wire:navigate variant="primary">
                @if($hasTaken)
                    Take Again
                @else
                    Start Assessment
                @endif
            </flux:button>
        @else
            <flux:badge variant="danger" size="lg">No Attempts Remaining</flux:badge>
        @endif
    </div>
</div>
