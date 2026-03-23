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

    <!-- PWA -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="QMS Monitor">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <link rel="shortcut icon" href="/icons/icon-96x96.png">

    <!-- YouTube IFrame Player API — required for programmatic volume/play control -->
    <script src="https://www.youtube.com/iframe_api"></script>
    
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
            grid-template-columns: 65fr 35fr;
            grid-template-rows: 80px 1fr auto;
            height: 100vh;
            gap: 10px;
            padding: 10px;
        }
        
        /* ========================================
           HEADER SECTION
           ======================================== */
        .monitor-header {
            grid-column: 1 / -1;
            grid-row: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1.5rem;
            background: var(--bg-header);
            border-radius: 16px;
            box-shadow: 0 8px 32px var(--shadow);
            z-index: 1;
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
        
        .header-marquee {
            flex-grow: 1;
            overflow: hidden;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 2rem;
            height: 100%;
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
        
        .marquee-content {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            overflow: hidden;
            position: absolute;
        }
        
        .marquee-text {
            color: white;
            font-size: 1.4rem;
            font-weight: 700;
            white-space: nowrap;
            padding-left: 100%;
            animation: marquee 35s linear infinite;
        }
        
        /* Call Notification Banner - Upper Center with Bell Icon (Hidden by Default) */
        .call-banner {
            position: fixed;
            left: 50%;
            top: 100px;
            transform: translateX(-50%) translateY(-20px);
            opacity: 0;
            pointer-events: none;
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .call-banner.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        
        .call-banner.speaking .call-banner-card::before {
            animation: bell-ring 0.5s ease-in-out infinite, pulse-glow 1s ease-in-out infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { 
                text-shadow: 0 0 20px var(--accent);
                transform: translateX(-50%) rotate(0deg) scale(1);
            }
            50% { 
                text-shadow: 0 0 40px var(--accent), 0 0 60px var(--accent);
                transform: translateX(-50%) rotate(0deg) scale(1.1);
            }
        }
        
        .call-banner-card {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.95), rgba(16, 25, 62, 0.95));
            backdrop-filter: blur(20px);
            border: 3px solid var(--accent);
            border-radius: 24px;
            padding: 1.5rem 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8), 
                        0 0 40px rgba(16, 185, 129, 0.3);
            min-width: 450px;
            text-align: center;
            position: relative;
        }
        
        .call-banner-card::before {
            content: '\f0f3';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 2.5rem;
            color: var(--accent);
            animation: bell-ring 1s ease-in-out infinite;
            text-shadow: 0 0 20px var(--accent);
        }
        
        @keyframes bell-ring {
            0%, 100% { transform: translateX(-50%) rotate(0deg); }
            10%, 30% { transform: translateX(-50%) rotate(-15deg); }
            20%, 40% { transform: translateX(-50%) rotate(15deg); }
            50% { transform: translateX(-50%) rotate(0deg); }
        }
        
        .call-banner-title {
            font-size: 1rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--accent);
            margin-bottom: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .call-banner-number {
            font-size: 3.5rem;
            font-weight: 900;
            color: white;
            text-shadow: 0 4px 30px var(--accent);
            margin-bottom: 0.5rem;
            line-height: 1;
            letter-spacing: 0.05em;
        }
        
        .call-banner-counter {
            font-size: 1.1rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            letter-spacing: 0.02em;
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
        
        /* Connection Status - Bottom Left */
        .connection-status {
            position: fixed;
            bottom: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 1000;
            border: 1px solid rgba(255, 255, 255, 0.1);
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

        /* YouTube persistent overlay fills the section */
        #yt-overlay iframe {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            border: 0;
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
        
        .waiting-queue-section {
            grid-column: 1 / -1;
            grid-row: 3;
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
        
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }
        
        .marquee-icon {
            display: inline-block;
            margin: 0 1.5rem;
            font-size: 1.2rem;
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
        
        /* Now Serving Items - Compact Format like Counter 1: 0002 */
        .serving-row {
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid rgba(16, 185, 129, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            transition: all 0.3s ease;
            flex-wrap: wrap;
        }
        
        .serving-row:last-child {
            border-bottom: none;
        }
        
        /* Blink animation - only 5 times when called/notified */
        .serving-row.notify {
            background: rgba(16, 185, 129, 0.12);
            border-left: 4px solid rgba(16, 185, 129, 0.8);
            animation: blink-five-times 1.5s ease-in-out 1;
        }
        
        @keyframes blink-five-times {
            0%, 10%, 20%, 30%, 40%, 50%, 60%, 70%, 80%, 90%, 100% { 
                background: rgba(16, 185, 129, 0.12);
                border-left-color: rgba(16, 185, 129, 0.9);
            }
            5%, 15%, 25%, 35%, 45%, 55%, 65%, 75%, 85%, 95% { 
                background: rgba(16, 185, 129, 0.03);
                border-left-color: rgba(16, 185, 129, 0.3);
            }
        }
        
        .serving-counter-label {
            font-size: 1.5rem;
            font-weight: 700;
            color: #10b981;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .serving-queue-number {
            font-size: 3rem;
            font-weight: 900;
            color: var(--accent);
            text-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
            white-space: nowrap;
            flex-shrink: 0;
            letter-spacing: 0.05em;
        }
        
        /* Waiting Queue Rows - Compact Format */
        .waiting-row {
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid rgba(245, 158, 11, 0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
        }
        
        .waiting-row:last-child {
            border-bottom: none;
        }
        
        .waiting-counter-label {
            font-weight: 700;
            color: #f59e0b;
            white-space: nowrap;
            min-width: fit-content;
            flex-shrink: 0;
        }
        
        .waiting-queue-numbers {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            flex: 1;
            overflow: hidden;
        }
        
        .waiting-queue-number {
            font-weight: 800;
            font-size: 1.3rem;
            white-space: nowrap;
            display: inline-block;
            padding: 0.18rem 0.7rem;
            border-radius: 8px;
            letter-spacing: 0.05em;
            line-height: 1.6;
            border: 1px solid transparent;
        }

        /* Alternating pill colors — semi-transparent background + matching border for dark monitor theme */
        .waiting-queue-number.alt-0 { color: #fde68a; background: rgba(251,191,36,0.14); border-color: rgba(251,191,36,0.35); }  /* amber  */
        .waiting-queue-number.alt-1 { color: #93c5fd; background: rgba(96,165,250,0.14); border-color: rgba(96,165,250,0.35); }  /* blue   */
        .waiting-queue-number.alt-2 { color: #6ee7b7; background: rgba(52,211,153,0.14); border-color: rgba(52,211,153,0.35); }  /* emerald */
        .waiting-queue-number.alt-3 { color: #fca5a5; background: rgba(248,113,113,0.14); border-color: rgba(248,113,113,0.35); } /* rose   */
        .waiting-queue-number.alt-4 { color: #c4b5fd; background: rgba(167,139,250,0.14); border-color: rgba(167,139,250,0.35); } /* violet */

        /* ========================================
           WAITING QUEUE — COMPACT BOTTOM STRIP
           Vertical label on left + horizontal scroll
           ======================================== */
        .waiting-queue-section .queue-card {
            flex-direction: row;
            align-items: stretch;
            min-height: unset;
        }

        /* Left label column — replaces the tall card header */
        .waiting-queue-section .queue-card-header {
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 0 0.75rem;
            border-bottom: none;
            border-right: 1px solid var(--border);
            flex-shrink: 0;
            gap: 0.2rem;
            min-width: 68px;
        }

        .waiting-queue-section .queue-icon {
            width: 24px;
            height: 24px;
            font-size: 0.65rem;
            border-radius: 6px;
        }

        .waiting-queue-section .queue-card-title {
            font-size: 0.58rem;
            letter-spacing: 0.07em;
        }

        /* Scrollable horizontal row of queue entries */
        .waiting-queue-section .queue-card-content {
            flex: 1;
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            align-items: center;
            height: 60px;
            padding: 0 0.25rem;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .waiting-queue-section .queue-card-content::-webkit-scrollbar {
            display: none;
        }

        /* Each counter entry is an inline non-wrapping row */
        .waiting-queue-section .waiting-row {
            flex-direction: row;
            flex-shrink: 0;
            flex-wrap: nowrap;
            border-bottom: none;
            border-right: 1px solid rgba(245, 158, 11, 0.12);
            padding: 0 0.6rem;
            align-items: center;
            gap: 0.3rem;
            height: 100%;
        }

        .waiting-queue-section .waiting-counter-label {
            font-size: 0.72rem;
        }

        .waiting-queue-section .waiting-queue-numbers {
            gap: 0.22rem;
            overflow: visible;
            flex-wrap: nowrap;
        }

        .waiting-queue-section .waiting-queue-number {
            font-size: 0.78rem;
            padding: 0.05rem 0.35rem;
            line-height: 1.5;
            border-radius: 5px;
            font-weight: 700;
        }

        /* Compact empty state for the strip */
        .waiting-queue-section .empty-state {
            flex-direction: row;
            padding: 0 1rem;
            height: 100%;
            gap: 0.5rem;
        }

        .waiting-queue-section .empty-state i {
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .waiting-queue-section .empty-state p {
            font-size: 0.75rem;
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
           RESPONSIVE DESIGN
           ======================================== */
        @media (max-width: 1400px) {
            .monitor-container {
                grid-template-columns: 1fr 360px;
            }
            
            .call-banner {
                top: 90px;
            }
            
            .call-banner-card {
                min-width: 400px;
                padding: 1.25rem 2rem;
            }
            
            .call-banner-card::before {
                font-size: 2rem;
                top: -18px;
            }
            
            .call-banner-number {
                font-size: 3rem;
            }
            
            .serving-queue-number {
                font-size: 1.3rem;
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
            
            .header-title h1 {
                font-size: 1.25rem;
            }
            
            .header-time {
                font-size: 1.25rem;
            }
            
            .call-banner {
                top: 80px;
            }
            
            .call-banner-card {
                min-width: 350px;
                padding: 1rem 1.75rem;
            }
            
            .call-banner-card::before {
                font-size: 1.75rem;
                top: -16px;
            }
            
            .call-banner-number {
                font-size: 2.5rem;
            }
            
            .call-banner-counter {
                font-size: 1rem;
            }
            
            .serving-counter-label {
                font-size: 0.9rem;
            }
            
            .serving-queue-number {
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 768px) {
            .monitor-container {
                padding: 5px;
                gap: 5px;
            }
            
            .header-title h1 {
                font-size: 1rem;
            }
            
            .header-logo {
                width: 36px;
                height: 36px;
            }
            
            .connection-status {
                font-size: 0.65rem;
                padding: 4px 8px;
            }
            
            .call-banner {
                top: 70px;
            }
            
            .call-banner-card {
                min-width: 300px;
                padding: 0.875rem 1.5rem;
            }
            
            .call-banner-card::before {
                font-size: 1.5rem;
                top: -14px;
            }
            
            .call-banner-title {
                font-size: 0.85rem;
            }
            
            .call-banner-number {
                font-size: 2rem;
            }
            
            .call-banner-counter {
                font-size: 0.9rem;
            }
            
            .serving-row {
                padding: 0.65rem 1rem;
                flex-direction: row;
                gap: 0.75rem;
            }
            
            .serving-counter-label {
                font-size: 0.85rem;
            }
            
            .serving-queue-number {
                font-size: 1.1rem;
            }
            
            .waiting-row {
                /* keep flex-row in the strip — no stacking */
                gap: 0.3rem;
            }
            
            .waiting-counter-label {
                font-size: 0.7rem;
            }
            
            .waiting-queue-number {
                font-size: 0.75rem;
                padding: 0.1rem 0.45rem;
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

            <div class="header-marquee" id="marqueeSection" style="display: none;">
                <div class="marquee-content">
                    <span class="marquee-text" id="marqueeText"></span>
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
            
            <!-- Audio Status Indicator (for debugging) -->
            <div style="position: fixed; bottom: 10px; right: 10px; display: flex; gap: 8px; z-index: 1000;">
                <div class="audio-status" id="audioStatus" style="background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(10px); padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; gap: 8px; border: 1px solid rgba(255, 255, 255, 0.1); cursor: pointer;" onclick="testNotificationSound()">
                    <i class="fas fa-volume-up"></i>
                    <span>Test Bell</span>
                </div>
                <div style="background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(10px); padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; gap: 8px; border: 1px solid rgba(255, 255, 255, 0.1); cursor: pointer;" onclick="testVoiceAnnouncement()">
                    <i class="fas fa-microphone"></i>
                    <span>Test Voice</span>
                </div>
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
            <!-- Persistent YouTube overlay — created once, never destroyed.
                 Shown/hidden via display property. loadVideoById() switches
                 videos in < 1 s without re-initialising the IFrame API. -->
            <div id="yt-overlay" style="display:none;position:absolute;top:0;left:0;width:100%;height:100%;z-index:3;background:#000;">
                <div id="yt-player-div" style="width:100%;height:100%;"></div>
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
        </div>
        
        <div class="waiting-queue-section">
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
        

    </div>
    
    <!-- Notification Sound - Uses uploaded custom bell or default -->
    <audio id="notificationSound" preload="auto" crossorigin="anonymous">
        @if($videoControl && $videoControl->bell_sound_path)
            <!-- Custom uploaded bell sound -->
            <source src="{{ asset('storage/' . $videoControl->bell_sound_path) }}" type="audio/{{ pathinfo($videoControl->bell_sound_path, PATHINFO_EXTENSION) }}">
        @endif
        <!-- Fallback default bell sound -->
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFAtGmN7yvmwhBjiKz/HPgTQGG2W07O2hUhELRZff8r5sIQc4iM/x0H40BhtktOztolIRC0WX3/K+bCEHOIjP8dB+NAYbZLTs7aFSEQtFl9/yvmwhBziIz/HQfjQGG2S07O2hUhELRZff8r5sIQc4h8/x0H40BhtktOztolIRC0SX3vK+bCEHN4fP8c9+MwYaZLPr7aFSEQxEl97yvmwhBzeHz/HPfjMGGmSz6+2hUhEMRJfe8r5sIQc3h8/xz34zBhpks+vtoVIRDESX3vK+ayEHN4bP8c9+MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQc3hs/xz30zBhpks+vtoVIRDESX3vK/ayEHN4bP8c99MwYaZLPr7aFSEQxEl97yv2shBzeGz/HPfTMGGmSz6+2hUhEMRJfe8r9rIQ=" type="audio/wav">
        Your browser does not support the audio element.
    </audio>
    
    <script>
        // ========================================
        // MONITOR REFACTORED - JAVASCRIPT
        // ========================================
        
        const CONFIG = {
            orgCode: '{{ $companyCode }}',
            refreshInterval: 1500,         // 1.5 s  — normal poll
            slowPollInterval: 10000,       // 10 s   — degraded mode after repeated failures
            maxFailuresBeforeSlowdown: 5,  // consecutive failures before slowing down
            _slowMode: false,              // internal flag
            callBannerDuration: 8000,      // Show banner for 8 seconds
            voiceEnabled: true,            // Enable train station-style voice announcements
            voiceRate: 0.9,                // Speech rate (0.1 to 10)
            voicePitch: 1.0,               // Speech pitch (0 to 2)
            voiceVolume: 1.0,              // Speech volume (0 to 1)
        };
        
        const STATE = {
            isConnected: true,
            isInitialized: false,  // true after the first successful poll
            isFetching: false,
            failureCount: 0,       // consecutive failed polls since last success
            previousServingState: new Map(),
            currentVideo: null,
            videoRotationIndex: 0,
            videos: @json($videos ?? []),
            videoControl: @json($videoControl ?? null),
            // Playlist rotation state
            playlistRotationIndex: 0,  // current position in STATE.videos array
            playlistOrder: [],          // video IDs in admin's queue sequence_order (for auto-advance)
            lastServerVideoId: null,   // last current_video_id received from server (detect changes)
            lastControlUpdatedAt: null, // updated_at of last processed video_control (detect re-play of same video)
            playlistTimer: null,       // setTimeout handle for YouTube rotation
            // YouTube IFrame API state
            ytPlayer: null,            // YT.Player instance — persistent, never destroyed
            ytPlayerReady: false,      // true after onReady fires (loadVideoById is safe to call)
            ytApiReady: false,         // true when onYouTubeIframeAPIReady fires
            ytCurrentVideoId: null,    // YouTube video ID string loaded in player (not our DB id)
            ytAudioUnlocked: false,    // true after first user interaction → can unmute YouTube
            lastVolume: -1,            // last volume sent to ytPlayer (avoid redundant calls)
            pendingYouTubeLoad: null,  // { ytId, videoControl } queued before player is ready
            // Pre-populated from server-side render — same shape as getData() returns.
            // Used to fill the counter/queue panels immediately on page load.
            initialCounters: @json($initialCounterData ?? []),
            initialWaitingQueues: @json($initialWaitingQueues ?? []),
        };

        // ========================================
        // LOCAL STORAGE CACHE
        // Persists the last-known display state so:
        //   • page reloads restore data instantly (no blank screen)
        //   • network blips never wipe the customer display
        // ========================================
        const LS_CACHE_KEY = 'qms_monitor_' + CONFIG.orgCode;

        function saveToLocalStorage(data) {
            try {
                localStorage.setItem(LS_CACHE_KEY, JSON.stringify({
                    t: Date.now(),
                    counters:       data.counters       || [],
                    waiting_queues: data.waiting_queues || [],
                    videos:         Array.isArray(data.videos) ? data.videos : [],
                    video_control:  data.video_control  || null,
                    marquee:        data.marquee        || null,
                }));
            } catch (e) { /* storage quota or private-mode — ignore */ }
        }

        function loadFromLocalStorage() {
            try {
                const raw = localStorage.getItem(LS_CACHE_KEY);
                if (!raw) return null;
                const cached = JSON.parse(raw);
                // Discard cache older than 4 hours to avoid extremely stale data
                if (!cached.t || Date.now() - cached.t > 4 * 60 * 60 * 1000) {
                    localStorage.removeItem(LS_CACHE_KEY);
                    return null;
                }
                return cached;
            } catch (e) { return null; }
        }

        // YouTube IFrame API readiness callback — called automatically by the API script
        window.onYouTubeIframeAPIReady = function () {
            STATE.ytApiReady = true;
            console.log('[YouTube] IFrame API ready.');
            // If a video was queued before the API loaded, create the persistent player now
            if (STATE.pendingYouTubeLoad) {
                const p = STATE.pendingYouTubeLoad;
                STATE.pendingYouTubeLoad = null;
                _createYouTubePlayer(p.ytId, p.videoControl);
            }
        };

        // Debug: Log video and audio information
        console.log('📹 Video Debug Info:');
        console.log('- Total videos:', STATE.videos.length);
        console.log('- Videos:', STATE.videos);
        console.log('- Video control:', STATE.videoControl);
        STATE.videos.forEach((v, idx) => {
            console.log(`  ${idx + 1}. ${v.title} (${v.video_type})`, {
                id: v.id,
                is_youtube: v.is_youtube,
                is_file: v.is_file,
                youtube_embed_url: v.youtube_embed_url,
                file_path: v.file_path,
                is_active: v.is_active
            });
        });
        
        // Debug: Log notification bell sound info
        const notifAudio = document.getElementById('notificationSound');
        if (notifAudio) {
            const sources = notifAudio.querySelectorAll('source');
            console.log('🔔 Notification Bell Sound Info:');
            console.log('- Total sources:', sources.length);
            sources.forEach((source, idx) => {
                console.log(`  ${idx + 1}. ${source.src.substring(0, 100)}... (${source.type})`);
            });
            @if($videoControl && $videoControl->bell_sound_path)
            console.log('- Using CUSTOM uploaded bell sound');
            console.log('- Bell path: {{ $videoControl->bell_sound_path }}');
            @else
            console.log('- Using DEFAULT base64 bell sound');
            @endif
        }
        
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
            
            // Show audio unlock prompt after 2 seconds if audio hasn't been unlocked
            setTimeout(() => {
                const audio = document.getElementById('notificationSound');
                if (audio && audio.paused) {
                    console.log('💡 Audio not yet unlocked - showing prompt');
                    updateAudioStatus('blocked');
                }
            }, 2000);
        });
        
        function initializeMonitor() {
            updateTime();
            setInterval(updateTime, 1000);

            // Render the initial video state immediately from blade data so the
            // player is never stuck on "No active video" while the first poll is in-flight.
            updateVideoPlayer(STATE.videoControl);

            // Render the initial counters/queues from blade data immediately so the
            // monitor shows real data before the first HTTP poll arrives.
            if (STATE.initialCounters.length > 0 || STATE.initialWaitingQueues.length > 0) {
                updateCountersDisplay(STATE.initialCounters, STATE.initialWaitingQueues);
            }

            // Restore from localStorage as extra fallback:
            //   • when blade data is empty (e.g. server issue at page-load time)
            //   • on hard-reload while the server is temporarily unreachable
            const lsCached = loadFromLocalStorage();
            if (lsCached) {
                if (STATE.initialCounters.length === 0 && STATE.initialWaitingQueues.length === 0) {
                    updateCountersDisplay(lsCached.counters || [], lsCached.waiting_queues || []);
                    console.log('[Monitor] Restored display data from localStorage cache.');
                }
                if (!STATE.videoControl && lsCached.video_control) {
                    STATE.videoControl = lsCached.video_control;
                    updateVideoPlayer(lsCached.video_control);
                }
                if (lsCached.videos && lsCached.videos.length > 0 && STATE.videos.length === 0) {
                    STATE.videos = lsCached.videos;
                }
            }

            // Initialize audio element
            const audio = document.getElementById('notificationSound');
            if (audio) {
                audio.muted = false;
                audio.volume = 1.0;
                audio.preload = 'auto';
                
                // Load the audio immediately
                audio.load();
                
                console.log('🔔 Notification sound initialized');
                console.log('- Sources:', audio.querySelectorAll('source').length);
                console.log('- Ready state:', audio.readyState);
                
                // Check if audio loaded successfully
                audio.addEventListener('loadeddata', () => {
                    console.log('✅ Notification sound loaded successfully');
                });
                
                audio.addEventListener('error', (e) => {
                    console.error('❌ Notification sound load error:', e);
                    console.error('- Error code:', audio.error?.code);
                    console.error('- Error message:', audio.error?.message);
                });
            }
            
            // Unlock audio on first user interaction (multiple event types)
            const unlockEvents = ['click', 'touchstart', 'keydown', 'pointerdown', 'mousedown'];
            unlockEvents.forEach(eventType => {
                document.addEventListener(eventType, unlockAudio, { once: true, passive: true });
            });
            
            // Auto-trigger unlock after 2 seconds if no interaction
            setTimeout(() => {
                if (audio && audio.paused && audio.readyState === 0) {
                    console.log('⚠️ Audio not loaded yet, attempting manual load...');
                    audio.load();
                }
            }, 2000);
        }
        
        function unlockAudio() {
            const audio = document.getElementById('notificationSound');
            if (!audio) {
                console.error('Audio element not found for unlock');
                return;
            }
            
            console.log('🔓 Attempting to unlock audio...');
            
            try {
                // Ensure audio is ready
                audio.muted = false;
                audio.volume = 1.0;
                
                // Force reload if not loaded
                if (audio.readyState === 0) {
                    audio.load();
                }
                
                // Try to play and immediately pause to unlock audio context
                const playPromise = audio.play();
                
                if (playPromise !== undefined) {
                    playPromise
                        .then(() => {
                            audio.pause();
                            audio.currentTime = 0;
                            console.log('✅ Audio unlocked successfully');
                            updateAudioStatus('ready');
                            // Unmute the YouTube player now that the user has interacted
                            STATE.ytAudioUnlocked = true;
                            if (STATE.ytPlayer && typeof STATE.ytPlayer.unMute === 'function') {
                                STATE.ytPlayer.unMute();
                                const vol = STATE.videoControl?.volume ?? 80;
                                STATE.ytPlayer.setVolume(vol);
                                console.log('[YouTube] Unmuted after user interaction — volume:', vol);
                            }
                        })
                        .catch(error => {
                            console.warn('⚠️ Audio unlock failed:', error.name, error.message);
                            
                            if (error.name === 'NotSupportedError') {
                                console.error('Audio format not supported, trying fallback...');
                                // Try to load again
                                audio.load();
                            }
                        });
                } else {
                    console.log('✅ Audio unlocked (no promise)');
                }
            } catch (error) {
                console.error('❌ Error in unlockAudio:', error);
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
                    // Immediately refresh data when the tab/window regains focus
                    refreshMonitorData();
                    // Re-acquire wake lock — it is released automatically on visibility loss
                    if ('wakeLock' in navigator) {
                        navigator.wakeLock.request('screen').catch(() => {});
                    }
                }
            });

            // Keep screen awake (initial acquisition)
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
            // Do NOT update status here — only update based on the result below.
            
            try {
                const response = await fetch(`/${CONFIG.orgCode}/monitor/data`, {
                    method: 'GET',
                    cache: 'no-store',
                    credentials: 'same-origin',
                    headers: { 
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
                    }
                });
                
                if (!response.ok) {
                    // Clone so we can read the body AND later inspect it without consuming the stream.
                    const bodyText = await response.clone().text().catch(() => '(could not read body)');
                    console.error(`[Monitor] Data fetch failed: HTTP ${response.status} ${response.statusText}`);
                    console.error('[Monitor] Response body:', bodyText.substring(0, 500));
                    throw new Error(`Network response was not ok: ${response.status}`);
                }
                
                const data = await response.json();

                // Always keep STATE.videos as the FULL library so any video (including
                // those not in the queue) can be looked up by current_video_id.
                if (Array.isArray(data.videos)) {
                    STATE.videos = data.videos;
                }
                // Separately track the admin's curated queue order (for auto-advance).
                // advancePlaylist() uses these IDs to cycle through videos in queue order.
                if (Array.isArray(data.playlist) && data.playlist.length > 0) {
                    STATE.playlistOrder = data.playlist.map(v => v.id);
                } else {
                    STATE.playlistOrder = [];
                }
                // Keep STATE.videoControl current so advancePlaylist() can reference it.
                if (data.video_control !== undefined) {
                    STATE.videoControl = data.video_control;
                }

                updateCountersDisplay(data.counters || [], data.waiting_queues || []);
                updateVideoPlayer(data.video_control || STATE.videoControl);
                updateMarqueeDisplay(data.marquee);

                // ── Persistence: save every successful response to localStorage
                // so a page reload never shows a blank screen.
                saveToLocalStorage(data);

                // ── Reset consecutive-failure tracking
                STATE.failureCount = 0;

                // ── If we were in slow-poll degraded mode, return to normal fast polling
                if (CONFIG._slowMode) {
                    CONFIG._slowMode = false;
                    clearInterval(refreshTimer);
                    refreshTimer = setInterval(refreshMonitorData, CONFIG.refreshInterval);
                    console.log('[Monitor] Restored to normal poll interval.');
                }

                // Mark initialized after the first successful poll
                STATE.isInitialized = true;

                // Only update status dot when recovering from a disconnected state
                if (!STATE.isConnected) {
                    STATE.isConnected = true;
                    updateConnectionStatus(true);
                }
            } catch (error) {
                console.error('Refresh failed:', error);

                // Track consecutive failures
                STATE.failureCount++;

                // After several failures, switch to a slower poll rate to reduce
                // server load during sustained outages. The display keeps showing
                // the last cached data — customer transactions are never interrupted.
                if (!CONFIG._slowMode && STATE.failureCount >= CONFIG.maxFailuresBeforeSlowdown) {
                    CONFIG._slowMode = true;
                    clearInterval(refreshTimer);
                    refreshTimer = setInterval(refreshMonitorData, CONFIG.slowPollInterval);
                    console.warn('[Monitor] Switched to slow-poll mode after', STATE.failureCount, 'consecutive failures.');
                }

                // Only show status change if we were previously connected AND
                // already initialized (so the blade-pre-rendered data stays visible
                // during the very first poll without alarming the display).
                if (STATE.isConnected && STATE.isInitialized) {
                    STATE.isConnected = false;
                    updateConnectionStatus(false);
                }

                // Do NOT call location.reload() — that causes an infinite reload loop.
                // The polling interval keeps retrying automatically.
                console.warn('[Monitor] Data fetch error, retrying automatically:', error.message);
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
                // ── The polling interval keeps running — NO stopRefreshCycle() here.
                // Stopping polling would freeze the display for customers if the
                // retry happened to also fail.  Let the interval handle retries
                // automatically (at normal rate, or slow-poll rate if degraded).
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
            
            // Only show online counters that have active queues
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
            
            // Detect new calls/notifications (for alerts)
            const alerts = detectAlerts(servingCounters);
            
            // Play notification sound, show banner, and announce via voice if there are NEW alerts
            if (alerts.length > 0 && STATE.previousServingState.size > 0) {
                const alertItem = alerts[0];
                const queueNumber = alertItem.queue?.queue_number || '';
                const counterNumber = alertItem.counter?.counter_number || '';
                const alertType = alertItem.alertType || 'call';
                
                console.log(`🚨 New alert detected: ${alertType.toUpperCase()} - Queue ${queueNumber} at Counter ${counterNumber}`);
                
                // Show visual banner
                showCallBanner(alertItem);
                
                // Play bell and voice announcement (train station style)
                // Bell plays FIRST, then voice after 1.5 seconds
                announceQueueCall(queueNumber, counterNumber, alertType);
            }
            
            // Update previous state for next comparison
            updatePreviousState(servingCounters);
            
            // Render serving items in compact format: Counter 1: 0002
            // Only the alerted counter blinks (5 times)
            const html = servingCounters.map(item => {
                const counter = item.counter;
                const queue = item.queue;
                // Only this specific counter will blink if it's in the alerts array
                const isAlert = alerts.some(a => a.queue?.id === queue?.id);
                const counterLabel = counter.display_name || `Counter ${counter.counter_number}`;
                
                return `
                    <div class="serving-row ${isAlert ? 'notify' : ''}">
                        <div class="serving-counter-label">${counterLabel}:</div>
                        <div class="serving-queue-number">${queue.queue_number}</div>
                    </div>
                `;
            }).join('');
            
            servingList.innerHTML = html;
        }
        
        function updateWaitingQueues(waitingGroups) {
            const waitingList = document.getElementById('waitingList');
            
            // Only show counters that have waiting queues
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
            
            // Compact format: Counter 1: 0001 0002 0003 0004
            const html = groups.map(group => {
                const counterName = group.display_name || `Counter ${group.counter_number}`;
                const queueNumbers = group.queues.map(q => q.queue_number).join('   ');
                
                return `
                    <div class="waiting-row">
                        <div class="waiting-counter-label">${counterName}:</div>
                        <div class="waiting-queue-numbers">
                            ${group.queues.map((q, i) => `<span class="waiting-queue-number alt-${i % 5}">${q.queue_number}</span>`).join('')}
                        </div>
                    </div>
                `;
            }).join('');
            
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
                    recent_recall: item.recent_recall,
                    status: queue.status
                };
                
                const prev = STATE.previousServingState.get(queueKey);
                
                // Detect different types of alerts:
                // 1. New queue called (Next Serve button clicked)
                const isNewCall = !prev || (prev.called_at !== current.called_at && current.called_at);
                
                // 2. Notify button clicked
                const notifyChanged = prev && prev.notified_at !== current.notified_at && current.notified_at;
                
                // 3. Recall button clicked
                const recallTriggered = current.recent_recall && (!prev || !prev.recent_recall);
                
                // Trigger alert for any of these events
                if (isNewCall || notifyChanged || recallTriggered) {
                    alerts.push({
                        ...item,
                        alertType: isNewCall ? 'call' : notifyChanged ? 'notify' : 'recall'
                    });
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
            
            // Update banner content with new call
            number.textContent = alertItem.queue?.queue_number || '—';
            counter.textContent = `Please proceed to Counter ${alertItem.counter?.counter_number || ''}`;
            
            // Show the banner with speaking animation
            banner.classList.add('show', 'speaking');
            
            // Remove speaking class after voice announcement completes (approximately 5 seconds)
            setTimeout(() => {
                banner.classList.remove('speaking');
            }, 5000);
            
            // Hide banner after duration
            if (callBannerTimer) clearTimeout(callBannerTimer);
            callBannerTimer = setTimeout(() => {
                banner.classList.remove('show', 'speaking');
            }, CONFIG.callBannerDuration);
        }
        
        function hideCallBanner() {
            const banner = document.getElementById('callBanner');
            if (banner) {
                banner.classList.remove('show');
            }
        }
        
        function playNotificationSound() {
            const audio = document.getElementById('notificationSound');
            if (!audio) {
                console.error('Notification sound element not found');
                return;
            }
            
            try {
                // Ensure audio is ready and unmuted
                audio.muted = false;
                audio.volume = 1.0;
                audio.currentTime = 0;
                
                // Attempt to play
                const playPromise = audio.play();
                
                if (playPromise !== undefined) {
                    playPromise
                        .then(() => {
                            console.log('✅ Notification sound played successfully');
                            updateAudioStatus('playing');
                        })
                        .catch(error => {
                            console.error('❌ Audio play failed:', error);
                            updateAudioStatus('blocked');
                            
                            // If autoplay is blocked, try to unlock audio again
                            if (error.name === 'NotAllowedError') {
                                console.log('Autoplay blocked - waiting for user interaction');
                                // Show visual indicator
                                showAudioBlockedMessage();
                            }
                        });
                }
            } catch (error) {
                console.error('Error playing notification sound:', error);
                updateAudioStatus('error');
            }
        }
        
        // Train station-style voice announcement
        // Plays notification bell FIRST, then voice announcement
        function announceQueueCall(queueNumber, counterNumber, alertType = 'call') {
            const audio = document.getElementById('notificationSound');
            
            if (!audio) {
                console.error('❌ Notification sound element not found');
                speakAnnouncement(queueNumber, counterNumber, alertType);
                return;
            }
            
            console.log('🚨 Announcement triggered:', { queue: queueNumber, counter: counterNumber, type: alertType });
            
            // Stop any currently playing audio to prevent interruption errors
            if (!audio.paused) {
                audio.pause();
                audio.currentTime = 0;
                console.log('🔇 Stopped previous audio playback.');
            }
            
            // Cancel any ongoing speech synthesis
            if (window.speechSynthesis && window.speechSynthesis.speaking) {
                window.speechSynthesis.cancel();
                console.log('🗣️ Cancelled ongoing speech.');
            }

            try {
                if (audio.readyState < 2) { // HAVE_NOTHING or HAVE_METADATA
                    console.log('⚠️ Audio not ready, loading...');
                    audio.load();
                }
                
                audio.muted = false;
                audio.volume = 1.0;
                audio.currentTime = 0;
                
                console.log('🔔 Playing notification bell...');
                const playPromise = audio.play();
                
                if (playPromise !== undefined) {
                    playPromise
                        .then(() => {
                            console.log('✅ Bell played successfully');
                            updateAudioStatus('playing');
                            
                            setTimeout(() => {
                                console.log('🎙️ Starting voice announcement...');
                                speakAnnouncement(queueNumber, counterNumber, alertType);
                            }, 1500);
                        })
                        .catch(error => {
                            console.error('❌ Bell play failed:', error);
                            updateAudioStatus('blocked');
                            
                            if (error.name === 'NotAllowedError') {
                                console.log('🔓 Attempting to unlock audio...');
                                unlockAudio();
                                showAudioBlockedMessage();
                            }
                            
                            setTimeout(() => {
                                speakAnnouncement(queueNumber, counterNumber, alertType);
                            }, 500);
                        });
                }
            } catch (error) {
                console.error('❌ Error in announceQueueCall:', error);
                updateAudioStatus('error');
                
                setTimeout(() => {
                    speakAnnouncement(queueNumber, counterNumber, alertType);
                }, 500);
            }
        }
        
        // Text-to-speech announcement function
        function speakAnnouncement(queueNumber, counterNumber, alertType = 'call') {
            if (!('speechSynthesis' in window)) {
                console.warn('Text-to-speech not supported in this browser');
                return;
            }
            
            try {
                // Cancel any ongoing speech
                window.speechSynthesis.cancel();
                
                // Format the queue number for better pronunciation
                const formattedQueueNumber = formatQueueNumberForSpeech(queueNumber);
                
                // Create the announcement message based on alert type
                let message;
                if (alertType === 'notify') {
                    message = `Attention, priority number ${formattedQueueNumber}. Please proceed immediately to counter ${counterNumber}`;
                } else if (alertType === 'recall') {
                    message = `Final call, priority number ${formattedQueueNumber}. Please proceed to counter ${counterNumber}`;
                } else {
                    message = `Now serving, priority number ${formattedQueueNumber}. Please proceed to counter ${counterNumber}`;
                }
                
                console.log('🔊 Voice announcement:', message);
                
                // Create speech synthesis utterance
                const utterance = new SpeechSynthesisUtterance(message);
                
                // Configure speech properties
                utterance.lang = 'en-US';
                utterance.rate = 0.9; // Slightly slower for clarity
                utterance.pitch = 1.0;
                utterance.volume = 1.0;
                
                // Try to use a female voice (more pleasant for announcements)
                const voices = window.speechSynthesis.getVoices();
                const preferredVoice = voices.find(voice => 
                    voice.lang.startsWith('en') && 
                    (voice.name.includes('Female') || voice.name.includes('Google') || voice.name.includes('Samantha'))
                );
                
                if (preferredVoice) {
                    utterance.voice = preferredVoice;
                    console.log('🎤 Using voice:', preferredVoice.name);
                }
                
                // Event handlers
                utterance.onstart = () => {
                    console.log('🎙️ Voice announcement started');
                    // Add speaking class to banner during speech
                    const banner = document.getElementById('callBanner');
                    if (banner) {
                        banner.classList.add('speaking');
                    }
                };
                
                utterance.onend = () => {
                    console.log('✅ Voice announcement completed');
                    // Remove speaking class after speech ends
                    const banner = document.getElementById('callBanner');
                    if (banner) {
                        setTimeout(() => {
                            banner.classList.remove('speaking');
                        }, 500);
                    }
                };
                
                utterance.onerror = (event) => {
                    console.error('❌ Voice announcement error:', event.error);
                };
                
                // Speak the announcement
                window.speechSynthesis.speak(utterance);
                
            } catch (error) {
                console.error('Error in voice announcement:', error);
            }
        }
        
        // Format queue number for better speech pronunciation
        function formatQueueNumberForSpeech(queueNumber) {
            // Convert number to individual digits for clearer pronunciation
            // Example: "0123" becomes "zero one two three"
            const digitWords = {
                '0': 'zero', '1': 'one', '2': 'two', '3': 'three', '4': 'four',
                '5': 'five', '6': 'six', '7': 'seven', '8': 'eight', '9': 'nine'
            };
            
            const digits = String(queueNumber).split('');
            const spokenDigits = digits.map(d => digitWords[d] || d).join(' ');
            
            return spokenDigits;
        }
        
        // Load voices when available (some browsers load voices asynchronously)
        if ('speechSynthesis' in window) {
            window.speechSynthesis.onvoiceschanged = () => {
                const voices = window.speechSynthesis.getVoices();
                console.log('🎤 Available voices:', voices.length);
            };
            
            // Trigger voice loading
            window.speechSynthesis.getVoices();
        }
        
        // Test function for manual testing
        function testNotificationSound() {
            console.log('🔔 Testing notification sound...');
            const audio = document.getElementById('notificationSound');
            
            if (!audio) {
                console.error('❌ Audio element not found');
                alert('Error: Audio element not found!');
                return;
            }
            
            console.log('📊 Audio Diagnostics:');
            console.log('- Ready State:', audio.readyState, '(0=HAVE_NOTHING, 1=HAVE_METADATA, 2=HAVE_CURRENT_DATA, 3=HAVE_FUTURE_DATA, 4=HAVE_ENOUGH_DATA)');
            console.log('- Network State:', audio.networkState, '(0=EMPTY, 1=IDLE, 2=LOADING, 3=NO_SOURCE)');
            console.log('- Paused:', audio.paused);
            console.log('- Muted:', audio.muted);
            console.log('- Volume:', audio.volume);
            console.log('- Duration:', audio.duration);
            console.log('- Current Src:', audio.currentSrc);
            console.log('- Error:', audio.error);
            
            const sources = audio.querySelectorAll('source');
            console.log('- Sources:', sources.length);
            sources.forEach((src, idx) => {
                console.log(`  Source ${idx + 1}:`, src.src.substring(0, 80) + '...', '(type:', src.type + ')');
            });
            
            // Try to play
            audio.currentTime = 0;
            audio.muted = false;
            audio.volume = 1.0;
            
            const playPromise = audio.play();
            if (playPromise) {
                playPromise
                    .then(() => {
                        console.log('✅ Test bell played successfully!');
                        alert('✅ Bell sound played successfully!');
                    })
                    .catch((error) => {
                        console.error('❌ Test bell failed:', error);
                        alert('❌ Bell sound failed: ' + error.message);
                    });
            }
        }
        
        // Test voice announcement with sample data
        function testVoiceAnnouncement() {
            console.log('🎙️ Testing voice announcement...');
            const sampleQueue = '0123';
            const sampleCounter = '5';
            const testType = 'call'; // Can be 'call', 'notify', or 'recall'
            
            announceQueueCall(sampleQueue, sampleCounter, testType);
            
            // Also show banner for visual feedback
            const banner = document.getElementById('callBanner');
            const number = document.getElementById('callBannerNumber');
            const counter = document.getElementById('callBannerCounter');
            
            if (banner) {
                number.textContent = sampleQueue;
                counter.textContent = `Please proceed to Counter ${sampleCounter}`;
                banner.classList.add('show', 'speaking');
                
                setTimeout(() => {
                    banner.classList.remove('speaking');
                }, 6000);
                
                setTimeout(() => {
                    banner.classList.remove('show');
                }, 10000);
            }
        }
        
        // Update audio status indicator
        function updateAudioStatus(status) {
            const statusEl = document.getElementById('audioStatus');
            if (statusEl) {
                const icon = statusEl.querySelector('i');
                const text = statusEl.querySelector('span');
                
                switch(status) {
                    case 'ready':
                        icon.className = 'fas fa-volume-up text-blue-400';
                        text.textContent = 'Bell Ready';
                        break;
                    case 'playing':
                        icon.className = 'fas fa-volume-up text-green-400';
                        text.textContent = 'Bell OK';
                        break;
                    case 'blocked':
                        icon.className = 'fas fa-volume-mute text-red-400';
                        text.textContent = 'Click to Enable';
                        break;
                    case 'error':
                        icon.className = 'fas fa-volume-xmark text-yellow-400';
                        text.textContent = 'Bell Error';
                        break;
                    default:
                        icon.className = 'fas fa-volume-up';
                        text.textContent = 'Test Bell';
                }
            }
        }
        
        // Show message when audio is blocked
        function showAudioBlockedMessage() {
            const message = document.createElement('div');
            message.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: rgba(0, 0, 0, 0.95);
                color: white;
                padding: 2rem;
                border-radius: 16px;
                border: 2px solid #ef4444;
                z-index: 10000;
                text-align: center;
                max-width: 400px;
            `;
            message.innerHTML = `
                <i class="fas fa-volume-xmark text-4xl mb-3" style="color: #ef4444;"></i>
                <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem;">Sound Blocked</h3>
                <p style="font-size: 0.875rem; color: rgba(255,255,255,0.8); margin-bottom: 1rem;">
                    Click anywhere on the screen to enable notification sounds
                </p>
                <button onclick="this.parentElement.remove(); unlockAudio();" style="
                    background: #10b981;
                    color: white;
                    padding: 0.75rem 1.5rem;
                    border-radius: 8px;
                    border: none;
                    font-weight: 600;
                    cursor: pointer;
                ">Enable Sound</button>
            `;
            document.body.appendChild(message);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (message.parentElement) {
                    message.remove();
                }
            }, 5000);
        }
        
        // ========================================
        // YOUTUBE IFRAME API HELPERS
        // ========================================

        /** Extract the YouTube video ID from an embed URL like https://www.youtube.com/embed/VIDEOID */
        function extractYouTubeId(embedUrl) {
            if (!embedUrl) return null;
            const match = embedUrl.match(/\/embed\/([^?&#/]+)/);
            return match ? match[1] : null;
        }

        /**
         * Create the YT.Player ONCE into the persistent #yt-overlay.
         * After this call STATE.ytPlayer is reused forever — no destroy/recreate.
         * To switch videos call STATE.ytPlayer.loadVideoById(newId) directly.
         */
        function _createYouTubePlayer(ytId, videoControl) {
            STATE.ytPlayer = new YT.Player('yt-player-div', {
                videoId: ytId,
                playerVars: {
                    autoplay: 1,
                    mute: 1,          // start muted for autoplay policy; unmute on user interaction
                    modestbranding: 1,
                    rel: 0,
                    playsinline: 1,
                    controls: 0,
                },
                events: {
                    onReady: function (event) {
                        STATE.ytPlayerReady = true;
                        event.target.playVideo();
                        applyYouTubeControls(videoControl);
                        // Show the overlay now that the player is ready
                        const overlay = document.getElementById('yt-overlay');
                        if (overlay) overlay.style.display = 'block';
                        console.log('[YouTube] Persistent player ready — video:', ytId);
                        // Drain any pending switch that arrived before API was ready
                        if (STATE.pendingYouTubeLoad) {
                            const p = STATE.pendingYouTubeLoad;
                            STATE.pendingYouTubeLoad = null;
                            _ytSwitch(p.ytId, p.videoControl);
                        }
                    },
                    onStateChange: function (event) {
                        if (event.data === YT.PlayerState.ENDED) {
                            advancePlaylist();
                        }
                    },
                    onError: function (event) {
                        console.error('[YouTube] Player error:', event.data);
                    }
                }
            });
        }

        /**
         * Fast video switch — uses loadVideoById() so the player pre-buffers
         * the new video without any iframe teardown. Typically < 1 s to start.
         */
        function _ytSwitch(ytId, videoControl) {
            const overlay = document.getElementById('yt-overlay');
            if (overlay) overlay.style.display = 'block';

            if (STATE.ytCurrentVideoId !== ytId) {
                STATE.ytCurrentVideoId = ytId;
                STATE.lastVolume = -1;  // force volume re-apply
                try {
                    STATE.ytPlayer.loadVideoById(ytId);   // pre-buffers + plays instantly
                } catch (e) {
                    console.error('[YouTube] loadVideoById failed:', e);
                }
            }
            applyYouTubeControls(videoControl);
        }

        /**
         * Apply the admin-controlled volume and play/pause state to the live YouTube player.
         * Called on every poll tick when the same YouTube video is already loaded.
         */
        function applyYouTubeControls(videoControl) {
            if (!STATE.ytPlayer || typeof STATE.ytPlayer.setVolume !== 'function') return;

            const vol = (videoControl && typeof videoControl.volume === 'number') ? videoControl.volume : 80;

            if (vol !== STATE.lastVolume) {
                STATE.lastVolume = vol;
                STATE.ytPlayer.setVolume(vol);
                if (STATE.ytAudioUnlocked) {
                    STATE.ytPlayer.unMute();
                }
            }

            if (videoControl && !videoControl.is_playing) {
                try { STATE.ytPlayer.pauseVideo(); } catch (e) {}
            } else {
                try { STATE.ytPlayer.playVideo(); } catch (e) {}
            }
        }

        // ========================================
        // VIDEO PLAYER UPDATE
        // ========================================
        
        function updateVideoPlayer(videoControl) {
            const player  = document.getElementById('videoPlayer');
            const overlay = document.getElementById('yt-overlay');

            // ── Helper: hide YT overlay and silence any ghost audio ─────────────
            function _hideYT() {
                if (overlay) overlay.style.display = 'none';
                if (STATE.ytPlayer && STATE.ytPlayerReady) {
                    // Pause instead of destroy — keeps the player alive for next use.
                    try { STATE.ytPlayer.pauseVideo(); } catch (e) {}
                }
                STATE.ytCurrentVideoId = null;
            }

            // ── Paused / stopped by admin ────────────────────────────────────────
            if (!videoControl || !videoControl.is_playing) {
                clearTimeout(STATE.playlistTimer);
                STATE.playlistTimer = null;
                _hideYT();
                player.innerHTML = `
                    <div class="no-video">
                        <i class="fas fa-pause-circle"></i>
                        <p>Display paused</p>
                    </div>
                `;
                return;
            }

            if (!STATE.videos || STATE.videos.length === 0) {
                _hideYT();
                player.innerHTML = `
                    <div class="no-video">
                        <i class="fas fa-video-slash"></i>
                        <p>No videos available</p>
                    </div>
                `;
                return;
            }

            // ── Snap rotation index when admin explicitly picks a video ──────────
            const serverVideoId   = videoControl.current_video_id ? parseInt(videoControl.current_video_id) : null;
            const controlUpdatedAt = videoControl.updated_at ?? null;
            // Detect a new play command: either a different video OR admin re-played
            // the same video (updated_at changed but video_id stayed the same).
            const videoChanged   = serverVideoId !== STATE.lastServerVideoId;
            const replayDetected = !videoChanged && serverVideoId &&
                                   controlUpdatedAt && controlUpdatedAt !== STATE.lastControlUpdatedAt;
            if (videoChanged || replayDetected) {
                STATE.lastServerVideoId    = serverVideoId;
                STATE.lastControlUpdatedAt = controlUpdatedAt;
                if (serverVideoId) {
                    const idx = STATE.videos.findIndex(v => parseInt(v.id) === serverVideoId);
                    if (idx !== -1) STATE.playlistRotationIndex = idx;
                }
                clearTimeout(STATE.playlistTimer);
                STATE.playlistTimer = null;
            }

            const video = STATE.videos[STATE.playlistRotationIndex] || STATE.videos[0];
            if (!video) {
                _hideYT();
                player.innerHTML = `<div class="no-video"><i class="fas fa-video"></i><p>Video not available</p></div>`;
                return;
            }

            const multipleVideos = STATE.videos.length > 1;

            // ════════════════════════════════════════════════════════════════════
            // YOUTUBE PATH — persistent player, loadVideoById for instant switch
            // ════════════════════════════════════════════════════════════════════
            if (video.is_youtube && video.youtube_embed_url) {
                const ytId = extractYouTubeId(video.youtube_embed_url);
                if (!ytId) {
                    _hideYT();
                    player.innerHTML = `<div class="no-video"><i class="fas fa-video"></i><p>Invalid YouTube URL</p></div>`;
                    return;
                }

                // Stop any file <video> element to prevent ghost audio
                const existingFileVideo = player.querySelector('video');
                if (existingFileVideo) {
                    existingFileVideo.pause();
                    existingFileVideo.src = '';
                    existingFileVideo.remove();
                }
                // Clear the idle / paused placeholder text
                const noVideoEl = player.querySelector('.no-video');
                if (noVideoEl) noVideoEl.remove();

                if (!STATE.ytPlayer) {
                    // ── First time: create the persistent player ─────────────────
                    STATE.ytCurrentVideoId = ytId;
                    if (STATE.ytApiReady) {
                        _createYouTubePlayer(ytId, videoControl);
                    } else {
                        // API script hasn't loaded yet — queue it
                        STATE.pendingYouTubeLoad = { ytId, videoControl };
                    }
                } else if (STATE.ytPlayerReady) {
                    // ── Player already exists — fast switch via loadVideoById ────
                    _ytSwitch(ytId, videoControl);
                } else {
                    // Player exists but not ready yet — queue the switch
                    STATE.pendingYouTubeLoad = { ytId, videoControl };
                }

            // ════════════════════════════════════════════════════════════════════
            // FILE VIDEO PATH
            // ════════════════════════════════════════════════════════════════════
            } else if (video.file_path) {
                // Hide the YT overlay (player stays alive, just out of view)
                _hideYT();

                const src = `/storage/${video.file_path}`;
                const existing = player.querySelector('video');

                // Live volume / pause sync for an already-playing file video
                if (existing && existing.querySelector(`source[src="${src}"]`)) {
                    const vol = (videoControl && typeof videoControl.volume === 'number') ? (videoControl.volume / 100) : 0.8;
                    existing.volume = vol;
                    if (!videoControl.is_playing) {
                        existing.pause();
                    } else if (existing.paused) {
                        existing.play().catch(() => {});
                    }
                } else {
                    clearTimeout(STATE.playlistTimer);
                    STATE.playlistTimer = null;
                    player.innerHTML = `
                        <video autoplay style="width:100%;height:100%;object-fit:cover;">
                            <source src="${src}" type="video/mp4">
                        </video>
                    `;
                    const videoEl = player.querySelector('video');
                    if (videoEl) {
                        videoEl.muted  = true;
                        videoEl.volume = (videoControl && typeof videoControl.volume === 'number') ? (videoControl.volume / 100) : 0.8;
                        videoEl.loop   = !multipleVideos;
                        if (multipleVideos) {
                            videoEl.addEventListener('ended', advancePlaylist, { once: true });
                        }
                        videoEl.play().catch(e => console.log('[Video] play failed:', e));
                    }
                }

            } else {
                _hideYT();
                player.innerHTML = `<div class="no-video"><i class="fas fa-video"></i><p>Video not available</p></div>`;
            }

            STATE.currentVideo = video;
        }

        // Advance to the next video in the playlist and re-render the player.
        function advancePlaylist() {
            clearTimeout(STATE.playlistTimer);
            STATE.playlistTimer = null;
            if (!STATE.videos || STATE.videos.length === 0) return;

            // Use admin's queue order if defined; fall back to full library order.
            const order = STATE.playlistOrder.length > 0 ? STATE.playlistOrder : STATE.videos.map(v => v.id);
            if (order.length <= 1) return;

            // Find where the current video sits in the sequence, then advance.
            const currentId = STATE.videos[STATE.playlistRotationIndex]?.id;
            const orderIdx  = currentId ? order.indexOf(parseInt(currentId)) : -1;
            const nextId    = order[(orderIdx + 1) % order.length];
            const nextIdx   = STATE.videos.findIndex(v => parseInt(v.id) === parseInt(nextId));
            STATE.playlistRotationIndex = nextIdx !== -1 ? nextIdx : 0;

            updateVideoPlayer(STATE.videoControl);
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

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js', { scope: '/' })
                    .then(function (registration) {
                        registration.update();
                        registration.addEventListener('updatefound', function () {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', function () {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    console.log('[SW] New version available.');
                                }
                            });
                        });
                    })
                    .catch(function (err) {
                        console.warn('[SW] Registration failed:', err);
                    });
            });
        }
    </script>
</body>
</html>