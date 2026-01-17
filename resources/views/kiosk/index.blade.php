<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $organization->organization_name }} - Queue Kiosk</title>
    <script src="/js/counter-realtime.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: {{ $settings->primary_color }};
            --secondary-color: {{ $settings->secondary_color }};
            --accent-color: {{ $settings->accent_color }};
            --text-color: {{ $settings->text_color }};
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            min-height: 100dvh;
            width: 100vw;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            position: relative;
        }
        
        /* Animated background particles */
        .bg-particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .bg-particles::before,
        .bg-particles::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: float 20s ease-in-out infinite;
        }
        
        .bg-particles::before {
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }
        
        .bg-particles::after {
            bottom: -100px;
            right: -100px;
            animation-delay: 10s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(50px, 50px) scale(1.1); }
            50% { transform: translate(-50px, 100px) scale(0.9); }
            75% { transform: translate(100px, -50px) scale(1.05); }
        }
        
        /* Glass morphism effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
        }
        
        /* Step indicator animations */
        .step-indicator {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .step-active {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            transform: scale(1.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .step-completed {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }
        
        /* Counter button effects */
        .counter-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .counter-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(59, 130, 246, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .counter-btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .counter-btn:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        .counter-btn:active {
            transform: translateY(-4px) scale(1.01);
        }
        
        /* Pulse animation */
        @keyframes pulse-scale {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .pulse-animation {
            animation: pulse-scale 2s ease-in-out infinite;
        }
        
        /* Fade in animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .animate-fadeInScale {
            animation: fadeInScale 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 12px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: var(--accent-color);
        }
        
        /* Progress bar animation */
        @keyframes progressSlide {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(400%); }
        }
        
        .progress-bar {
            animation: progressSlide 1.5s ease-in-out infinite;
        }
        
        /* Badge ping animation */
        @keyframes ping-small {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            75%, 100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }
        
        .animate-ping-small {
            animation: ping-small 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        
        /* Viewport-based responsive sizing */
        .main-container {
            height: 100dvh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Let the grid area truly fill available height */
        .counter-grid-height {
            flex: 1;
            min-height: 0;
            max-height: none;
        }
        
        .step-header {
            flex-shrink: 0;
        }
        
        .step-content {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        /* Height-based responsive text and spacing */
        @media (max-height: 600px) {
            .logo-size { height: 2.5rem !important; }
            .title-size { font-size: 1.5rem !important; }
            .subtitle-size { font-size: 1rem !important; }
            .section-title { font-size: 1.25rem !important; }
            .counter-grid-height { max-height: none !important; }
            .queue-number-size { font-size: 2.25rem !important; }
            .step-indicator-text { font-size: 0.75rem !important; }
            .step-indicator-number { font-size: 1rem !important; }
            .btn-text { font-size: 0.875rem !important; }
            .spacing-sm { margin-bottom: 0.5rem !important; }
            .spacing-md { margin-bottom: 0.75rem !important; }
            .spacing-lg { margin-bottom: 1rem !important; }
            .padding-sm { padding: 0.5rem !important; }
            .padding-md { padding: 1rem !important; }
            .counter-card-padding { padding: 0.75rem !important; }
        }
        
        @media (min-height: 601px) and (max-height: 800px) {
            .logo-size { height: 3.5rem !important; }
            .title-size { font-size: 2.5rem !important; }
            .subtitle-size { font-size: 1.25rem !important; }
            .section-title { font-size: 1.5rem !important; }
            .counter-grid-height { max-height: none !important; }
            .queue-number-size { font-size: 3rem !important; }
            .step-indicator-text { font-size: 0.875rem !important; }
            .step-indicator-number { font-size: 1.5rem !important; }
            .btn-text { font-size: 1rem !important; }
            .spacing-sm { margin-bottom: 0.75rem !important; }
            .spacing-md { margin-bottom: 1rem !important; }
            .spacing-lg { margin-bottom: 1.5rem !important; }
            .padding-sm { padding: 0.75rem !important; }
            .padding-md { padding: 1.5rem !important; }
            .counter-card-padding { padding: 1rem !important; }
        }
        
        @media (min-height: 801px) and (max-height: 1000px) {
            .logo-size { height: 4rem !important; }
            .title-size { font-size: 3rem !important; }
            .subtitle-size { font-size: 1.5rem !important; }
            .section-title { font-size: 1.75rem !important; }
            .counter-grid-height { max-height: none !important; }
            .queue-number-size { font-size: 3.75rem !important; }
            .step-indicator-text { font-size: 0.875rem !important; }
            .step-indicator-number { font-size: 1.75rem !important; }
            .btn-text { font-size: 1rem !important; }
            .spacing-sm { margin-bottom: 1rem !important; }
            .spacing-md { margin-bottom: 1.5rem !important; }
            .spacing-lg { margin-bottom: 2rem !important; }
            .padding-sm { padding: 1rem !important; }
            .padding-md { padding: 2rem !important; }
            .counter-card-padding { padding: 1.25rem !important; }
        }
        
        @media (min-height: 1001px) {
            .logo-size { height: 5rem !important; }
            .title-size { font-size: 4rem !important; }
            .subtitle-size { font-size: 2rem !important; }
            .section-title { font-size: 2rem !important; }
            .counter-grid-height { max-height: none !important; }
            .queue-number-size { font-size: 4.5rem !important; }
            .step-indicator-text { font-size: 1rem !important; }
            .step-indicator-number { font-size: 2rem !important; }
            .btn-text { font-size: 1.125rem !important; }
            .spacing-sm { margin-bottom: 1rem !important; }
            .spacing-md { margin-bottom: 2rem !important; }
            .spacing-lg { margin-bottom: 2.5rem !important; }
            .padding-sm { padding: 1rem !important; }
            .padding-md { padding: 2.5rem !important; }
            .counter-card-padding { padding: 1.5rem !important; }
        }
        
        /* Width responsive adjustments */
        @media (max-width: 480px) {
            .step-indicators-container { transform: scale(0.75); }
            .counter-grid-cols { grid-template-columns: repeat(1, minmax(0, 1fr)) !important; }
            .settings-btn { top: 0.5rem !important; right: 0.5rem !important; font-size: 0.75rem !important; padding: 0.5rem !important; }
            .settings-btn-icon { font-size: 1rem !important; }
        }
        
        @media (min-width: 481px) and (max-width: 640px) {
            .step-indicators-container { transform: scale(0.85); }
            .counter-grid-cols { grid-template-columns: repeat(1, minmax(0, 1fr)) !important; }
        }
        
        @media (min-width: 641px) and (max-width: 1024px) {
            .counter-grid-cols { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        }
        
        @media (min-width: 1025px) and (max-width: 1440px) {
            .counter-grid-cols { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
        }
        
        @media (min-width: 1441px) {
            .counter-grid-cols { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
        }
        
        /* Landscape mode adjustments for mobile */
        @media (max-height: 500px) and (orientation: landscape) {
            .logo-size { height: 2rem !important; }
            .title-size { font-size: 1.25rem !important; }
            .subtitle-size { font-size: 0.875rem !important; }
            .section-title { font-size: 1rem !important; }
            .counter-grid-height { max-height: none !important; }
            .queue-number-size { font-size: 1.75rem !important; }
            .step-indicator-text { font-size: 0.625rem !important; }
            .step-indicator-number { font-size: 0.875rem !important; }
            .btn-text { font-size: 0.75rem !important; }
            .spacing-sm { margin-bottom: 0.25rem !important; }
            .spacing-md { margin-bottom: 0.5rem !important; }
            .spacing-lg { margin-bottom: 0.75rem !important; }
            .padding-sm { padding: 0.5rem !important; }
            .padding-md { padding: 0.75rem !important; }
            .counter-card-padding { padding: 0.5rem !important; }
            .step-indicators-container { transform: scale(0.7); }
        }
        
        /* Touch-friendly targets */
        @media (hover: none) and (pointer: coarse) {
            .counter-btn { min-height: 60px; }
            button, .btn-primary { min-height: 48px; }
        }
        
        /* Print styles */
        @media print {
            .settings-btn, .step-indicators-container, 
            button[onclick="testPrint()"], 
            button[onclick="saveSettings()"] { 
                display: none !important; 
            }
        }
    </style>
</head>
<body>
    <!-- Background particles removed for cleaner kiosk look -->
    
    <!-- Settings Button -->
    <button onclick="showSettings()" 
            class="settings-btn fixed top-4 right-4 z-50 glass-card px-3 sm:px-6 py-2 sm:py-3 rounded-xl shadow-2xl hover:shadow-3xl transition-all transform hover:scale-105 text-gray-700 font-semibold text-sm sm:text-base">
        <i class="settings-btn-icon fas fa-cog mr-0 sm:mr-2 text-lg sm:text-xl"></i>
        <span class="hidden sm:inline">Settings</span>
    </button>
    
    <!-- Main Container -->
    <div class="main-container relative z-10 p-2 sm:p-4">
        
        <!-- Step Indicator -->
        <div class="step-header spacing-sm">
            <div class="flex justify-center">
                <div class="glass-card padding-sm rounded-2xl shadow-xl step-indicators-container">
                    <div class="flex items-center space-x-2 sm:space-x-4">
                        <div id="step1Indicator" class="step-indicator step-active px-3 sm:px-6 py-2 sm:py-3 rounded-xl flex flex-col items-center min-w-[60px] sm:min-w-[80px]">
                            <div class="step-indicator-number font-bold mb-0.5 sm:mb-1">1</div>
                            <div class="step-indicator-text font-semibold">Select</div>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400 text-lg sm:text-2xl"></i>
                        <div id="step2Indicator" class="step-indicator bg-gray-100 text-gray-600 px-3 sm:px-6 py-2 sm:py-3 rounded-xl flex flex-col items-center min-w-[60px] sm:min-w-[80px]">
                            <div class="step-indicator-number font-bold mb-0.5 sm:mb-1">2</div>
                            <div class="step-indicator-text font-semibold">Process</div>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400 text-lg sm:text-2xl"></i>
                        <div id="step3Indicator" class="step-indicator bg-gray-100 text-gray-600 px-3 sm:px-6 py-2 sm:py-3 rounded-xl flex flex-col items-center min-w-[60px] sm:min-w-[80px]">
                            <div class="step-indicator-number font-bold mb-0.5 sm:mb-1">3</div>
                            <div class="step-indicator-text font-semibold">Done</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Step 1: Counter Selection -->
        <div id="step1" class="step-content animate-fadeInUp">
            <div class="flex flex-col h-full">
                <!-- Header -->
                <div class="text-center flex-shrink-0 mb-2">
                    <div class="flex items-center justify-center gap-2 sm:gap-3 mb-2">
                        @if($settings->company_logo)
                             @if(isset($settings) && $settings->logo_url)
                                <img src="{{ $settings->logo_url }}" alt="Organization Logo" class="h-10 w-auto rounded-lg shadow-sm">
                            @endif
                        @else
                            <div class="logo-size flex items-center justify-center rounded-lg" style="background: rgba(255,255,255,0.2); max-height: 50px;">
                                <i class="fas fa-building text-white text-2xl"></i>
                            </div>
                        @endif
                        <div>
                            <h1 class="title-size font-black drop-shadow-lg animate-fadeInScale leading-tight" 
                                style="color: #ffffff;" data-org-name>
                                {{ $organization->organization_name }}
                            </h1>
                            <p class="text-xs sm:text-sm font-bold drop-shadow-md" 
                               style="color: var(--text-color); opacity: 0.85;">
                                Queue Kiosk
                            </p>
                        </div>
                    </div>
                    <div class="inline-block glass-card px-3 sm:px-6 py-1.5 sm:py-2 rounded-lg sm:rounded-xl text-xs sm:text-sm">
                        <p class="font-semibold flex items-center justify-center gap-1.5" style="color: var(--text-color); opacity: 0.9;">
                            <i class="fas fa-hand-pointer"></i>
                            <span class="hidden sm:inline">Select a counter to get your queue number</span>
                            <span class="sm:hidden">Tap to select</span>
                        </p>
                    </div>
                </div>
                
                <!-- Counters Grid -->
                <div class="flex-1 min-h-0 flex flex-col">
                    <div class="glass-card rounded-2xl sm:rounded-3xl shadow-2xl padding-md flex-1 min-h-0 flex flex-col">
                        <h2 class="section-title font-bold spacing-sm text-center flex-shrink-0" style="color: var(--primary-color);">
                            <i class="fas fa-desktop mr-2"></i>
                            <span class="hidden sm:inline">Available Service Counters</span>
                            <span class="sm:hidden">Select Counter</span>
                        </h2>
                        
                        <div class="counter-grid-height overflow-y-auto custom-scrollbar pr-1 flex-1 min-h-0">
                            <div id="countersGrid" class="counter-grid-cols grid gap-2 sm:gap-4">
                                <!-- Counters will be injected here -->
                            </div>
                            <div id="noCounters" class="hidden text-center py-8 sm:py-16">
                                <div class="inline-block p-4 sm:p-6 bg-gray-100 rounded-full spacing-sm">
                                    <i class="fas fa-clock text-gray-400 text-4xl sm:text-6xl"></i>
                                </div>
                                <h3 class="section-title font-bold text-gray-700 spacing-sm">No Counters Available</h3>
                                <p class="text-gray-500 btn-text spacing-sm">All service counters are currently offline</p>
                                <div class="flex items-center justify-center space-x-2">
                                    <i class="fas fa-spinner fa-spin text-blue-600 text-xl sm:text-3xl"></i>
                                    <span class="text-gray-600 font-medium text-sm sm:text-base">Checking...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Step 2: Generating -->
        <div id="step2" class="hidden step-content animate-fadeInUp">
            <div class="flex items-center justify-center h-full">
                <div class="glass-card rounded-2xl sm:rounded-3xl shadow-2xl padding-md text-center max-w-2xl w-full">
                    <div class="spacing-md">
                        <div class="inline-block p-6 sm:p-8 rounded-full spacing-sm shadow-2xl" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                            <i class="fas fa-spinner fa-spin text-white text-5xl sm:text-7xl"></i>
                        </div>
                        <h2 class="section-title sm:text-4xl font-bold spacing-sm" style="color: var(--primary-color);">
                            Processing Your Request
                        </h2>
                        <p class="btn-text text-gray-600 font-medium">Generating your queue number...</p>
                    </div>
                    
                    <div class="relative bg-gray-200 h-3 sm:h-4 rounded-full overflow-hidden">
                        <div class="absolute h-full w-1/3 bg-gradient-to-r from-blue-500 via-purple-600 to-blue-500 progress-bar"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Step 3: Queue Display -->
        <div id="step3" class="hidden step-content animate-fadeInUp">
            <div class="flex items-center justify-center h-full overflow-y-auto custom-scrollbar">
                <div class="glass-card rounded-xl sm:rounded-2xl shadow-lg padding-sm sm:padding-md text-center max-w-lg sm:max-w-xl w-full" id="queueContent">
                    <div class="spacing-sm sm:spacing-md">
                        <div class="inline-block p-2.5 sm:p-3 rounded-full spacing-sm shadow-lg" style="background: linear-gradient(135deg, var(--accent-color), var(--primary-color));">
                            <i class="fas fa-check-circle text-white text-2xl sm:text-4xl"></i>
                        </div>
                        <h2 class="section-title sm:text-3xl font-black spacing-sm" style="color: var(--accent-color);">
                            Success!
                        </h2>
                        <p class="btn-text font-semibold" style="color: var(--primary-color); opacity: 0.9;">Your queue number is ready</p>
                    </div>
                    
                    <!-- Queue Number Card -->
                    <div class="relative overflow-hidden rounded-xl sm:rounded-2xl spacing-sm sm:spacing-md shadow-lg" 
                         style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                        <div class="absolute inset-0 opacity-10" 
                             style="background-image: radial-gradient(circle, rgba(255,255,255,0.4) 2px, transparent 2px); background-size: 30px 30px;"></div>
                        <div class="relative z-10 padding-sm">
                            <p class="text-[10px] sm:text-xs font-bold mb-2 tracking-widest opacity-90" 
                               style="color: var(--text-color);">
                                YOUR QUEUE NUMBER
                            </p>
                            <div class="queue-number-size font-black spacing-sm tracking-wider pulse-animation" 
                                 id="queueNumber" 
                                 style="color: var(--text-color); text-shadow: 0 6px 12px rgba(0,0,0,0.15);"></div>
                            <div class="btn-text font-bold mb-1" id="counterInfo" style="color: var(--text-color);"></div>
                            <div class="text-xs sm:text-sm opacity-90" id="queueTime" style="color: var(--text-color);"></div>
                        </div>
                    </div>
                    
                    <!-- Important Notice -->
                    <div class="glass-card border-l-4 rounded-lg sm:rounded-xl padding-sm spacing-sm sm:spacing-md text-left shadow-sm" style="border-left-color: var(--accent-color);">
                        <p class="text-gray-800 font-medium flex items-start text-xs sm:text-sm">
                            <i class="fas fa-info-circle text-base mr-2 mt-0.5 flex-shrink-0" style="color: var(--accent-color);"></i>
                            <span>
                                <strong class="block mb-1 text-xs sm:text-sm" style="color: var(--primary-color);">Important:</strong>
                                <span class="text-xs sm:text-sm">Please wait for your number to be called on the display monitor.</span>
                            </span>
                        </p>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 spacing-sm">
                        <button onclick="printQueue()" 
                                class="px-4 sm:px-5 py-2 sm:py-3 rounded-lg sm:rounded-xl font-bold shadow-md transform hover:scale-[1.01] hover:shadow-lg transition-all text-white btn-text"
                                style="background: linear-gradient(135deg, var(--accent-color), var(--primary-color));">
                            <i class="fas fa-print mr-2 text-sm sm:text-lg"></i>
                            <span class="hidden sm:inline">Print Number</span>
                            <span class="sm:hidden">Print</span>
                        </button>
                        <button onclick="capturePhoto()" 
                                class="px-4 sm:px-5 py-2 sm:py-3 rounded-lg sm:rounded-xl font-bold shadow-md transform hover:scale-[1.01] hover:shadow-lg transition-all btn-text"
                                style="background: linear-gradient(135deg, var(--secondary-color), var(--accent-color)); color: var(--text-color);">
                            <i class="fas fa-camera mr-2 text-sm sm:text-lg"></i>
                            <span class="hidden sm:inline">Take Screenshot</span>
                            <span class="sm:hidden">Screenshot</span>
                        </button>
                    </div>
                    
                    <button onclick="finishAndReset()" 
                            class="w-full px-4 sm:px-5 py-2 sm:py-3 rounded-xl sm:rounded-2xl font-bold shadow-md transform hover:scale-[1.01] hover:shadow-lg transition-all text-white btn-text"
                            style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: var(--text-color);">
                        <i class="fas fa-redo mr-2 text-sm sm:text-base"></i>Get Another Number
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Settings Modal -->
    <div id="settingsModal" class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 backdrop-blur-sm p-2 sm:p-4">
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-2xl max-w-lg w-full transform transition-all animate-fadeInScale mx-2 max-h-[95vh] overflow-y-auto">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 px-4 sm:px-6 py-3 sm:py-5 rounded-t-xl sm:rounded-t-2xl sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white bg-opacity-20 rounded-lg sm:rounded-xl flex items-center justify-center">
                            <i class="fas fa-cog text-white text-lg sm:text-2xl"></i>
                        </div>
                        <h2 class="text-lg sm:text-2xl font-bold text-white">Printer Settings</h2>
                    </div>
                    <button onclick="closeSettings()" 
                            class="text-white hover:bg-white hover:bg-opacity-10 rounded-lg p-1.5 sm:p-2 transition min-w-[40px] min-h-[40px] flex items-center justify-center">
                        <i class="fas fa-times text-xl sm:text-2xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Body -->
            <div class="p-4 sm:p-6 space-y-3 sm:space-y-4">
                <!-- Printer Selection -->
                <div class="bg-gray-50 rounded-lg sm:rounded-xl p-3 sm:p-4">
                    <label class="block text-sm sm:text-base font-bold text-gray-700 mb-2 sm:mb-3 flex items-center">
                        <i class="fas fa-print text-blue-600 mr-2"></i>Printer Type
                    </label>
                    <div class="space-y-2 sm:space-y-3">
                        <label class="flex items-center p-3 sm:p-4 bg-white rounded-lg sm:rounded-xl border-2 cursor-pointer hover:border-blue-400 transition-all group min-h-[56px]">
                            <input type="radio" name="printerType" value="thermal" checked 
                                   onchange="updatePrinterSettings()" 
                                   class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 flex-shrink-0">
                            <div class="ml-2 sm:ml-3 flex-1">
                                <div class="font-bold text-sm sm:text-base text-gray-800 group-hover:text-blue-600 transition">USB Thermal Printer (80mm)</div>
                                <div class="text-xs sm:text-sm text-gray-500">Direct thermal printing via USB</div>
                            </div>
                            <i class="fas fa-receipt text-blue-600 text-xl sm:text-2xl opacity-30 group-hover:opacity-100 transition"></i>
                        </label>
                        
                        <label class="flex items-center p-3 sm:p-4 bg-white rounded-lg sm:rounded-xl border-2 cursor-pointer hover:border-blue-400 transition-all group min-h-[56px]">
                            <input type="radio" name="printerType" value="browser" 
                                   onchange="updatePrinterSettings()" 
                                   class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 flex-shrink-0">
                            <div class="ml-2 sm:ml-3 flex-1">
                                <div class="font-bold text-sm sm:text-base text-gray-800 group-hover:text-blue-600 transition">Browser Print</div>
                                <div class="text-xs sm:text-sm text-gray-500">Standard browser print dialog</div>
                            </div>
                            <i class="fas fa-print text-blue-600 text-xl sm:text-2xl opacity-30 group-hover:opacity-100 transition"></i>
                        </label>
                        
                        <label class="flex items-center p-3 sm:p-4 bg-white rounded-lg sm:rounded-xl border-2 cursor-pointer hover:border-blue-400 transition-all group min-h-[56px]">
                            <input type="radio" name="printerType" value="none" 
                                   onchange="updatePrinterSettings()" 
                                   class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 flex-shrink-0">
                            <div class="ml-2 sm:ml-3 flex-1">
                                <div class="font-bold text-sm sm:text-base text-gray-800 group-hover:text-blue-600 transition">Screenshot Only</div>
                                <div class="text-xs sm:text-sm text-gray-500">Save as image file</div>
                            </div>
                            <i class="fas fa-camera text-blue-600 text-xl sm:text-2xl opacity-30 group-hover:opacity-100 transition"></i>
                        </label>
                    </div>
                </div>
                
                <!-- Thermal Printer Settings -->
                <div id="thermalSettings" class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg sm:rounded-xl p-3 sm:p-4">
                    <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-usb text-blue-600 mr-2"></i>Vendor ID (Optional)
                    </label>
                    <input type="text" id="vendorId" placeholder="0x0fe6" 
                           class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:border-blue-500 focus:outline-none transition text-sm sm:text-base min-h-[44px]">
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Leave empty for default (0x0fe6 - Bixolon)
                    </p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 rounded-b-xl sm:rounded-b-2xl flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 sticky bottom-0">
                <button onclick="testPrint()" 
                        class="flex-1 px-4 sm:px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg sm:rounded-xl font-bold shadow-lg hover:shadow-xl transition-all hover:scale-105 text-sm sm:text-base min-h-[48px]">
                    <i class="fas fa-print mr-2"></i>Test Print
                </button>
                <button onclick="saveSettings()" 
                        class="flex-1 px-4 sm:px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg sm:rounded-xl font-bold shadow-lg hover:shadow-xl transition-all hover:scale-105 text-sm sm:text-base min-h-[48px]">
                    <i class="fas fa-save mr-2"></i>Save Settings
                </button>
            </div>
        </div>
    </div>

    <script>
    let currentQueue = null;
    let connectedPrinter = null;
    let printerSettings = {
        type: 'thermal',
        vendorId: '0x0fe6'
    };
    const countersEndpoint = '{{ route('kiosk.counters', ['organization_code' => $companyCode]) }}';
    const initialCounters = @json($onlineCounters);

    // Load settings from localStorage
    function loadSettings() {
        const saved = localStorage.getItem('kioskPrinterSettings');
        if (saved) {
            printerSettings = JSON.parse(saved);
        }
    }

    function renderCounters(counters) {
        const countersGrid = document.getElementById('countersGrid');
        const noCounters = document.getElementById('noCounters');
        countersGrid.innerHTML = '';

        if (!counters || counters.length === 0) {
            noCounters.classList.remove('hidden');
            return;
        }

        noCounters.classList.add('hidden');

        counters.forEach(counter => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'counter-btn relative bg-white border-2 border-gray-200 rounded-xl sm:rounded-2xl counter-card-padding text-left shadow-lg hover:shadow-xl transition-all';
            button.onclick = () => selectCounter(counter.id, counter.counter_number, counter.display_name);
            button.innerHTML = `
                <div class="flex items-start justify-between mb-2 sm:mb-4">
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-lg sm:rounded-xl flex items-center justify-center text-base sm:text-xl font-black text-white shadow-lg" style="background: linear-gradient(135deg, var(--accent-color), var(--primary-color));">
                            ${counter.counter_number}
                        </div>
                        <div>
                            <div class="text-sm sm:text-xl font-bold text-gray-800 mb-0.5 sm:mb-1">${counter.display_name}</div>
                            <div class="text-xs sm:text-sm text-gray-500 line-clamp-1">${counter.short_description || 'Ready to serve'}</div>
                        </div>
                    </div>
                    <span class="relative inline-flex items-center px-2 py-1 sm:px-3 sm:py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700 flex-shrink-0">
                        <span class="absolute -top-1 -right-1 w-2 h-2 sm:w-3 sm:h-3 bg-green-500 rounded-full animate-ping-small"></span>
                        <span class="relative hidden sm:inline">Available</span>
                        <span class="relative sm:hidden">●</span>
                    </span>
                </div>
                <div class="flex items-center text-blue-600 font-semibold text-xs sm:text-base">
                    <i class="fas fa-hand-pointer mr-1 sm:mr-2"></i>
                    <span class="hidden sm:inline">Tap to select</span>
                    <span class="sm:hidden">Tap here</span>
                </div>
            `;
            countersGrid.appendChild(button);
        });
    }

    function refreshCounters() {
        if (refreshCounters.inFlight) return;
        refreshCounters.inFlight = true;

        try {
            if (refreshCounters.controller) {
                refreshCounters.controller.abort();
            }
            refreshCounters.controller = new AbortController();
        } catch (e) {
            refreshCounters.controller = null;
        }

        fetch(countersEndpoint, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Accept': 'application/json' },
            signal: refreshCounters.controller ? refreshCounters.controller.signal : undefined,
        })
            .then(response => response.ok ? response.json() : Promise.reject(response))
            .then(data => {
                // Update the initial counters data
                initialCounters.splice(0, initialCounters.length, ...(data.counters || []));

                // Only re-render if on step 1
                if (!document.getElementById('step1').classList.contains('hidden')) {
                    renderCounters(data.counters || []);
                }
            })
            .catch(error => {
                if (error && error.name === 'AbortError') return;
                console.error('Refresh failed:', error);
            })
            .finally(() => {
                refreshCounters.inFlight = false;
            });
    }

    refreshCounters.inFlight = false;
    refreshCounters.controller = null;

    // function refreshColorSettings() {
    //     const orgCode = '{{ $companyCode }}';
    //     fetch(`/${orgCode}/api/settings`)
    //         .then(response => response.json())
    //         .then(data => {
    //             const root = document.documentElement;
    //             if (data.primary_color) root.style.setProperty('--primary-color', data.primary_color);
    //             if (data.secondary_color) root.style.setProperty('--secondary-color', data.secondary_color);
    //             if (data.accent_color) root.style.setProperty('--accent-color', data.accent_color);
    //             if (data.text_color) root.style.setProperty('--text-color', data.text_color);
    //         })
    //         .catch(error => console.error('Color settings refresh failed:', error));
    // }

    loadSettings();
    renderCounters(initialCounters);
    setInterval(refreshCounters, 5000);
    // setInterval(refreshColorSettings, 5000);

    // Queue generation optimizations
    let isGenerating = false;    function selectCounter(counterId, counterNumber, counterName) {
        if (isGenerating) return;
        
        // Validate counter_id before proceeding
        if (!counterId || counterId === undefined || counterId === null) {
            console.error('Invalid counter_id:', counterId);
            showError('Invalid counter selection. Please try again.');
            return;
        }
        
        isGenerating = true;
        moveToStep(2);
        document.querySelectorAll('.counter-btn').forEach(btn => btn.disabled = true);

        const attemptGenerate = async (didRetry = false) => {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 15000);

            try {
                console.log('Sending counter_id:', counterId);
                const url = `{{ route('kiosk.generate', ['organization_code' => $companyCode]) }}?counter_id=${encodeURIComponent(counterId)}`;
                console.log('Request URL:', url);
                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    signal: controller.signal,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const status = response.status;
                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    data = { success: false, message: `Server error (status ${status})` };
                }



                if (!response.ok) {
                    throw new Error(data.message || `Request failed with status ${status}`);
                }

                if (data.success && data.queue) {
                    currentQueue = data.queue;
                    showQueueDisplay(data.queue);
                } else {
                    throw new Error(data.message || 'Failed to generate queue number');
                }
            } catch (error) {
                console.error('Generate failed:', error);
                const msg = (error && error.name === 'AbortError')
                    ? 'Request timed out. Please try again.'
                    : (error.message || 'Error generating queue number. Please try again.');
                showError(msg);
            } finally {
                clearTimeout(timeoutId);
            }
        };

        attemptGenerate();
    }

    function moveToStep(stepNumber) {
        ['step1', 'step2', 'step3'].forEach(id => {
            document.getElementById(id).classList.add('hidden');
        });

        ['step1Indicator', 'step2Indicator', 'step3Indicator'].forEach((id, index) => {
            const elem = document.getElementById(id);
            elem.classList.remove('step-active', 'step-completed', 'bg-gray-100', 'text-gray-600');
            
            if (index + 1 < stepNumber) {
                elem.classList.add('step-completed');
            } else if (index + 1 === stepNumber) {
                elem.classList.add('step-active');
            } else {
                elem.classList.add('bg-gray-100', 'text-gray-600');
            }
        });

        document.getElementById('step' + stepNumber).classList.remove('hidden');
    }

    function showQueueDisplay(queue) {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
        
        const queueParts = String(queue.queue_number).split('-');
        const displayNumber = queueParts[queueParts.length - 1];
        
        document.getElementById('queueNumber').textContent = displayNumber;
        document.getElementById('counterInfo').textContent = 
            `Counter ${queue.counter.counter_number} - ${queue.counter.display_name}`;
        document.getElementById('queueTime').textContent = `Generated at ${timeString}`;
        
        moveToStep(3);
        isGenerating = false;
    }

    function showError(message) {
        alert(message);
        document.querySelectorAll('.counter-btn').forEach(btn => btn.disabled = false);
        moveToStep(1);
        isGenerating = false;
    }

    function finishAndReset() {
        currentQueue = null;
        moveToStep(1);
        document.querySelectorAll('.counter-btn').forEach(btn => btn.disabled = false);
        isGenerating = false;
    }

    function showSettings() {
        document.getElementById('settingsModal').classList.remove('hidden');
        document.querySelectorAll('input[name="printerType"]').forEach(input => {
            input.checked = input.value === printerSettings.type;
        });
        document.getElementById('vendorId').value = printerSettings.vendorId || '';
        updatePrinterSettings();
    }

    function closeSettings() {
        document.getElementById('settingsModal').classList.add('hidden');
    }

    function updatePrinterSettings() {
        const selected = document.querySelector('input[name="printerType"]:checked').value;
        const thermalSettings = document.getElementById('thermalSettings');
        
        if (selected === 'thermal') {
            thermalSettings.classList.remove('hidden');
        } else {
            thermalSettings.classList.add('hidden');
        }
    }

    function saveSettings() {
        printerSettings.type = document.querySelector('input[name="printerType"]:checked').value;
        printerSettings.vendorId = document.getElementById('vendorId').value || '0x0fe6';
        
        localStorage.setItem('kioskPrinterSettings', JSON.stringify(printerSettings));
        
        alert('Settings saved successfully!');
        closeSettings();
    }

    function testPrint() {
        if (!currentQueue) {
            currentQueue = {
                queue_number: 'TEST-0001',
                counter: {
                    counter_number: '1',
                    display_name: 'Test Counter'
                }
            };
        }
        printQueue();
    }

    function printQueue() {
        if (!currentQueue) {
            alert('No queue number to print');
            return;
        }
        
        switch(printerSettings.type) {
            case 'thermal':
                printToThermalPrinter();
                break;
            case 'browser':
                printToBrowser();
                break;
            case 'none':
                capturePhoto();
                break;
            default:
                printToBrowser();
        }
    }

    function printToThermalPrinter() {
        if (!navigator.usb) {
            alert('USB printing not supported in this browser. Using browser print instead.');
            printToBrowser();
            return;
        }

        if (connectedPrinter) {
            sendToPrinter(connectedPrinter);
            return;
        }

        let vendorId = parseInt(printerSettings.vendorId);
        if (isNaN(vendorId)) vendorId = 0x0fe6;

        navigator.usb.requestDevice({ filters: [{ vendorId: vendorId }] })
            .then(device => {
                connectedPrinter = device;
                return device.open();
            })
            .then(() => sendToPrinter(connectedPrinter))
            .catch(error => {
                console.log('Thermal printer error:', error);
                alert('Could not connect to thermal printer. Using browser print instead.');
                connectedPrinter = null;
                printToBrowser();
            });
    }

    async function sendToPrinter(device) {
        try {
            if (!device.opened) await device.open();
            if (device.configuration === null) await device.selectConfiguration(1);
            await device.claimInterface(0);

            const encoder = new TextEncoder();
            const now = new Date().toLocaleString('en-US', { 
                month: 'short', day: 'numeric', year: 'numeric',
                hour: '2-digit', minute: '2-digit', hour12: true 
            });
            
            const commands = [
                '\x1B\x40', '\x1B\x61\x01', '\x1B\x45\x01', '\x1D\x21\x11',
                '{{ $organization->organization_name }}\n',
                '\x1B\x45\x00', '\x1D\x21\x00', '\n',
                'QUEUE MANAGEMENT SYSTEM\n',
                '================================\n', '\n',
                '\x1B\x45\x01', '\x1D\x21\x11', 'Priority Number\n',
                '\x1D\x21\x22', currentQueue.queue_number.split('-').pop() + '\n',
                '\x1D\x21\x00', '\x1B\x45\x00', '\n',
                '================================\n',
                '\x1B\x45\x01', 'Counter ' + currentQueue.counter.counter_number + '\n',
                '\x1B\x45\x00', currentQueue.counter.display_name + '\n', '\n',
                '================================\n',
                'INSTRUCTIONS:\n',
                '1. Watch the monitor display\n',
                '2. Listen for your number\n',
                '3. Proceed to Counter ' + currentQueue.counter.counter_number + '\n',
                '================================\n', '\n',
                'Generated: ' + now + '\n',
                '\x1B\x61\x01', 'Thank you!\n', '\n\n\n', '\x1D\x56\x00',
            ];

            await device.transferOut(1, encoder.encode(commands.join('')));
            console.log('Print job sent successfully');
            
        } catch (error) {
            console.error('Print error:', error);
            alert('Failed to print: ' + error.message);
            connectedPrinter = null;
        }
    }

    function printToBrowser() {
        if (!currentQueue) return;
        
        const printWindow = window.open('', '_blank', 'width=350,height=500');
        const now = new Date().toLocaleString('en-US', { 
            month: 'short', day: 'numeric', year: 'numeric',
            hour: '2-digit', minute: '2-digit', hour12: true 
        });
        
        const displayNumber = currentQueue.queue_number.split('-').pop();
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Queue Number - ${displayNumber}</title>
                <style>
                    @media print { @page { margin: 0; size: 80mm auto; } body { margin: 0; padding: 10mm; } }
                    body { font-family: Arial, sans-serif; text-align: center; padding: 20px; max-width: 300px; margin: 0 auto; }
                    .header { font-size: 24px; font-weight: bold; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px double #000; }
                    .organization-name { font-size: 18px; margin-bottom: 10px; color: #333; }
                    .title { font-size: 18px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; }
                    .queue-number { font-size: 84px; font-weight: bold; margin: 30px 0; letter-spacing: 4px; 
                                   border: 5px solid #000; padding: 25px; background: linear-gradient(135deg, #f0f0f0, #fff); 
                                   box-shadow: inset 0 2px 4px rgba(0,0,0,0.1); }
                    .counter-info { font-size: 28px; font-weight: bold; margin: 25px 0 10px 0; }
                    .counter-name { font-size: 20px; margin-bottom: 25px; color: #333; }
                    .instructions { font-size: 15px; margin: 25px 0; padding: 20px; border-top: 3px double #000; 
                                   border-bottom: 3px double #000; line-height: 1.8; text-align: left; }
                    .instructions strong { display: block; text-align: center; margin-bottom: 10px; font-size: 16px; }
                    .footer { font-size: 13px; color: #666; margin-top: 20px; padding-top: 15px; border-top: 2px dashed #999; }
                    .barcode { margin: 20px 0; padding: 15px; background: #fff; border: 2px solid #333; 
                              font-family: 'Courier New', monospace; font-size: 18px; letter-spacing: 3px; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class="organization-name" data-org-name>{{ $organization->organization_name }}</div>
                <div class="header">QUEUE MANAGEMENT SYSTEM</div>
                <div class="title">Priority Number</div>
                <div class="queue-number">${displayNumber}</div>
                <div class="barcode">*${currentQueue.queue_number}*</div>
                <div class="counter-info">Counter ${currentQueue.counter.counter_number}</div>
                <div class="counter-name">${currentQueue.counter.display_name}</div>
                <div class="instructions">
                    <strong>📋 INSTRUCTIONS</strong>
                    1. Watch the monitor display<br>
                    2. Listen for your number<br>
                    3. Proceed to Counter ${currentQueue.counter.counter_number}<br>
                    4. Keep this ticket visible
                </div>
                <div class="footer">Generated: ${now}<br><strong>Thank you for your patience!</strong></div>
            </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.onload = function() {
            setTimeout(() => {
                printWindow.print();
                setTimeout(() => printWindow.close(), 500);
            }, 250);
        };
    }

    function capturePhoto() {
        if (!currentQueue) {
            alert('No queue number to capture');
            return;
        }

        const element = document.getElementById('queueContent');
        
        if (typeof html2canvas === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
            script.onload = () => captureWithHtml2Canvas(element);
            document.head.appendChild(script);
        } else {
            captureWithHtml2Canvas(element);
        }
    }

    function captureWithHtml2Canvas(element) {
        html2canvas(element, {
            backgroundColor: '#ffffff',
            scale: 2,
            logging: false,
            windowWidth: element.scrollWidth,
            windowHeight: element.scrollHeight
        }).then(canvas => {
            canvas.toBlob(blob => {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `queue-number-${currentQueue.queue_number}.png`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                alert('Queue number saved as image!');
            });
        }).catch(error => {
            console.error('Capture error:', error);
            alert('Could not capture photo. Please try print instead.');
        });
    }
    </script>
</body>
</html>