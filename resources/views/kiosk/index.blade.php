<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $organization->organization_name ?? 'Queue Kiosk' }} - Queue Kiosk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: {{ $settings->primary_color ?? '#3b82f6' }};
            --secondary: {{ $settings->secondary_color ?? '#8b5cf6' }};
            --accent: {{ $settings->accent_color ?? '#10b981' }};
            --text-color: {{ $settings->text_color ?? '#ffffff' }};
            /* legacy variable names used elsewhere in templates */
            --primary-color: {{ $settings->primary_color ?? '#3b82f6' }};
            --secondary-color: {{ $settings->secondary_color ?? '#8b5cf6' }};
            --accent-color: {{ $settings->accent_color ?? '#10b981' }};
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        *, ::before, ::after {
            box-sizing: border-box;
            border-width: 2px;
            border-style: solid;
            border-color: #e5e7eb;
        }
        html, body {
            height: 100%;
            min-height: 100dvh;
            overflow: hidden;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        body {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%);
            background-attachment: fixed;
        }

        .glass-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* helper: center contents inside a glass card when applied */
        .glass-card.center {
            /* display: flex; */
            align-items: center;
            justify-content: center;
            padding: 10px;
        }
        .counter-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            transform: translateZ(0);
        }
        .counter-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 32px 64px rgba(0,0,0,0.2);
        }
        .counter-card:active { transform: translateY(-4px) scale(0.98); }

        .counter-card.selected {
            ring: 4px solid var(--accent);
            ring-offset: 4px;
            ring-offset-white;
        }

        @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.5); } 50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.8); } }
        .pulse-glow { animation: pulse-glow 2s infinite; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fadeInUp { animation: fadeInUp 0.6s ease-out; }

        @keyframes fadeInScale { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        .animate-fadeInScale { animation: fadeInScale 0.5s ease-out; }

        @keyframes slideInRight { from { opacity: 0; transform: translateX(50px); } to { opacity: 1; transform: translateX(0); } }
        .animate-slideInRight { animation: slideInRight 0.5s ease-out; }

        @keyframes progress { 0% { width: 0%; } 100% { width: 100%; } }
        .progress-bar { animation: progress 2s ease-in-out; }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.3); border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.5); }

        .touch-target { min-height: 44px; min-width: 44px; }

        /* Enhanced responsive counter grid for all devices */
        .counter-grid {
            display: grid;
            gap: 0.75rem;
            padding: 0.75rem;
            width: 100%;
            justify-content: center;
        }
        
        /* Mobile (default): 1-2 columns based on available space */
        .counter-grid {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }
        
        /* Small mobile: single column for very small screens */
        @media (max-width: 360px) {
            .counter-grid {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            }
        }
        
        /* Tablet: 2-3 columns */
        @media (min-width: 640px) {
            .counter-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 1rem;
                padding: 1rem;
            }
        }
        
        /* Desktop: 3-4 columns */
        @media (min-width: 1024px) {
            .counter-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 1.25rem;
                padding: 1.25rem;
            }
        }
        
        /* Large desktop: 4-5 columns */
        @media (min-width: 1280px) {
            .counter-grid {
                grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
                gap: 1.5rem;
                padding: 1.5rem;
            }
        }
        
        /* Counter button styling */
        .counter-btn {
            width: 100%;
            min-height: 100px;
            padding: 0.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-radius: 0.75rem;
            background: white;
            border: 2px solid rgba(0,0,0,0.1);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .counter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }
        
        .counter-btn:active {
            transform: translateY(0);
        }
        
        /* Counter number styling */
        .counter-btn .counter-number {
            width: 48px;
            height: 48px;
            font-size: 1.1rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            color: white;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
            box-shadow: 0 3px 6px rgba(0,0,0,0.15);
        }
        
        /* Counter title and description */
        .counter-btn .counter-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }
        
        .counter-btn .counter-desc {
            font-size: 0.75rem;
            color: rgba(15,23,42,0.6);
            line-height: 1.2;
        }
        
        /* Status indicator */
        .counter-btn .status-indicator {
            margin-top: 0.5rem;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.7rem;
            font-weight: 600;
            background: rgba(34, 197, 94, 0.1);
            color: rgb(21, 128, 61);
        }
        
        /* Queue number responsive sizes */
        .queue-number { font-size: 4rem; }
        @media (min-width: 640px) { .queue-number { font-size: 5rem; } }
        @media (min-width: 768px) { .queue-number { font-size: 6rem; } }
        @media (min-width: 1024px) { .queue-number { font-size: 7rem; } }
        /* vertical center helper */
        .vertical-center { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:1rem; min-height: calc(100dvh - 2rem); }
    </style>
