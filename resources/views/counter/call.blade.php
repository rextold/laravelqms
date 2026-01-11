@extends('layouts.guest')

@section('title', 'Service Station')

@section('content')
<div class="min-h-screen flex flex-col p-4 bg-gray-50 overflow-hidden">
    <!-- Header with Logo and Organization Name -->
    <div id="panelHeader" class="w-full mb-4">
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    @if($settings->logo_url)
                        <img src="{{ $settings->logo_url }}" alt="Organization Logo" class="h-12 w-auto">
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $organization->organization_name ?? 'QMS' }}</h1>
                        <p class="text-sm text-gray-600">Counter {{ $counter->counter_number }} - {{ $counter->display_name }}</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="text-right">
                        <div class="text-lg font-semibold text-gray-700" id="headerTime"></div>
                        <div class="text-xs text-gray-500" id="headerDate"></div>
                    </div>
                    <button id="btnToggleMinimize" type="button" onclick="toggleMinimize()" class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition" title="Minimize">
                        <i class="fas fa-window-minimize"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div id="panelMain" class="flex-1 flex items-center justify-center">
        <div class="w-full max-w-3xl">
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-xl p-6 border-2 border-white">
            <div class="text-center">
                <p class="text-white text-sm mb-2">NOW SERVING</p>
                <div id="currentNumber" class="responsive-queue-number font-extrabold text-white drop-shadow-2xl">---</div>
                <div class="grid grid-cols-5 gap-2 mt-6 max-w-xl mx-auto">
                    <button type="button" id="btnNotify" onclick="notifyCustomer(this)" class="bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-bell mr-2"></i>Notify
                    </button>
                    <button type="button" id="btnSkip" onclick="skipCurrent()" class="bg-orange-500 hover:bg-orange-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-forward mr-2"></i>Skip
                    </button>
                    <button type="button" id="btnComplete" onclick="moveToNext(this)" class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-check-circle mr-2"></i>Complete
                    </button>
                    <button type="button" id="btnTransfer" onclick="openTransferModal()" class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-exchange-alt mr-2"></i>Transfer
                    </button>
                    <button type="button" id="btnCallNext" onclick="callNext(this)" class="bg-white text-indigo-700 hover:bg-gray-100 disabled:bg-gray-400 disabled:text-gray-600 disabled:cursor-not-allowed px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
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

    <!-- Minimized dock bar (for easy corner docking) -->
    <div id="panelDock" class="hidden fixed bottom-4 right-4 bg-white rounded-lg shadow-md px-4 py-3 flex items-center space-x-4 z-40">
        <div class="text-sm font-semibold text-gray-700">Counter {{ $counter->counter_number }}</div>
        <div class="flex items-baseline space-x-2">
            <div class="text-xs text-gray-500">Now</div>
            <div id="dockCurrentNumber" class="text-xl font-extrabold text-gray-900">---</div>
        </div>
        <button type="button" onclick="toggleMinimize(false)" class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition" title="Restore">
            <i class="fas fa-window-restore"></i>
        </button>
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
                <button type="button" onclick="closeTransferModal()" class="text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
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
            <button type="button" onclick="closeTransferModal()" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">Cancel</button>
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
                <button type="button" onclick="closeSkipModal()" class="text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
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
            <button type="button" onclick="closeSkipModal()" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">Cancel</button>
            <button type="button" onclick="confirmSkip(this)" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition">
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
let selectedTransferQueueId = null;

const ACTION_COOLDOWN_SECONDS = 5;
const buttonCooldowns = new Map();

function isButtonCooling(btnEl) {
    if (!btnEl) return false;
    const until = buttonCooldowns.get(btnEl.id);
    return typeof until === 'number' && Date.now() < until;
}

function getCooldownRemainingSeconds(btnEl) {
    if (!btnEl) return 0;
    const until = buttonCooldowns.get(btnEl.id);
    if (typeof until !== 'number') return 0;
    return Math.max(0, Math.ceil((until - Date.now()) / 1000));
}

function startButtonCooldown(btnEl, seconds) {
    if (!btnEl || !btnEl.id) return;

    const existing = buttonCooldowns.get(btnEl.id);
    if (typeof existing === 'number' && Date.now() < existing) return;

    const until = Date.now() + (seconds * 1000);
    buttonCooldowns.set(btnEl.id, until);

    if (!btnEl.dataset.originalHtml) {
        btnEl.dataset.originalHtml = btnEl.innerHTML;
    }

    btnEl.disabled = true;

    const tick = () => {
        const remaining = getCooldownRemainingSeconds(btnEl);
        if (remaining <= 0) {
            buttonCooldowns.delete(btnEl.id);
            if (btnEl.dataset.originalHtml) btnEl.innerHTML = btnEl.dataset.originalHtml;
            delete btnEl.dataset.originalHtml;
            // Let the next poll decide enabled/disabled state
            return;
        }

        const baseHtml = btnEl.dataset.originalHtml || btnEl.innerHTML;
        btnEl.innerHTML = `${baseHtml} <span class="ml-2 text-xs opacity-90">(${remaining}s)</span>`;
    };

    tick();
    const timer = setInterval(() => {
        const remaining = getCooldownRemainingSeconds(btnEl);
        if (remaining <= 0) {
            clearInterval(timer);
            tick();
            return;
        }
        tick();
    }, 250);
}

