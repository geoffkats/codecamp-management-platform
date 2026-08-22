<div>
    @if(($lesson->video_url || $lesson->lesson_type === 'video') && $lesson->video_url)
        @php
            $videoUrl = $lesson->video_url;
            $isYouTube = false;
            $isVimeo = false;
            $embedUrl = '';
            $videoId = '';
            
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoUrl, $matches)) {
                $isYouTube = true;
                $videoId = $matches[1];
                $embedUrl = 'https://www.youtube.com/embed/' . $videoId . '?enablejsapi=1&origin=' . urlencode(request()->getSchemeAndHttpHost()) . '&rel=0';
                if ($videoWatchedSeconds > 0) {
                    $embedUrl .= '&start=' . round($videoWatchedSeconds);
                }
            } elseif (preg_match('/vimeo\.com\/(?:.*\/)?(\d+)/', $videoUrl, $matches)) {
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
                                    
                                    setInterval(function() {
                                        if (ytPlayer && ytPlayer.getPlayerState() === YT.PlayerState.PLAYING) {
                                            const currentTime = Math.floor(ytPlayer.getCurrentTime());
                                            const duration = Math.floor(ytPlayer.getDuration());
                                            if (duration > 0) {
                                                @this.updateVideoProgress(currentTime, duration, false);
                                            }
                                        }
                                    }, 5000);
                                }
                            @elseif($isVimeo)
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
                                        }, 1000);
                                    }
                                }

                                function handleVideoEnded() {
                                    @this.updateVideoProgress(
                                        Math.floor(video.duration),
                                        Math.floor(video.duration),
                                        true
                                    );
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
</div>
