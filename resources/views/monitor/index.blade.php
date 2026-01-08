<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Display</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { overflow: hidden; }
        .marquee {
            width: 100%;
            overflow: hidden;
            white-space: nowrap;
            box-sizing: border-box;
        }
        .marquee span {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 20s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translate(0, 0); }
            100% { transform: translate(-100%, 0); }
        }
        .video-container {
            position: relative;
            width: 100%;
            height: 50vh;
            background: #000;
        }
        video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    </style>
</head>
<body class="bg-gray-900 text-white">
    <!-- Header -->
    <div class="bg-blue-600 py-4">
        <h1 class="text-4xl font-bold text-center">Queue Management System</h1>
    </div>

    <!-- Video Section -->
    @if($videos->count() > 0)
    <div class="video-container">
        <video id="displayVideo" autoplay muted loop>
            <source src="{{ asset('storage/' . $videos->first()->file_path) }}" type="video/mp4">
        </video>
    </div>
    @endif

    <!-- Counters Display -->
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="countersGrid">
            @foreach($onlineCounters as $counter)
                <div class="bg-white text-gray-900 rounded-lg p-6 shadow-lg" data-counter-id="{{ $counter->id }}">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 mb-2">Counter {{ $counter->counter_number }}</div>
                        <div class="text-lg font-semibold mb-2">{{ $counter->display_name }}</div>
                        <div class="text-sm text-gray-600 mb-4">{{ $counter->short_description }}</div>
                        <div class="bg-blue-100 p-4 rounded">
                            <div class="text-sm text-gray-600 mb-1">Now Serving</div>
                            <div class="text-4xl font-bold text-blue-600 current-queue">
                                @if(isset($counterQueues[$counter->id]) && $counterQueues[$counter->id])
                                    {{ $counterQueues[$counter->id]->queue_number }}
                                @else
                                    ---
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Marquee -->
    @if($marquee)
    <div class="bg-yellow-400 text-gray-900 py-3 fixed bottom-0 w-full">
        <div class="marquee">
            <span class="text-2xl font-bold">{{ $marquee->text }}</span>
        </div>
    </div>
    @endif

    <script>
    // Video Control
    const video = document.getElementById('displayVideo');
    let videoControl = {!! json_encode($videoControl) !!};
    
    if (video) {
        video.volume = (videoControl.volume || 50) / 100;
        if (videoControl.is_playing) {
            video.play();
        } else {
            video.pause();
        }
    }

    // Auto-refresh data every 5 seconds
    setInterval(updateDisplay, 5000);

    function updateDisplay() {
        fetch('{{ route('monitor.data') }}')
            .then(response => response.json())
            .then(data => {
                // Update video control
                if (video && data.video_control) {
                    video.volume = (data.video_control.volume || 50) / 100;
                    if (data.video_control.is_playing && video.paused) {
                        video.play();
                    } else if (!data.video_control.is_playing && !video.paused) {
                        video.pause();
                    }
                }

                // Update counters
                data.counters.forEach(item => {
                    const counterDiv = document.querySelector(`[data-counter-id="${item.counter.id}"]`);
                    if (counterDiv) {
                        const queueDisplay = counterDiv.querySelector('.current-queue');
                        const newQueue = item.queue ? item.queue.queue_number : '---';
                        
                        if (queueDisplay.textContent.trim() !== newQueue) {
                            queueDisplay.textContent = newQueue;
                            // Flash animation
                            counterDiv.classList.add('animate-pulse');
                            setTimeout(() => counterDiv.classList.remove('animate-pulse'), 2000);
                        }
                    }
                });
            });
    }

    // Update marquee animation speed
    @if($marquee)
    const marqueeSpeed = {{ $marquee->speed ?? 50 }};
    const marqueeElement = document.querySelector('.marquee span');
    if (marqueeElement) {
        marqueeElement.style.animationDuration = (100 - marqueeSpeed) / 2 + 's';
    }
    @endif
    </script>
</body>
</html>
