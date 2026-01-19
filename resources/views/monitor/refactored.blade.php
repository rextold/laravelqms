<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Queue Monitor - {{ $organization->organization_name }}</title>
    <meta name="description" content="Queue Management System - Customer Display Monitor">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ========================================
           MONITOR REFACTORED - CSS VARIABLES & BASE
           ======================================== */
        :root {
            --primary: {{ $settings->primary_color ?? '#3b82f6' }};
            --secondary: {{ $settings->secondary_color ?? '#8b5cf6' }};
            --accent: {{ $settings->accent_color ?? '#10b981' }};
            --text: {{ $settings->text_color ?? '#ffffff' }};
            --bg-dark: #0a0a0a;
            --bg-card: #1a1a2e;
            --bg-surface: #16213e;
            --border: rgba(255, 255, 255, 0.08);
            --shadow: rgba(0, 0, 0, 0.5);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            overflow: hidden;
            background: linear-gradient(135deg, var(--bg-dark) 0%, var(--bg-card) 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* ========================================
           MONITOR LAYOUT - CSS GRID
           ======================================== */
        .monitor-container {
            display: grid;
            grid-template-columns: 1fr 420px;
            grid-template-rows: 80px 1fr 70px;
            height: 100vh;
            gap: 10px;
            padding: 10px;
        }
        
        /* ========================================
           HEADER SECTION
           ======================================== */
        .monitor-header {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 16px;
            padding: 0 2.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
            position: relative;
            overflow: hidden;
        }
        
        .monitor-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(255, 255, 255, 0.05) 50%, 
                transparent 100%);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            z-index: 1;
        }
        
        .header-logo {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .header-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 6px;
        }
        
        .header-logo i {
            font-size: 1.5rem;
            color: white;
        }
        
        .header-title h1 {
            color: white;
            font-size: 1.75rem;
            font-weight: 800;
            margin: 0;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.02em;
        }
        
        .header-title p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.8rem;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 500;
        }
        
        /* Call Notification Banner - Inside Header */
        .call-banner {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%) scale(0.95);
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 10;
        }
        
        .call-banner.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
        
        .call-banner-card {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(20px);
            border: 3px solid var(--accent);
            border-radius: 20px;
            padding: 1rem 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
            min-width: 400px;
            text-align: center;
        }
        
        .call-banner-title {
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.5rem;
        }
        
        .call-banner-number {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--accent);
            text-shadow: 0 4px 20px var(--accent);
            margin-bottom: 0.25rem;
            line-height: 1;
        }
        
        .call-banner-counter {
            font-size: 1rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .header-time {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.25rem;
            z-index: 1;
        }
        
        .header-date {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
        }
        
        /* Connection Status */
        .connection-status {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            z-index: 2;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            animation: pulse-dot 2s infinite;
        }
        
        .status-dot.disconnected {
            background: #ef4444;
            animation: none;
        }
        
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* ========================================
           VIDEO PLAYER SECTION
           ======================================== */
        .video-section {
            grid-column: 1;
            grid-row: 2;
            background: var(--bg-card);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 40px var(--shadow);
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
        
        .video-player video,
        .video-player iframe {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: none;
        }
        
        .no-video {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.3);
            height: 100%;
        }
        
        .no-video i {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            opacity: 0.4;
        }
        
        .no-video p {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        /* ========================================
           QUEUE SIDEBAR
           ======================================== */
        .queue-sidebar {
            grid-column: 2;
            grid-row: 2;
            display: flex;
            flex-direction: column;
            gap: 12px;
            overflow: hidden;
        }
        
        .queue-card {
            background: var(--bg-card);
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .queue-card-header {
            padding: 1rem 1.25rem;
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .queue-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        
        .queue-icon.serving {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .queue-icon.waiting {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .queue-card-title {
            font-size: 0.95rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .queue-card-title.serving {
            color: #10b981;
        }
        
        .queue-card-title.waiting {
            color: #f59e0b;
        }
        
        .queue-card-content {
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }
        
        .queue-card-content::-webkit-scrollbar {
            width: 6px;
        }
        
        .queue-card-content::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
        }
        
        .queue-card-content::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 3px;
        }
        
        /* Now Serving Items */
        .serving-item {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            transition: all 0.3s ease;
            animation: slideInRight 0.5s ease;
        }
        
        .serving-item:last-child {
            border-bottom: none;
        }
        
        .serving-item.notify {
            background: rgba(16, 185, 129, 0.1);
            animation: pulse-border 1.5s ease-in-out infinite;
        }
        
        @keyframes pulse-border {
            0%, 100% { 
                background: rgba(16, 185, 129, 0.1);
                border-left: 4px solid rgba(16, 185, 129, 0.8);
            }
            50% { 
                background: rgba(16, 185, 129, 0.05);
                border-left: 4px solid rgba(16, 185, 129, 0.3);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .serving-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .counter-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .queue-number {
            font-size: 1.75rem;
            font-weight: 900;
            color: var(--accent);
            text-shadow: 0 2px 10px var(--accent);
        }
        
        .queue-number.callout {
            animation: pulse-scale 1.2s ease-in-out infinite;
        }
        
        @keyframes pulse-scale {
            0%, 100% { 
                transform: scale(1);
                text-shadow: 0 2px 10px var(--accent);
            }
            50% { 
                transform: scale(1.08);
                text-shadow: 0 4px 20px var(--accent);
            }
        }
        
        .counter-info {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 0.25rem;
        }
        
        /* Waiting Queue Groups */
        .waiting-group {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(245, 158, 11, 0.15);
            transition: all 0.3s ease;
        }
        
        .waiting-group:hover {
            background: rgba(245, 158, 11, 0.05);
        }
        
        .waiting-group:last-child {
            border-bottom: none;
        }
        
        .waiting-group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(245, 158, 11, 0.2);
        }
        
        .waiting-counter-name {
            font-size: 0.9rem;
            font-weight: 700;
            color: #f59e0b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .waiting-count-badge {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        .waiting-numbers {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .waiting-number-badge {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.2));
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: #fbbf24;
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            transition: all 0.2s ease;
        }
        
        .waiting-number-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        
        /* Empty States */
        .empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.3);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-state p {
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        /* ========================================
           MARQUEE SECTION
           ======================================== */
        .marquee-section {
            grid-column: 1 / -1;
            grid-row: 3;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 32px var(--shadow);
            display: flex;
            align-items: center;
        }
        
        .marquee-content {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            overflow: hidden;
        }
        
        .marquee-text {
            color: white;
            font-size: 1.4rem;
            font-weight: 700;
            white-space: nowrap;
            padding-left: 100%;
            animation: marquee 35s linear infinite;
        }
        
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }
        
        .marquee-icon {
            display: inline-block;
            margin: 0 1.5rem;
            font-size: 1.2rem;
        }
        
        /* ========================================
           RESPONSIVE DESIGN
           ======================================== */
        @media (max-width: 1400px) {
            .monitor-container {
                grid-template-columns: 1fr 360px;
            }
        }
        
        @media (max-width: 1024px) {
            .monitor-container {
                grid-template-columns: 1fr;
                grid-template-rows: 70px 1fr 300px 60px;
            }
            
            .queue-sidebar {
                grid-column: 1;
                grid-row: 3;
                flex-direction: row;
                gap: 10px;
            }
            
            .queue-card {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="monitor-container">
        <!-- Header -->
        <div class="monitor-header">
            <div class="header-left">
                <div class="header-logo">
                    @if($settings->company_logo ?? false)
                        <img src="{{ asset('storage/' . $settings->company_logo) }}" alt="{{ $organization->organization_name }}">
                    @else
                        <i class="fas fa-building"></i>
                    @endif
                </div>
                <div class="header-title">
                    <h1 data-org-name>{{ $organization->organization_name }}</h1>
                    <p>Queue Management System</p>
                </div>
            </div>
            
            <!-- Call Banner (centered in header) -->
            <div id="callBanner" class="call-banner">
                <div class="call-banner-card">
                    <div class="call-banner-title">Now Calling</div>
                    <div id="callBannerNumber" class="call-banner-number">—</div>
                    <div id="callBannerCounter" class="call-banner-counter">Please proceed to counter</div>
                </div>
            </div>
            
            <div class="header-time">
                <div id="currentTime">00:00:00</div>
                <div class="header-date" id="currentDate">Loading...</div>
            </div>
            
            <!-- Connection Status -->
            <div class="connection-status">
                <span class="status-dot" id="statusDot"></span>
                <span id="statusText">Connected</span>
            </div>
        </div>
        
        <!-- Video Player -->
        <div class="video-section">
            <div class="video-player" id="videoPlayer">
                <div class="no-video">
                    <i class="fas fa-film"></i>
                    <p>No active video</p>
                </div>
            </div>
        </div>
        
        <!-- Queue Sidebar -->
        <div class="queue-sidebar">
            <!-- Now Serving -->
            <div class="queue-card" style="flex: 1;">
                <div class="queue-card-header">
                    <div class="queue-icon serving">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="queue-card-title serving">Now Serving</div>
                </div>
                <div class="queue-card-content" id="servingList">
                    <div class="empty-state">
                        <i class="fas fa-hourglass-end"></i>
                        <p>No active service</p>
                    </div>
                </div>
            </div>
            
            <!-- Waiting Queue -->
            <div class="queue-card" style="flex: 1;">
                <div class="queue-card-header">
                    <div class="queue-icon waiting">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="queue-card-title waiting">Waiting Queue</div>
                </div>
                <div class="queue-card-content" id="waitingList">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No waiting customers</p>
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
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFAtGmN7yvmwhBjiKz/HPgTQGG2W07O2hUhELRZff8r5sIQc4iM/x0H40BhtktOztolIRC0WX3/K+bCEHOIjP8dB+NAYbZLTs7aFSEQtFl9/yvmwhBziIz/HQfjQGG2S07O2hUhELRZff8r5sIQc4h8/x0H40BhtktOztolIRC0SX3vK+bCEHN4fP8c9+MwYaZLPr7aFSEQxEl97yvmwhBzeHz/HPfjMGGmSz6+2hUhEMRJfe8r5sIQc3h8/xz34zBhpks+vtoVIRDESX3vK+ayEHN4bP8c9+MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQ=" type="audio/wav">
    </audio>
    
    <script>
        // ========================================
        // MONITOR REFACTORED - JAVASCRIPT
        // ========================================
        
        const CONFIG = {
            orgCode: '{{ $companyCode }}',
            refreshInterval: 1500, // 1.5 seconds
            reconnectDelay: 3000,
            callBannerDuration: 5000,
        };
        
        const STATE = {
            isConnected: true,
            isFetching: false,
            previousServingState: new Map(),
            currentVideo: null,
            videoRotationIndex: 0,
            videos: @json($videos ?? []),
            videoControl: @json($videoControl ?? null),
        };
        
        let refreshTimer = null;
        let callBannerTimer = null;
        
        // ========================================
        // INITIALIZATION
        // ========================================
        
        document.addEventListener('DOMContentLoaded', () => {
            initializeMonitor();
            initializeMarquee();
            startRefreshCycle();
            setupEventListeners();
        });
        
        function initializeMonitor() {
            updateTime();
            setInterval(updateTime, 1000);
            
            // Unlock audio on first user interaction
            document.addEventListener('click', unlockAudio, { once: true });
            document.addEventListener('keydown', unlockAudio, { once: true });
        }
        
        function unlockAudio() {
            const audio = document.getElementById('notificationSound');
            if (audio) {
                audio.muted = false;
                audio.play().then(() => audio.pause()).catch(() => {});
            }
        }
        
        function initializeMarquee() {
            const marquee = @json($marquee ?? null);
            if (marquee && marquee.is_active && (marquee.message || marquee.text)) {
                const text = marquee.message || marquee.text;
                document.getElementById('marqueeText').textContent = text;
                document.getElementById('marqueeSection').style.display = 'flex';
            }
        }
        
        function setupEventListeners() {
            // Handle visibility change
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    refreshMonitorData();
                }
            });
            
            // Keep screen awake
            if ('wakeLock' in navigator) {
                navigator.wakeLock.request('screen').catch(() => {});
            }
        }
        
        // ========================================
        // TIME & DATE UPDATE
        // ========================================
        
        function updateTime() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', {
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', {
                weekday: 'long',
                month: 'short',
                day: '2-digit',
                year: 'numeric'
            });
        }
        
        // ========================================
        // DATA REFRESH CYCLE
        // ========================================
        
        function startRefreshCycle() {
            refreshMonitorData();
            refreshTimer = setInterval(refreshMonitorData, CONFIG.refreshInterval);
        }
        
        function stopRefreshCycle() {
            if (refreshTimer) {
                clearInterval(refreshTimer);
                refreshTimer = null;
            }
        }
        
        async function refreshMonitorData() {
            if (STATE.isFetching) return;
            
            STATE.isFetching = true;
            updateConnectionStatus(true);
            
            try {
                const response = await fetch(`/${CONFIG.orgCode}/monitor/data`, {
                    cache: 'no-store',
                    headers: { 'Accept': 'application/json' }
                });
                
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                
                updateCountersDisplay(data.counters || [], data.waiting_queues || []);
                updateVideoPlayer(data.video_control || STATE.videoControl);
                updateMarqueeDisplay(data.marquee);
                
                STATE.isConnected = true;
                updateConnectionStatus(true);
            } catch (error) {
                console.error('Refresh failed:', error);
                STATE.isConnected = false;
                updateConnectionStatus(false);
            } finally {
                STATE.isFetching = false;
            }
        }
        
        function updateConnectionStatus(connected) {
            const statusDot = document.getElementById('statusDot');
            const statusText = document.getElementById('statusText');
            
            if (connected) {
                statusDot.classList.remove('disconnected');
                statusText.textContent = 'Connected';
            } else {
                statusDot.classList.add('disconnected');
                statusText.textContent = 'Reconnecting...';
                
                // Attempt reconnection
                setTimeout(() => {
                    if (!STATE.isConnected) refreshMonitorData();
                }, CONFIG.reconnectDelay);
            }
        }
        
        // ========================================
        // COUNTERS DISPLAY UPDATE
        // ========================================
        
        function updateCountersDisplay(counters, waitingQueues) {
            updateServingCounters(counters);
            updateWaitingQueues(waitingQueues);
        }
        
        function updateServingCounters(counters) {
            const servingList = document.getElementById('servingList');
            const servingCounters = counters.filter(item => item.queue);
            
            if (servingCounters.length === 0) {
                servingList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-hourglass-end"></i>
                        <p>No active service</p>
                    </div>
                `;
                STATE.previousServingState.clear();
                return;
            }
            
            // Detect new calls/notifications
            const alerts = detectAlerts(servingCounters);
            
            // Play notification sound if there are alerts
            if (alerts.length > 0 && STATE.previousServingState.size > 0) {
                playNotificationSound();
                showCallBanner(alerts[0]);
            }
            
            // Update previous state
            updatePreviousState(servingCounters);
            
            // Render serving items
            const html = servingCounters.map(item => {
                const counter = item.counter;
                const queue = item.queue;
                const isAlert = alerts.some(a => a.queue?.id === queue?.id);
                
                return `
                    <div class="serving-item ${isAlert ? 'notify' : ''}">
                        <div class="serving-header">
                            <div class="counter-label">Counter ${counter.counter_number}</div>
                        </div>
                        <div class="queue-number ${isAlert ? 'callout' : ''}">${queue.queue_number}</div>
                        <div class="counter-info">${counter.display_name || 'Service Counter'}</div>
                    </div>
                `;
            }).join('');
            
            servingList.innerHTML = html;
        }
        
        function updateWaitingQueues(waitingGroups) {
            const waitingList = document.getElementById('waitingList');
            const groups = waitingGroups.filter(g => g.queues && g.queues.length > 0);
            
            if (groups.length === 0) {
                waitingList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No waiting customers</p>
                    </div>
                `;
                return;
            }
            
            const html = groups.map(group => `
                <div class="waiting-group">
                    <div class="waiting-group-header">
                        <div class="waiting-counter-name">
                            <i class="fas fa-user-clock"></i>
                            ${group.display_name || 'Counter ' + group.counter_number}
                        </div>
                        <div class="waiting-count-badge">${group.queues.length}</div>
                    </div>
                    <div class="waiting-numbers">
                        ${group.queues.map(q => `
                            <div class="waiting-number-badge">${q.queue_number}</div>
                        `).join('')}
                    </div>
                </div>
            `).join('');
            
            waitingList.innerHTML = html;
        }
        
        function detectAlerts(servingCounters) {
            const alerts = [];
            const nextState = new Map();
            
            servingCounters.forEach(item => {
                const queue = item.queue;
                if (!queue?.id) return;
                
                const queueKey = String(queue.id);
                const current = {
                    called_at: queue.called_at,
                    notified_at: queue.notified_at,
                    recent_recall: item.recent_recall
                };
                
                const prev = STATE.previousServingState.get(queueKey);
                const isNew = !prev;
                const notifyChanged = prev && prev.notified_at !== current.notified_at && current.notified_at;
                const recallTriggered = current.recent_recall && (!prev || !prev.recent_recall);
                
                if (isNew || notifyChanged || recallTriggered) {
                    alerts.push(item);
                }
                
                nextState.set(queueKey, current);
            });
            
            return alerts;
        }
        
        function updatePreviousState(servingCounters) {
            const nextState = new Map();
            servingCounters.forEach(item => {
                if (item.queue?.id) {
                    nextState.set(String(item.queue.id), {
                        called_at: item.queue.called_at,
                        notified_at: item.queue.notified_at,
                        recent_recall: item.recent_recall
                    });
                }
            });
            STATE.previousServingState = nextState;
        }
        
        // ========================================
        // CALL BANNER NOTIFICATION
        // ========================================
        
        function showCallBanner(alertItem) {
            const banner = document.getElementById('callBanner');
            const number = document.getElementById('callBannerNumber');
            const counter = document.getElementById('callBannerCounter');
            
            if (!banner || !alertItem) return;
            
            number.textContent = alertItem.queue?.queue_number || '—';
            counter.textContent = `Please proceed to Counter ${alertItem.counter?.counter_number || ''}`;
            
            banner.classList.add('show');
            
            if (callBannerTimer) clearTimeout(callBannerTimer);
            callBannerTimer = setTimeout(() => {
                banner.classList.remove('show');
            }, CONFIG.callBannerDuration);
        }
        
        function playNotificationSound() {
            const audio = document.getElementById('notificationSound');
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(e => console.log('Audio play failed:', e));
            }
        }
        
        // ========================================
        // VIDEO PLAYER UPDATE
        // ========================================
        
        function updateVideoPlayer(videoControl) {
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
            
            if (!STATE.videos || STATE.videos.length === 0) {
                player.innerHTML = `
                    <div class="no-video">
                        <i class="fas fa-video-slash"></i>
                        <p>No videos available</p>
                    </div>
                `;
                return;
            }
            
            // Find video to play
            let video = null;
            if (videoControl.current_video_id) {
                video = STATE.videos.find(v => v.id === videoControl.current_video_id);
            }
            
            if (!video) {
                STATE.videoRotationIndex = Math.floor(Date.now() / 10000) % STATE.videos.length;
                video = STATE.videos[STATE.videoRotationIndex];
            }
            
            if (!video) return;
            
            // Render video player
            if (video.is_youtube && video.youtube_embed_url) {
                const src = video.youtube_embed_url + '?autoplay=1&mute=1&loop=1&modestbranding=1&rel=0';
                const existing = player.querySelector('iframe');
                
                if (!existing || existing.src !== src) {
                    player.innerHTML = `<iframe src="${src}" allow="autoplay; encrypted-media" allowfullscreen></iframe>`;
                }
            } else if (video.file_path) {
                const existing = player.querySelector('video');
                const src = `/storage/${video.file_path}`;
                
                if (!existing || !existing.querySelector(`source[src="${src}"]`)) {
                    player.innerHTML = `
                        <video autoplay loop muted>
                            <source src="${src}" type="video/mp4">
                        </video>
                    `;
                    const videoEl = player.querySelector('video');
                    if (videoEl) {
                        videoEl.volume = (videoControl.volume || 50) / 100;
                        videoEl.play().catch(() => {});
                    }
                }
            }
            
            STATE.currentVideo = video;
        }
        
        // ========================================
        // MARQUEE UPDATE
        // ========================================
        
        function updateMarqueeDisplay(marquee) {
            const section = document.getElementById('marqueeSection');
            const text = document.getElementById('marqueeText');
            
            if (marquee && marquee.is_active && (marquee.text || marquee.message)) {
                const content = marquee.text || marquee.message;
                
                if (text.textContent !== content) {
                    text.textContent = content;
                    // Restart animation
                    text.style.animation = 'none';
                    setTimeout(() => {
                        text.style.animation = 'marquee 35s linear infinite';
                    }, 10);
                }
                
                section.style.display = 'flex';
            } else {
                section.style.display = 'none';
            }
        }
    </script>
</body>
</html>