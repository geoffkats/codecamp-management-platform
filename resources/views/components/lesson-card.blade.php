@props([
    'lesson' => null,
    'title' => null,
    'description' => null,
    'difficulty' => null,
    'duration' => null,
    'icon' => null,
    'progress' => 0,
    'thumbnail' => null
])

@php
    // Support both lesson object and individual props
    $lessonTitle = $lesson?->title ?? $title ?? 'Untitled Lesson';
    $lessonDescription = $lesson?->description ?? $description ?? '';
    $lessonDifficulty = $lesson?->difficulty ?? $difficulty ?? null;
    $lessonDuration = $lesson?->duration ?? $duration ?? null;
    $lessonIcon = $icon ?? '🎮';
    $lessonId = $lesson?->id ?? '#';
@endphp

<div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
    {{-- Thumbnail/Hero Image --}}
    <div class="relative h-48 bg-gradient-to-br from-orange-400 via-pink-500 to-purple-600 overflow-hidden">
        @if($thumbnail)
            <img src="{{ $thumbnail }}" alt="{{ $lessonTitle }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
        @else
            {{-- Default Icon or Scratch Cat Illustration --}}
            <div class="absolute inset-0 flex items-center justify-center">
                @if($icon)
                    <div class="text-8xl">{{ $lessonIcon }}</div>
                @else
                    <svg class="w-32 h-32 text-white opacity-80" viewBox="0 0 100 100" fill="currentColor">
                        <circle cx="50" cy="50" r="40"/>
                        <circle cx="40" cy="45" r="5" fill="#000"/>
                        <circle cx="60" cy="45" r="5" fill="#000"/>
                        <path d="M 35 60 Q 50 70 65 60" stroke="#000" stroke-width="3" fill="none"/>
                    </svg>
                @endif
            </div>
        @endif
        
        {{-- Progress Badge --}}
        @if($progress > 0)
            <div class="absolute top-4 right-4 bg-white dark:bg-gray-800 rounded-full px-3 py-1 shadow-lg">
                <span class="text-sm font-bold text-purple-600 dark:text-purple-400">{{ $progress }}%</span>
            </div>
        @endif

        {{-- Status Badge --}}
        @if($progress >= 100)
            <div class="absolute top-4 left-4 bg-green-500 text-white rounded-full px-3 py-1 shadow-lg flex items-center gap-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-xs font-bold">Complete</span>
            </div>
        @elseif($progress > 0)
            <div class="absolute top-4 left-4 bg-blue-500 text-white rounded-full px-3 py-1 shadow-lg flex items-center gap-1">
                <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                </svg>
                <span class="text-xs font-bold">In Progress</span>
            </div>
        @endif
    </div>

    {{-- Content --}}
    <div class="p-6">
        {{-- Title --}}
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
            {{ $lessonTitle }}
        </h3>

        {{-- Description --}}
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
            {{ $lessonDescription ?: 'Learn Scratch programming with interactive lessons and projects.' }}
        </p>

        {{-- Meta Info --}}
        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 mb-4">
            @if($lessonDuration)
                <div class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $lessonDuration }} min</span>
                </div>
            @endif
            
            @if($lessonDifficulty)
                <div class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>{{ ucfirst($lessonDifficulty) }}</span>
                </div>
            @endif
        </div>

        {{-- Progress Bar --}}
        @if($progress > 0)
            <div class="mb-4">
                <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                    <span>Progress</span>
                    <span class="font-semibold">{{ $progress }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-full rounded-full transition-all duration-500" 
                         style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @endif

        {{-- Action Button --}}
        @if($lesson)
            <a href="{{ route('lessons.view', $lessonId) }}" 
               class="block w-full text-center px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-xl transition-all duration-300 transform group-hover:scale-105 shadow-lg">
                @if($progress >= 100)
                    Review Lesson
                @elseif($progress > 0)
                    Continue Learning
                @else
                    Start Lesson
                @endif
            </a>
        @else
            <div class="block w-full text-center px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl shadow-lg">
                @if($progress >= 100)
                    Review Lesson
                @elseif($progress > 0)
                    Continue Learning
                @else
                    Start Lesson
                @endif
            </div>
        @endif
    </div>
</div>
