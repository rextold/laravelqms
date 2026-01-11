@extends('layouts.guest')

@section('title', 'Counter Service Panel')

@section('content')
<div class="min-h-screen flex flex-col p-4 bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden">
    <!-- Header Section -->
    <div id="panelHeader" class="w-full mb-4">
        <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <!-- Organization Info -->
                <div class="flex items-center space-x-4">
                    @if($settings->logo_url)
                        <img src="{{ $settings->logo_url }}" alt="Logo" class="h-14 w-auto object-contain">
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $organization->organization_name ?? 'Queue Management' }}</h1>
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-desktop mr-1"></i>Counter {{ $counter->counter_number }} - {{ $counter->display_name }}
                        </p>
                    </div>
                </div>
                
                <!-- Time and Controls -->
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-lg font-semibold text-gray-700" id="headerTime">--:--</div>
                        <div class="text-xs text-gray-500" id="headerDate">Loading...</div>
                    </div>
                    <button id="btnToggleMinimize" type="button" onclick="CounterPanel.toggleMinimize()" 
                            class="px-4 py-2 rounded-lg bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 transition-all shadow-sm" 
                            title="Minimize">
                        <i class="fas fa-window-minimize"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Service Area -->
    <div id="panelMain" class="flex-1 flex items-center justify-center">
        <div class="w-full max-w-4xl space-y-4">
            <!-- Now Serving Card -->
            <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-700 rounded-2xl shadow-2xl p-8 border-4 border-white">
                <div class="text-center">
                    <p class="text-white/90 text-sm font-medium mb-2 tracking-wider uppercase">Now Serving</p>
                    <div id="currentNumber" class="responsive-queue-number font-black text-white drop-shadow-2xl tracking-tight">---</div>
                    
                    <!-- Action Buttons -->
                    <div class="grid grid-cols-5 gap-3 mt-8 max-w-2xl mx-auto">
                        <button id="btnNotify" onclick="CounterPanel.notifyCustomer(this)" 
                                class="action-btn bg-yellow-500 hover:bg-yellow-600" disabled>
                            <i class="fas fa-bell"></i>
                            <span class="text-xs mt-1">Notify</span>
                        </button>
                        <button id="btnSkip" onclick="CounterPanel.skipCurrent()" 
                                class="action-btn bg-orange-500 hover:bg-orange-600" disabled>
                            <i class="fas fa-forward"></i>
                            <span class="text-xs mt-1">Skip</span>
                        </button>
                        <button id="btnComplete" onclick="CounterPanel.moveToNext(this)" 
                                class="action-btn bg-green-600 hover:bg-green-700" disabled>
                            <i class="fas fa-check-circle"></i>
                            <span class="text-xs mt-1">Complete</span>
                        </button>
                        <button id="btnTransfer" onclick="CounterPanel.openTransferModal()" 
                                class="action-btn bg-blue-600 hover:bg-blue-700" disabled>
                            <i class="fas fa-exchange-alt"></i>
                            <span class="text-xs mt-1">Transfer</span>
                        </button>
                        <button id="btnCallNext" onclick="CounterPanel.callNext(this)" 
                                class="action-btn bg-white text-indigo-700 hover:bg-indigo-50" disabled>
                            <i class="fas fa-phone"></i>
                            <span class="text-xs mt-1">Call Next</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Queue Lists -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-xl shadow-lg p-5 border border-gray-200">
                    <h3 class="text-lg font-bold mb-3 text-gray-800 flex items-center">
                        <i class="fas fa-clock mr-2 text-blue-500"></i>Waiting Queue
                    </h3>
                    <div id="waitingList" class="space-y-2 max-h-48 overflow-y-auto"></div>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-5 border border-gray-200">
                    <h3 class="text-lg font-bold mb-3 text-gray-800 flex items-center">
                        <i class="fas fa-pause-circle mr-2 text-orange-500"></i>Skipped Queue
                    </h3>
                    <div id="skippedList" class="space-y-2 max-h-48 overflow-y-auto"></div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Minimized Dock Bar -->
    <div id="panelDock" class="hidden fixed bottom-4 right-4 bg-white rounded-xl shadow-2xl px-6 py-4 flex items-center space-x-4 z-50 border border-gray-300">
        <div class="flex items-center space-x-3">
            <i class="fas fa-desktop text-indigo-600"></i>
            <div class="text-sm font-semibold text-gray-700">Counter {{ $counter->counter_number }}</div>
        </div>
        <div class="flex items-center space-x-2 border-l pl-4">
            <span class="text-xs text-gray-500">Serving:</span>
            <div id="dockCurrentNumber" class="text-2xl font-black text-indigo-600">---</div>
        </div>
        <button type="button" onclick="CounterPanel.toggleMinimize(false)" 
                class="ml-2 px-3 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white transition-all shadow-sm" 
                title="Restore">
            <i class="fas fa-window-restore"></i>
        </button>
    </div>

