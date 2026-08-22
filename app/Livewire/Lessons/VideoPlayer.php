<?php

namespace App\Livewire\Lessons;

use App\Models\Lesson;
use App\Models\VideoProgress;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VideoPlayer extends Component
{
    public Lesson $lesson;
    public $videoProgress = 0;
    public $videoWatchedSeconds = 0;
    public $isVideoCompleted = false;
    public $isLessonCompleted = false;

    public function updateVideoProgress($watchedSeconds, $duration, $isCompleted = false)
    {
        if (!$this->lesson->video_url && $this->lesson->lesson_type !== 'video') {
            return;
        }

        $progressPercentage = $duration > 0 ? round(($watchedSeconds / $duration) * 100, 2) : 0;
        
        $videoProgress = VideoProgress::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'lesson_id' => $this->lesson->id,
            ],
            [
                'video_url' => $this->lesson->video_url,
                'duration_seconds' => $duration,
                'watched_seconds' => $watchedSeconds,
                'progress_percentage' => $progressPercentage,
                'last_position_seconds' => $watchedSeconds,
                'is_completed' => $isCompleted || $progressPercentage >= 90,
                'last_watched_at' => now(),
            ]
        );
        
        $videoProgress->increment('watch_count');
        $videoProgress->refresh();

        $this->videoProgress = $videoProgress->progress_percentage;
        $this->videoWatchedSeconds = $videoProgress->watched_seconds;
        $this->isVideoCompleted = $videoProgress->is_completed;

        $this->dispatch('lesson-video-progress', progress: $this->videoProgress, watchedSeconds: $this->videoWatchedSeconds, isCompleted: $this->isVideoCompleted);

        if ($videoProgress->is_completed && !$this->isLessonCompleted) {
            $this->dispatch('lesson-video-completed');
        }
    }

    public function render()
    {
        return view('livewire.lessons.video-player');
    }
}

