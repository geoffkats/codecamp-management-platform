<div>
@php
    $user = auth()->user();
    $isInstructor = $course->instructor_id === $user->id || $user->hasRole('admin') || $user->hasRole('supervisor');
    $isLocked = $lesson->is_locked && !$isInstructor;
@endphp

@if($isLocked)
    {{-- Locked Lesson View for Students --}}
    <div class="flex flex-col items-center justify-center min-h-screen p-6">
        <div class="max-w-2xl w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-8 text-center">
            <div class="mb-6">
                <svg class="w-24 h-24 mx-auto text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Lesson Locked</h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                This lesson is currently locked. Please wait for your instructor to unlock it.
            </p>
            
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>{{ $lesson->title }}</strong><br>
                    Module {{ $lesson->module->order_index }}: {{ $lesson->module->title }}
                </p>
            </div>
            
            {{-- Show available quizzes and assignments --}}
            @if($lesson->assessments->count() > 0 || $lesson->assignments->count() > 0)
                <div class="text-left mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Available Activities</h3>
                    
                    @if($lesson->assessments->count() > 0)
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quizzes & Assessments</h4>
                            <div class="space-y-2">
                                @foreach($lesson->assessments as $assessment)
                                    @if(!$assessment->is_locked)
                                        {{-- Unlocked Assessment --}}
                                        <a href="{{ route('assessments.take', $assessment) }}" 
                                           class="block p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                    </svg>
                                                    <span class="font-medium text-gray-900 dark:text-white">{{ $assessment->title }}</span>
                                                </div>
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </div>
                                            <p class="text-xs text-green-700 dark:text-green-300 mt-1 ml-7">✓ Available - Click to start</p>
                                        </a>
                                    @else
                                        {{-- Locked Assessment --}}
                                        <div class="block p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg opacity-75">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $assessment->title }}</span>
                                                </div>
                                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <p class="text-xs text-red-700 dark:text-red-300 mt-1 ml-7">🔒 Locked - Wait for instructor to unlock</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @if($lesson->assignments->count() > 0)
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assignments</h4>
                            <div class="space-y-2">
                                @foreach($lesson->assignments as $assignment)
                                    @if(!$assignment->is_locked)
                                        {{-- Unlocked Assignment --}}
                                        <a href="{{ route('assignments.show', $assignment) }}" 
                                           class="block p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                    </svg>
                                                    <span class="font-medium text-gray-900 dark:text-white">{{ $assignment->title }}</span>
                                                </div>
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </div>
                                            <p class="text-xs text-green-700 dark:text-green-300 mt-1 ml-7">✓ Available - Click to view</p>
                                        </a>
                                    @else
                                        {{-- Locked Assignment --}}
                                        <div class="block p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg opacity-75">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $assignment->title }}</span>
                                                </div>
                                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <p class="text-xs text-red-700 dark:text-red-300 mt-1 ml-7">🔒 Locked - Wait for instructor to unlock</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
            
            <a href="{{ route('courses.learn', $course) }}" 
               class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Course
            </a>
        </div>
    </div>
