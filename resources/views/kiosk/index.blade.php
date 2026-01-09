<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk - {{ $settings->company_name }}</title>
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
            height: 100vh;
            overflow: hidden;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        .kiosk-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }
        /* Animated background pattern */
        .kiosk-container::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            animation: backgroundMove 20s ease-in-out infinite;
        }
        @keyframes backgroundMove {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-50px, -50px); }
        }
        .kiosk-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            overflow-y: auto;
            position: relative;
            z-index: 1;
        }
        /* Glassmorphism card effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .step-indicator {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .step-indicator::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            background: #e5e7eb;
            top: 20px;
            left: 50%;
            z-index: -1;
        }
        .step-active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--text-color);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            transform: scale(1.05);
        }
        .step-completed {
            background: linear-gradient(135deg, var(--accent-color), #059669);
            color: white;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bg-brand-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        /* Counter button hover effect */
        .counter-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .counter-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        .counter-btn:hover::before {
            left: 100%;
        }
        .counter-btn:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border-color: var(--primary-color);
        }
        .counters-grid-container {
            max-height: calc(100vh - 28rem);
            overflow-y: auto;
            padding-right: 4px;
        }
        .counters-grid-container::-webkit-scrollbar {
            width: 10px;
        }
        .counters-grid-container::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }
        .counters-grid-container::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
        }
        .counters-grid-container::-webkit-scrollbar-thumb:hover {
            background: var(--accent-color);
        }
        /* Pulse animation for queue number */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .pulse-animation {
            animation: pulse 2s ease-in-out infinite;
        }
        /* Button ripple effect */
        .btn-primary {
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }
        .btn-primary:active {
            transform: scale(0.95);
        }
        /* Orientation-specific styles */
        @media (orientation: landscape) and (max-height: 500px) {
            .kiosk-content {
                padding: 0.5rem;
            }
            .step-indicator {
                font-size: 0.875rem;
                padding: 0.25rem 0.5rem !important;
            }
            h1, .text-2xl, .text-3xl, .text-4xl { font-size: 1.25rem !important; }
            h2, .text-lg { font-size: 1rem !important; }
            p, .text-sm, .text-base { font-size: 0.8rem !important; }
            .counter-btn { padding: 0.5rem !important; }
            #queueNumber { font-size: 3rem !important; }
            .counters-grid-container { max-height: calc(100vh - 12rem); }
        }
        @media (orientation: landscape) and (max-height: 600px) {
            .mb-2, .mb-3, .mb-4 { margin-bottom: 0.25rem !important; }
            .mb-1 { margin-bottom: 0.1rem !important; }
            .p-2, .p-3, .p-4, .p-6 { padding: 0.5rem !important; }
            .py-1, .py-2, .py-3 { padding-top: 0.25rem !important; padding-bottom: 0.25rem !important; }
        }
        @media (orientation: portrait) and (max-height: 600px) {
            .kiosk-content { padding: 0.5rem; }
            .counters-grid-container { max-height: calc(100vh - 18rem); }
            h1 { font-size: 1.5rem !important; }
            h2 { font-size: 1rem !important; }
            p { font-size: 0.85rem !important; }
        }
        @media (orientation: landscape) and (min-height: 600px) and (max-height: 800px) {
            h1 { font-size: 2rem !important; }
            h2 { font-size: 1.25rem !important; }
            #queueNumber { font-size: 4rem !important; }
            .bg-brand-gradient { padding: 0.75rem !important; }
        }
        @media (min-width: 1024px) and (orientation: landscape) {
            .counters-grid { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
        }
        @media (max-height: 700px) {
            .step-indicator {
                font-size: 0.875rem;
                padding: 0.5rem 1rem !important;
            }
            .step-indicator .text-2xl { font-size: 1.25rem; }
            .step-indicator .text-sm { font-size: 0.75rem; }
        }
    </style>
</head>
<body class="bg-brand-gradient">
    <div class="kiosk-container">
        <!-- Settings Button -->
        <button onclick="showSettings()" class="fixed top-4 right-4 glass-card text-gray-700 px-4 py-3 sm:px-6 sm:py-3 rounded-2xl shadow-lg hover:shadow-2xl transition-all z-50 text-sm sm:text-base font-semibold hover:scale-105">
            <i class="fas fa-cog mr-2 animate-spin-slow"></i> Settings
        </button>

        <!-- Main Content -->
        <div class="kiosk-content">
            <div class="w-full max-w-6xl mx-auto px-4">
                <!-- Step Indicator -->
                <div class="mb-4 sm:mb-6 flex justify-center">
                    <div class="flex justify-center items-center space-x-1 sm:space-x-2 relative">
                        <div class="flex items-center space-x-3 sm:space-x-5 glass-card px-4 py-3 sm:px-8 sm:py-4 rounded-2xl sm:rounded-3xl shadow-xl">
                            <div id="step1Indicator" class="step-indicator flex flex-col items-center step-active px-4 py-3 sm:px-5 sm:py-4 rounded-xl transition-all">
                                <div class="text-lg sm:text-3xl font-bold mb-1">1</div>
                                <div class="text-xs sm:text-sm font-semibold">Select</div>
                            </div>
                            <i class="fas fa-arrow-right text-gray-400 text-base sm:text-2xl"></i>
                            <div id="step2Indicator" class="step-indicator flex flex-col items-center bg-gray-100 text-gray-600 px-4 py-3 sm:px-5 sm:py-4 rounded-xl transition-all">
                                <div class="text-lg sm:text-3xl font-bold mb-1">2</div>
                                <div class="text-xs sm:text-sm font-semibold">Process</div>
                            </div>
                            <i class="fas fa-arrow-right text-gray-400 text-base sm:text-2xl"></i>
                            <div id="step3Indicator" class="step-indicator flex flex-col items-center bg-gray-100 text-gray-600 px-4 py-3 sm:px-5 sm:py-4 rounded-xl transition-all">
                                <div class="text-lg sm:text-3xl font-bold mb-1">3</div>
                                <div class="text-xs sm:text-sm font-semibold">Done</div>
                            </div>
                        </div>
                    </div>
                </div>

        <!-- Step 1: Counter Selection -->
        <div id="step1" class="animate-fadeIn">
            <div class="text-center mb-4 sm:mb-8">
                @if($settings->logo_url)
                    <div class="mb-4 sm:mb-6">
                        <img src="{{ $settings->logo_url }}" alt="{{ $settings->company_name }}" class="h-16 sm:h-20 lg:h-24 mx-auto drop-shadow-2xl">
                    </div>
                @endif
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-3 sm:mb-4 drop-shadow-2xl" style="color: var(--text-color); text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">Welcome!</h1>
                <p class="text-xl sm:text-2xl lg:text-3xl font-bold drop-shadow-lg mb-3" style="color: var(--text-color); opacity: 0.95;">{{ $settings->company_name }}</p>
                <div class="inline-block glass-card px-6 py-3 rounded-2xl">
                    <p class="text-base sm:text-lg lg:text-xl font-medium" style="color: var(--text-color); opacity: 0.9;">
                        <i class="fas fa-hand-pointer mr-2 animate-bounce"></i>Select a counter to get started
                    </p>
                </div>
            </div>

            <div class="glass-card rounded-2xl sm:rounded-3xl shadow-2xl p-5 sm:p-8 lg:p-10">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-6 sm:mb-8 text-center bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    <i class="fas fa-desktop mr-3"></i>Available Service Counters
                </h2>
                <div class="counters-grid-container">
                    <div id="countersGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
                        <!-- counters injected via JS -->
                    </div>
                </div>
                <div id="noCounters" class="hidden col-span-full text-center py-12 sm:py-16">
                    <i class="fas fa-info-circle text-5xl sm:text-7xl text-gray-300 mb-4 sm:mb-6"></i>
                    <h3 class="text-2xl sm:text-3xl font-bold text-gray-700 mb-3">No Counters Available</h3>
                    <p class="text-gray-500 text-base sm:text-lg">All service counters are currently offline. Please wait a moment.</p>
                    <div class="mt-4 sm:mt-6">
                        <i class="fas fa-spinner fa-spin text-blue-500 text-2xl sm:text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>

                <!-- Step 2: Generating -->
                <div id="step2" class="hidden animate-fadeIn">
                    <div class="glass-card rounded-2xl sm:rounded-3xl shadow-2xl p-10 sm:p-16 text-center max-w-2xl mx-auto">
                        <div class="mb-8">
                            <div class="inline-block p-6 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full mb-6">
                                <i class="fas fa-spinner fa-spin text-white text-5xl sm:text-7xl"></i>
                            </div>
                            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-4 sm:mb-6">Processing Your Request</h2>
                            <p class="text-lg sm:text-xl text-gray-600">Generating your queue number...</p>
                        </div>
                        <div class="relative bg-gray-200 h-4 rounded-full overflow-hidden">
                            <div class="absolute h-full bg-gradient-to-r from-blue-500 via-purple-600 to-blue-500 animate-pulse" style="width: 100%; animation: progress 2s ease-in-out infinite;"></div>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Step 3: Queue Display -->
                <div id="step3" class="hidden animate-fadeIn flex justify-center items-center">
                    <div class="glass-card rounded-2xl sm:rounded-3xl shadow-2xl p-8 sm:p-12 lg:p-14 text-center max-w-4xl w-full mx-auto" id="queueContent">
                        <div class="mb-6 sm:mb-8">
                            <div class="inline-block p-4 bg-green-100 rounded-full mb-4">
                                <i class="fas fa-check-circle text-green-500 text-5xl sm:text-7xl"></i>
                            </div>
                            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-3 bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">Success!</h2>
                            <p class="text-base sm:text-lg lg:text-xl text-gray-600 font-medium">Your queue number is ready</p>
                        </div>
                        
                        <div class="relative bg-brand-gradient text-white p-8 sm:p-12 lg:p-16 rounded-2xl sm:rounded-3xl mb-6 sm:mb-8 shadow-2xl overflow-hidden">
                            <div class="absolute inset-0 bg-white opacity-10" style="background-image: radial-gradient(circle, rgba(255,255,255,0.3) 1px, transparent 1px); background-size: 20px 20px;"></div>
                            <div class="relative z-10">
                                <p class="text-sm sm:text-base lg:text-lg font-bold mb-3 opacity-90" style="color: var(--text-color); letter-spacing: 2px;">YOUR QUEUE NUMBER</p>
                                <div class="text-7xl sm:text-8xl lg:text-9xl font-black mb-4 sm:mb-6 tracking-wider pulse-animation" id="queueNumber" style="color: var(--text-color); text-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
                                <div class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2" id="counterInfo" style="color: var(--text-color);"></div>
                                <div class="text-base sm:text-lg opacity-90" id="queueTime" style="color: var(--text-color);"></div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-500 p-5 sm:p-6 mb-6 sm:mb-8 rounded-lg text-left shadow-md">
                            <p class="text-sm sm:text-base lg:text-lg text-gray-800 font-medium">
                                <i class="fas fa-info-circle mr-2 text-yellow-600"></i>
                                <strong>Important:</strong> Please wait for your number to be called on the display monitor.
                            </p>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 mb-5 sm:mb-6">
                            <button onclick="printQueue()" class="btn-primary text-white px-6 py-4 sm:px-8 sm:py-5 rounded-xl text-base sm:text-lg font-bold shadow-xl transform hover:scale-105 hover:shadow-2xl transition-all" style="background: linear-gradient(135deg, var(--accent-color), var(--primary-color));">
                                <i class="fas fa-print mr-3 text-lg sm:text-xl"></i>Print Number
                            </button>
                            <button onclick="capturePhoto()" class="btn-primary px-6 py-4 sm:px-8 sm:py-5 rounded-xl text-base sm:text-lg font-bold shadow-xl transform hover:scale-105 hover:shadow-2xl transition-all" style="background: linear-gradient(135deg, var(--secondary-color), var(--accent-color)); color: var(--text-color);">
                                <i class="fas fa-camera mr-3 text-lg sm:text-xl"></i>Take Screenshot
                            </button>
                        </div>
                        
                        <button onclick="finishAndReset()" class="w-full btn-primary bg-brand-gradient text-white px-6 py-5 sm:px-8 sm:py-6 rounded-2xl text-lg sm:text-xl font-bold shadow-xl transform hover:scale-105 hover:shadow-2xl transition-all" style="color: var(--text-color);">
                            <i class="fas fa-redo mr-3"></i>Get Another Number
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div id="settingsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-cog mr-3 text-blue-600"></i>Printer Settings
                </h2>
                <button onclick="closeSettings()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <div class="space-y-6">
                <!-- Printer Selection -->
                <div class="bg-gray-50 p-6 rounded-2xl">
                    <label class="block text-xl font-semibold text-gray-700 mb-4">
                        <i class="fas fa-print mr-2 text-blue-600"></i>Select Printer Type
                    </label>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 bg-white rounded-xl border-2 cursor-pointer hover:border-blue-400 transition-all">
                            <input type="radio" name="printerType" value="thermal" checked onchange="updatePrinterSettings()" class="w-6 h-6 text-blue-600">
                            <div class="ml-4">
                                <div class="font-semibold text-lg">USB Thermal Printer (80mm)</div>
                                <div class="text-gray-600 text-sm">Direct connection via USB (ESC/POS)</div>
                            </div>
                        </label>
                        <label class="flex items-center p-4 bg-white rounded-xl border-2 cursor-pointer hover:border-blue-400 transition-all">
                            <input type="radio" name="printerType" value="browser" onchange="updatePrinterSettings()" class="w-6 h-6 text-blue-600">
                            <div class="ml-4">
                                <div class="font-semibold text-lg">Browser Print Dialog</div>
                                <div class="text-gray-600 text-sm">Use system default printer</div>
                            </div>
                        </label>
                        <label class="flex items-center p-4 bg-white rounded-xl border-2 cursor-pointer hover:border-blue-400 transition-all">
                            <input type="radio" name="printerType" value="none" onchange="updatePrinterSettings()" class="w-6 h-6 text-blue-600">
                            <div class="ml-4">
                                <div class="font-semibold text-lg">No Printer (Photo Only)</div>
                                <div class="text-gray-600 text-sm">Capture screenshot instead</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Thermal Printer Settings -->
                <div id="thermalSettings" class="bg-blue-50 p-6 rounded-2xl">
                    <label class="block text-lg font-semibold text-gray-700 mb-3">
                        <i class="fas fa-usb mr-2 text-blue-600"></i>USB Vendor ID (Optional)
                    </label>
                    <input type="text" id="vendorId" placeholder="0x0fe6 (leave empty for auto-detect)" 
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:outline-none text-lg">
                    <p class="text-sm text-gray-600 mt-2">Common: Epson (0x04b8), Star (0x0519), Bixolon (0x0fe6)</p>
                </div>

                <!-- Test Print -->
                <button onclick="testPrint()" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-4 rounded-xl hover:from-blue-700 hover:to-purple-700 text-lg font-semibold shadow-lg transition-all">
                    <i class="fas fa-print mr-2"></i>Test Print
                </button>

                <!-- Save Button -->
                <button onclick="saveSettings()" class="w-full bg-green-600 text-white px-6 py-4 rounded-xl hover:bg-green-700 text-lg font-semibold shadow-lg transition-all">
                    <i class="fas fa-save mr-2"></i>Save Settings
                </button>
            </div>
        </div>
    </div>

    <script>
    let currentQueue = null;
    let printerSettings = {
        type: 'thermal', // thermal, browser, none
        vendorId: '0x0fe6'
    };
    const countersGrid = document.getElementById('countersGrid');
    const noCounters = document.getElementById('noCounters');
    const countersEndpoint = '{{ route('kiosk.counters', ['company_code' => $companyCode]) }}';
    const initialCounters = @json($onlineCounters);

    // Load settings from localStorage
    function loadSettings() {
        const saved = localStorage.getItem('kioskPrinterSettings');
        if (saved) {
            printerSettings = JSON.parse(saved);
        }
    }

    function renderCounters(counters) {
        countersGrid.innerHTML = '';

        if (!counters || counters.length === 0) {
            noCounters.classList.remove('hidden');
            return;
        }

        noCounters.classList.add('hidden');

        counters.forEach(counter => {
            const button = document.createElement('button');
            button.className = 'counter-btn w-full bg-white border-2 border-gray-100 rounded-lg sm:rounded-xl p-2 sm:p-3 text-left shadow hover:shadow-lg transition transform hover:-translate-y-1';
            button.onclick = () => selectCounter(counter.id, counter.counter_number, counter.display_name);
            button.innerHTML = `
                <div class="flex items-center justify-between mb-1 sm:mb-2">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-blue-50 flex items-center justify-center text-sm sm:text-lg font-bold text-blue-600">#${counter.counter_number}</div>
                        <div>
                            <div class="text-sm sm:text-base font-bold text-gray-800">${counter.display_name}</div>
                            <div class="text-xs text-gray-500">${counter.short_description || 'Ready to serve'}</div>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Available</span>
                </div>
                <div class="text-gray-600 flex items-center space-x-1 text-xs sm:text-sm">
                    <i class="fas fa-bell text-blue-500"></i>
                    <span>Tap</span>
                </div>
            `;

            countersGrid.appendChild(button);
        });
    }

    function refreshCounters() {
        // Only refresh while on selection screen
        if (document.getElementById('step1').classList.contains('hidden')) return;

        fetch(countersEndpoint)
            .then(response => response.ok ? response.json() : Promise.reject('Failed to load counters'))
            .then(data => renderCounters(data.counters || []))
            .catch(error => console.error('Counters refresh failed:', error));
    }

    // Initialize on page load
    loadSettings();
    renderCounters(initialCounters);
    setInterval(refreshCounters, 5000);

    function selectCounter(counterId, counterNumber, counterName) {
        // Move to step 2
        moveToStep(2);
        
        // Disable all counter buttons
        document.querySelectorAll('.counter-btn').forEach(btn => btn.disabled = true);

        fetch('{{ route('kiosk.generate', ['company_code' => $companyCode]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ counter_id: counterId })
        })
        .then(async response => {
            const status = response.status;
            let data;
            try {
                data = await response.json();
            } catch (e) {
                // Non-JSON response (e.g., HTML error page)
                data = { success: false, message: `Server error (status ${status})` };
            }

            if (!response.ok) {
                const msg = (data && data.message) ? data.message : `Request failed with status ${status}`;
                throw new Error(msg);
            }

            return data;
        })
        .then(data => {
            if (data.success && data.queue) {
                currentQueue = data.queue;
                setTimeout(() => {
                    showQueueDisplay(data.queue);
                }, 1500);
            } else {
                showError(data.message || 'Failed to generate priority number');
            }
        })
        .catch(error => {
            console.error('Generate queue failed:', error);
            showError(error.message || 'Error generating priority number. Please try again.');
        });
    }

    function moveToStep(stepNumber) {
        // Hide all steps
        document.getElementById('step1').classList.add('hidden');
        document.getElementById('step2').classList.add('hidden');
        document.getElementById('step3').classList.add('hidden');

        // Update indicators
        const indicators = ['step1Indicator', 'step2Indicator', 'step3Indicator'];
        indicators.forEach((id, index) => {
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

        // Show current step
        const stepElement = document.getElementById('step' + stepNumber);
        stepElement.classList.remove('hidden');
        stepElement.classList.add('animate-fadeIn');
    }

    function showQueueDisplay(queue) {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
        
        // Display format: sequence only, e.g., 0001
        const suffixFromQueue = (qn) => {
            const parts = String(qn).split('-');
            return parts[parts.length - 1];
        };
        const displayQueueNumber = suffixFromQueue(queue.queue_number);
        
        document.getElementById('queueNumber').textContent = displayQueueNumber;
        document.getElementById('counterInfo').textContent = 
            `Counter ${queue.counter.counter_number} - ${queue.counter.display_name}`;
        document.getElementById('queueTime').textContent = `Generated at ${timeString}`;
        
        moveToStep(3);
    }

    function showError(message) {
        alert(message);
        // Reset to step 1
        document.querySelectorAll('.counter-btn').forEach(btn => btn.disabled = false);
        moveToStep(1);
    }

    function finishAndReset() {
        currentQueue = null;
        moveToStep(1);
        // Re-enable counter buttons
        document.querySelectorAll('.counter-btn').forEach(btn => btn.disabled = false);
    }

    // Settings Functions
    function showSettings() {
        document.getElementById('settingsModal').classList.remove('hidden');
        // Load current settings
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
            // Create test queue data
            currentQueue = {
                queue_number: 'TEST-01-0001',
                counter: {
                    counter_number: '1',
                    display_name: 'Test Counter'
                }
            };
        }
        
        printQueue();
    }

    // Print Functions
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
        // Check if Web USB is supported
        if (!navigator.usb) {
            alert('USB printing not supported in this browser. Using browser print instead.');
            printToBrowser();
            return;
        }

        // Parse vendor ID
        let vendorId = parseInt(printerSettings.vendorId);
        if (isNaN(vendorId)) {
            vendorId = 0x0fe6; // Default Bixolon
        }

        // Request USB device
        navigator.usb.requestDevice({ filters: [{ vendorId: vendorId }] })
            .then(device => {
                alert('Thermal printer detected. Connecting...');
                // In production, implement ESC/POS commands here
                // For now, fallback to browser print with thermal format
                printToBrowser();
            })
            .catch(error => {
                console.log('No thermal printer found:', error);
                alert('No thermal printer detected. Using browser print instead.');
                printToBrowser();
            });
    }

    function printToBrowser() {
        if (!currentQueue) return;
        
        const printWindow = window.open('', '_blank', 'width=350,height=500');
        const now = new Date().toLocaleString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric',
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
        
        // Extract last 4 digits for display
        const displayQueueNumber = currentQueue.queue_number;
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Priority Number - ${displayQueueNumber}</title>
                <style>
                    @media print {
                        @page { 
                            margin: 0; 
                            size: 80mm auto; 
                        }
                        body { margin: 0; padding: 10mm; }
                    }
                    body { 
                        font-family: 'Arial', sans-serif; 
                        text-align: center; 
                        padding: 20px;
                        max-width: 300px;
                        margin: 0 auto;
                    }
                    .header { 
                        font-size: 24px; 
                        font-weight: bold; 
                        margin-bottom: 20px;
                        padding-bottom: 15px;
                        border-bottom: 3px double #000;
                    }
                    .company-name {
                        font-size: 18px;
                        margin-bottom: 10px;
                        color: #333;
                    }
                    .title {
                        font-size: 18px;
                        font-weight: bold;
                        margin-bottom: 20px;
                        text-transform: uppercase;
                    }
                    .queue-number { 
                        font-size: 84px; 
                        font-weight: bold; 
                        margin: 30px 0;
                        letter-spacing: 4px;
                        border: 5px solid #000;
                        padding: 25px;
                        background: linear-gradient(135deg, #f0f0f0, #ffffff);
                        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
                    }
                    .counter-info { 
                        font-size: 28px; 
                        font-weight: bold;
                        margin: 25px 0 10px 0;
                    }
                    .counter-name {
                        font-size: 20px;
                        margin-bottom: 25px;
                        color: #333;
                    }
                    .instructions {
                        font-size: 15px;
                        margin: 25px 0;
                        padding: 20px;
                        border-top: 3px double #000;
                        border-bottom: 3px double #000;
                        line-height: 1.8;
                        text-align: left;
                    }
                    .instructions strong {
                        display: block;
                        text-align: center;
                        margin-bottom: 10px;
                        font-size: 16px;
                    }
                    .footer {
                        font-size: 13px;
                        color: #666;
                        margin-top: 20px;
                        padding-top: 15px;
                        border-top: 2px dashed #999;
                    }
                    .barcode {
                        margin: 20px 0;
                        padding: 15px;
                        background: #fff;
                        border: 2px solid #333;
                        font-family: 'Courier New', monospace;
                        font-size: 18px;
                        letter-spacing: 3px;
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>
                <div class="company-name">{{ $settings->company_name }}</div>
                <div class="header">QUEUE MANAGEMENT SYSTEM</div>
                <div class="title">Priority Number</div>
                <div class="queue-number">${displayQueueNumber}</div>
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
                <div class="footer">
                    Generated: ${now}<br>
                    <strong>Thank you for your patience!</strong>
                </div>
            </body>
            </html>
        `);
        
        printWindow.document.close();
        
        // Auto print after content loads
        printWindow.onload = function() {
            setTimeout(function() {
                printWindow.print();
            }, 250);
        };
    }

    function capturePhoto() {
        if (!currentQueue) {
            alert('No queue number to capture');
            return;
        }

        const element = document.getElementById('queueContent');
        
        // Load html2canvas if not already loaded
        if (typeof html2canvas === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
            script.onload = function() {
                captureWithHtml2Canvas(element);
            };
            document.head.appendChild(script);
        } else {
            captureWithHtml2Canvas(element);
        }
    }

    function captureWithHtml2Canvas(element) {
        // Show loading
        const originalContent = element.innerHTML;
        
        html2canvas(element, {
            backgroundColor: '#ffffff',
            scale: 2,
            logging: false,
            windowWidth: element.scrollWidth,
            windowHeight: element.scrollHeight
        }).then(canvas => {
            // Convert to blob and download
            canvas.toBlob(function(blob) {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `priority-number-${currentQueue.queue_number}.png`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                
                // Show success message
                alert('Priority number saved as image!');
            });
        }).catch(error => {
            console.error('Capture error:', error);
            alert('Could not capture photo. Please try print instead.');
        });
    }

    </script>
</body>
</html>
