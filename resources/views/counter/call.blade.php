@extends('layouts.guest')

@section('title', 'Service Station')

@section('content')
<div class="min-h-screen flex flex-col p-4 bg-gray-50 overflow-hidden">
    <!-- Header with Logo and Organization Name -->
    <div class="w-full mb-4">
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    @if($settings->logo_url)
                        <img src="{{ $settings->logo_url }}" alt="{{ $settings->company_name }}" class="h-12 w-auto">
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $settings->company_name }}</h1>
                        <p class="text-sm text-gray-600">Counter {{ $counter->counter_number }} - {{ $counter->display_name }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-lg font-semibold text-gray-700" id="headerTime"></div>
                    <div class="text-xs text-gray-500" id="headerDate"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex items-center justify-center">
        <div class="w-full max-w-3xl">
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-xl p-6 border-2 border-white">
            <div class="text-center">
                <p class="text-white text-sm mb-2">NOW SERVING</p>
                <div id="currentNumber" class="responsive-queue-number font-extrabold text-white drop-shadow-2xl">---</div>
                <div class="grid grid-cols-5 gap-2 mt-6 max-w-xl mx-auto">
                    <button id="btnNotify" onclick="notifyCustomer()" class="bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-bell mr-2"></i>Notify
                    </button>
                    <button id="btnSkip" onclick="skipCurrent()" class="bg-orange-500 hover:bg-orange-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-forward mr-2"></i>Skip
                    </button>
                    <button id="btnComplete" onclick="moveToNext()" class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-check-circle mr-2"></i>Complete
                    </button>
                    <button id="btnTransfer" onclick="openTransferModal()" class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-exchange-alt mr-2"></i>Transfer
                    </button>
                    <button id="btnCallNext" onclick="callNext()" class="bg-white text-indigo-700 hover:bg-gray-100 disabled:bg-gray-400 disabled:text-gray-600 disabled:cursor-not-allowed px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-bell mr-2"></i>Call Next
                    </button>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-base font-bold mb-3">Waiting</h3>
                <div id="waitingList" class="space-y-2 max-h-40 overflow-hidden"></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-base font-bold mb-3">Skipped</h3>
                <div id="skippedList" class="space-y-2 max-h-40 overflow-hidden"></div>
            </div>
        </div>
    </div>
</div>
</div>

<style>
.responsive-queue-number { font-size: 6rem; }
@media (orientation: portrait) { .responsive-queue-number { font-size: 4.5rem; } }
@media (max-width: 768px) and (orientation: portrait) { .responsive-queue-number { font-size: 3rem; } }
@media (max-width: 768px) and (orientation: landscape) { .responsive-queue-number { font-size: 3.5rem; } }
</style>

<!-- Transfer Queue Modal -->
<div id="transfer-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300 opacity-0">
    <div id="transfer-modal-content" class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-exchange-alt text-white text-xl"></i>
                    <h3 class="text-xl font-bold text-white">Transfer Queue</h3>
                </div>
                <button onclick="closeTransferModal()" class="text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-4">Select a counter to transfer the current queue:</p>
            <div id="countersList" class="space-y-2 max-h-64 overflow-y-auto">
                <!-- Populated dynamically -->
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Note:</strong> The queue number will remain the same (first come first serve).
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            <button onclick="closeTransferModal()" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">Cancel</button>
        </div>
    </div>
</div>

<!-- Skip Confirmation Modal -->
<div id="skip-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300 opacity-0">
    <div id="skip-modal-content" class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-forward text-white text-xl"></i>
                    <h3 class="text-xl font-bold text-white">Skip Current Queue?</h3>
                </div>
                <button onclick="closeSkipModal()" class="text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-4">Are you sure you want to skip the current customer queue?</p>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                <p class="text-sm text-orange-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Note:</strong> The skipped queue will be moved to the skipped list and can be recalled later.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            <button onclick="closeSkipModal()" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">Cancel</button>
            <button onclick="confirmSkip()" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition">
                <i class="fas fa-forward mr-2"></i>Skip Queue
            </button>
        </div>
    </div>
</div>

@push('scripts')
@push('styles')
<style>
html, body { overflow: hidden; }
</style>
@endpush
<script>
const COUNTER_NUM = {{ $counter->counter_number }};
let currentQueueData = null;
let onlineCounters = [];

// Update header time display
function updateHeaderTime() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
    });
    const dateStr = now.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    document.getElementById('headerTime').textContent = timeStr;
    document.getElementById('headerDate').textContent = dateStr;
}