@else
    {{-- Normal Lesson View --}}
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
                @unless(auth()->user()->isIctTeacher())
                    {{-- Discussion Button --}}
                    @if($lesson->id)
                        <flux:button 
                            href="{{ route('discussions.index', ['lesson' => $lesson->id]) }}"
                            variant="ghost"
                            wire:navigate>
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            Discuss This Lesson
                        </flux:button>
                    @endif
                    
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
                @endunless
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
                @php
                    $videoUrl = $lesson->video_url;
                    $isYouTube = false;
                    $isVimeo = false;
                    $embedUrl = '';
                    $videoId = '';
                    
                    // Check if YouTube URL
                    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoUrl, $matches)) {
                        $isYouTube = true;
                        $videoId = $matches[1];
                        $embedUrl = 'https://www.youtube.com/embed/' . $videoId . '?enablejsapi=1&origin=' . urlencode(request()->getSchemeAndHttpHost()) . '&rel=0';
                        if ($videoWatchedSeconds > 0) {
                            $embedUrl .= '&start=' . round($videoWatchedSeconds);
                        }
                    }
                    // Check if Vimeo URL
                    elseif (preg_match('/vimeo\.com\/(?:.*\/)?(\d+)/', $videoUrl, $matches)) {
                        $isVimeo = true;
                        $videoId = $matches[1];
                        $embedUrl = 'https://player.vimeo.com/video/' . $videoId . '?api=1';
                        if ($videoWatchedSeconds > 0) {
                            $embedUrl .= '&time=' . round($videoWatchedSeconds);
                        }
                    }
                @endphp

                <div class="bg-black rounded-xl shadow-lg overflow-hidden">
                    <div class="aspect-video">
                        @if($isYouTube || $isVimeo)
                            {{-- YouTube/Vimeo Embed --}}
                            <iframe 
                                id="lesson-video-iframe"
                                class="w-full h-full"
                                src="{{ $embedUrl }}"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                                loading="lazy">
                            </iframe>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    @if($isYouTube)
                                        // YouTube API for progress tracking
                                        let tag = document.createElement('script');
                                        tag.src = "https://www.youtube.com/iframe_api";
                                        let firstScriptTag = document.getElementsByTagName('script')[0];
                                        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

                                        let ytPlayer;
                                        window.onYouTubeIframeAPIReady = function() {
                                            ytPlayer = new YT.Player('lesson-video-iframe', {
                                                events: {
                                                    'onStateChange': function(event) {
                                                        if (event.data === YT.PlayerState.ENDED) {
                                                            @this.updateVideoProgress(
                                                                Math.floor(ytPlayer.getDuration()),
                                                                Math.floor(ytPlayer.getDuration()),
                                                                true
                                                            );
                                                        } else if (event.data === YT.PlayerState.PLAYING) {
                                                            @this.dispatch('video-started');
                                                        }
                                                    }
                                                }
                                            });
                                            
                                            // Update progress periodically when playing
                                            setInterval(function() {
                                                if (ytPlayer && ytPlayer.getPlayerState() === YT.PlayerState.PLAYING) {
                                                    const currentTime = Math.floor(ytPlayer.getCurrentTime());
                                                    const duration = Math.floor(ytPlayer.getDuration());
                                                    if (duration > 0) {
                                                        @this.updateVideoProgress(currentTime, duration, false);
                                                    }
                                                }
                                            }, 5000); // Update every 5 seconds
                                        }
                                    @elseif($isVimeo)
                                        // Vimeo API for progress tracking
                                        const iframe = document.getElementById('lesson-video-iframe');
                                        const player = new Vimeo.Player(iframe);
                                        
                                        player.on('play', function() {
                                            @this.dispatch('video-started');
                                        });
                                        
                                        player.on('timeupdate', function(data) {
                                            @this.updateVideoProgress(
                                                Math.floor(data.seconds),
                                                Math.floor(data.duration),
                                                false
                                            );
                                        });
                                        
                                        player.on('ended', function() {
                                            player.getDuration().then(function(duration) {
                                                @this.updateVideoProgress(
                                                    Math.floor(duration),
                                                    Math.floor(duration),
                                                    true
                                                );
                                            });
                                        });
                                    @endif
                                });
                            </script>
                            
                            @if($isVimeo)
                                <script src="https://player.vimeo.com/api/player.js"></script>
                            @endif
                        @else
                            {{-- Direct Video File --}}
                            <video 
                                id="lesson-video"
                                class="w-full h-full"
                                controls
                                preload="metadata"
                                @play="$wire.dispatch('video-started')"
                                @timeupdate="handleVideoProgress()"
                                @ended="handleVideoEnded()"
                            >
                                <source src="{{ $videoUrl }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>

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
                    </div>
                </div>
            @endif

            {{-- Scratch Project Embed (Priority for Interactive Lessons) --}}
            @if($lesson->scratch_project_id && $lesson->lesson_type === 'interactive')
                <x-scratch-embed 
                    :projectId="$lesson->scratch_project_id"
                    :title="$lesson->title"
                />
            @endif

            {{-- Interactive Steps (only for lessons with steps) --}}
            @if(!empty($lesson->lesson_steps) && is_array($lesson->lesson_steps) && ($lesson->lesson_type === 'interactive' || $lesson->scratch_project_id))
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl shadow-lg border-2 border-purple-200 dark:border-purple-800 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            Step-by-Step Guide
                        </h2>
                        <span class="text-xs text-purple-600 dark:text-purple-400 font-semibold bg-purple-100 dark:bg-purple-900/40 px-2 py-1 rounded">
                            {{ count($lesson->lesson_steps) }} Steps
                        </span>
                    </div>
                    <div class="space-y-2">
                        @foreach($lesson->lesson_steps as $index => $step)
                            <x-lesson-step 
                                :number="$index + 1"
                                :title="$step['title'] ?? 'Step ' . ($index + 1)"
                                :image="$step['image'] ?? null"
                                :tryItUrl="$step['try_it_url'] ?? null"
                            >
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $step['description'] ?? $step['content'] ?? '' }}
                                </p>
                                @if(!empty($step['code']))
                                    <pre class="mt-2 p-3 bg-gray-100 dark:bg-gray-900 rounded-lg overflow-x-auto text-xs"><code>{{ $step['code'] }}</code></pre>
                                @endif
                            </x-lesson-step>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Scratch Blocks Reference (only for Scratch lessons) --}}
            @if(!empty($lesson->scratch_blocks) && is_array($lesson->scratch_blocks) && $lesson->scratch_project_id)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-2 border-orange-200 dark:border-orange-800 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                        Scratch Blocks Reference
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Blocks you'll use in this Scratch project:
                    </p>
                    <div class="flex flex-wrap gap-3">
                        @foreach($lesson->scratch_blocks as $block)
                            <x-scratch-block 
                                :type="$block['category'] ?? 'motion'"
                                :text="$block['text'] ?? $block['name'] ?? 'Block'"
                            />
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Text Content --}}
            @if($lesson->content)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Lesson Content</h2>
                    @php
                        $hasHtml = strip_tags($lesson->content) !== $lesson->content;
                    @endphp
                    <div class="lesson-content prose prose-lg dark:prose-invert max-w-none {{ $hasHtml ? '' : 'whitespace-pre-wrap' }} break-words">
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
            
            {{-- Python Code Editor (for Python lessons) --}}
            @if(stripos($lesson->title, 'python') !== false || stripos($lesson->content ?? '', 'python') !== false || $lesson->lesson_type === 'code')
                @php
                    // Extract code from lesson content or use default
                    $pythonCode = $lesson->code_example ?? "# Python Code Editor\nprint('Hello, World!')\n\n# Try writing your own code:\nname = 'Student'\nprint(f'Welcome, {name}!')";
                @endphp
                <x-code-editor 
                    language="python"
                    :code="$pythonCode"
                    title="Python Practice"
                />
            @endif

            {{-- Web Development Editor (for HTML/CSS/JS lessons) --}}
            @if(stripos($lesson->title, 'web') !== false || stripos($lesson->title, 'html') !== false || stripos($lesson->title, 'css') !== false || stripos($lesson->title, 'javascript') !== false)
                @php
                    // Normalize escaped newlines that may come from JSON/text storage
                    $normalizeCode = fn($code) => str_replace(["\\r\\n", "\\n", "\r\n"], "\n", $code ?? '');

                    $htmlCode = $normalizeCode($lesson->html_example) ?: "<h1>Hello World!</h1>\n<p>Welcome to web development!</p>\n<button onclick=\"alert('Hello!')\">Click Me</button>";
                    $cssCode = $normalizeCode($lesson->css_example) ?: "body {\n  font-family: Arial, sans-serif;\n  padding: 20px;\n  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\n  color: white;\n}\n\nh1 {\n  text-align: center;\n  font-size: 3em;\n}\n\nbutton {\n  background: white;\n  color: #667eea;\n  padding: 10px 20px;\n  border: none;\n  border-radius: 5px;\n  cursor: pointer;\n  font-size: 1.2em;\n}";
                    $jsCode = $normalizeCode($lesson->js_example) ?: "// JavaScript code\nconsole.log('Page loaded!');\n\n// Add interactivity\ndocument.addEventListener('DOMContentLoaded', function() {\n  console.log('Ready to code!');\n});";
                @endphp
                <x-web-editor 
                    :html="$htmlCode"
                    :css="$cssCode"
                    :javascript="$jsCode"
                    title="Web Development Playground"
                />
            @endif

            {{-- JavaScript Code Editor (for standalone JS lessons) --}}
            @if(stripos($lesson->title, 'javascript') !== false && stripos($lesson->title, 'web') === false && stripos($lesson->title, 'html') === false)
                @php
                    $jsCode = $lesson->code_example ?? "// JavaScript Code Editor\nconsole.log('Hello, JavaScript!');\n\n// Try your own code:\nconst numbers = [1, 2, 3, 4, 5];\nconst sum = numbers.reduce((a, b) => a + b, 0);\nconsole.log('Sum:', sum);";
                @endphp
                <x-code-editor 
                    language="javascript"
                    :code="$jsCode"
                    title="JavaScript Practice"
                />
            @endif

            {{-- Learning Objectives --}}
            @if($lesson->objectives)
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl shadow-lg border-2 border-blue-200 dark:border-blue-800 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        What You'll Learn
                    </h2>
                    <div class="prose dark:prose-invert max-w-none">
                        {!! nl2br(e($lesson->objectives)) !!}
                    </div>
                </div>
            @endif

            {{-- Attachments --}}
            @if($lesson->attachments && is_array($lesson->attachments) && count($lesson->attachments) > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Attachments</h2>
                    <div class="space-y-2">
                        @foreach($lesson->attachments as $attachment)
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

            {{-- Post-Lesson Feedback CTA --}}
            @if($isLessonCompleted)
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl shadow-lg border-2 border-emerald-200 dark:border-emerald-800 p-6">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-md">
                            ⭐
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Rate this lesson</h3>
                            <p class="text-sm text-gray-700 dark:text-gray-300">Share a quick rating and send feedback to your teacher to improve the next lesson.</p>
                        </div>
                    </div>
                    <form method="GET" action="{{ url('/feedback/teacher') }}" class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                        <input type="hidden" name="lesson_title" value="{{ $lesson->title }}">
                        <input type="hidden" name="source" value="lesson_view">

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Rating</label>
                            <select name="rating" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="" disabled selected>Choose rating</option>
                                <option value="5">Excellent (5)</option>
                                <option value="4">Good (4)</option>
                                <option value="3">Okay (3)</option>
                                <option value="2">Needs work (2)</option>
                                <option value="1">Poor (1)</option>
                            </select>
                        </div>

                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Quick feedback (optional)</label>
                            <textarea name="note" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="What worked well? What could improve?" ></textarea>
                        </div>

                        <div class="flex flex-wrap gap-2 md:col-span-2">
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-md transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Send feedback
                            </button>
                            <a href="{{ url('/feedback/teacher') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-emerald-600 text-emerald-700 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30">
                                Open full feedback page
                            </a>
                        </div>
                    </form>
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
                                                $percentage = $maxScore > 0 ? ($attemptScore / $maxScore) * 100 : 0;
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
                                    @php
                                        $passedAttempt = $bestAttempt && $bestAttempt->is_passed;
                                        $failedAttempt = $bestAttempt && !$bestAttempt->is_passed;
                                    @endphp
                                    <flux:button 
                                        href="{{ $passedAttempt
                                            ? route('assessments.results', ['assessment' => $assessment->id, 'attempt' => $bestAttempt->id])
                                            : ($failedAttempt ? route('assessments.take', $assessment) : route('assessments.show', $assessment)) }}" 
                                        wire:navigate 
                                        variant="outline" 
                                        size="sm"
                                        class="flex-1">
                                        {{ $passedAttempt ? 'View Results' : ($failedAttempt ? 'Retake' : 'View Details') }}
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


