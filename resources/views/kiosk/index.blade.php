<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk - Queue System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <h1 class="text-5xl font-bold text-white mb-4">Welcome to Queue System</h1>
            <p class="text-xl text-white">Please select a counter to get your queue number</p>
        </div>

        <div id="counterSelection" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
            @forelse($onlineCounters as $counter)
                <button onclick="selectCounter({{ $counter->id }})" 
                        class="bg-white p-8 rounded-2xl shadow-2xl hover:shadow-3xl transition-all transform hover:scale-110 hover:-translate-y-2 border-4 border-transparent hover:border-blue-400">
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-blue-500 to-purple-600 text-white text-7xl font-bold mb-4 w-32 h-32 rounded-full flex items-center justify-center mx-auto shadow-xl">
                            {{ $counter->counter_number }}
                        </div>
                        <h3 class="text-3xl font-bold mb-3 text-gray-800">{{ $counter->display_name }}</h3>
                        <p class="text-gray-600 text-lg mb-4">{{ $counter->short_description }}</p>
                        <div class="mt-4">
                            <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full text-base font-semibold shadow-md">
                                <i class="fas fa-circle text-green-600 animate-pulse"></i> Available Now
                            </span>
                        </div>
                        <div class="mt-4 text-blue-600 font-semibold">
                            <i class="fas fa-hand-pointer mr-2"></i> Tap to Get Queue
                        </div>
                    </div>
                </button>
            @empty
                <div class="col-span-full text-center">
                    <div class="bg-white p-12 rounded-2xl shadow-2xl">
                        <i class="fas fa-info-circle text-8xl text-gray-300 mb-6"></i>
                        <h3 class="text-4xl font-bold text-gray-700 mb-4">No Counters Available</h3>
                        <p class="text-gray-500 text-xl">Please wait for a counter to come online</p>
                        <div class="mt-6">
                            <div class="inline-block">
                                <i class="fas fa-spinner fa-spin text-blue-500 text-3xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Queue Display Modal -->
        <div id="queueModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-12 rounded-lg shadow-2xl max-w-2xl w-full text-center">
                <h2 class="text-3xl font-bold mb-6">Your Queue Number</h2>
                <div class="bg-blue-600 text-white p-8 rounded-lg mb-6">
                    <div class="text-7xl font-bold mb-2" id="queueNumber"></div>
                    <div class="text-2xl" id="counterInfo"></div>
                </div>
                <div class="space-y-4">
                    <button onclick="printQueue()" class="w-full bg-green-600 text-white px-6 py-4 rounded-lg hover:bg-green-700 text-xl">
                        <i class="fas fa-print mr-2"></i>Print Queue Number
                    </button>
                    <button onclick="capturePhoto()" class="w-full bg-purple-600 text-white px-6 py-4 rounded-lg hover:bg-purple-700 text-xl">
                        <i class="fas fa-camera mr-2"></i>Capture Photo
                    </button>
                    <button onclick="closeModal()" class="w-full bg-gray-600 text-white px-6 py-4 rounded-lg hover:bg-gray-700 text-xl">
                        <i class="fas fa-times mr-2"></i>Done
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let currentQueue = null;

    function selectCounter(counterId) {
        fetch('{{ route('kiosk.generate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ counter_id: counterId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentQueue = data.queue;
                showQueueModal(data.queue);
            } else {
                alert(data.message || 'Failed to generate queue');
            }
        });
    }

    function showQueueModal(queue) {
        document.getElementById('queueNumber').textContent = queue.queue_number;
        document.getElementById('counterInfo').textContent = 
            `Counter ${queue.counter.counter_number} - ${queue.counter.display_name}`;
        document.getElementById('queueModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('queueModal').classList.add('hidden');
        location.reload();
    }

    function printQueue() {
        if (!currentQueue) return;
        
        // Create print window
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>Queue Number</title>
                <style>
                    body { font-family: Arial; text-align: center; padding: 20px; }
                    .queue-number { font-size: 48px; font-weight: bold; margin: 20px 0; }
                    .counter-info { font-size: 24px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <h1>Queue Management System</h1>
                <div class="queue-number">${currentQueue.queue_number}</div>
                <div class="counter-info">Counter ${currentQueue.counter.counter_number}</div>
                <div class="counter-info">${currentQueue.counter.display_name}</div>
                <p>Please wait for your number to be called</p>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }

    function capturePhoto() {
        // Use html2canvas or similar library for screenshot
        alert('Photo capture feature - implement with html2canvas library');
    }

    // Auto-refresh counters every 30 seconds
    setInterval(() => {
        if (!document.getElementById('queueModal').classList.contains('hidden')) return;
        location.reload();
    }, 30000);
    </script>
</body>
</html>