<style>
/* Responsive queue number sizing */
.responsive-queue-number { 
    font-size: 7rem; 
    line-height: 1;
}
@media (orientation: portrait) { 
    .responsive-queue-number { font-size: 5rem; } 
}
@media (max-width: 768px) and (orientation: portrait) { 
    .responsive-queue-number { font-size: 3.5rem; } 
}
@media (max-width: 768px) and (orientation: landscape) { 
    .responsive-queue-number { font-size: 4rem; } 
}

/* Action buttons styling */
.action-btn {
    @apply flex flex-col items-center justify-center px-4 py-3 rounded-xl text-white font-semibold transition-all shadow-md;
    @apply disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-50;
}
.action-btn:not(:disabled):hover {
    @apply transform scale-105 shadow-lg;
}
.action-btn:not(:disabled):active {
    @apply transform scale-95;
}

/* Hide body scrollbar */
html, body { 
    overflow: hidden; 
}

/* Custom scrollbar for queue lists */
#waitingList::-webkit-scrollbar,
#skippedList::-webkit-scrollbar {
    width: 6px;
}
#waitingList::-webkit-scrollbar-track,
#skippedList::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
#waitingList::-webkit-scrollbar-thumb,
#skippedList::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}
#waitingList::-webkit-scrollbar-thumb:hover,
#skippedList::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<!-- Transfer Queue Modal -->
<div id="transfer-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 transition-opacity duration-300 opacity-0">
    <div id="transfer-modal-content" class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-exchange-alt text-white text-2xl"></i>
                    <h3 class="text-xl font-bold text-white">Transfer Queue</h3>
                </div>
                <button onclick="CounterPanel.closeTransferModal()" class="text-white/90 hover:text-white text-3xl leading-none transition">&times;</button>
            </div>
        </div>

        <div class="px-6 py-5">
            <p class="text-gray-700 mb-4 font-medium">Select a counter to transfer this queue:</p>
            <div id="countersList" class="space-y-2 max-h-64 overflow-y-auto pr-2">
                <!-- Populated dynamically -->
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mt-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    The queue number will be preserved (first-come, first-served).
                </p>
            </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end border-t border-gray-200">
            <button onclick="CounterPanel.closeTransferModal()" 
                    class="px-5 py-2 text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg font-semibold transition shadow-sm">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Skip Confirmation Modal -->
<div id="skip-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 transition-opacity duration-300 opacity-0">
    <div id="skip-modal-content" class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
        <div class="bg-gradient-to-r from-orange-600 to-orange-700 px-6 py-5 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-forward text-white text-2xl"></i>
                    <h3 class="text-xl font-bold text-white">Skip Current Queue</h3>
                </div>
                <button onclick="CounterPanel.closeSkipModal()" class="text-white/90 hover:text-white text-3xl leading-none transition">&times;</button>
            </div>
        </div>

        <div class="px-6 py-5">
            <p class="text-gray-700 mb-4 font-medium">Skip the current customer queue?</p>
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
                <p class="text-sm text-orange-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    The queue will be moved to the skipped list and can be recalled later.
                </p>
            </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            <button onclick="CounterPanel.closeSkipModal()" 
                    class="px-5 py-2 text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg font-semibold transition shadow-sm">
                Cancel
            </button>
            <button onclick="CounterPanel.confirmSkip(this)" 
                    class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition shadow-sm">
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

