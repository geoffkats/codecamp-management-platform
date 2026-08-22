@props(['assessment'])

@php
    $typeInfo = [
        'quiz'               => ['label' => 'Quiz',             'color' => 'bg-blue-500',   'ring' => 'ring-blue-200 dark:ring-blue-800'],
        'assignment'         => ['label' => 'Assignment',       'color' => 'bg-purple-500', 'ring' => 'ring-purple-200 dark:ring-purple-800'],
        'unit_survey'        => ['label' => 'Survey',           'color' => 'bg-green-500',  'ring' => 'ring-green-200 dark:ring-green-800'],
        'rubric_assessment'  => ['label' => 'Rubric',           'color' => 'bg-orange-500', 'ring' => 'ring-orange-200 dark:ring-orange-800'],
        'peer_review'        => ['label' => 'Peer Review',      'color' => 'bg-pink-500',   'ring' => 'ring-pink-200 dark:ring-pink-800'],
        'self_assessment'    => ['label' => 'Self-Assessment',  'color' => 'bg-indigo-500', 'ring' => 'ring-indigo-200 dark:ring-indigo-800'],
        'pre_project_test'   => ['label' => 'Pre-Project Test', 'color' => 'bg-yellow-500', 'ring' => 'ring-yellow-200 dark:ring-yellow-800'],
        'post_project_test'  => ['label' => 'Post-Project',     'color' => 'bg-yellow-600', 'ring' => 'ring-yellow-200 dark:ring-yellow-800'],
    ];
    $info = $typeInfo[$assessment->assessment_type]
        ?? ['label' => ucfirst(str_replace('_', ' ', $assessment->assessment_type)), 'color' => 'bg-gray-500', 'ring' => 'ring-gray-200 dark:ring-gray-700'];

    // Query only THIS user's attempts — never show other students' data
    $userAttempts = \App\Models\AssessmentAttempt::where('assessment_id', $assessment->id)
        ->where('user_id', auth()->id())
        ->orderByDesc('created_at')
        ->get();

    $bestAttempt   = $userAttempts->sortByDesc('percentage_score')->first();
    $latestAttempt = $userAttempts->first();
    $attemptCount  = $userAttempts->count();
    $canTake       = $assessment->max_attempts == 0 || $attemptCount < $assessment->max_attempts;

    // Pending = submitted but teacher hasn't graded yet (auto_scored=false AND score=null)
    $pendingAttempt = $bestAttempt && !$bestAttempt->auto_scored && $bestAttempt->score === null;
    $passedAttempt  = $bestAttempt && $bestAttempt->is_passed;
    $failedAttempt  = $bestAttempt && !$bestAttempt->is_passed && !$pendingAttempt;

    // Calculate best percentage from the best attempt
    if ($bestAttempt) {
        $questionCount = $assessment->questions()->count();
        $maxScore = $questionCount > 0 ? $assessment->questions()->sum('points') : 100;
        $maxScore = $maxScore ?: 100;
        $bestPct = $bestAttempt->auto_scored
            ? min(round(($bestAttempt->score / $maxScore) * 100, 1), 100)
            : ($bestAttempt->score !== null ? min(round($bestAttempt->score, 1), 100) : null);
    } else {
        $bestPct = null;
        $maxScore = 100;
    }
@endphp

<div class="flex flex-col bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden hover:shadow-md transition-shadow">

    {{-- Card header stripe --}}
    <div class="h-1 {{ $info['color'] }}"></div>

    <div class="p-5 flex-1 flex flex-col gap-3">

        {{-- Type badge + title --}}
        <div>
            <div class="flex items-center gap-2 mb-2 flex-wrap">
                <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold text-white {{ $info['color'] }}">
                    {{ $info['label'] }}
                </span>
                @if($assessment->is_required)
                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30">
                        Required
                    </span>
                @endif
                @if($pendingAttempt)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/30">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Under Review
                    </span>
                @elseif($passedAttempt)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/30">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Passed
                    </span>
                @elseif($failedAttempt)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Not Passed
                    </span>
                @endif
            </div>
            <h4 class="font-bold text-gray-900 dark:text-white leading-snug">{{ $assessment->title }}</h4>
            @if($assessment->description)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $assessment->description }}</p>
            @endif
        </div>

        {{-- Meta info --}}
        <div class="flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400">
            @if($assessment->time_limit_minutes)
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $assessment->time_limit_minutes }} min
                </span>
            @endif
            @if($assessment->xp_reward)
                <span class="flex items-center gap-1 text-yellow-600 dark:text-yellow-400 font-semibold">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    +{{ $assessment->xp_reward }} XP
                </span>
            @endif
            @if($assessment->passing_score)
                <span>Pass: {{ $assessment->passing_score }}%</span>
            @endif
            @if($assessment->max_attempts > 0)
                <span>{{ $attemptCount }}/{{ $assessment->max_attempts }} attempts</span>
            @endif
        </div>

        {{-- Best score bar (only when attempted) --}}
        @if($attemptCount > 0 && $bestPct !== null)
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2.5">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-xs text-gray-500 dark:text-gray-400">Best score</span>
                <span class="text-sm font-bold {{ $passedAttempt ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $bestPct }}%
                </span>
            </div>
            <div class="h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                <div class="h-full rounded-full {{ $passedAttempt ? 'bg-green-500' : 'bg-red-500' }}"
                     style="width: {{ min($bestPct, 100) }}%"></div>
            </div>
            @if($assessment->passing_score)
            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">
                Need {{ $assessment->passing_score }}% to pass
            </p>
            @endif
        </div>
        @endif

        {{-- Action buttons --}}
        <div class="flex gap-2 mt-auto pt-1">
            @if($pendingAttempt)
                <a href="{{ route('assessments.results', ['assessment' => $assessment->id, 'attempt' => $bestAttempt->id]) }}"
                   wire:navigate
                   class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold border border-amber-300 dark:border-amber-600 text-amber-700 dark:text-amber-300 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View Submission
                </a>
            @elseif($passedAttempt || $failedAttempt)
                {{-- View results --}}
                <a href="{{ route('assessments.results', ['assessment' => $assessment->id, 'attempt' => $bestAttempt->id]) }}"
                   wire:navigate
                   class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ $passedAttempt ? 'View Results' : 'Review Answers' }}
                </a>
                @if($canTake)
                <a href="{{ route('assessments.take', $assessment) }}"
                   wire:navigate
                   class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Retake
                </a>
                @endif
            @elseif($canTake)
                <a href="{{ route('assessments.show', $assessment) }}"
                   wire:navigate
                   class="flex-shrink-0 inline-flex items-center justify-center px-3 py-2 text-xs font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Details
                </a>
                <a href="{{ route('assessments.take', $assessment) }}"
                   wire:navigate
                   class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-bold bg-orange-600 hover:bg-orange-700 active:scale-95 text-white rounded-lg transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Start
                </a>
            @else
                <span class="flex-1 inline-flex items-center justify-center px-3 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 rounded-lg cursor-not-allowed">
                    Max Attempts Reached
                </span>
            @endif
        </div>

    </div>
</div>
