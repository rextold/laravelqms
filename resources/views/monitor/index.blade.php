<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Display - {{ $settings->company_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: {{ $settings->primary_color }};
            --secondary-color: {{ $settings->secondary_color }};
            --accent-color: {{ $settings->accent_color }};
            --text-color: {{ $settings->text_color }};
        }
        body { 
            overflow: hidden; 
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }
        .full-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            grid-template-rows: 60px 1fr 35px;
            height: 100vh;
            gap: 0;
        }
        .header-section {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 0.5rem 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            z-index: 10;
            display: flex;
            align-items: center;
        }
        .video-section {
            grid-column: 1;
            grid-row: 2;
            background: #000;
            position: relative;
            overflow: hidden;
        }
        .counters-section {
            grid-column: 2;
            grid-row: 2;
            background: linear-gradient(180deg, #1e293b, #0f172a);
            overflow: hidden;
            padding: 0.75rem;
            display: flex;
            flex-direction: column;
        }
        .marquee-section {
            grid-column: 1 / -1;
            grid-row: 3;
        }
        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
            object-fit: cover;
        }
        .marquee {
            width: 100%;
            overflow: hidden;
            white-space: nowrap;
            box-sizing: border-box;
        }
        .marquee span {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 25s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translate(0, 0); }
            100% { transform: translate(-100%, 0); }
        }
        @keyframes flash {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        @keyframes pulse-scale {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .serving-now {
            animation: flash 1s ease-in-out 3, pulse-scale 0.5s ease-in-out;
            background: linear-gradient(135deg, var(--accent-color), #059669) !important;
        }

        .waiting-list {
            max-height: none;
            overflow: hidden;
        }
        .counter-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .counter-card.serving {
            border-color: var(--accent-color);
            box-shadow: 0 0 20px var(--accent-color);
        }
        .counters-grid {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .counters-grid::-webkit-scrollbar {
            width: 6px;
        }
        .counters-grid::-webkit-scrollbar-track {
            background: #1e293b;
        }
        .counters-grid::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 3px;
        }
        .notification-banner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--text-color);
            padding: 3rem 5rem;
            border-radius: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            z-index: 100;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .notification-banner.show {
            opacity: 1;
            animation: pulse-scale 0.5s ease-in-out 3;
        }
    </style>
</head>
<body>
    @php
        $formatQueueDisplay = function ($queueNumber, $counterNumber) {
            if (empty($queueNumber)) {
                return null;
            }

            $suffix = $queueNumber;
            $parts = explode('-', $queueNumber);
            if (count($parts) > 1) {
                $last = end($parts);
                $suffix = $last ?: $queueNumber;
            }

            return $counterNumber . '-' . $suffix;
        };
    @endphp

    <div class="full-layout">
        <!-- Header Section -->
        <div class="header-section" style="position: relative;">
            <div class="flex items-center space-x-3">
                @if($settings->logo_url)
                    <img src="{{ $settings->logo_url }}" alt="{{ $settings->company_name }}" class="h-10">
                @endif
                <div>
                    <h1 class="text-2xl font-bold" style="color: var(--text-color)">{{ $settings->company_name }}</h1>
                    <p class="text-xs opacity-90" style="color: var(--text-color)">Queue Management System</p>
                </div>
            </div>
            <div class="text-right" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); padding-right: 1.5rem;">
                <div class="text-xl font-bold" style="color: var(--text-color)" id="currentTime"></div>
                <div class="text-xs opacity-90" style="color: var(--text-color)" id="currentDate"></div>
            </div>
        </div>

        <!-- Video Entertainment Section -->
        <div class="video-section">
            @if($videos->count() > 0)
                @php
                    $currentVideo = $videos->first();
                @endphp
                
                @if($currentVideo->isYoutube())
                    <iframe 
                        id="displayVideo" 
                        src="{{ $currentVideo->youtube_embed_url }}" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                @else
                    <video id="displayVideo" autoplay loop playsinline>
                        <source src="{{ asset('storage/' . $currentVideo->file_path) }}" type="video/mp4">
                    </video>
                    <!-- Play button overlay (shown if autoplay blocked) -->
                    <div id="playOverlay" class="absolute inset-0 bg-black bg-opacity-70 flex items-center justify-center cursor-pointer" style="display: none;">
                        <div class="text-center">
                            <i class="fas fa-play-circle text-white text-8xl mb-4 opacity-80 hover:opacity-100 transition-opacity"></i>
                            <p class="text-white text-2xl">Click to play video</p>
                        </div>
                    </div>
                @endif
                
                <div class="absolute bottom-4 left-4 bg-black bg-opacity-50 text-white px-4 py-2 rounded-lg">
                    <i class="fas {{ $currentVideo->isYoutube() ? 'fa-youtube' : 'fa-film' }} mr-2"></i>
                    {{ $currentVideo->isYoutube() ? 'YouTube' : 'Entertainment' }}
                </div>
            @else
                <div class="flex items-center justify-center h-full text-white text-2xl">
                    <div class="text-center">
                        <i class="fas fa-video text-6xl mb-4 opacity-50"></i>
                        <p class="opacity-50">No videos available</p>
                    </div>
                </div>
            @endif
            
            <!-- Notification Banner -->
            <div id="notificationBanner" class="notification-banner">
                <div class="text-center">
                    <i class="fas fa-bell text-6xl mb-4"></i>
                    <div class="text-5xl font-bold mb-2" id="notificationQueue"></div>
                    <div class="text-2xl mb-2">Please proceed to</div>
                    <div class="text-4xl font-bold" id="notificationCounter"></div>
                </div>
            </div>
        </div>

        <!-- Counters Section -->
        <div class="counters-section">
            <div class="mb-2 text-center flex-shrink-0">
                <h2 class="text-lg font-bold text-white">
                    <i class="fas fa-desktop mr-2"></i>Now Serving
                </h2>
            </div>

            <div class="counters-grid space-y-2" id="countersGrid">
                @foreach($onlineCounters as $counter)
                    <div class="counter-card bg-gradient-to-br from-slate-800 to-slate-900 rounded-lg p-2.5 shadow-xl" data-counter-id="{{ $counter->id }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="text-white text-lg font-bold rounded-full w-10 h-10 flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color))">
                                    {{ $counter->counter_number }}
                                </div>
                                <div>
                                    <div class="text-white font-bold text-sm">{{ $counter->display_name }}</div>
                                    <div class="text-gray-400 text-xs">{{ $counter->short_description }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 bg-slate-950 rounded-lg p-2">
                            <div class="text-xs text-gray-400 mb-1 font-semibold uppercase">Currently Serving</div>
                            <div class="text-2xl font-bold text-white current-queue" data-queue="{{ isset($counterQueues[$counter->id]) && $counterQueues[$counter->id] ? $counterQueues[$counter->id]->queue_number : '' }}">
                                @if(isset($counterQueues[$counter->id]) && $counterQueues[$counter->id])
                                    {{ $formatQueueDisplay($counterQueues[$counter->id]->queue_number, $counter->counter_number) }}
                                @else
                                    <span class="opacity-30">---</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Waiting Queue Section -->
            <div class="mt-2 bg-gradient-to-br from-slate-800 to-slate-900 rounded-lg p-2.5 shadow-xl flex-shrink-0">
                <h3 class="text-sm font-bold text-white mb-2 flex items-center">
                    <i class="fas fa-clock mr-2" style="color: var(--accent-color)"></i>
                    Waiting Queue
                </h3>
                <div class="waiting-list space-y-1" id="waitingQueue">
                    <!-- Populated dynamically via JavaScript -->
                </div>
            </div>
        </div>

        <!-- Marquee Section -->
        @if($marquee)
        <div class="marquee-section bg-yellow-400 text-gray-900 py-1.5 shadow-inner">
            <div class="marquee">
                <span class="text-base font-bold">
                    <i class="fas fa-bullhorn mr-3"></i>{{ $marquee->text }}
                    <span class="mx-8">•</span>
                    {{ $marquee->text }}
                    <span class="mx-8">•</span>
                    {{ $marquee->text }}
                </span>
            </div>
        </div>
        @endif
    </div>

    <!-- Audio Elements -->
    <audio id="bellSound" preload="auto">
        @if($videoControl->bell_sound_path)
            <source src="{{ asset('storage/' . $videoControl->bell_sound_path) }}" type="audio/{{ pathinfo($videoControl->bell_sound_path, PATHINFO_EXTENSION) === 'mp3' ? 'mpeg' : pathinfo($videoControl->bell_sound_path, PATHINFO_EXTENSION) }}">
        @else
            <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFA==" type="audio/wav">
        @endif
    </audio>

    <script>
    // Initialize
    const bellSound = document.getElementById('bellSound');
    const notificationBanner = document.getElementById('notificationBanner');
    const video = document.getElementById('displayVideo');
    const playOverlay = document.getElementById('playOverlay');
    let videoControl = {!! json_encode($videoControl) !!};
    let previousQueues = {};
    let lastNotified = {};
    let lastRecall = {};
    
    // Check if video is YouTube iframe or HTML5 video
    const isYouTube = video && video.tagName === 'IFRAME';
    
    // Initialize previous queues
    document.querySelectorAll('.current-queue').forEach(el => {
        const counterId = el.closest('[data-counter-id]').getAttribute('data-counter-id');
        previousQueues[counterId] = el.getAttribute('data-queue');
    });

    // Format queue number as {counter_number}-{sequence}, e.g., 2-0001
    function formatDisplayQueue(queueNumber, counterNumber) {
        if (!queueNumber) return '---';
        const parts = String(queueNumber).split('-');
        const suffix = parts.length ? (parts[parts.length - 1] || queueNumber) : queueNumber;
        return `${counterNumber}-${suffix}`;
    }

    // Video Control and Auto-play (only for HTML5 videos, YouTube handles autoplay via embed URL)
    if (video && !isYouTube) {
        // Set volume
        video.volume = (videoControl.volume || 50) / 100;
        
        // Force video to play
        const playVideo = () => {
            if (videoControl.is_playing) {
                // Try playing with sound first
                video.muted = false;
                video.play().then(() => {
                    console.log('Video playing successfully with sound');
                    if (playOverlay) playOverlay.style.display = 'none';
                }).catch(error => {
                    console.log('Autoplay with sound blocked, trying muted:', error);
                    // If blocked, try muted autoplay
                    video.muted = true;
                    video.play().then(() => {
                        console.log('Video playing muted');
                        if (playOverlay) {
                            playOverlay.style.display = 'flex';
                            playOverlay.addEventListener('click', () => {
                                video.muted = false;
                                video.play().then(() => {
                                    playOverlay.style.display = 'none';
                                }).catch(err => {
                                    console.log('Play failed:', err);
                                });
                            }, { once: true });
                        }
                    }).catch(err => {
                        console.log('Muted autoplay also failed:', err);
                    });
                });
            } else {
                video.pause();
            }
        };
        
        // Try to play immediately
        playVideo();
        
        // Ensure video loops
        video.addEventListener('ended', () => {
            if (videoControl.is_playing) {
                video.currentTime = 0;
                video.play().catch(() => {});
            }
        });
        
        // Hide play overlay when video starts playing
        video.addEventListener('play', () => {
            if (playOverlay) playOverlay.style.display = 'none';
        });
    }

    // Update time display
    function updateTime() {
        const now = new Date();
        document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
    updateTime();
    setInterval(updateTime, 1000);

    // Play notification
    function playNotification(queueNumber, counterName) {
        // Play bell sound 3 times with configured volume
        const bellVolume = (videoControl.bell_volume || 100) / 100;
        bellSound.volume = bellVolume;
        let playCount = 0;
        function playBell() {
            if (playCount < 3) {
                bellSound.currentTime = 0;
                bellSound.play().catch(err => {
                    console.log('Bell sound play failed:', err);
                });
                playCount++;
                setTimeout(playBell, 800);
            }
        }
        playBell();
        
        // Try to unmute video after user interaction
        if (video && !isYouTube && video.muted) {
            video.muted = false;
        }

        // Show notification banner
        document.getElementById('notificationQueue').textContent = queueNumber;
        document.getElementById('notificationCounter').textContent = counterName;
        notificationBanner.classList.add('show');
        
        setTimeout(() => {
            notificationBanner.classList.remove('show');
        }, 5000);
    }

    // Auto-refresh data every 2 seconds for real-time updates (reduced from 1s to improve performance)
    setInterval(updateDisplay, 2000);

    function updateDisplay() {
        fetch('{{ route('monitor.data', ['company_code' => $companyCode]) }}')
            .then(response => response.json())
            .then(data => {
                // Update video control (only for HTML5 videos, not YouTube)
                if (video && !isYouTube && data.video_control) {
                    const newVolume = (data.video_control.volume || 50) / 100;
                    if (Math.abs(video.volume - newVolume) > 0.01) {
                        video.volume = newVolume;
                    }
                    
                    if (data.video_control.is_playing && video.paused) {
                        video.play().catch(error => {
                            console.log('Error playing video:', error);
                        });
                    } else if (!data.video_control.is_playing && !video.paused) {
                        video.pause();
                    }
                }

                // Track currently displayed counter IDs
                const currentCounterIds = new Set(
                    Array.from(document.querySelectorAll('[data-counter-id]')).map(el => 
                        parseInt(el.getAttribute('data-counter-id'))
                    )
                );
                
                // Track new counter IDs from API
                const newCounterIds = new Set(data.counters.map(item => item.counter.id));

                // Remove counters that went offline (no longer in API response)
                currentCounterIds.forEach(id => {
                    if (!newCounterIds.has(id)) {
                        const counterElement = document.querySelector(`[data-counter-id="${id}"]`);
                        if (counterElement) {
                            // Animate removal
                            counterElement.style.transition = 'all 0.5s ease-out';
                            counterElement.style.opacity = '0';
                            counterElement.style.transform = 'scale(0.8)';
                            setTimeout(() => {
                                if (counterElement.parentNode) {
                                    counterElement.remove();
                                }
                            }, 500);
                            
                            // Clean up tracking
                            delete previousQueues[id];
                            delete lastNotified[id];
                            delete lastRecall[id];
                        }
                    }
                });

                // Add new counters that came online or update existing ones
                data.counters.forEach(item => {
                    const counterId = item.counter.id;
                    let counterCard = document.querySelector(`[data-counter-id="${counterId}"]`);
                    
                    if (!counterCard) {
                        // Counter came online - add it
                        counterCard = document.createElement('div');
                        counterCard.className = 'counter-card bg-gradient-to-br from-slate-800 to-slate-900 rounded-lg p-2.5 shadow-xl';
                        counterCard.setAttribute('data-counter-id', counterId);
                        counterCard.style.opacity = '0';
                        counterCard.style.transform = 'scale(0.8)';
                        counterCard.style.transition = 'all 0.5s ease-out';
                        
                        counterCard.innerHTML = `
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="text-white text-lg font-bold rounded-full w-10 h-10 flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color))">
                                        ${item.counter.counter_number}
                                    </div>
                                    <div>
                                        <div class="text-white font-bold text-sm">${item.counter.display_name}</div>
                                        <div class="text-gray-400 text-xs">${item.counter.short_description}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 bg-slate-950 rounded-lg p-2">
                                <div class="text-xs text-gray-400 mb-1 font-semibold uppercase">Currently Serving</div>
                                <div class="text-2xl font-bold text-white current-queue" data-queue="">
                                    <span class="opacity-30">---</span>
                                </div>
                            </div>
                        `;
                        
                        document.getElementById('countersGrid').appendChild(counterCard);
                        
                        // Animate in
                        setTimeout(() => {
                            counterCard.style.opacity = '1';
                            counterCard.style.transform = 'scale(1)';
                        }, 10);
                        
                        previousQueues[counterId] = '';
                    }
                    
                    // Update queue for this counter (both new and existing)
                    const queueElement = counterCard.querySelector('.current-queue');
                    const newQueue = item.queue ? item.queue.queue_number : '';
                    const oldQueue = previousQueues[counterId] || '';

                    // Check if queue changed
                    if (newQueue && newQueue !== oldQueue) {
                        const displayQueue = formatDisplayQueue(newQueue, item.counter.counter_number);
                        
                        // Update queue number
                        queueElement.textContent = displayQueue;
                        queueElement.setAttribute('data-queue', newQueue);
                        
                        // Add serving animation
                        counterCard.classList.add('serving');
                        queueElement.closest('.bg-slate-950').classList.add('serving-now');
                        
                        // Play notification
                        playNotification(displayQueue, item.counter.display_name);
                        
                        // Remove animation after 3 seconds
                        setTimeout(() => {
                            counterCard.classList.remove('serving');
                            queueElement.closest('.bg-slate-950').classList.remove('serving-now');
                        }, 3000);
                        
                        // Update previous queue
                        previousQueues[counterId] = newQueue;
                    } else if (!newQueue && oldQueue) {
                        // Queue cleared
                        queueElement.innerHTML = '<span class="opacity-30">---</span>';
                        queueElement.setAttribute('data-queue', '');
                        previousQueues[counterId] = '';
                    }

                    // Blink on notify (without changing queue)
                    if (item.queue && item.queue.notified_at) {
                        const notifyKey = item.queue.id;
                        const notifiedAt = item.queue.notified_at;
                        if (lastNotified[notifyKey] !== notifiedAt) {
                            lastNotified[notifyKey] = notifiedAt;
                            counterCard.classList.add('serving');
                            const servingPanel = queueElement.closest('.bg-slate-950');
                            if (servingPanel) servingPanel.classList.add('serving-now');
                            setTimeout(() => {
                                counterCard.classList.remove('serving');
                                if (servingPanel) servingPanel.classList.remove('serving-now');
                            }, 3000);
                        }
                    }

                    // Play bell on recent recall
                    if (item.queue && item.recent_recall) {
                        const recallKey = item.queue.id;
                        const recallStamp = item.queue.notified_at || new Date().toISOString();
                        if (lastRecall[recallKey] !== recallStamp) {
                            lastRecall[recallKey] = recallStamp;
                            const displayQueue = formatDisplayQueue(item.queue.queue_number, item.counter.counter_number);
                            playNotification(displayQueue, item.counter.display_name);
                        }
                    }
                });

                // Update waiting queue in real-time
                if (data.waiting_queues) {
                    const waitingQueueContainer = document.getElementById('waitingQueue');
                    
                    if (data.waiting_queues.length === 0) {
                        waitingQueueContainer.innerHTML = `
                            <div class="text-gray-500 text-center py-2 text-xs">
                                <i class="fas fa-check-circle text-lg mb-1"></i>
                                <p>No waiting queues</p>
                            </div>
                        `;
                    } else {
                        waitingQueueContainer.innerHTML = data.waiting_queues.map(group => {
                            const queuesHtml = (group.queues || []).map(q => {
                                const displayQueue = formatDisplayQueue(q.queue_number, group.counter_number);
                                return `<div class="bg-slate-900 rounded px-2 py-1 text-white text-sm font-bold">${displayQueue}</div>`;
                            }).join('');

                            return `
                                <div class="bg-slate-950 rounded p-2 mb-2">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="text-gray-300 text-xs font-semibold">Counter ${group.counter_number}</div>
                                        <div class="text-gray-500 text-2xs">${group.display_name || ''}</div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        ${queuesHtml || '<span class="text-gray-500 text-xs">No queues</span>'}
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                }

                // Update marquee if changed
                if (data.marquee) {
                    const marqueeSpan = document.querySelector('.marquee span');
                    if (marqueeSpan) {
                        const currentText = marqueeSpan.textContent.split('•')[0].trim();
                        const newText = data.marquee.text;
                        if (currentText !== newText) {
                            marqueeSpan.innerHTML = `<i class="fas fa-bullhorn mr-3"></i>${newText}<span class="mx-8">•</span>${newText}<span class="mx-8">•</span>${newText}`;
                        }
                        const newSpeed = (100 - (data.marquee.speed || 50)) / 2;
                        if (marqueeSpan.style.animationDuration !== `${newSpeed}s`) {
                            marqueeSpan.style.animationDuration = `${newSpeed}s`;
                        }
                    }
                }
            })
            .catch(error => console.error('Error updating display:', error));
    }

    // Initial load
    updateDisplay();
    </script>
</body>
</html>