<script>
// ============================================================================
// Counter Panel Application - Refactored and Organized
// ============================================================================
const CounterPanel = (function() {
    'use strict';
    
    // Configuration
    const CONFIG = {
        COUNTER_NUMBER: {{ $counter->counter_number }},
        POLL_INTERVAL: 1000,
        ACTION_COOLDOWN: 5,
        ROUTES: {
            data: '{{ route('counter.data', ['organization_code' => request()->route('organization_code')]) }}',
            notify: '{{ route('counter.notify', ['organization_code' => request()->route('organization_code')]) }}',
            skip: '{{ route('counter.skip', ['organization_code' => request()->route('organization_code')]) }}',
            complete: '{{ route('counter.move-next', ['organization_code' => request()->route('organization_code')]) }}',
            callNext: '{{ route('counter.call-next', ['organization_code' => request()->route('organization_code')]) }}',
            recall: '{{ route('counter.recall', ['organization_code' => request()->route('organization_code')]) }}',
            transfer: '{{ route('counter.transfer', ['organization_code' => request()->route('organization_code')]) }}'
        },
        CSRF_TOKEN: '{{ csrf_token() }}'
    };
    
    // State
    const state = {
        currentQueue: null,
        onlineCounters: [],
        selectedTransferQueueId: null,
        isMinimized: false,
        fetchInFlight: false,
        fetchController: null,
        buttonCooldowns: new Map()
    };
    
    // ========================================================================
    // Utility Functions
    // ========================================================================
    
    function formatQueueNumber(queueNumber) {
        if (!queueNumber) return '---';
        const parts = String(queueNumber).split('-');
        return parts.length ? (parts[parts.length - 1] || String(queueNumber)) : String(queueNumber);
    }
    
    function updateDateTime() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', minute: '2-digit', hour12: true 
        });
        const dateStr = now.toLocaleDateString('en-US', { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
        });
        
        const timeEl = document.getElementById('headerTime');
        const dateEl = document.getElementById('headerDate');
        if (timeEl) timeEl.textContent = timeStr;
        if (dateEl) dateEl.textContent = dateStr;
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
            
            // Train station chime: C5, E5, G5
            playChime(now, 523, 0.35);
            playChime(now + 0.4, 659, 0.35);
            playChime(now + 0.8, 784, 0.4);
        } catch (err) {
            console.warn('Audio playback failed:', err);
        }
    }
    
    // ========================================================================
    // HTTP Functions
    // ========================================================================
    
    async function postJson(url, payload = null) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CONFIG.CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: payload ? JSON.stringify(payload) : null
        });
        
        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            throw new Error(data.message || `HTTP ${response.status}`);
        }
        
        return response.json();
    }
    
    async function fetchData() {
        if (state.fetchInFlight) return;
        state.fetchInFlight = true;
        
        try {
            if (state.fetchController) {
                state.fetchController.abort();
            }
            state.fetchController = new AbortController();
        } catch (e) {
            state.fetchController = null;
        }
        
        try {
            const response = await fetch(CONFIG.ROUTES.data, {
                cache: 'no-store',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CONFIG.CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: state.fetchController?.signal
            });
            
            if (!response.ok) {
                if (response.status === 403) {
                    const errorData = await response.json().catch(() => ({ message: 'Access forbidden' }));
                    console.error('403 Forbidden:', errorData.message);
                    
                    alert(`Access Denied: ${errorData.message || 'You do not have permission to access this organization.'}\n\nPlease contact your administrator or login with the correct account.`);
                    
                    // Stop polling on 403
                    return;
                } else if (response.status === 401) {
                    console.error('401 Unauthorized - Session expired');
                    if (confirm('Your session has expired. Click OK to login again.')) {
                        window.location.href = '/login';
                    }
                    return;
                } else if (response.status === 419) {
                    console.error('419 CSRF Token Mismatch - Reloading page');
                    window.location.reload();
                    return;
                }
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            if (data.success) {
                renderUI(data);
            }
        } catch (err) {
            if (err.name !== 'AbortError') {
                console.error('Data fetch failed:', err);
            }
        } finally {
            state.fetchInFlight = false;
        }
    }
    
    // ========================================================================
    // Button Cooldown Management
    // ========================================================================
    
    function isButtonCooling(btnEl) {
        if (!btnEl) return false;
        const until = state.buttonCooldowns.get(btnEl.id);
        return typeof until === 'number' && Date.now() < until;
    }
    
    function getCooldownRemaining(btnEl) {
        if (!btnEl) return 0;
        const until = state.buttonCooldowns.get(btnEl.id);
        if (typeof until !== 'number') return 0;
        return Math.max(0, Math.ceil((until - Date.now()) / 1000));
    }
    
    function startCooldown(btnEl, seconds) {
        if (!btnEl?.id) return;
        
        const until = Date.now() + (seconds * 1000);
        state.buttonCooldowns.set(btnEl.id, until);
        
        if (!btnEl.dataset.originalHtml) {
            btnEl.dataset.originalHtml = btnEl.innerHTML;
        }
        
        btnEl.disabled = true;
        
        const updateDisplay = () => {
            const remaining = getCooldownRemaining(btnEl);
            if (remaining <= 0) {
                state.buttonCooldowns.delete(btnEl.id);
                if (btnEl.dataset.originalHtml) {
                    btnEl.innerHTML = btnEl.dataset.originalHtml;
                }
                delete btnEl.dataset.originalHtml;
                return true;
            }
            
            const baseHtml = btnEl.dataset.originalHtml || btnEl.innerHTML;
            btnEl.innerHTML = `${baseHtml} <span class="opacity-75">(${remaining}s)</span>`;
            return false;
        };
        
        updateDisplay();
        const timer = setInterval(() => {
            if (updateDisplay()) {
                clearInterval(timer);
            }
        }, 250);
    }
    
    async function runWithCooldown(btnEl, actionFn, seconds = CONFIG.ACTION_COOLDOWN) {
        if (btnEl && isButtonCooling(btnEl)) return;
        if (btnEl) startCooldown(btnEl, seconds);
        
        try {
            await actionFn();
        } catch (err) {
            // On error, stop cooldown early
            if (btnEl?.id) {
                state.buttonCooldowns.delete(btnEl.id);
                if (btnEl.dataset.originalHtml) {
                    btnEl.innerHTML = btnEl.dataset.originalHtml;
                }
                delete btnEl.dataset.originalHtml;
            }
            alert(err?.message || 'Action failed. Please try again.');
            console.error(err);
        }
    }
    
    // ========================================================================
    // UI Rendering
    // ========================================================================
    
    function renderUI(data) {
        state.currentQueue = data.current_queue;
        state.onlineCounters = data.online_counters || [];
        
        // Update current queue display
        const queueText = formatQueueNumber(data.current_queue?.queue_number);
        const currentEl = document.getElementById('currentNumber');
        const dockEl = document.getElementById('dockCurrentNumber');
        if (currentEl) currentEl.textContent = queueText;
        if (dockEl) dockEl.textContent = queueText;
        
        // Update button states
        updateButtonStates(data);
        
        // Render queue lists
        renderWaitingList(data.waiting_queues || []);
        renderSkippedList(data.skipped || []);
    }
    
    function updateButtonStates(data) {
        const hasCurrentQueue = !!data.current_queue;
        const hasWaitingQueues = data.waiting_queues && data.waiting_queues.length > 0;
        const hasOnlineCounters = state.onlineCounters.length > 0;
        
        const buttons = {
            btnNotify: document.getElementById('btnNotify'),
            btnSkip: document.getElementById('btnSkip'),
            btnComplete: document.getElementById('btnComplete'),
            btnTransfer: document.getElementById('btnTransfer'),
            btnCallNext: document.getElementById('btnCallNext')
        };
        
        if (buttons.btnNotify) {
            buttons.btnNotify.disabled = !hasCurrentQueue || isButtonCooling(buttons.btnNotify);
        }
        if (buttons.btnSkip) {
            buttons.btnSkip.disabled = !hasCurrentQueue || isButtonCooling(buttons.btnSkip);
        }
        if (buttons.btnComplete) {
            buttons.btnComplete.disabled = !hasCurrentQueue || isButtonCooling(buttons.btnComplete);
        }
        if (buttons.btnTransfer) {
            buttons.btnTransfer.disabled = !hasCurrentQueue || !hasOnlineCounters || isButtonCooling(buttons.btnTransfer);
        }
        if (buttons.btnCallNext) {
            buttons.btnCallNext.disabled = !hasWaitingQueues || hasCurrentQueue || isButtonCooling(buttons.btnCallNext);
        }
    }
    
    function renderWaitingList(queues) {
        const container = document.getElementById('waitingList');
        if (!container) return;
        
        if (queues.length === 0) {
            container.innerHTML = '<div class="text-gray-400 text-center py-4 text-sm italic">No waiting queues</div>';
            return;
        }
        
        container.innerHTML = queues.map(q => `
            <div class="p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all group"
                 onclick="CounterPanel.openTransferModal(${q.id})"
                 title="Click to transfer this queue">
                <div class="flex justify-between items-center">
                    <div class="font-bold text-lg text-gray-800 group-hover:text-blue-600">${formatQueueNumber(q.queue_number)}</div>
                    <button onclick="event.stopPropagation(); CounterPanel.openTransferModal(${q.id})" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg font-semibold text-sm transition shadow-sm">
                        <i class="fas fa-exchange-alt mr-1"></i>Transfer
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    function renderSkippedList(queues) {
        const container = document.getElementById('skippedList');
        if (!container) return;
        
        if (queues.length === 0) {
            container.innerHTML = '<div class="text-gray-400 text-center py-4 text-sm italic">No skipped queues</div>';
            return;
        }
        
        container.innerHTML = queues.map(q => `
            <div class="p-3 border-2 border-orange-200 rounded-lg flex justify-between items-center bg-orange-50">
                <span class="font-bold text-lg text-orange-700">${formatQueueNumber(q.queue_number)}</span>
                <button onclick="CounterPanel.recallQueue(${q.id})" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-lg font-semibold text-sm transition shadow-sm">
                    <i class="fas fa-undo mr-1"></i>Recall
                </button>
            </div>
        `).join('');
    }
    
    // ========================================================================
    // Action Functions
    // ========================================================================
    
    async function notifyCustomer(btnEl) {
        await runWithCooldown(btnEl, async () => {
            await postJson(CONFIG.ROUTES.notify);
            playNotificationSound();
            fetchData();
        });
    }
    
    function skipCurrent() {
        openSkipModal();
    }
    
    async function moveToNext(btnEl) {
        await runWithCooldown(btnEl, async () => {
            await postJson(CONFIG.ROUTES.complete);
            fetchData();
        });
    }
    
    async function callNext(btnEl) {
        await runWithCooldown(btnEl, async () => {
            await postJson(CONFIG.ROUTES.callNext);
            playNotificationSound();
            fetchData();
        });
    }
    
    async function recallQueue(id) {
        try {
            const result = await postJson(CONFIG.ROUTES.recall, { queue_id: id });
            if (result.success) {
                playNotificationSound();
                fetchData();
            } else {
                throw new Error(result.message || 'Recall failed');
            }
        } catch (err) {
            alert(err.message || 'Failed to recall queue');
            console.error(err);
        }
    }
    
    async function confirmSkip(btnEl) {
        closeSkipModal();
        await runWithCooldown(btnEl, async () => {
            await postJson(CONFIG.ROUTES.skip);
            fetchData();
        });
    }
    
    async function confirmTransfer(toCounterId) {
        if (!state.selectedTransferQueueId) {
            alert('No queue selected');
            closeTransferModal();
            return;
        }
        
        closeTransferModal();
        
        try {
            const result = await postJson(CONFIG.ROUTES.transfer, {
                queue_id: state.selectedTransferQueueId,
                to_counter_id: toCounterId
            });
            
            if (result.success) {
                state.selectedTransferQueueId = null;
                fetchData();
            } else {
                throw new Error(result.message || 'Transfer failed');
            }
        } catch (err) {
            alert('Transfer failed: ' + err.message);
            console.error(err);
            state.selectedTransferQueueId = null;
            fetchData();
        }
    }
    
    // ========================================================================
    // Modal Functions
    // ========================================================================
    
    function openModal(modalId, contentId) {
        const modal = document.getElementById(modalId);
        const content = document.getElementById(contentId);
        if (!modal || !content) return;
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
    
    function closeModal(modalId, contentId) {
        const modal = document.getElementById(modalId);
        const content = document.getElementById(contentId);
        if (!modal || !content) return;
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        modal.classList.remove('opacity-100');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
    
    function openSkipModal() {
        openModal('skip-modal', 'skip-modal-content');
    }
    
    function closeSkipModal() {
        closeModal('skip-modal', 'skip-modal-content');
    }
    
    function openTransferModal(queueId) {
        const idToTransfer = typeof queueId === 'number' ? queueId : state.currentQueue?.id;
        if (!idToTransfer) {
            alert('No queue to transfer');
            return;
        }
        
        state.selectedTransferQueueId = idToTransfer;
        
        if (state.onlineCounters.length === 0) {
            alert('No available counters to transfer to');
            return;
        }
        
        // Populate counters list
        const countersList = document.getElementById('countersList');
        if (countersList) {
            countersList.innerHTML = state.onlineCounters.map(counter => `
                <button onclick="CounterPanel.confirmTransfer(${counter.id})" 
                        class="w-full p-4 border-2 border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 rounded-xl text-left transition-all shadow-sm hover:shadow-md group">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-bold text-gray-800 group-hover:text-indigo-600">Counter ${counter.counter_number}</div>
                            <div class="text-sm text-gray-600">${counter.display_name}</div>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-indigo-500"></i>
                    </div>
                </button>
            `).join('');
        }
        
        openModal('transfer-modal', 'transfer-modal-content');
    }
    
    function closeTransferModal() {
        closeModal('transfer-modal', 'transfer-modal-content');
    }
    
    // ========================================================================
    // Minimize/Restore Functions
    // ========================================================================
    
    function setMinimized(minimized) {
        state.isMinimized = !!minimized;
        
        const header = document.getElementById('panelHeader');
        const main = document.getElementById('panelMain');
        const dock = document.getElementById('panelDock');
        const btn = document.getElementById('btnToggleMinimize');
        
        if (state.isMinimized) {
            header?.classList.add('hidden');
            main?.classList.add('hidden');
            dock?.classList.remove('hidden');
            closeSkipModal();
            closeTransferModal();
        } else {
            header?.classList.remove('hidden');
            main?.classList.remove('hidden');
            dock?.classList.add('hidden');
        }
        
        if (btn) {
            btn.title = state.isMinimized ? 'Restore' : 'Minimize';
            btn.innerHTML = state.isMinimized 
                ? '<i class="fas fa-window-restore"></i>' 
                : '<i class="fas fa-window-minimize"></i>';
        }
        
        try {
            localStorage.setItem('counterPanelMinimized', state.isMinimized ? '1' : '0');
        } catch (e) {}
    }
    
    function toggleMinimize(force) {
        if (typeof force === 'boolean') {
            setMinimized(force);
        } else {
            setMinimized(!state.isMinimized);
        }
    }
    
    // ========================================================================
    // Initialization
    // ========================================================================
    
    function initialize() {
        // Check authentication on page load
        checkAuthentication();
        
        // Start date/time display
        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        // Restore minimized state
        try {
            const saved = localStorage.getItem('counterPanelMinimized');
            if (saved === '1') setMinimized(true);
        } catch (e) {}
        
        // Start data polling
        fetchData();
        setInterval(fetchData, CONFIG.POLL_INTERVAL);
    }
    
    async function checkAuthentication() {
        try {
            const response = await fetch(CONFIG.ROUTES.data, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CONFIG.CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.status === 401 || response.status === 403) {
                console.error('Authentication check failed:', response.status);
                if (confirm('Authentication required. Click OK to login.')) {
                    window.location.href = '/login';
                }
            }
        } catch (err) {
            console.error('Authentication check error:', err);
        }
    }
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
    
    // ========================================================================
    // Public API
    // ========================================================================
    
    return {
        notifyCustomer,
        skipCurrent,
        moveToNext,
        callNext,
        recallQueue,
        confirmSkip,
        confirmTransfer,
        openTransferModal,
        closeTransferModal,
        openSkipModal,
        closeSkipModal,
        toggleMinimize,
        fetchData
    };
    
})();
</script>
@endsection
