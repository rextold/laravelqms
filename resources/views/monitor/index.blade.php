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
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 py-6 shadow-2xl">
        <h1 class="text-5xl font-bold text-center text-white drop-shadow-lg animate-pulse">Queue Management System</h1>
        <p class="text-center text-white text-xl mt-2 opacity-90"><i class="fas fa-broadcast-tower"></i> Live Display</p>
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
    <div class="container mx-auto px-6 py-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6" id="countersGrid">
            @foreach($onlineCounters as $counter)
                <div class="bg-gradient-to-br from-white to-gray-100 text-gray-900 rounded-2xl p-6 shadow-2xl transform hover:scale-105 transition-all border-4 border-blue-200" data-counter-id="{{ $counter->id }}">
                    <div class="text-center">
                        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white text-4xl font-bold rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-3 shadow-lg">
                            {{ $counter->counter_number }}
                        </div>
                        <div class="text-xl font-bold mb-2 text-gray-800">{{ $counter->display_name }}</div>
                        <div class="text-sm text-gray-600 mb-4">{{ $counter->short_description }}</div>
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6 rounded-xl shadow-inner">
                            <div class="text-xs text-blue-100 mb-2 font-semibold">NOW SERVING</div>
                            <div class="text-5xl font-bold text-white current-queue drop-shadow-lg">
                                @if(isset($counterQueues[$counter->id]) && $counterQueues[$counter->id])
                                    {{ $counterQueues[$counter->id]->queue_number }}
                                @else
                                    <span class="opacity-50">---</span>
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
                    const newVolume = (data.video_control.volume || 50) / 100;
                    if (Math.abs(video.volume - newVolume) > 0.01) {
                        video.volume = newVolume;
                    }
                    
                    if (data.video_control.is_playing && video.paused) {
                        video.play().catch(() => {});
                    } else if (!data.video_control.is_playing && !video.paused) {
                        video.pause();
                    }
                }

                // Track existing counter IDs on screen
                const existingCounterIds = new Set(
                    Array.from(document.querySelectorAll('[data-counter-id]'))
                        .map(el => el.getAttribute('data-counter-id'))
                );

                // Track new counter IDs from data
                const newCounterIds = new Set(data.counters.map(item => String(item.counter.id)));

                // Remove counters that went offline
                existingCounterIds.forEach(id => {
                    if (!newCounterIds.has(id)) {
                        const counterDiv = document.querySelector(`[data-counter-id="${id}"]`);
                        if (counterDiv) {
                            counterDiv.style.transition = 'all 0.5s ease-out';
                            counterDiv.style.opacity = '0';
                            counterDiv.style.transform = 'scale(0.8)';
                            setTimeout(() => counterDiv.remove(), 500);
                        }
                    }
                });

                // Update existing counters and add new ones
                data.counters.forEach(item => {
                    let counterDiv = document.querySelector(`[data-counter-id="${item.counter.id}"]`);
                    
                    // Add new counter if it doesn't exist
                    if (!counterDiv) {
                        const gridContainer = document.getElementById('countersGrid');
                        counterDiv = createCounterElement(item);
                        gridContainer.appendChild(counterDiv);
                        
                        // Fade in animation
                        setTimeout(() => {
                            counterDiv.style.opacity = '1';
                            counterDiv.style.transform = 'scale(1)';
                        }, 10);
                    } else {
                        // Update existing counter queue
                        const queueDisplay = counterDiv.querySelector('.current-queue');
                        const currentText = queueDisplay.textContent.trim().replace(/\s+/g, '');
                        const newQueue = item.queue ? item.queue.queue_number : '---';
                        
                        if (currentText !== newQueue) {
                            // Animate queue change
                            queueDisplay.style.transition = 'all 0.3s ease';
                            queueDisplay.style.transform = 'scale(1.2)';
                            queueDisplay.style.color = '#10b981';
                            
                            setTimeout(() => {
                                if (newQueue === '---') {
                                    queueDisplay.innerHTML = '<span class="opacity-50">---</span>';
                                } else {
                                    queueDisplay.textContent = newQueue;
                                }
                                
                                setTimeout(() => {
                                    queueDisplay.style.transform = 'scale(1)';
                                    queueDisplay.style.color = '';
                                }, 150);
                            }, 150);

                            // Flash border
                            counterDiv.style.borderColor = '#3b82f6';
                            setTimeout(() => {
                                counterDiv.style.borderColor = '';
                            }, 1000);
                        }
                    }
                });

                // Update marquee if changed
                if (data.marquee) {
                    const marqueeElement = document.querySelector('.marquee span');
                    if (marqueeElement && marqueeElement.textContent.trim() !== data.marquee.text.trim()) {
                        marqueeElement.textContent = data.marquee.text;
                        marqueeElement.style.animationDuration = (100 - (data.marquee.speed || 50)) / 2 + 's';
                    }
                }
            })
            .catch(error => {
                console.log('Update error:', error);
            });
    }

    function createCounterElement(item) {
        const div = document.createElement('div');
        div.className = 'bg-gradient-to-br from-white to-gray-100 text-gray-900 rounded-2xl p-6 shadow-2xl transform hover:scale-105 transition-all border-4 border-blue-200';
        div.setAttribute('data-counter-id', item.counter.id);
        div.style.opacity = '0';
        div.style.transform = 'scale(0.8)';
        div.style.transition = 'all 0.5s ease-out';
        
        const queueNumber = item.queue ? item.queue.queue_number : '<span class="opacity-50">---</span>';
        
        div.innerHTML = `
            <div class="text-center">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white text-4xl font-bold rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-3 shadow-lg">
                    ${item.counter.counter_number}
                </div>
                <div class="text-xl font-bold mb-2 text-gray-800">${item.counter.display_name}</div>
                <div class="text-sm text-gray-600 mb-4">${item.counter.short_description || ''}</div>
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6 rounded-xl shadow-inner">
                    <div class="text-xs text-blue-100 mb-2 font-semibold">NOW SERVING</div>
                    <div class="text-5xl font-bold text-white current-queue drop-shadow-lg">
                        ${queueNumber}
                    </div>
                </div>
            </div>
        `;
        
        return div;
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
