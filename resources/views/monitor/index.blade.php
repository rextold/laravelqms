<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Queue Display - {{ $organization->organization_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: {{ $settings->primary_color }};
            --secondary: {{ $settings->secondary_color }};
            --accent: {{ $settings->accent_color }};
            --text: {{ $settings->text_color }};
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            overflow: hidden;
            background: #000;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .monitor-grid {
            display: grid;
            grid-template-columns: 1fr 420px;
            grid-template-rows: 70px 1fr 60px;
            height: 100vh;
            gap: 8px;
            padding: 8px;
            background: #000;
        }
        .header {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            z-index: 100;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .header-logo {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .header-title h1 {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .header-title p {
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
            margin: 0;
        }
        .header-time {
            color: white;
            font-size: 1.4rem;
            font-weight: 600;
        }
        .video-container {
            grid-column: 1;
            grid-row: 2;
            background: #1a1a1a;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
        }
        .video-player {
            width: 100%;
            height: 100%;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
        .no-video {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #666;
        }
        .no-video i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .counters-panel {
            grid-column: 2;
            grid-row: 2;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .counters-header {
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 8px;
            color: white;
            font-weight: 700;
            text-align: center;
            font-size: 1.1rem;
        }
        .counters-list {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding-right: 0.5rem;
        }
        .counters-list::-webkit-scrollbar {
            width: 6px;
        }
        .counters-list::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
        }
        .counters-list::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
        }
        .counters-list::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.3);
        }
        .counter-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1rem;
            color: white;
            transition: all 0.3s ease;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .counter-card:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }
        .counter-card.active {
            border-color: var(--accent);
            background: rgba(16, 185, 129, 0.1);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        .counter-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .counter-name {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.6);
            margin-bottom: 0.5rem;
        }
        .counter-queue {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--accent);
            text-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
            animation: glow 1s ease-in-out infinite;
        }
        @keyframes glow {
            0%, 100% { text-shadow: 0 0 10px rgba(16, 185, 129, 0.5); }
            50% { text-shadow: 0 0 20px rgba(16, 185, 129, 0.8); }
        }
        .counter-queue.idle {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.5);
            animation: none;
            text-shadow: none;
        }
        .marquee-section {
            grid-column: 1 / -1;
            grid-row: 3;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
        }
        .marquee-content {
            color: white;
            font-size: 1.3rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            width: 100%;
            padding: 0 2rem;
            height: 100%;
            display: flex;
            align-items: center;
        }
        .marquee-text {
            display: inline-block;
            animation: slide 30s linear infinite;
            padding-left: 100%;
        }
        @keyframes slide {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }
        .marquee-icon {
            display: inline-block;
            margin: 0 1rem;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="monitor-grid">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="header-logo">
                    <i class="fas fa-tv"></i>
                </div>
                <div class="header-title">
                    <h1>Queue Management System</h1>
                    <p>{{ $organization->organization_name }}</p>
                </div>
            </div>
            <div class="header-time" id="currentTime">00:00:00</div>
        </div>

        <!-- Video Display -->
        <div class="video-container">
            <div class="video-player" id="videoPlayer">
                <div class="no-video">
                    <i class="fas fa-video"></i>
                    <p>No active video</p>
                </div>
            </div>
        </div>

        <!-- Counters Panel -->
        <div class="counters-panel">
            <div class="counters-header">
                <i class="fas fa-headset mr-2"></i>Service Counters
            </div>
            <div class="counters-list" id="countersList">
                <div class="text-center text-gray-400 py-8">
                    <i class="fas fa-spinner animate-spin text-2xl mb-2"></i>
                    <p>Loading counters...</p>
                </div>
            </div>
        </div>

        <!-- Marquee -->
        <div class="marquee-section" id="marqueeSection" style="display: none;">
            <div class="marquee-content">
                <span class="marquee-text" id="marqueeText"></span>
            </div>
        </div>
    </div>

    <script>
        const orgCode = '{{ $companyCode }}';
        let currentPlayingVideo = null;
        let refreshInterval = null;
        let lastDataUpdate = Date.now();
        let videoRotationIndex = 0;

        // Update time with milliseconds for smooth animation
        function updateTime() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        updateTime();
        setInterval(updateTime, 500);

        // Initialize marquee
        function initializeMarquee() {
            const marquee = @json($marquee);
            if (marquee && marquee.is_active && marquee.message) {
                document.getElementById('marqueeText').textContent = marquee.message;
                document.getElementById('marqueeSection').style.display = 'flex';
            }
        }
        initializeMarquee();

        // Fetch and update data with high frequency
        function refreshMonitorData() {
            fetch(`/${orgCode}/monitor/data`)
                .then(response => response.json())
                .then(data => {
                    updateCounters(data.counters);
                    updateVideo(data.video_control);
                    if (data.marquee) {
                        updateMarquee(data.marquee);
                    }
                    lastDataUpdate = Date.now();
                })
                .catch(error => console.error('Refresh failed:', error));
        }

        function updateCounters(counters) {
            const containerList = document.getElementById('countersList');
            
            if (!counters || counters.length === 0) {
                containerList.innerHTML = `
                    <div class="text-center text-gray-400 py-8">
                        <i class="fas fa-inbox text-2xl mb-2"></i>
                        <p>No active counters</p>
                    </div>
                `;
                return;
            }

            // Check if content changed to trigger animations
            const currentCards = document.querySelectorAll('.counter-card');
            const newHTML = counters.map(item => {
                const counter = item.counter;
                const queue = item.queue;
                const isActive = !!queue;
                const queueDisplay = queue ? `#${queue.queue_number}` : 'Idle';

                return `
                    <div class="counter-card ${isActive ? 'active' : ''}" data-counter-id="${counter.id}">
                        <div class="counter-number">Counter ${counter.counter_number}</div>
                        <div class="counter-name">${counter.display_name}</div>
                        <div class="counter-status" style="font-size: 0.9rem; color: rgba(255,255,255,0.7); margin-bottom: 0.5rem;">
                            ${isActive ? '🔴 <strong>Serving</strong>' : '⚪ <strong>Waiting</strong>'}
                        </div>
                        <div class="counter-queue" style="margin-top: 0.5rem;">
                            <strong>${queueDisplay}</strong>
                        </div>
                    </div>
                `;
            }).join('');

            containerList.innerHTML = newHTML;

            // Animate new or updated counters
            document.querySelectorAll('.counter-card.active').forEach(card => {
                card.style.animation = 'none';
                setTimeout(() => {
                    card.style.animation = 'pulse 2s ease-in-out infinite';
                }, 10);
            });
        }

        function updateVideo(videoControl) {
            const player = document.getElementById('videoPlayer');
            
            if (!videoControl || !videoControl.is_playing) {
                player.innerHTML = `
                    <div class="no-video">
                        <i class="fas fa-pause-circle"></i>
                        <p>Display paused</p>
                    </div>
                `;
                return;
            }

            const videos = @json($videos);
            if (!videos || videos.length === 0) {
                player.innerHTML = `
                    <div class="no-video">
                        <i class="fas fa-video"></i>
                        <p>No videos available</p>
                    </div>
                `;
                return;
            }

            // Rotate through videos every 10 seconds
            videoRotationIndex = Math.floor(Date.now() / 10000) % videos.length;
            const video = videos[videoRotationIndex];

            if (video.is_youtube) {
                const newHTML = `
                    <iframe src="${video.youtube_embed_url}?autoplay=1&mute=1&loop=1&modestbranding=1&rel=0" 
                            allow="autoplay; encrypted-media" 
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
                `;
                if (!player.querySelector('iframe') || player.querySelector('iframe').src !== video.youtube_embed_url) {
                    player.innerHTML = newHTML;
                }
            } else if (video.file_path) {
                if (!player.querySelector('video')) {
                    player.innerHTML = `
                        <video autoplay muted loop style="width: 100%; height: 100%; object-fit: cover;">
                            <source src="/storage/${video.file_path}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    `;
                }
            } else {
                player.innerHTML = `
                    <div class="no-video">
                        <i class="fas fa-video"></i>
                        <p>Video not available</p>
                    </div>
                `;
            }
        }

        function updateMarquee(marquee) {
            const marqueeSection = document.getElementById('marqueeSection');
            const marqueeText = document.getElementById('marqueeText');
            
            if (marquee && marquee.is_active && marquee.message) {
                // Update text if changed
                if (marqueeText.textContent !== marquee.message) {
                    marqueeText.textContent = marquee.message;
                    // Restart animation
                    marqueeText.style.animation = 'none';
                    setTimeout(() => {
                        marqueeText.style.animation = 'slide 30s linear infinite';
                    }, 10);
                }
                marqueeSection.style.display = 'flex';
            } else {
                marqueeSection.style.display = 'none';
            }
        }

        // Add status indicator
        function addStatusIndicator() {
            const header = document.querySelector('.header');
            if (!document.getElementById('statusDot')) {
                const indicator = document.createElement('div');
                indicator.id = 'statusDot';
                indicator.style.cssText = `
                    width: 12px;
                    height: 12px;
                    background: #10b981;
                    border-radius: 50%;
                    animation: statusPulse 2s ease-in-out infinite;
                    position: absolute;
                    right: 2rem;
                    top: 1.5rem;
                `;
                header.style.position = 'relative';
                header.appendChild(indicator);
            }
        }

        // CSS for status indicator
        const styleSheet = document.createElement('style');
        styleSheet.textContent = `
            @keyframes statusPulse {
                0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
                50% { opacity: 0.9; box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            }
        `;
        document.head.appendChild(styleSheet);
        addStatusIndicator();

        // Initial load
        refreshMonitorData();

        // Aggressive real-time refresh: 1 second for counters, 2 seconds for everything
        let counterRefresh = setInterval(refreshMonitorData, 1000);

        // Refresh video and marquee every 3 seconds
        let videoRefresh = setInterval(() => {
            fetch(`/${orgCode}/monitor/data`)
                .then(response => response.json())
                .then(data => {
                    if (data.video_control) updateVideo(data.video_control);
                    if (data.marquee) updateMarquee(data.marquee);
                })
                .catch(error => console.error('Video/Marquee refresh failed:', error));
        }, 3000);

        // Prevent sleep/screensaver and track visibility
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                console.log('Monitor hidden');
            } else {
                console.log('Monitor visible, refreshing...');
                refreshMonitorData();
            }
        });

        // Keep screen active
        setInterval(() => {
            if (document.hidden === false) {
                navigator.wakeLock?.request('screen').catch(() => {});
            }
        }, 5000);
    </script>
</body>
</html>
