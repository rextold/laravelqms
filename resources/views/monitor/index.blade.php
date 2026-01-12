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
        .header-center {
            flex: 1;
            display: flex;
            justify-content: center;
            padding: 0 1rem;
            pointer-events: none;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .header-logo {
            width: 35px;
            height: 35px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            overflow: hidden;
        }
        .header-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 4px;
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
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.25rem;
        }
        .header-date {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.8);
            font-weight: 400;
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
        .section-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            flex: 1;
            min-height: 0;
        }
        .section-title {
            padding: 0.5rem 0.75rem;
            background: rgba(255,255,255,0.05);
            border-left: 3px solid var(--accent);
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-title.serving {
            border-left-color: #10b981;
            color: #10b981;
        }
        .section-title.waiting {
            border-left-color: #f59e0b;
            color: #f59e0b;
        }
        .serving-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex: 1;
            min-height: 0;
        }
        .waiting-list {
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 0;
            max-height: none;
            overflow-x: auto;
            overflow-y: hidden;
            font-size: 2.5rem;
            padding: 1.5rem 0;
        }
        .waiting-list::-webkit-scrollbar {
            width: 4px;
        }
        .waiting-list::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.02);
        }
        .waiting-list::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.15);
            border-radius: 4px;
        }
        .counter-card-small {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 0.75rem;
            color: white;
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        .counter-card-small:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.15);
        }
        .counter-card-small.serving {
            border-color: rgba(16, 185, 129, 0.5);
            background: rgba(16, 185, 129, 0.08);
        }
        .counter-card-small.serving.notify {
            animation: blink 1.5s ease-in-out infinite;
        }
        .queue-badge.callout {
            animation: calloutPulse 1.2s ease-in-out infinite;
            transform-origin: center;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; border-color: rgba(16, 185, 129, 0.8); }
            50% { opacity: 0.5; border-color: rgba(16, 185, 129, 0.3); }
        }
        @keyframes calloutPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 rgba(0,0,0,0); }
            50% { transform: scale(1.12); box-shadow: 0 0 18px rgba(0,0,0,0.6); }
        }

        /* Temporary non-blocking banner to highlight the called queue number */
        .call-overlay {
            display: none;
            pointer-events: none;
        }
        .call-overlay.show {
            display: block;
            animation: overlayFadeIn 180ms ease-out;
        }
        @keyframes overlayFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .call-overlay-card {
            width: min(820px, 52vw);
            border-radius: 14px;
            padding: 0.55rem 0.9rem;
            background: rgba(0,0,0,0.55);
            border: 2px solid var(--accent);
            box-shadow: 0 14px 40px rgba(0,0,0,0.55);
            text-align: center;
            color: #fff;
        }
        .call-overlay-title {
            font-weight: 800;
            letter-spacing: 1px;
            font-size: 0.95rem;
            text-transform: uppercase;
            opacity: 0.95;
        }
        .call-overlay-number {
            margin-top: 0.25rem;
            font-weight: 900;
            font-size: clamp(1.35rem, 2.6vw, 2.05rem);
            line-height: 1;
            text-shadow: 0 8px 22px rgba(0,0,0,0.55);
        }
        .call-overlay-counter {
            margin-top: 0.25rem;
            font-size: 0.9rem;
            font-weight: 700;
            opacity: 0.9;
        }
        .counter-card-small.waiting {
            border-color: rgba(245, 158, 11, 0.3);
            background: rgba(245, 158, 11, 0.05);
        }
        .counter-info {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
        .counter-info-name {
            font-weight: 600;
            color: white;
        }
        .counter-info-queue {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
        }
        .queue-badge {
            background: var(--accent);
            color: #000;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 700;
            font-size: 0.8rem;
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
                <div class="header-logo org-logo" data-org-logo>
                    @if($settings->company_logo)
                        <img src="{{ asset('storage/' . $settings->company_logo) }}" alt="Logo" data-org-logo>
                    @else
                        <i class="fas fa-tv"></i>
                    @endif
                </div>
                <div class="header-title">
                    <h1>Queue Management System</h1>
                    <p data-org-name>{{ $organization->organization_name }}</p>
                </div>
            </div>

            <div class="header-center">
                <!-- Call Banner (high-visibility, non-blocking, inside header) -->
                <div id="callOverlay" class="call-overlay" aria-hidden="true">
                    <div class="call-overlay-card">
                        <div class="call-overlay-title">Now Calling</div>
                        <div id="callOverlayNumber" class="call-overlay-number">---</div>
                        <div id="callOverlayCounter" class="call-overlay-counter">Please proceed to the counter</div>
                    </div>
                </div>
            </div>

            <div class="header-time">
                <div id="currentTime">00:00:00</div>
                <div class="header-date" id="currentDate">Jan 01, 2026</div>
            </div>
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
            <!-- Now Serving Section -->
            <div class="section-group">
                <div class="section-title serving">
                    <i class="fas fa-bell mr-2"></i>Now Serving
                </div>
                <div class="serving-list" id="servingList">
                    <div class="text-center text-gray-400 py-4">
                        <i class="fas fa-hourglass-end text-xl opacity-50"></i>
                        <p class="text-sm mt-1">No active service</p>
                    </div>
                </div>
            </div>

            <!-- Waiting Section -->
            <div class="section-group">
                <div class="section-title waiting">
                    <i class="fas fa-users mr-2"></i>Waiting Queue
                </div>
                <div class="waiting-list" id="waitingList">
                    <div class="text-center text-gray-400 py-4">
                        <i class="fas fa-inbox text-xl opacity-50"></i>
                        <p class="text-sm mt-1">No waiting customers</p>
                    </div>
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

    <!-- Notification Sound -->
    <audio id="notificationSound" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFAtGmN7yvmwhBjiKz/HPgTQGG2W07O2hUhELRZff8r5sIQc4iM/x0H40BhtktOztolIRC0WX3/K+bCEHOIjP8dB+NAYbZLTs7aFSEQtFl9/yvmwhBziIz/HQfjQGG2S07O2hUhELRZff8r5sIQc4h8/x0H40BhtktOztolIRC0SX3vK+bCEHN4fP8c9+MwYaZLPr7aFSEQxEl97yvmwhBzeHz/HPfjMGGmSz6+2hUhEMRJfe8r5sIQc3h8/xz34zBhpks+vtoVIRDESX3vK+ayEHN4bP8c9+MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQ=" type="audio/wav">
    </audio>
    <script>
        const orgCode = '{{ $companyCode }}';
        let currentPlayingVideo = null;
        let refreshInterval = null;
        let lastDataUpdate = Date.now();
        let videoRotationIndex = 0;
        let previousServingState = new Map();
        let overlayHideTimer = null;
        const notificationSound = document.getElementById('notificationSound');

        function showCallOverlay(queueNumber, counterNumber) {
            const overlay = document.getElementById('callOverlay');
            const numberEl = document.getElementById('callOverlayNumber');
            const counterEl = document.getElementById('callOverlayCounter');
            if (!overlay || !numberEl || !counterEl) return;

            numberEl.textContent = queueNumber || '---';
            counterEl.textContent = counterNumber ? `Please proceed to Counter ${counterNumber}` : 'Please proceed to the counter';
            overlay.classList.add('show');
            overlay.setAttribute('aria-hidden', 'false');

            if (overlayHideTimer) clearTimeout(overlayHideTimer);
            overlayHideTimer = setTimeout(() => {
                overlay.classList.remove('show');
                overlay.setAttribute('aria-hidden', 'true');
            }, 6000);
        }

        // Update time and date
        function updateTime() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', {
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            
            // Update date
            document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', {
                month: 'short',
                day: '2-digit',
                year: 'numeric'
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
        let monitorFetchInFlight = false;
        let monitorFetchController = null;
        function refreshMonitorData() {
            // Prevent stacking requests when the server/network is slow
            if (monitorFetchInFlight) return;
            monitorFetchInFlight = true;

            try {
                if (monitorFetchController) {
                    monitorFetchController.abort();
                }
                monitorFetchController = new AbortController();
            } catch (e) {
                monitorFetchController = null;
            }

            fetch(`/${orgCode}/monitor/data`, {
                cache: 'no-store',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
                signal: monitorFetchController ? monitorFetchController.signal : undefined,
            })
                .then(response => response.json())
                .then(data => {
                    updateCounters(data.counters, data.waiting_queues);
                    updateVideo(data.video_control);
                    if (data.marquee) {
                        updateMarquee(data.marquee);
                    }
                    lastDataUpdate = Date.now();
                })
                .catch(error => {
                    if (error && error.name === 'AbortError') return;
                    console.error('Refresh failed:', error);
                })
                .finally(() => {
                    monitorFetchInFlight = false;
                });
        }

        function updateCounters(counters, waitingGroups) {
            const servingList = document.getElementById('servingList');
            const waitingList = document.getElementById('waitingList');
            
            if (!counters || counters.length === 0) {
                servingList.innerHTML = `
                    <div class="text-center text-gray-400 py-4">
                        <i class="fas fa-hourglass-end text-xl opacity-50"></i>
                        <p class="text-sm mt-1">No active service</p>
                    </div>
                `;
                waitingList.innerHTML = `
                    <div class="text-center text-gray-400 py-4">
                        <i class="fas fa-inbox text-xl opacity-50"></i>
                        <p class="text-sm mt-1">No waiting customers</p>
                    </div>
                `;
                return;
            }

            // Separate serving counters
            const servingCounters = counters.filter(item => !!item.queue);

            // Waiting queues are returned as a top-level grouped array: data.waiting_queues
            const groups = Array.isArray(waitingGroups) ? waitingGroups : [];
            const totalWaiting = groups.reduce((sum, g) => {
                const count = (g && Array.isArray(g.queues)) ? g.queues.length : 0;
                return sum + count;
            }, 0);

            // Update Now Serving section with notification detection
            if (servingCounters.length === 0) {
                servingList.innerHTML = `
                    <div class="text-center text-gray-400 py-4">
                        <i class="fas fa-hourglass-end text-xl opacity-50"></i>
                        <p class="text-sm mt-1">No active service</p>
                    </div>
                `;
                previousServingState = new Map();
            } else {
                // Detect alert-worthy changes: new called queue, notify pressed, or recall
                const alerts = [];
                const nextState = new Map();

                servingCounters.forEach(item => {
                    const queue = item.queue;
                    const queueId = queue?.id;
                    if (!queueId) return;

                    const queueKey = String(queueId);

                    const current = {
                        called_at: queue.called_at || null,
                        notified_at: queue.notified_at || null,
                        recent_recall: !!item.recent_recall,
                    };

                    const prev = previousServingState.get(queueKey);
                    const isNew = !prev;
                    const notifyChanged = !!prev && prev.notified_at !== current.notified_at && !!current.notified_at;
                    const recallTriggered = !!current.recent_recall && (!prev || !prev.recent_recall);

                    if (isNew || notifyChanged || recallTriggered) {
                        alerts.push(item);
                    }

                    nextState.set(queueKey, current);
                });

                // Play sound when something new/notify/recall happens (avoid initial load blast)
                if (alerts.length > 0 && previousServingState.size > 0) {
                    try {
                        notificationSound.currentTime = 0;
                        notificationSound.play().catch(e => console.log('Audio play failed:', e));
                    } catch(e) {
                        console.log('Audio error:', e);
                    }
                }

                // Overlay for the first alert item
                if (alerts.length > 0) {
                    const first = alerts[0];
                    showCallOverlay(first.queue?.queue_number, first.counter?.counter_number);
                }
                
                previousServingState = nextState;
                
                const servingHTML = servingCounters.map(item => {
                    const counter = item.counter;
                    const queue = item.queue;
                    const isAlert = alerts.some(a => String(a.queue?.id) === String(queue?.id));
                    return `
                        <div class="counter-card-small serving ${isAlert ? 'notify' : ''}">
                            <div class="counter-info">
                                <div class="counter-info-name">Counter ${counter.counter_number}</div>
                                <div class="counter-info-queue">${counter.display_name}</div>
                            </div>
                            <div class="queue-badge ${isAlert ? 'callout' : ''}">${queue.queue_number}</div>
                        </div>
                    `;
                }).join('');
                servingList.innerHTML = servingHTML;
            }

            // Update Waiting section - grouped by counter, fit to one line
            if (totalWaiting === 0) {
                waitingList.innerHTML = `
                    <div class="text-center text-gray-400 py-4">
                        <i class="fas fa-inbox text-xl opacity-50"></i>
                        <p class="text-sm mt-1">No waiting customers</p>
                    </div>
                `;
            } else {
                // Each counter group is inline-flex, small font, single line, no vertical scroll
                let waitingHTML = `<div style="display: inline-flex; flex-wrap: nowrap; align-items: center; gap: 2.5rem; width: 100%; overflow-x: auto; overflow-y: hidden; max-height: 100%;">`;
                waitingHTML += groups.map(group => {
                    const counterName = group.display_name || `Counter ${group.counter_number}`;
                    const queues = Array.isArray(group.queues) ? group.queues : [];
                    const queueNumbers = queues.map(queue =>
                        `<span class=\"waiting-queue-number\" style=\"background: #f59e0b; color: #fff; border-radius: 4px; padding: 0.15em 0.5em; margin: 0 0.15em; font-size: 1.2rem;\">${queue.queue_number}</span>`
                    ).join('');
                    return `
                        <div style=\"display: flex; flex-direction: row; align-items: center; gap: 0.5em; font-size: 1.1rem;\">
                            <span style=\"font-weight: 600; color: #f59e0b; margin-right: 0.4em; white-space: nowrap;\">${counterName}:</span>
                            <span>${queueNumbers || '<span style=\\"color: #bbb;\\">No waiting</span>'}</span>
                        </div>
                    `;
                }).join('');
                waitingHTML += `</div>`;
                waitingList.innerHTML = waitingHTML;
            }
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
                    <iframe src="${video.youtube_embed_url}?autoplay=1&loop=1&modestbranding=1&rel=0" 
                            allow="autoplay; encrypted-media" 
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
                `;
                if (!player.querySelector('iframe') || player.querySelector('iframe').src !== video.youtube_embed_url) {
                    player.innerHTML = newHTML;
                }
            } else if (video.file_path) {
                if (!player.querySelector('video')) {
                    player.innerHTML = `
                        <video autoplay loop style="width: 100%; height: 100%; object-fit: cover;">
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
            
            if (marquee && marquee.is_active && marquee.text) {
                // Update text if changed
                if (marqueeText.textContent !== marquee.text) {
                    marqueeText.textContent = marquee.text;
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

        // Refresh color settings in real-time
        function updateColorSettings() {
            fetch(`/${orgCode}/admin/organization-settings/api/get`)
                .then(response => response.json())
                .then(data => {
                    const root = document.documentElement;
                    if (data.primary_color) root.style.setProperty('--primary', data.primary_color);
                    if (data.secondary_color) root.style.setProperty('--secondary', data.secondary_color);
                    if (data.accent_color) root.style.setProperty('--accent', data.accent_color);
                    if (data.text_color) root.style.setProperty('--text', data.text_color);
                })
                .catch(error => console.error('Color settings refresh failed:', error));
        }

        // Initial load
        refreshMonitorData();

        // Aggressive real-time refresh
        let counterRefresh = setInterval(refreshMonitorData, 1000);

        // Refresh colors every 5 seconds
        let colorRefresh = setInterval(updateColorSettings, 5000);

        // Prevent sleep/screensaver and track visibility
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                console.log('Monitor hidden');
            } else {
                console.log('Monitor visible, refreshing...');
                refreshMonitorData();
                updateColorSettings();
            }
        });

        // Keep screen active
        setInterval(() => {
            if (document.hidden === false) {
                navigator.wakeLock?.request('screen').catch(() => {});
            }
        }, 5000);
    </script>
    <script src="{{ asset('js/settings-sync.js') }}"></script>
</body>
</html>
