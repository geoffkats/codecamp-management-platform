@props([
    'lesson',
    'videoProgress' => 0,
    'videoWatchedSeconds' => 0,
    'isVideoCompleted' => false,
    'isLessonCompleted' => false,
])

{{-- Video Player --}}
<livewire:lessons.video-player
    :lesson="$lesson"
    :video-progress="$videoProgress"
    :video-watched-seconds="$videoWatchedSeconds"
    :is-video-completed="$isVideoCompleted"
    :is-lesson-completed="$isLessonCompleted"
/>

{{-- Scratch Project Embed (Lazy Loaded) --}}
@if($lesson->scratch_project_id && $lesson->lesson_type === 'interactive')
    <x-lazy-section
        placeholder-title="Loading Scratch project..."
        placeholder-tone="orange"
        class="mt-6"
    >
        <x-scratch-embed
            :projectId="$lesson->scratch_project_id"
            :title="$lesson->title"
        />
    </x-lazy-section>
@endif