<style>
    /* Additional styling for Summernote-generated content */
    .lesson-content-display {
        line-height: 1.75;
    }
    
    /* Ensure all text colors work in both light and dark mode */
    .lesson-content-display * {
        color: inherit;
    }
    
    /* Handle font colors from Summernote */
    .lesson-content-display [style*="color"] {
        /* Preserve inline color styles from Summernote */
    }
    
    /* Handle background colors from Summernote */
    .lesson-content-display [style*="background-color"] {
        /* Preserve inline background colors from Summernote */
    }
    
    /* Ensure images are responsive */
    .lesson-content-display img {
        max-width: 100%;
        height: auto;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }
    
    /* Handle embedded videos */
    .lesson-content-display iframe {
        max-width: 100%;
        border-radius: 0.5rem;
        margin: 1.5rem auto;
        display: block;
    }
    
    /* Style tables nicely */
    .lesson-content-display table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
    }
    
    .lesson-content-display table th,
    .lesson-content-display table td {
        padding: 0.75rem;
        border: 1px solid #e5e7eb;
    }
    
    .dark .lesson-content-display table th,
    .dark .lesson-content-display table td {
        border-color: #374151;
    }
    
    .lesson-content-display table th {
        background-color: #f3f4f6;
        font-weight: 600;
    }
    
    .dark .lesson-content-display table th {
        background-color: #1f2937;
    }
    
    /* Handle font sizes from Summernote */
    .lesson-content-display [style*="font-size"] {
        /* Preserve font sizes */
    }
    
    /* Handle text alignment */
    .lesson-content-display [style*="text-align: center"] {
        text-align: center;
    }
    
    .lesson-content-display [style*="text-align: right"] {
        text-align: right;
    }
    
    .lesson-content-display [style*="text-align: left"] {
        text-align: left;
    }
    
    .lesson-content-display [style*="text-align: justify"] {
        text-align: justify;
    }
    
    /* Handle line height */
    .lesson-content-display [style*="line-height"] {
        /* Preserve line height */
    }
    
    /* Ensure code blocks are readable */
    .lesson-content-display pre {
        background-color: #1f2937;
        color: #f3f4f6;
        padding: 1rem;
        border-radius: 0.5rem;
        overflow-x: auto;
        margin: 1.5rem 0;
    }
    
    .lesson-content-display code {
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.875rem;
    }
    
    /* Handle blockquotes */
    .lesson-content-display blockquote {
        border-left: 4px solid #3b82f6;
        padding-left: 1rem;
        margin: 1.5rem 0;
        font-style: italic;
        color: #6b7280;
    }
    
    .dark .lesson-content-display blockquote {
        color: #9ca3af;
    }
    
    /* Handle horizontal rules */
    .lesson-content-display hr {
        border: none;
        border-top: 1px solid #e5e7eb;
        margin: 2rem 0;
    }
    
    .dark .lesson-content-display hr {
        border-top-color: #374151;
    }
    
    /* Ensure proper spacing for nested lists */
    .lesson-content-display ul ul,
    .lesson-content-display ol ol,
    .lesson-content-display ul ol,
    .lesson-content-display ol ul {
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    /* Handle Summernote's default paragraph spacing */
    .lesson-content-display p:empty {
        min-height: 1.5rem;
    }
    
    /* Ensure links are visible and clickable */
    .lesson-content-display a {
        color: #2563eb;
        text-decoration: underline;
        cursor: pointer;
    }
    
    .dark .lesson-content-display a {
        color: #60a5fa;
    }
    
    .lesson-content-display a:hover {
        color: #1d4ed8;
    }
    
    .dark .lesson-content-display a:hover {
        color: #93c5fd;
    }
</style>
@endif
</div>