// Format queue display as sequence only
function formatDisplayQueue(queueNumber, counterNum = COUNTER_NUM) {
    if (!queueNumber) return '';
    const parts = queueNumber.split('-');
    const sequence = parts[parts.length - 1];
    return sequence;
}

// Default notification sound - Train Station Lobby chime
function playNotificationSound() {
    // Create audio context for generating sound
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const now = audioContext.currentTime;
    
    // Helper function to create a chime note
    const playChime = (startTime, frequency, duration) => {
        const osc = audioContext.createOscillator();
        const gain = audioContext.createGain();
        
        osc.connect(gain);
        gain.connect(audioContext.destination);
        
        osc.frequency.value = frequency;
        osc.type = 'sine';
        
        // Quick attack, smooth decay for chime effect
        gain.gain.setValueAtTime(0.35, startTime);
        gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
        
        osc.start(startTime);
        osc.stop(startTime + duration);
    };
    
    // Train station style - upward melody (low to high)
    playChime(now, 523, 0.35);      // C5 note
    playChime(now + 0.4, 659, 0.35); // E5 note (higher)
    playChime(now + 0.8, 784, 0.4);  // G5 note (even higher)
}

function renderLists(data) {
    // Current
    currentQueueData = data.current_queue;
    const current = data.current_queue ? formatDisplayQueue(data.current_queue.queue_number) : '---';
    document.getElementById('currentNumber').textContent = current;
    
    // Store online counters for transfer
    onlineCounters = data.online_counters || [];
    
    // Enable/disable action buttons based on whether there's a current queue
    const hasCurrentQueue = !!data.current_queue;
    document.getElementById('btnNotify').disabled = !hasCurrentQueue;
    document.getElementById('btnSkip').disabled = !hasCurrentQueue;
    document.getElementById('btnComplete').disabled = !hasCurrentQueue;
    document.getElementById('btnTransfer').disabled = !hasCurrentQueue || onlineCounters.length === 0;
    
    // Disable Call Next if no waiting queues OR if still serving current queue
    const hasWaitingQueues = data.waiting_queues && data.waiting_queues.length > 0;
    document.getElementById('btnCallNext').disabled = !hasWaitingQueues || hasCurrentQueue;

    // Waiting
    const waiting = document.getElementById('waitingList');
    waiting.innerHTML = '';
    if (data.waiting_queues && Array.isArray(data.waiting_queues)) {
        data.waiting_queues.forEach(w => {
            const row = document.createElement('div');
            row.className = 'p-3 border rounded flex justify-between items-center';
            row.innerHTML = `<span class="font-semibold">${formatDisplayQueue(w.queue_number)}</span>`;
            waiting.appendChild(row);
        });
    }

    // Skipped
    const skipped = document.getElementById('skippedList');
    skipped.innerHTML = '';
    data.skipped.forEach(s => {
        const row = document.createElement('div');
        row.className = 'p-3 border rounded flex justify-between items-center bg-orange-50';
        row.innerHTML = `<span class="font-semibold text-orange-700">${formatDisplayQueue(s.queue_number)}</span>
                         <button class="bg-blue-600 text-white px-3 py-1 rounded" onclick="recallQueue(${s.id})">Recall</button>`;
        skipped.appendChild(row);
    });
}