function runActionWithCooldown(btnEl, actionFn, seconds = ACTION_COOLDOWN_SECONDS) {
    if (btnEl && isButtonCooling(btnEl)) return;
    if (btnEl) startButtonCooldown(btnEl, seconds);

    return Promise.resolve()
        .then(actionFn)
        .catch(err => {
            // On error, stop cooldown early so user can retry
            if (btnEl && btnEl.id) {
                buttonCooldowns.delete(btnEl.id);
                if (btnEl.dataset.originalHtml) btnEl.innerHTML = btnEl.dataset.originalHtml;
                delete btnEl.dataset.originalHtml;
            }
            console.error(err);
            alert(err?.message || 'Action failed. Please try again.');
        });
}

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

// Format queue display as sequence only (digits-only)
function formatDisplayQueue(queueNumber) {
    if (!queueNumber) return '---';
    const parts = String(queueNumber).split('-');
    return parts.length ? (parts[parts.length - 1] || String(queueNumber)) : String(queueNumber);
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
    const dockEl = document.getElementById('dockCurrentNumber');
    if (dockEl) dockEl.textContent = current;
    
    // Store online counters for transfer
    onlineCounters = data.online_counters || [];
    
    // Enable/disable action buttons based on whether there's a current queue
    const hasCurrentQueue = !!data.current_queue;
    const btnNotify = document.getElementById('btnNotify');
    const btnSkip = document.getElementById('btnSkip');
    const btnComplete = document.getElementById('btnComplete');
    const btnTransfer = document.getElementById('btnTransfer');
    const btnCallNext = document.getElementById('btnCallNext');

    if (btnNotify) btnNotify.disabled = !hasCurrentQueue || isButtonCooling(btnNotify);
    if (btnSkip) btnSkip.disabled = !hasCurrentQueue || isButtonCooling(btnSkip);
    if (btnComplete) btnComplete.disabled = !hasCurrentQueue || isButtonCooling(btnComplete);
    if (btnTransfer) btnTransfer.disabled = !hasCurrentQueue || onlineCounters.length === 0 || isButtonCooling(btnTransfer);
    
    // Disable Call Next if no waiting queues OR if still serving current queue
    const hasWaitingQueues = data.waiting_queues && data.waiting_queues.length > 0;
    if (btnCallNext) btnCallNext.disabled = !hasWaitingQueues || hasCurrentQueue || isButtonCooling(btnCallNext);

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
                         <button type="button" class="bg-blue-600 text-white px-3 py-1 rounded" onclick="recallQueue(${s.id})">Recall</button>`;
        skipped.appendChild(row);
    });
}



let counterFetchInFlight = false;
let counterFetchController = null;
function fetchData() {
    // Prevent stacking requests when server/network is slow
    if (counterFetchInFlight) return;
    counterFetchInFlight = true;

    try {
        if (counterFetchController) {
            counterFetchController.abort();
        }
        counterFetchController = new AbortController();
    } catch (e) {
        counterFetchController = null;
    }

    fetch('{{ route('counter.data', ['organization_code' => request()->route('organization_code')]) }}', {
        cache: 'no-store',
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
        signal: counterFetchController ? counterFetchController.signal : undefined,
    })
        .then(r => r.json())
        .then(d => { if (d.success) renderLists(d); })
        .catch(err => {
            if (err && err.name === 'AbortError') return;
            console.error('Counter refresh failed:', err);
        })
        .finally(() => {
            counterFetchInFlight = false;
        });
}

let isMinimized = false;

function setMinimized(minimized) {
    isMinimized = !!minimized;
    const header = document.getElementById('panelHeader');
    const main = document.getElementById('panelMain');
    const dock = document.getElementById('panelDock');
    const btn = document.getElementById('btnToggleMinimize');

    if (isMinimized) {
        if (header) header.classList.add('hidden');
        if (main) main.classList.add('hidden');
        if (dock) dock.classList.remove('hidden');
        // Ensure modals don't block the screen while minimized
        try { closeSkipModal(); } catch (e) {}
        try { closeTransferModal(); } catch (e) {}
    } else {
        if (header) header.classList.remove('hidden');
        if (main) main.classList.remove('hidden');
        if (dock) dock.classList.add('hidden');
    }

    if (btn) {
        btn.title = isMinimized ? 'Restore' : 'Minimize';
        btn.innerHTML = isMinimized ? '<i class="fas fa-window-restore"></i>' : '<i class="fas fa-window-minimize"></i>';
    }

    try {
        localStorage.setItem('counterPanelMinimized', isMinimized ? '1' : '0');
    } catch (e) {}
}

function toggleMinimize(force) {
    if (typeof force === 'boolean') {
        setMinimized(force);
        return;
    }
    setMinimized(!isMinimized);
}

// Rapid polling for real-time counter updates
setInterval(fetchData, 1000);
fetchData();

function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '{{ csrf_token() }}';
}

function postJson(url, payload) {
    return fetch(url, {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: payload ? JSON.stringify(payload) : null,
    }).then(r => {
        if (!r.ok) {
            return r.json().catch(() => ({ success: false, message: `HTTP ${r.status}` }))
                .then(data => Promise.reject(new Error(data.message || `HTTP ${r.status}`)));
        }
        return r.json();
    });
}

function notifyCustomer(btnEl) { 
    return runActionWithCooldown(btnEl, () =>
        postJson('{{ route('counter.notify', ['organization_code' => request()->route('organization_code')]) }}')
            .then((data) => {
                if (data && data.success) {
                    playNotificationSound();
                    fetchData();
                } else {
                    throw new Error(data?.message || 'Notification failed');
                }
            })
            .catch((err) => {
                console.error('Notify error:', err);
                alert('Failed to notify customer: ' + (err.message || 'Unknown error'));
                fetchData();
            })
    );
}
function skipCurrent() { openSkipModal(); }
function moveToNext(btnEl) {
    return runActionWithCooldown(btnEl, () =>
        postJson('{{ route('counter.move-next', ['organization_code' => request()->route('organization_code')]) }}')
            .then(() => fetchData())
    );
}
function callNext(btnEl) { 
    return runActionWithCooldown(btnEl, () =>
        postJson('{{ route('counter.call-next', ['organization_code' => request()->route('organization_code')]) }}')
            .then(() => {
                playNotificationSound();
                fetchData();
            })
    );
}
function recallQueue(id) { 
    postJson('{{ route('counter.recall', ['organization_code' => request()->route('organization_code')]) }}', { queue_id: id })
        .then((res) => {
            if (!res || res.success !== true) {
                const msg = (res && res.message) ? res.message : 'Recall failed. Please try again.';
                throw new Error(msg);
            }
            playNotificationSound();
            fetchData();
        })
        .catch((err) => {
            alert(err?.message || 'Recall failed. Please try again.');
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

function confirmSkip(btnEl) {
    closeSkipModal();
    return runActionWithCooldown(btnEl, () =>
        postJson('{{ route('counter.skip', ['organization_code' => request()->route('organization_code')]) }}')
            .then(() => fetchData())
    );
}

function openTransferModal(queueId) {
    const idToTransfer = typeof queueId === 'number' ? queueId : (currentQueueData ? currentQueueData.id : null);
    if (!idToTransfer) {
        alert('No queue to transfer');
        return;
    }

    selectedTransferQueueId = idToTransfer;

    if (onlineCounters.length === 0) {
        alert('No available counters to transfer to');
        return;
    }
    
    const modal = document.getElementById('transfer-modal');
    const content = document.getElementById('transfer-modal-content');
    const countersList = document.getElementById('countersList');
    
    // Populate counters list
    countersList.innerHTML = onlineCounters.map(counter => `
        <button type="button" onclick="confirmTransfer(${counter.id})" class="w-full p-3 border-2 border-gray-200 hover:border-blue-500 hover:bg-blue-50 rounded-lg text-left transition">
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
    if (!selectedTransferQueueId) {
        alert('No queue to transfer');
        closeTransferModal();
        return;
    }
    
    closeTransferModal();
    
    fetch('{{ route('counter.transfer', ['organization_code' => request()->route('organization_code')]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            queue_id: selectedTransferQueueId,
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
            selectedTransferQueueId = null;
            fetchData();
        } else {
            alert('Transfer failed: ' + (data.message || 'Unknown error'));
            selectedTransferQueueId = null;
            fetchData();
        }
    })
    .catch(err => {
        console.error('Transfer error:', err);
        alert('Transfer failed: ' + err.message);
        selectedTransferQueueId = null;
        fetchData();
    });
}

// Initialize time display and update every second
document.addEventListener('DOMContentLoaded', function() {
    updateHeaderTime();
    setInterval(updateHeaderTime, 1000);

    // Restore minimized state if user left it minimized
    try {
        const saved = localStorage.getItem('counterPanelMinimized');
        if (saved === '1') setMinimized(true);
    } catch (e) {}
});
</script>
@endpush
@endsection
