@extends('layouts.counter')

@section('title', 'Service Station')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <!-- Main Service Panel -->
    <div id="panelMain" class="flex-1 p-6">
        <div class="max-w-6xl mx-auto">
            <!-- Current Queue Display -->
            <div class="glass-card p-8 mb-6">
                <div class="text-center">
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-user-check mr-2"></i>
                            NOW SERVING
                        </span>
                    </div>
                    <div id="currentNumber" class="queue-number font-extrabold text-gray-900 mb-6 tracking-wider">---</div>
                    
                    <!-- Action Buttons Component -->
                @include('components.counter.action-buttons', ['disabled' => true])
                </div>
            </div>

            <!-- Queue Lists Component -->
            @include('components.counter.queue-lists')

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-check text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-600">Served Today</p>
                            <p class="text-xl font-bold text-gray-900" id="servedToday">0</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-green-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-600">Avg Wait</p>
                            <p class="text-xl font-bold text-gray-900" id="avgWait">0m</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-stopwatch text-purple-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-600">Avg Service</p>
                            <p class="text-xl font-bold text-gray-900" id="avgService">0m</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-indigo-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-600">Efficiency</p>
                            <p class="text-xl font-bold text-gray-900" id="efficiency">100%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Minimized dock bar (for easy corner docking) -->
    @include('components.counter.dock', ['counter' => $counter, 'organizationCode' => $organization->organization_code ?? ''])

<style>
.responsive-queue-number { font-size: 6rem; }
@media (orientation: portrait) { .responsive-queue-number { font-size: 4.5rem; } }
@media (max-width: 768px) and (orientation: portrait) { .responsive-queue-number { font-size: 3rem; } }
@media (max-width: 768px) and (orientation: landscape) { .responsive-queue-number { font-size: 3.5rem; } }

/* Enhanced dock styling for better visibility */
#panelDock {
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

/* Mobile responsive dock */
@media (max-width: 640px) {
    #panelDock {
        bottom: 1rem;
        right: 1rem;
        left: 1rem;
        padding: 0.75rem;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    #panelDock > div:first-child {
        order: 1;
        flex-basis: 100%;
        text-align: center;
        margin-bottom: 0.5rem;
    }
    
    #panelDock .bg-blue-100 {
        order: 2;
        flex: 1;
        min-width: 0;
    }
    
    #dockRecallBtn {
        order: 3;
        flex-shrink: 0;
    }
    
    #panelDock button[onclick*="toggleMinimize"] {
        order: 4;
        flex-shrink: 0;
    }
}

/* Small screens - extra compact */
@media (max-width: 390px) {
    #panelDock #dockRecallBtn {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
    }
    
    #panelDock #dockRecallBtn i {
        margin-right: 0.25rem;
    }
}
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
                        <button type="button" onclick="closeTransferModal()" class="counter-btn px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold">Cancel</button>
                        <button type="button" onclick="confirmTransfer()" class="counter-btn px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold">
                            <i class="fas fa-exchange-alt mr-2"></i>Transfer Customer
                        </button>
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
            <button type="button" onclick="closeSkipModal()" class="counter-btn px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold">Cancel</button>
            <button type="button" onclick="confirmSkip(this)" class="counter-btn px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold">
                <i class="fas fa-forward mr-2"></i>Skip Queue
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
html, body { overflow: hidden; }
</style>
@endpush

@push('scripts')
<script>
// ============================================================
// COUNTER PANEL - MAIN CONFIGURATION
// ============================================================
const COUNTER_ID = {{ $counter->id }};
const COUNTER_NUM = {{ $counter->counter_number }};
const ORG_CODE = '{{ request()->route('organization_code') }}';

// State Management
let currentQueueData = null;
let onlineCounters = [];
let selectedTransferQueueId = null;
let lastErrorTime = 0;

const ACTION_COOLDOWN_SECONDS = 3;
const buttonCooldowns = new Map();
const FETCH_INTERVAL = 1000; // 1 second real-time updates