// Format display as {counter}-{sequence}, e.g., 1-0001
function formatDisplayQueue(queueNumber) {
    if (!queueNumber) return '---';
    const parts = String(queueNumber).split('-');
    const suffix = parts.length ? (parts[parts.length - 1] || queueNumber) : queueNumber;
    return `${COUNTER_NUM}-${suffix}`;
}

function fetchData() {
    fetch('{{ route('counter.data', ['company_code' => request()->route('company_code')]) }}')
        .then(r => r.json())
        .then(d => { if (d.success) renderLists(d); });
}

// Increase polling interval to 2 seconds (from 1s) to reduce server load while maintaining responsiveness
setInterval(fetchData, 2000);
fetchData();

function postJson(url, payload) {
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: payload ? JSON.stringify(payload) : null,
    }).then(r => r.json());
}

function notifyCustomer() { 
    postJson('{{ route('counter.notify', ['company_code' => request()->route('company_code')]) }}')
        .then(() => {
            playNotificationSound();
            fetchData();
        });
}
function skipCurrent() { openSkipModal(); }
function moveToNext() { postJson('{{ route('counter.move-next', ['company_code' => request()->route('company_code')]) }}').then(() => fetchData()); }
function callNext() { 
    postJson('{{ route('counter.call-next', ['company_code' => request()->route('company_code')]) }}')
        .then(() => {
            playNotificationSound();
            fetchData();
        });
}
function recallQueue(id) { 
    postJson('{{ route('counter.recall', ['company_code' => request()->route('company_code')]) }}', { queue_id: id })
        .then(() => {
            playNotificationSound();
            fetchData();
        });
}

function openSkipModal() {
    const modal = document.getElementById('skip-modal');
    const content = document.getElementById('skip-modal-content');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.add('opacity-100');
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeSkipModal() {
    const modal = document.getElementById('skip-modal');
    const content = document.getElementById('skip-modal-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function confirmSkip() {
    closeSkipModal();
    postJson('{{ route('counter.skip', ['company_code' => request()->route('company_code')]) }}').then(() => fetchData());
}

function openTransferModal() {
    if (!currentQueueData || onlineCounters.length === 0) {
        alert('No available counters to transfer to');
        return;
    }
    
    const modal = document.getElementById('transfer-modal');
    const content = document.getElementById('transfer-modal-content');
    const countersList = document.getElementById('countersList');
    
    // Populate counters list
    countersList.innerHTML = onlineCounters.map(counter => `
        <button onclick="confirmTransfer(${counter.id})" class="w-full p-3 border-2 border-gray-200 hover:border-blue-500 hover:bg-blue-50 rounded-lg text-left transition">
            <div class="font-semibold text-gray-800">Counter ${counter.counter_number}</div>
            <div class="text-sm text-gray-600">${counter.display_name}</div>
        </button>
    `).join('');
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.add('opacity-100');
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeTransferModal() {
    const modal = document.getElementById('transfer-modal');
    const content = document.getElementById('transfer-modal-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function confirmTransfer(toCounterId) {
    if (!currentQueueData) {
        alert('No queue to transfer');
        closeTransferModal();
        return;
    }
    
    closeTransferModal();
    
    fetch('{{ route('counter.transfer', ['company_code' => request()->route('company_code')]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            queue_id: currentQueueData.id,
            to_counter_id: toCounterId
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || `HTTP ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            fetchData();
        } else {
            alert('Transfer failed: ' + (data.message || 'Unknown error'));
            fetchData();
        }
    })
    .catch(err => {
        console.error('Transfer error:', err);
        alert('Transfer failed: ' + err.message);
        fetchData();
    });
}

// Initialize time display and update every second
document.addEventListener('DOMContentLoaded', function() {
    updateHeaderTime();
    setInterval(updateHeaderTime, 1000);
});
</script>
@endpush
@endsection
