<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk - Queue System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .step-indicator {
            position: relative;
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
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
        }
        .step-completed {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-600 min-h-screen flex items-center justify-center p-4">
    <!-- Settings Button -->
    <button onclick="showSettings()" class="fixed top-4 right-4 bg-white text-gray-700 px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all z-50">
        <i class="fas fa-cog mr-2"></i> Settings
    </button>

    <!-- Main Container -->
    <div class="w-full max-w-6xl">
        <!-- Step Indicator -->
        <div class="mb-12">
            <div class="flex justify-center items-center space-x-4 relative">
                <div class="flex items-center space-x-4 bg-white px-8 py-4 rounded-2xl shadow-xl">
                    <div id="step1Indicator" class="step-indicator flex flex-col items-center step-active px-6 py-3 rounded-xl transition-all">
                        <div class="text-2xl font-bold mb-1">1</div>
                        <div class="text-sm font-medium">Select Counter</div>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400 text-2xl"></i>
                    <div id="step2Indicator" class="step-indicator flex flex-col items-center bg-gray-100 text-gray-600 px-6 py-3 rounded-xl transition-all">
                        <div class="text-2xl font-bold mb-1">2</div>
                        <div class="text-sm font-medium">Generating</div>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400 text-2xl"></i>
                    <div id="step3Indicator" class="step-indicator flex flex-col items-center bg-gray-100 text-gray-600 px-6 py-3 rounded-xl transition-all">
                        <div class="text-2xl font-bold mb-1">3</div>
                        <div class="text-sm font-medium">Your Number</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1: Counter Selection -->
        <div id="step1" class="animate-fadeIn">
            <div class="text-center mb-8">
                <h1 class="text-6xl font-bold text-white mb-4 drop-shadow-lg">Welcome!</h1>
                <p class="text-2xl text-white drop-shadow">Please select a counter to get your priority number</p>
            </div>

            <div class="bg-white rounded-3xl shadow-2xl p-8">
                <div id="counterSelection" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($onlineCounters as $counter)
                        <button onclick="selectCounter({{ $counter->id }}, '{{ $counter->counter_number }}', '{{ addslashes($counter->display_name) }}')" 
                                class="counter-btn bg-gradient-to-br from-blue-50 to-purple-50 p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all transform hover:scale-105 border-2 border-transparent hover:border-blue-400">
                            <div class="text-center">
                                <div class="bg-gradient-to-br from-blue-500 to-purple-600 text-white text-6xl font-bold mb-4 w-28 h-28 rounded-full flex items-center justify-center mx-auto shadow-xl">
                                    {{ $counter->counter_number }}
                                </div>
                                <h3 class="text-2xl font-bold mb-2 text-gray-800">{{ $counter->display_name }}</h3>
                                <p class="text-gray-600 mb-3">{{ $counter->short_description }}</p>
                                <span class="inline-block bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-semibold">
                                    <i class="fas fa-circle text-green-600 animate-pulse"></i> Available
                                </span>
                            </div>
                        </button>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <i class="fas fa-info-circle text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-3xl font-bold text-gray-700 mb-2">No Counters Available</h3>
                            <p class="text-gray-500 text-lg">Please wait for a counter to come online</p>
                            <div class="mt-4">
                                <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Step 2: Generating -->
        <div id="step2" class="hidden animate-fadeIn">
            <div class="bg-white rounded-3xl shadow-2xl p-16 text-center">
                <div class="mb-8">
                    <i class="fas fa-spinner fa-spin text-blue-500 text-8xl mb-6"></i>
                    <h2 class="text-5xl font-bold text-gray-800 mb-4">Generating Your Priority Number</h2>
                    <p class="text-2xl text-gray-600">Please wait a moment...</p>
                </div>
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-2 rounded-full overflow-hidden">
                    <div class="h-full bg-white animate-pulse" style="width: 100%; animation: progress 2s ease-in-out infinite;"></div>
                </div>
            </div>
        </div>

        <!-- Step 3: Queue Display -->
        <div id="step3" class="hidden animate-fadeIn">
            <div class="bg-white rounded-3xl shadow-2xl p-12 text-center" id="queueContent">
                <div class="mb-8">
                    <i class="fas fa-check-circle text-green-500 text-7xl mb-4"></i>
                    <h2 class="text-5xl font-bold mb-3 text-gray-800">Your Priority Number</h2>
                    <p class="text-xl text-gray-600">Please keep this number visible</p>
                </div>
                
                <div class="bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-600 text-white p-16 rounded-3xl mb-8 shadow-2xl">
                    <div class="text-9xl font-bold mb-6 tracking-wider" id="queueNumber"></div>
                    <div class="text-4xl font-semibold mb-2" id="counterInfo"></div>
                    <div class="text-2xl opacity-90" id="queueTime"></div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <button onclick="printQueue()" class="bg-green-600 text-white px-8 py-6 rounded-2xl hover:bg-green-700 text-xl font-semibold shadow-lg transform hover:scale-105 transition-all">
                        <i class="fas fa-print mr-3 text-2xl"></i>Print Number
                    </button>
                    <button onclick="capturePhoto()" class="bg-purple-600 text-white px-8 py-6 rounded-2xl hover:bg-purple-700 text-xl font-semibold shadow-lg transform hover:scale-105 transition-all">
                        <i class="fas fa-camera mr-3 text-2xl"></i>Capture Photo
                    </button>
                </div>
                
                <button onclick="finishAndReset()" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-6 rounded-2xl hover:from-blue-700 hover:to-purple-700 text-2xl font-bold shadow-lg transform hover:scale-105 transition-all">
                    <i class="fas fa-check mr-3"></i>Done - Get Another Number
                </button>
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

    // Load settings from localStorage
    function loadSettings() {
        const saved = localStorage.getItem('kioskPrinterSettings');
        if (saved) {
            printerSettings = JSON.parse(saved);
        }
    }

    // Initialize on page load
    loadSettings();

    function selectCounter(counterId, counterNumber, counterName) {
        // Move to step 2
        moveToStep(2);
        
        // Disable all counter buttons
        document.querySelectorAll('.counter-btn').forEach(btn => btn.disabled = true);

        fetch('{{ route('kiosk.generate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ counter_id: counterId })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.queue) {
                currentQueue = data.queue;
                // Add a small delay for better UX
                setTimeout(() => {
                    showQueueDisplay(data.queue);
                }, 1500);
            } else {
                showError(data.message || 'Failed to generate priority number');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error generating priority number. Please try again.');
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
        
        document.getElementById('queueNumber').textContent = queue.queue_number;
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
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Priority Number - ${currentQueue.queue_number}</title>
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
                <div class="header">QUEUE MANAGEMENT SYSTEM</div>
                <div class="title">Priority Number</div>
                <div class="queue-number">${currentQueue.queue_number}</div>
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

    // Auto-refresh counters every 30 seconds (only on step 1)
    setInterval(() => {
        const step1 = document.getElementById('step1');
        if (!step1.classList.contains('hidden')) {
            location.reload();
        }
    }, 30000);
    </script>
</body>
</html>