// ============================================================
// UTILITY FUNCTIONS
// ============================================================

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

function startButtonCooldown(btnEl, seconds = ACTION_COOLDOWN_SECONDS) {
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
            if (btnEl && btnEl.id) {
                buttonCooldowns.delete(btnEl.id);
                if (btnEl.dataset.originalHtml) btnEl.innerHTML = btnEl.dataset.originalHtml;
                delete btnEl.dataset.originalHtml;
            }
            console.error('Action error:', err);
            
            // Suppress 403 errors silently - don't interrupt user
            if (err.message && err.message.includes('403')) {
                console.warn('Access error suppressed - retrying...');
                return;
            }
            
            // Only alert for non-403 errors
            alert(err?.message || 'Action failed. Please try again.');
        });
}



function formatDisplayQueue(queueNumber) {
    if (!queueNumber) return '---';
    const parts = String(queueNumber).split('-');
    return parts.length > 0 ? (parts[parts.length - 1] || String(queueNumber)) : String(queueNumber);
}

function playNotificationSound() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const now = audioContext.currentTime;
        
        const playChime = (startTime, frequency, duration) => {
            const osc = audioContext.createOscillator();
            const gain = audioContext.createGain();
            
            osc.connect(gain);
            gain.connect(audioContext.destination);
            osc.frequency.value = frequency;
            osc.type = 'sine';
            gain.gain.setValueAtTime(0.35, startTime);
            gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
            osc.start(startTime);
            osc.stop(startTime + duration);
        };
        
        playChime(now, 523, 0.35);
        playChime(now + 0.4, 659, 0.35);
        playChime(now + 0.8, 784, 0.4);
    } catch (e) {
        console.log('Audio play error:', e);
    }
}

// ============================================================
// DATA RENDERING
// ============================================================

function renderLists(data) {
    // Current queue
    currentQueueData = data.current_queue;
    const current = data.current_queue ? formatDisplayQueue(data.current_queue.queue_number) : '---';
    
    const currentNum = document.getElementById('currentNumber');
    if (currentNum) currentNum.textContent = current;
    
    const dockNum = document.getElementById('dockCurrentNumber');
    if (dockNum) dockNum.textContent = current;
    
    // Store online counters
    onlineCounters = data.online_counters || [];
    
    // Update button states
    const hasCurrentQueue = !!data.current_queue;
    const hasWaitingQueues = data.waiting_queues && data.waiting_queues.length > 0;
    
    const btnNotify = document.getElementById('btnNotify');
    const btnSkip = document.getElementById('btnSkip');
    const btnComplete = document.getElementById('btnComplete');
    const btnTransfer = document.getElementById('btnTransfer');
    const btnCallNext = document.getElementById('btnCallNext');

    if (btnNotify) btnNotify.disabled = !hasCurrentQueue || isButtonCooling(btnNotify);
    if (btnSkip) btnSkip.disabled = !hasCurrentQueue || isButtonCooling(btnSkip);
    if (btnComplete) btnComplete.disabled = !hasCurrentQueue || isButtonCooling(btnComplete);
    if (btnTransfer) btnTransfer.disabled = !hasCurrentQueue || onlineCounters.length === 0 || isButtonCooling(btnTransfer);
    if (btnCallNext) btnCallNext.disabled = !hasWaitingQueues || hasCurrentQueue || isButtonCooling(btnCallNext);

    // Update waiting count
    const waitingCount = document.getElementById('waitingCount');
    if (waitingCount) {
        waitingCount.textContent = hasWaitingQueues ? data.waiting_queues.length : '0';
    }

    // Update served today
    const servedToday = data.served_today || 0;
    const servedTodayMain = document.getElementById('servedToday');
    if (servedTodayMain) servedTodayMain.textContent = servedToday;

    // Waiting queues
    const waitingList = document.getElementById('waitingList');
    if (waitingList) {
        waitingList.innerHTML = '';
        if (data.waiting_queues && Array.isArray(data.waiting_queues)) {
            data.waiting_queues.forEach(w => {
                        const row = document.createElement('div');
                        row.className = 'queue-item';
                        row.innerHTML = `
                            <div class="flex items-center">
                                <span class="text-lg font-bold text-gray-800 mr-3">${formatDisplayQueue(w.queue_number)}</span>
                                <span class="text-gray-600">${w.customer_name || 'Customer'}</span>
                            </div>
                            <span class="text-sm text-gray-500">${new Date(w.created_at).toLocaleTimeString()}</span>
                        `;
                        waitingList.appendChild(row);
                    });
        }
    }

    // Skipped queues
    const skippedList = document.getElementById('skippedList');
    if (skippedList) {
        skippedList.innerHTML = '';
        if (data.skipped && Array.isArray(data.skipped)) {
            data.skipped.forEach(s => {
                const row = document.createElement('div');
                row.className = 'queue-item bg-orange-50';
                row.innerHTML = `
                    <div class="flex items-center">
                        <span class="text-lg font-bold text-orange-700 mr-3">${formatDisplayQueue(s.queue_number)}</span>
                        <span class="text-gray-600">${s.customer_name || 'Customer'}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">${new Date(s.created_at).toLocaleTimeString()}</span>
                        <button type="button" class="bg-blue-600 text-white px-3 py-1 rounded text-sm" onclick="recallQueue(${s.id}, event)">Recall</button>
                    </div>
                `;
                skippedList.appendChild(row);
            });
        }
    }
    
    // Update dock recall button visibility
    const dockRecallBtn = document.getElementById('dockRecallBtn');
    if (dockRecallBtn) {
        if (data.skipped && data.skipped.length > 0) {
            dockRecallBtn.classList.remove('hidden');
            dockRecallBtn.title = `Recall skipped queue (${data.skipped.length} available)`;
        } else {
            dockRecallBtn.classList.add('hidden');
        }
    }
}