</head>
<body>
    <!-- Background particles removed for cleaner kiosk look -->
    
    <!-- Settings Button -->
    <button onclick="showSettings()"
            class="fixed top-6 right-6 z-50 glass-card px-4 py-3 rounded-2xl shadow-2xl hover:shadow-3xl transition-all transform hover:scale-105 text-white font-semibold text-sm touch-target">
        <i class="fas fa-cog text-xl"></i>
    </button>
    
    <!-- Main Container -->
    <div class="main-container vertical-center relative z-10 p-2 sm:p-4">
        
        <!-- Step Indicator -->
        <div class="step-header spacing-sm">
            <div class="flex justify-center">
                <div class="glass-card center padding-sm rounded-2xl shadow-xl step-indicators-container">
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
                         @if(isset($settings) && $settings->logo_url)    
                            <img src="{{ $settings->logo_url }}" alt="Organization Logo" class="h-10 w-auto rounded-lg shadow-sm">
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
                            <div id="countersGrid" class="counter-grid">
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
                <div class="glass-card center rounded-2xl sm:rounded-3xl shadow-2xl padding-md text-center max-w-2xl w-full">
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
                <div class="glass-card center rounded-xl sm:rounded-2xl shadow-lg padding-sm sm:padding-md text-center max-w-lg sm:max-w-xl w-full" id="queueContent">
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
                        <div class="relative z-10 p-4 sm:p-6">
                            <p class="text-[10px] sm:text-xs font-bold mb-2 tracking-widest opacity-90" 
                               style="color: var(--text-color);">
                                YOUR QUEUE NUMBER
                            </p>
                            <div class="queue-number-size font-black spacing-sm tracking-wider pulse-animation" 
                                 id="queueNumber" 
                                 style="color: var(--text-color); text-shadow: 0 6px 12px rgba(0,0,0,0.15);"></div>
                            <div class="btn-text font-bold mb-1" id="counterInfo" style="color: var(--text-color);"></div>
                            <div class="text-xs sm:text-sm opacity-90" id="queueTime" style="color: var(--text-color);"></div>
                            <div id="ticketSignature" class="text-xs sm:text-sm opacity-80 mt-1" style="color: var(--text-color);">Sig: N/A</div>
                            <div class="mt-2 inline-flex items-center justify-center space-x-2 glass-card px-3 py-1 rounded-full" style="background: rgba(255,255,255,0.06);">
                                <i class="fas fa-qrcode text-white text-sm"></i>
                                <span class="text-xs text-white font-semibold">Verifiable — Scan QR or visit verify link</span>
                            </div>
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
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 spacing-sm">
                        <button onclick="printQueue()" 
                                class="m-1 sm:m-1 px-4 sm:px-5 py-2 sm:py-2.5 rounded-md sm:rounded-lg font-bold shadow-md transform hover:scale-[1.01] hover:shadow-lg transition-all text-white btn-text text-sm"
                                style="background: linear-gradient(135deg, var(--accent-color), var(--primary-color));">
                            <i class="fas fa-print mr-2 text-sm"></i>
                            <span class="hidden sm:inline">Print Number</span>
                            <span class="sm:hidden">Print</span>
                        </button>
                        <button onclick="capturePhoto()" 
                                class="m-1 sm:m-1 px-4 sm:px-5 py-2 sm:py-2.5 rounded-md sm:rounded-lg font-bold shadow-md transform hover:scale-[1.01] hover:shadow-lg transition-all btn-text text-sm"
                                style="background: linear-gradient(135deg, var(--secondary-color), var(--accent-color)); color: var(--text-color);">
                            <i class="fas fa-camera mr-2 text-sm"></i>
                            <span class="hidden sm:inline">Save to Gallery</span>
                            <span class="sm:hidden">Save</span>
                        </button>
                    </div>

                    <button onclick="finishAndReset()" 
                            class="m-1 sm:m-1 w-full px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg sm:rounded-xl font-bold shadow-md transform hover:scale-[1.01] hover:shadow-lg transition-all text-white btn-text text-sm"
                            style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: var(--text-color);">
                        <i class="fas fa-redo mr-2 text-sm"></i>Get Another Number
                    </button>

                    <style>
                        /* small margin for counter cards and glass cards to create breathing room */
                        .counter-btn, .glass-card, .btn-text { margin: .35rem; }
                    </style>
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
            button.className = 'counter-btn';
            button.onclick = () => selectCounter(counter.id, counter.counter_number, counter.display_name);
            button.innerHTML = `
                <div class="counter-btn-inner">
                    <div class="counter-number-wrapper">
                        <div class="counter-number">${counter.counter_number}</div>
                    </div>
                    <div class="counter-title">${counter.display_name}</div>
                    <div class="counter-desc">${counter.short_description || 'Ready to serve'}</div>
                    <div class="counter-status">
                        <span class="status-indicator"></span>
                        <span class="status-text">Available</span>
                    </div>
                </div>
                <div class="counter-action">
                    <i class="fas fa-hand-pointer"></i>
                    <span>Tap to select</span>
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
                    console.log('Generated queue response:', data.queue);
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
        
        console.log('showQueueDisplay queue:', queue);
        document.getElementById('queueNumber').textContent = displayNumber;
        document.getElementById('counterInfo').textContent = 
            `Counter ${queue.counter.counter_number} - ${queue.counter.display_name}`;
        document.getElementById('queueTime').textContent = `Generated at ${timeString}`;
        const sigEl = document.getElementById('ticketSignature');
        if (sigEl) {
            sigEl.textContent = 'Sig: ' + (queue.signature ? String(queue.signature).slice(0, 10) : 'N/A');
        }
        
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
        console.log('capturePhoto called', currentQueue);
        if (!currentQueue) {
            alert('No queue number to capture');
            return;
        }

        try {
            generateTicketImage(currentQueue).then(() => {
                console.log('generateTicketImage resolved');
                alert('Queue ticket saved to your device.');
            }).catch(err => {
                console.error('Save failed (generateTicketImage):', err);
                alert('Could not save ticket image. Please try printing instead.');
            });
        } catch (err) {
            console.error('capturePhoto unexpected error:', err);
            alert('Unexpected error occurred. Check console for details.');
        }
    }

    async function generateTicketImage(queue) {
        console.log('generateTicketImage called', queue);
        return new Promise((resolve, reject) => {
            try {
                const orgName = `{{ $organization->organization_name }}` || 'Default Organization';
                const now = new Date();
                const nowStr = now.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });
                const displayNumber = String(queue.queue_number).split('-').pop();

                const scale = 2; // export scale for sharper image
                const width = 600;
                const height = 1100;

                const canvas = document.createElement('canvas');
                canvas.width = width * scale;
                canvas.height = height * scale;
                canvas.style.width = width + 'px';
                canvas.style.height = height + 'px';

                const ctx = canvas.getContext('2d');
                ctx.scale(scale, scale);

                // Background
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, width, height);

                // Helpers
                function centerText(text, y, font, color) {
                    ctx.font = font;
                    ctx.fillStyle = color || '#111827';
                    const metrics = ctx.measureText(text);
                    const x = (width - metrics.width) / 2;
                    ctx.fillText(text, x, y);
                }

                // Organization name
                ctx.textBaseline = 'top';
                centerText(orgName, 20, '800 20px Arial', '#111827');

                // QUEUE MANAGEMENT / SYSTEM (two-line header)
                centerText('QUEUE MANAGEMENT', 52, '700 18px Arial', '#111');
                centerText('SYSTEM', 76, '700 18px Arial', '#111');

                // Title
                centerText('PRIORITY NUMBER', 110, '700 16px Arial', '#111');

                // Large number area
                const boxY = 140;
                const boxHeight = 240;
                ctx.fillStyle = '#ffffff';
                ctx.strokeStyle = '#111';
                ctx.lineWidth = 3;
                ctx.fillRect(40, boxY, width - 80, boxHeight);
                ctx.strokeRect(40, boxY, width - 80, boxHeight);

                // Number text (big)
                ctx.textBaseline = 'middle';
                ctx.fillStyle = '#000';
                ctx.font = '900 140px Arial';
                let numberMetrics = ctx.measureText(displayNumber);
                ctx.fillText(displayNumber, (width - numberMetrics.width) / 2, boxY + boxHeight / 2);

                // Spaced barcode-like line: * 8 9 5 8 *
                const spaced = displayNumber.split('').join(' ');
                const barcodeText = `* ${spaced} *`;
                centerText(barcodeText, boxY + boxHeight + 36, '700 28px Courier New, monospace', '#111');

                // Counter info (two lines as in sample)
                centerText('Counter ' + queue.counter.counter_number, boxY + boxHeight + 86, '700 20px Arial', '#111');
                centerText(queue.counter.display_name, boxY + boxHeight + 112, '600 18px Arial', '#111');

                // Instructions block
                const instrY = boxY + boxHeight + 150;
                const instrX = 60;
                ctx.font = '700 14px Arial';
                ctx.fillStyle = '#111';
                ctx.fillText('📋 INSTRUCTIONS', instrX, instrY);

                ctx.font = '14px Arial';
                const lines = [
                    '1. Watch the monitor display',
                    '2. Listen for your number',
                    `3. Proceed to Counter ${queue.counter.counter_number}`,
                    '4. Keep this ticket visible'
                ];
                let ly = instrY + 28;
                lines.forEach(line => {
                    ctx.fillText(line, instrX, ly);
                    ly += 24;
                });

                // Generated timestamp and footer
                ctx.font = '13px Arial';
                ctx.fillStyle = '#444';
                centerText('Generated: ' + nowStr, ly + 18, '13px Arial', '#444');
                centerText('Thank you for your patience!', ly + 44, '700 14px Arial', '#111');

                // Include full verification URL + short signature snippet for tamper detection
                try {
                    const verifyUrl = `${location.origin}/{{ $companyCode }}/kiosk/verify-ticket?queue_number=${encodeURIComponent(queue.queue_number)}&signature=${encodeURIComponent(queue.signature)}`;
                    ctx.font = '11px Arial';
                    ctx.fillStyle = '#444';
                    // Print a short visible signature
                    if (queue.signature) {
                        const sig = String(queue.signature).slice(0, 10);
                        centerText('Sig: ' + sig, ly + 60, '12px Courier New, monospace', '#666');
                    }
                    // Print verification URL (small)
                    ctx.font = '11px Arial';
                    ctx.fillStyle = '#0066cc';
                    // Truncate URL display for layout but keep full URL in the QR
                    const shortUrl = verifyUrl.length > 64 ? verifyUrl.slice(0, 60) + '...' : verifyUrl;
                    centerText('Verify: ' + shortUrl, ly + 84, '11px Arial', '#0066cc');

                    // Generate QR code image via Google Charts and draw it on the ticket.
                    // Size in px
                    const qrSize = 180;
                    const qrSrc = `https://chart.googleapis.com/chart?cht=qr&chs=${qrSize}x${qrSize}&chl=${encodeURIComponent(verifyUrl)}`;
                    const img = new Image();
                    img.crossOrigin = 'Anonymous';
                    img.onload = () => {
                        try {
                            // draw QR at bottom-right area
                            const qrX = width - 40 - qrSize;
                            const qrY = ly + 100;
                            ctx.drawImage(img, qrX, qrY, qrSize, qrSize);

                            // proceed to export
                            canvas.toBlob(blob => {
                                if (!blob) {
                                    console.error('toBlob returned null after QR');
                                    return reject(new Error('Blob generation failed'));
                                }
                                try {
                                    const url = URL.createObjectURL(blob);
                                    const a = document.createElement('a');
                                    a.href = url;
                                    a.download = `ticket-${queue.queue_number}.png`;
                                    document.body.appendChild(a);
                                    a.click();
                                    document.body.removeChild(a);
                                    URL.revokeObjectURL(url);
                                    resolve();
                                } catch (err) {
                                    console.error('Error triggering download (after QR):', err);
                                    return reject(err);
                                }
                            }, 'image/png');
                        } catch (err) {
                            console.error('Failed drawing QR:', err);
                            // fallback: export without QR
                            canvas.toBlob(blob => {
                                if (!blob) return reject(new Error('Blob generation failed'));
                                const url = URL.createObjectURL(blob);
                                const a = document.createElement('a');
                                a.href = url;
                                a.download = `ticket-${queue.queue_number}.png`;
                                document.body.appendChild(a);
                                a.click();
                                document.body.removeChild(a);
                                URL.revokeObjectURL(url);
                                resolve();
                            }, 'image/png');
                        }
                    };
                    img.onerror = (e) => {
                        console.error('QR image load error', e);
                        // export without QR
                        canvas.toBlob(blob => {
                            if (!blob) return reject(new Error('Blob generation failed'));
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = `ticket-${queue.queue_number}.png`;
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);
                            resolve();
                        }, 'image/png');
                    };
                    img.src = qrSrc;
                    // return here; onload will handle toBlob
                    return;
                } catch (err) {
                    console.error('Failed to render verify URL on ticket:', err);
                }

                // Convert and download
                canvas.toBlob(blob => {
                    if (!blob) {
                        console.error('toBlob returned null');
                        return reject(new Error('Blob generation failed'));
                    }
                    try {
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `ticket-${queue.queue_number}.png`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                        resolve();
                    } catch (err) {
                        console.error('Error triggering download:', err);
                        return reject(err);
                    }
                }, 'image/png');
            } catch (err) {
                reject(err);
            }
        });
    }
    </script>
</body>
</html>