// ============================================================
// FETCH & POLLING
// ============================================================

let counterFetchInFlight = false;
let counterFetchController = null;
let lastSuccessfulData = null;

function handleFallbackData() {
    console.log('Using cached counter data as fallback');
    if (lastSuccessfulData) {
        renderLists(lastSuccessfulData);
    } else {
        // If no cached data, render empty state
        renderLists({
            success: true,
            current_queue: null,
            waiting_queues: [],
            skipped: [],
            online_counters: [],
            served_today: 0,
            stats: {
                waiting: 0,
                completed_today: 0,
                avg_wait_time: 0,
                avg_service_time: 0
            },
            analytics: {
                hourly: Array(24).fill(0),
                weekly: Array(7).fill(0),
                weekly_days: [],
                wait_times: Array(7).fill(0)
            }
        });
    }
}

function fetchData() {
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
    
    const url = new URL(`/${ORG_CODE}/counter/data`, window.location.origin);
    url.searchParams.append('counter_id', COUNTER_ID);
    console.log('Fetching counter data from', url.toString());
    
    fetch(url, {
        method: 'GET',
        headers: window.CounterSecurity ? window.CounterSecurity.getHeaders() : {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        signal: counterFetchController ? counterFetchController.signal : undefined,
    })
        .then(response => {
            // Handle authentication errors gracefully
            if (response.status === 403 || response.status === 401) {
                console.warn(`Counter endpoint ${response.status} - using fallback data`);
                handleFallbackData();
                counterFetchInFlight = false;
                return;
            }
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            return response.json().then(data => {
                if (data.success) {
                    // Cache successful data for fallback
                    lastSuccessfulData = data;
                    renderLists(data);
                    lastErrorTime = 0;
                } else {
                    console.warn('Counter data response not successful:', data);
                    handleFallbackData();
                }
            });
        })
        .catch(err => {
            if (err && err.name === 'AbortError') return;
            
            // Suppress repeated errors
            const now = Date.now();
            if (now - lastErrorTime > 5000) {
                console.error('Counter refresh failed:', err);
                lastErrorTime = now;
            }
            
            // Use fallback data on network errors
            handleFallbackData();
        })
        .finally(() => {
            counterFetchInFlight = false;
        });
}

// ============================================================
// MINIMIZE/RESTORE
// ============================================================

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

// ============================================================
// COUNTER ACTIONS - ALL USING GET REQUESTS
// ============================================================

function makeCounterRequest(action, params = {}) {
    const url = new URL(`/${ORG_CODE}/counter/${action}`, window.location.origin);
    
    // Add query parameters
    Object.keys(params).forEach(key => {
        if (params[key] !== undefined && params[key] !== null) {
            url.searchParams.append(key, params[key]);
        }
    });

    return fetch(url.toString(), {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            if (response.status === 403) {
                // Suppress 403 for counter operations
                console.warn(`Counter action '${action}' returned 403`);
                return { success: false, suppressed: true };
            }
            
            if (!response.ok) {
                return { success: false, message: `HTTP ${response.status}` };
            }
            
            return response.json();
        })
        .catch(err => {
            console.error(`Counter request error for '${action}':`, err);
            return { success: false, message: err.message };
        });
}

function notifyCustomer(btnEl, event) {
    if (event) event.preventDefault();
    return runActionWithCooldown(btnEl, () =>
        makeCounterRequest('notify')
            .then((data) => {
                if (data && data.success) {
                    playNotificationSound();
                    fetchData();
                } else if (data && data.suppressed) {
                    // Silently continue
                    fetchData();
                } else {
                    throw new Error(data?.message || 'Notification failed');
                }
            })
    );
    return false;
}

function skipCurrent() {
    openSkipModal();
}

function moveToNext(btnEl) {
    return runActionWithCooldown(btnEl, () =>
        makeCounterRequest('move-next')
            .then((data) => {
                if (data.success || data.suppressed) {
                    fetchData();
                } else {
                    throw new Error(data?.message || 'Failed to move to next');
                }
            })
    );
}

function callNext(btnEl) {
    return runActionWithCooldown(btnEl, () =>
        makeCounterRequest('call-next')
            .then((data) => {
                if (data.success || data.suppressed) {
                    playNotificationSound();
                    fetchData();
                } else {
                    throw new Error(data?.message || 'Failed to call next');
                }
            })
    );
}

function recallQueue(queueId, event) {
    if (event) event.preventDefault();
    
    if (!queueId) {
        alert('Invalid queue ID');
        return;
    }

    makeCounterRequest('recall', { queue_id: queueId })
        .then((data) => {
            if (data.success) {
                playNotificationSound();
                fetchData();
            } else if (data.suppressed) {
                // Silently retry
                fetchData();
            } else {
                alert(data?.message || 'Recall failed');
                fetchData();
            }
        });
}

function recallFromDock() {
    // Get the first available skipped queue when in dock mode
    if (lastSuccessfulData && lastSuccessfulData.skipped && lastSuccessfulData.skipped.length > 0) {
        const firstSkipped = lastSuccessfulData.skipped[0];
        if (firstSkipped && firstSkipped.id) {
            recallQueue(firstSkipped.id, null);
        } else {
            alert('No skipped queues available to recall');
        }
    } else {
        alert('No skipped queues available to recall');
    }
}

function confirmSkip(btnEl) {
    closeSkipModal();
    return runActionWithCooldown(btnEl, () =>
        makeCounterRequest('skip')
            .then((data) => {
                if (data.success || data.suppressed) {
                    fetchData();
                } else {
                    throw new Error(data?.message || 'Failed to skip');
                }
            })
    );
}

function confirmTransfer(toCounterId) {
    if (!selectedTransferQueueId) {
        alert('No queue to transfer');
        closeTransferModal();
        return;
    }

    closeTransferModal();

    makeCounterRequest('transfer', {
        queue_id: selectedTransferQueueId,
        to_counter_id: toCounterId
    })
        .then(data => {
            if (data.success) {
                selectedTransferQueueId = null;
                fetchData();
            } else if (data.suppressed) {
                selectedTransferQueueId = null;
                fetchData();
            } else {
                alert('Transfer failed: ' + (data.message || 'Unknown error'));
                selectedTransferQueueId = null;
                fetchData();
            }
        });
}

// ============================================================
// MODAL FUNCTIONS
// ============================================================

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

function openTransferModal() {
    const idToTransfer = currentQueueData ? currentQueueData.id : null;
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

    countersList.innerHTML = onlineCounters.map(counter => `
        <button type="button" onclick="confirmTransfer(${counter.id})" class="queue-item hover:bg-blue-50 cursor-pointer">
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

// ============================================================
// INITIALIZATION
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    // Rapid polling for real-time updates
    setInterval(fetchData, FETCH_INTERVAL);
    fetchData(); // Initial fetch

    // Handle visibility changes
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            console.log('Counter panel restored, refreshing data...');
            fetchData();
        }
    });

    // Handle network changes
    window.addEventListener('online', function() {
        console.log('Network restored');
        fetchData();
    });

    window.addEventListener('offline', function() {
        console.warn('Network disconnected - using cached data');
    });
    
    // Keyboard shortcuts for quick actions when minimized
    document.addEventListener('keydown', function(e) {
        // Only work when dock is visible (minimized state)
        const dock = document.getElementById('panelDock');
        if (dock && !dock.classList.contains('hidden')) {
            // R key for Recall (when recall button is visible)
            if (e.key === 'r' || e.key === 'R') {
                const recallBtn = document.getElementById('dockRecallBtn');
                if (recallBtn && !recallBtn.classList.contains('hidden')) {
                    e.preventDefault();
                    recallFromDock();
                }
            }
            // Space bar to restore from dock
            else if (e.key === ' ') {
                e.preventDefault();
                toggleMinimize(false);
            }
        }
    });
});
</script>

<!-- Enhanced CSS for better recall visibility -->
<style>
    /* Enhanced visibility for recall button */
    #dockRecallBtn {
        animation: pulse-orange 2s infinite;
        position: relative;
    }
    
    #dockRecallBtn:hover {
        animation: none;
        transform: scale(1.05);
    }
    
    @keyframes pulse-orange {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.7);
        }
        50% {
            box-shadow: 0 0 0 10px rgba(249, 115, 22, 0);
        }
    }
    
    /* High contrast mode support */
    @media (prefers-contrast: high) {
        #panelDock {
            border-width: 3px;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.8);
        }
        
        #dockRecallBtn {
            border: 2px solid #000;
        }
    }
    
    /* Reduced motion support */
    @media (prefers-reduced-motion: reduce) {
        #dockRecallBtn {
            animation: none;
        }
        
        .transition-all {
            transition: none !important;
        }
    }
    
    /* Enhanced responsive styles */
    @media (max-width: 640px) {
        #panelDock {
            bottom: 2px;
            right: 2px;
            padding: 8px 12px;
            font-size: 0.75rem;
        }
        
        #dockCurrentNumber {
            font-size: 1.25rem;
        }
        
        #dockRecallBtn {
            padding: 6px 10px;
            font-size: 0.7rem;
        }
    }
    
    @media (max-width: 480px) {
        #panelDock {
            bottom: 1px;
            right: 1px;
            padding: 6px 8px;
            border-width: 1px;
        }
        
        #dockCurrentNumber {
            font-size: 1rem;
        }
    }
    
    @media (max-width: 390px) {
        #panelDock {
            padding: 4px 6px;
        }
        
        #dockRecallBtn span:not(.fas) {
            display: none;
        }
        
        #dockRecallBtn {
            padding: 4px 6px;
        }
    }
    
    /* Ultra small screens */
    @media (max-width: 320px) {
        #panelDock {
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 8px 4px;
        }
        
        #dockRecallBtn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush
@endsection