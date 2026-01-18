@extends('layouts.counter')

@section('title', 'Service Station - Counter {{ $counter->counter_number }}')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <!-- Main Service Panel -->
    <div id="panelMain" class="flex-1 p-3 sm:p-4 md:p-6">
        <div class="max-w-6xl mx-auto">
            <!-- Current Queue Display -->
            <div class="glass-card p-4 sm:p-6 md:p-8 mb-3 sm:mb-4 md:mb-6">
                <div class="text-center">
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-user-check mr-2"></i>
                            NOW SERVING
                        </span>
                    </div>
                    <div id="currentNumber" class="queue-number font-extrabold text-gray-900 mb-6 tracking-wider">---</div>
                    
                    <!-- Action Buttons - Responsive grid: 2 cols on mobile, 5 on desktop -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 sm:gap-3 max-w-4xl mx-auto">
                        <button type="button" id="btnCallNext" 
                                class="counter-btn flex items-center justify-center px-3 sm:px-4 py-2.5 sm:py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg sm:rounded-xl font-semibold shadow-sm text-sm sm:text-base" disabled>
                            <i class="fas fa-phone mr-1.5 sm:mr-2"></i>
                            <span class="hidden xs:inline">Call </span>Next
                        </button>
                        
                        <button type="button" id="btnNotify" 
                                class="counter-btn flex items-center justify-center px-3 sm:px-4 py-2.5 sm:py-3 bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg sm:rounded-xl font-semibold shadow-sm text-sm sm:text-base" disabled>
                            <i class="fas fa-bell mr-1.5 sm:mr-2"></i>
                            Notify
                        </button>
                        
                        <button type="button" id="btnComplete" 
                                class="counter-btn flex items-center justify-center px-3 sm:px-4 py-2.5 sm:py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg sm:rounded-xl font-semibold shadow-sm text-sm sm:text-base" disabled>
                            <i class="fas fa-check-circle mr-1.5 sm:mr-2"></i>
                            Done
                        </button>
                        
                        <button type="button" id="btnSkip" 
                                class="counter-btn flex items-center justify-center px-3 sm:px-4 py-2.5 sm:py-3 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg sm:rounded-xl font-semibold shadow-sm text-sm sm:text-base" disabled>
                            <i class="fas fa-forward mr-1.5 sm:mr-2"></i>
                            Skip
                        </button>
                        
                        <button type="button" id="btnTransfer" 
                                class="counter-btn col-span-2 sm:col-span-1 flex items-center justify-center px-3 sm:px-4 py-2.5 sm:py-3 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg sm:rounded-xl font-semibold shadow-sm text-sm sm:text-base" disabled>
                            <i class="fas fa-exchange-alt mr-1.5 sm:mr-2"></i>
                            Transfer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Queue Lists -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Waiting Queue -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-users text-amber-600 mr-2"></i>
                            Waiting Queue
                        </h3>
                        <span id="waitingCount" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">0</span>
                    </div>
                    <div id="waitingList" class="space-y-2 max-h-80 overflow-y-auto"></div>
                </div>

                <!-- Skipped Queue -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-clock text-orange-600 mr-2"></i>
                            Skipped Queue
                        </h3>
                        <span id="skippedCount" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">0</span>
                    </div>
                    <div id="skippedList" class="space-y-2 max-h-80 overflow-y-auto"></div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
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

<!-- Transfer Queue Modal -->
<div id="transfer-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div id="transfer-modal-content" class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4">
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-white"><i class="fas fa-exchange-alt mr-2"></i>Transfer Queue</h3>
                <button type="button" id="closeTransferModalBtn" class="text-white hover:text-gray-200 text-2xl">&times;</button>
            </div>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-4">Select a counter to transfer the current queue:</p>
            <div id="countersList" class="space-y-2 max-h-64 overflow-y-auto"></div>
        </div>
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3">
            <button type="button" id="cancelTransferBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold">Cancel</button>
        </div>
    </div>
</div>

<!-- Skip Confirmation Modal -->
<div id="skip-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div id="skip-modal-content" class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4">
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-white"><i class="fas fa-forward mr-2"></i>Skip Current Queue?</h3>
                <button type="button" id="closeSkipModalBtn" class="text-white hover:text-gray-200 text-2xl">&times;</button>
            </div>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-4">Are you sure you want to skip the current customer?</p>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                <p class="text-sm text-orange-800"><i class="fas fa-info-circle mr-2"></i>The skipped queue can be recalled later.</p>
            </div>
        </div>
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3">
            <button type="button" id="cancelSkipBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold">Cancel</button>
            <button type="button" id="confirmSkipBtn" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold">Skip Queue</button>
        </div>
    </div>
</div>

@push('styles')
<style nonce="{{ session('csp_nonce', '') }}">
html, body { overflow-x: hidden; }
.queue-item { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: white; border-radius: 0.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; margin-bottom: 0.5rem; }

/* Mobile-first responsive design */
@media (max-width: 640px) {
    #panelMain { padding: 0.75rem !important; }
    .glass-card { padding: 1rem !important; border-radius: 1rem !important; }
    .queue-number { font-size: 3rem !important; }
    
    /* Stack action buttons in 2 columns on mobile */
    .grid.grid-cols-2.md\\:grid-cols-5 { 
        grid-template-columns: repeat(2, 1fr) !important; 
        gap: 0.5rem !important;
    }
    
    /* Make buttons more touch-friendly */
    .counter-btn { 
        padding: 0.75rem 0.5rem !important; 
        font-size: 0.75rem !important;
        min-height: 48px;
    }
    .counter-btn i { margin-right: 0.25rem !important; font-size: 0.875rem !important; }
    
    /* Queue lists side by side on mobile landscape, stacked on portrait */
    .grid.grid-cols-1.md\\:grid-cols-2 { gap: 0.75rem !important; }
    
    /* Stats grid - 2x2 on mobile */
    .grid.grid-cols-2.md\\:grid-cols-4 { gap: 0.5rem !important; }
    .grid.grid-cols-2.md\\:grid-cols-4 > div { padding: 0.75rem !important; }
    .grid.grid-cols-2.md\\:grid-cols-4 .text-xl { font-size: 1rem !important; }
    .grid.grid-cols-2.md\\:grid-cols-4 .text-sm { font-size: 0.65rem !important; }
    .grid.grid-cols-2.md\\:grid-cols-4 .w-10 { width: 2rem !important; height: 2rem !important; }
    
    /* Queue list items more compact */
    .queue-item { padding: 0.5rem 0.75rem !important; }
    .queue-item .text-lg { font-size: 1rem !important; }
    
    /* Modal improvements for mobile */
    #transfer-modal .max-w-md,
    #skip-modal .max-w-md { 
        max-width: calc(100vw - 2rem) !important;
        margin: 1rem !important;
    }
}

/* Small mobile (iPhone SE, etc) */
@media (max-width: 375px) {
    .queue-number { font-size: 2.5rem !important; }
    .counter-btn { font-size: 0.7rem !important; padding: 0.5rem !important; }
    .grid.grid-cols-2.md\\:grid-cols-5 { gap: 0.375rem !important; }
}

/* Landscape mode on mobile */
@media (max-height: 500px) and (orientation: landscape) {
    .glass-card { padding: 0.75rem !important; margin-bottom: 0.5rem !important; }
    .queue-number { font-size: 2rem !important; margin-bottom: 0.5rem !important; }
    .grid.grid-cols-1.md\\:grid-cols-2 { grid-template-columns: repeat(2, 1fr) !important; }
    .max-h-80 { max-height: 120px !important; }
    .grid.grid-cols-2.md\\:grid-cols-4 { display: none; } /* Hide stats in landscape */
}

/* Touch-friendly targets */
@media (hover: none) and (pointer: coarse) {
    .counter-btn { min-height: 48px; }
    button { min-height: 44px; }
    .queue-item button { min-height: 36px; padding: 0.5rem 0.75rem; }
}
</style>
@endpush

@push('scripts')
<script nonce="{{ session('csp_nonce', '') }}">
(function() {
    'use strict';
    
    // ============================================================
    // CONFIGURATION
    // ============================================================
    const CONFIG = {
        counterId: {{ $counter->id }},
        counterNum: {{ $counter->counter_number }},
        orgCode: '{{ request()->route('organization_code') }}',
        csrfToken: '{{ csrf_token() }}',
        pollInterval: 2000,
        cooldownSeconds: 3
    };
    
    // ============================================================
    // STATE
    // ============================================================
    let state = {
        currentQueue: null,
        waitingQueues: [],
        skippedQueues: [],
        onlineCounters: [],
        servedToday: 0,
        isOnline: {{ $counter->is_online ? 'true' : 'false' }},
        isFetching: false,
        buttonCooldowns: new Map()
    };
    
    // ============================================================
    // HELPERS
    // ============================================================
    function getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || CONFIG.csrfToken;
    }
    
    function formatQueueNumber(queueNumber) {
        if (!queueNumber) return '---';
        const parts = String(queueNumber).split('-');
        return parts[parts.length - 1] || queueNumber;
    }
    
    function playSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;
            [523, 659, 784].forEach((freq, i) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = freq;
                osc.type = 'sine';
                gain.gain.setValueAtTime(0.3, now + i * 0.15);
                gain.gain.exponentialRampToValueAtTime(0.01, now + i * 0.15 + 0.3);
                osc.start(now + i * 0.15);
                osc.stop(now + i * 0.15 + 0.3);
            });
        } catch (e) { console.log('Sound error:', e); }
    }
    
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 text-white font-medium ${type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600'}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
    
    function setCooldown(btnId, seconds = CONFIG.cooldownSeconds) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        
        state.buttonCooldowns.set(btnId, Date.now() + seconds * 1000);
        btn.disabled = true;
        
        const originalHtml = btn.innerHTML;
        const interval = setInterval(() => {
            const remaining = Math.ceil((state.buttonCooldowns.get(btnId) - Date.now()) / 1000);
            if (remaining <= 0) {
                clearInterval(interval);
                state.buttonCooldowns.delete(btnId);
                btn.innerHTML = originalHtml;
                updateButtonStates();
            } else {
                btn.innerHTML = originalHtml + ` (${remaining}s)`;
            }
        }, 250);
    }
    
    // ============================================================
    // API REQUESTS
    // ============================================================
    function apiRequest(action, params = {}, method = 'POST') {
        const url = `/${CONFIG.orgCode}/counter/${action}`;
        const options = {
            method: method,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCSRFToken()
            },
            credentials: 'same-origin'
        };
        
        if (method === 'POST') {
            const formData = new FormData();
            formData.append('_token', getCSRFToken());
            formData.append('counter_id', CONFIG.counterId);
            Object.keys(params).forEach(key => {
                if (params[key] !== undefined && params[key] !== null) {
                    formData.append(key, params[key]);
                }
            });
            options.body = formData;
        }
        
        console.log(`[API] ${method} ${action}`, params);
        
        return fetch(url, options)
            .then(response => {
                if (!response.ok) {
                    console.error(`[API] ${action} failed: HTTP ${response.status}`);
                    if (response.status === 401 || response.status === 403) {
                        showToast('Session expired. Please refresh.', 'error');
                    }
                    return { success: false, message: `HTTP ${response.status}` };
                }
                return response.json();
            })
            .catch(err => {
                console.error(`[API] ${action} error:`, err);
                return { success: false, message: err.message };
            });
    }
    
    // ============================================================
    // DATA FETCHING
    // ============================================================
    function fetchData() {
        if (state.isFetching) return;
        state.isFetching = true;
        
        const url = `/${CONFIG.orgCode}/counter/data?counter_id=${CONFIG.counterId}`;
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                state.currentQueue = data.current_queue;
                state.waitingQueues = data.waiting_queues || [];
                state.skippedQueues = data.skipped || [];
                state.onlineCounters = data.online_counters || [];
                state.servedToday = data.served_today || 0;
                state.isOnline = data.online_status;
                
                renderUI();
                updateOnlineStatus();
            }
        })
        .catch(err => console.error('[FETCH] Error:', err))
        .finally(() => { state.isFetching = false; });
    }
    
    // ============================================================
    // UI RENDERING
    // ============================================================
    function renderUI() {
        // Current queue number
        document.getElementById('currentNumber').textContent = formatQueueNumber(state.currentQueue?.queue_number);
        
        // Stats
        document.getElementById('servedToday').textContent = state.servedToday;
        document.getElementById('waitingCount').textContent = state.waitingQueues.length;
        document.getElementById('skippedCount').textContent = state.skippedQueues.length;
        
        // Waiting list
        const waitingList = document.getElementById('waitingList');
        waitingList.innerHTML = state.waitingQueues.map(q => `
            <div class="queue-item">
                <span class="text-lg font-bold text-gray-800">${formatQueueNumber(q.queue_number)}</span>
                <span class="text-sm text-gray-500">${new Date(q.created_at).toLocaleTimeString()}</span>
            </div>
        `).join('') || '<p class="text-gray-500 text-center py-4">No waiting queues</p>';
        
        // Skipped list
        const skippedList = document.getElementById('skippedList');
        skippedList.innerHTML = state.skippedQueues.map(q => `
            <div class="queue-item bg-orange-50">
                <span class="text-lg font-bold text-orange-700">${formatQueueNumber(q.queue_number)}</span>
                <button onclick="window.counterActions.recall(${q.id})" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Recall</button>
            </div>
        `).join('') || '<p class="text-gray-500 text-center py-4">No skipped queues</p>';
        
        updateButtonStates();
    }
    
    function updateButtonStates() {
        const hasCurrentQueue = !!state.currentQueue;
        const hasWaitingQueues = state.waitingQueues.length > 0;
        
        const buttons = {
            btnCallNext: hasWaitingQueues && !hasCurrentQueue,
            btnNotify: hasCurrentQueue,
            btnComplete: hasCurrentQueue,
            btnSkip: hasCurrentQueue,
            btnTransfer: hasCurrentQueue && state.onlineCounters.length > 0
        };
        
        Object.keys(buttons).forEach(id => {
            const btn = document.getElementById(id);
            if (btn && !state.buttonCooldowns.has(id)) {
                btn.disabled = !buttons[id];
            }
        });
    }
    
    function updateOnlineStatus() {
        const statusIcon = document.getElementById('statusIcon');
        const statusLabel = document.getElementById('statusLabel');
        const toggleBtn = document.getElementById('btnToggleOnline');
        
        if (state.isOnline) {
            if (statusIcon) {
                statusIcon.className = 'fas fa-circle text-green-500';
                statusIcon.style.animation = 'pulse 2s infinite';
            }
            if (statusLabel) {
                statusLabel.textContent = 'Online';
                statusLabel.className = 'text-xs font-semibold text-green-600 ml-1';
            }
            if (toggleBtn) toggleBtn.title = 'Online - Click to go offline';
        } else {
            if (statusIcon) {
                statusIcon.className = 'fas fa-circle text-red-500';
                statusIcon.style.animation = 'none';
            }
            if (statusLabel) {
                statusLabel.textContent = 'Offline';
                statusLabel.className = 'text-xs font-semibold text-red-600 ml-1';
            }
            if (toggleBtn) toggleBtn.title = 'Offline - Click to go online';
        }
    }
    
    // ============================================================
    // COUNTER ACTIONS
    // ============================================================
    const actions = {
        callNext: function() {
            setCooldown('btnCallNext');
            apiRequest('call-next').then(data => {
                if (data.success) {
                    playSound();
                    showToast('Queue called: ' + formatQueueNumber(data.queue?.queue_number));
                    fetchData();
                } else {
                    showToast(data.message || 'Failed to call next', 'error');
                }
            });
        },
        
        notify: function() {
            setCooldown('btnNotify');
            apiRequest('notify').then(data => {
                if (data.success) {
                    playSound();
                    showToast('Customer notified for queue: ' + (data.queue_number || formatQueueNumber(state.currentQueue?.queue_number)));
                    fetchData();
                } else {
                    showToast(data.message || 'Failed to notify', 'error');
                }
            });
        },
        
        complete: function() {
            setCooldown('btnComplete');
            apiRequest('move-next').then(data => {
                if (data.success) {
                    if (data.queue) playSound();
                    showToast(data.message || 'Queue completed');
                    fetchData();
                } else {
                    showToast(data.message || 'Failed to complete', 'error');
                }
            });
        },
        
        skip: function() {
            document.getElementById('skip-modal').classList.remove('hidden');
        },
        
        confirmSkip: function() {
            document.getElementById('skip-modal').classList.add('hidden');
            setCooldown('btnSkip');
            apiRequest('skip').then(data => {
                if (data.success) {
                    showToast('Queue skipped');
                    fetchData();
                } else {
                    showToast(data.message || 'Failed to skip', 'error');
                }
            });
        },
        
        transfer: function() {
            if (state.onlineCounters.length === 0) {
                showToast('No counters available for transfer', 'error');
                return;
            }
            
            const countersList = document.getElementById('countersList');
            countersList.innerHTML = state.onlineCounters.map(c => `
                <button onclick="window.counterActions.confirmTransfer(${c.id})" class="w-full text-left p-3 bg-gray-100 hover:bg-blue-100 rounded-lg">
                    <span class="font-semibold">Counter ${c.counter_number}</span>
                    <span class="text-sm text-gray-600 ml-2">${c.display_name || ''}</span>
                </button>
            `).join('');
            
            document.getElementById('transfer-modal').classList.remove('hidden');
        },
        
        confirmTransfer: function(toCounterId) {
            document.getElementById('transfer-modal').classList.add('hidden');
            setCooldown('btnTransfer');
            
            apiRequest('transfer', {
                queue_id: state.currentQueue?.id,
                to_counter_id: toCounterId
            }).then(data => {
                if (data.success) {
                    showToast('Queue transferred');
                    fetchData();
                } else {
                    showToast(data.message || 'Failed to transfer', 'error');
                }
            });
        },
        
        recall: function(queueId) {
            apiRequest('recall', { queue_id: queueId }).then(data => {
                if (data.success) {
                    playSound();
                    showToast('Queue recalled');
                    fetchData();
                } else {
                    showToast(data.message || 'Failed to recall', 'error');
                }
            });
        },
        
        toggleOnline: function() {
            fetch(`/${CONFIG.orgCode}/counter/toggle-online`, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    state.isOnline = data.is_online;
                    updateOnlineStatus();
                    showToast(data.is_online ? 'You are now online' : 'You are now offline');
                }
            });
        }
    };
    
    // Expose actions globally for onclick handlers
    window.counterActions = actions;
    
    // ============================================================
    // EVENT LISTENERS
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[INIT] Counter panel starting...');
        
        // Bind action buttons
        document.getElementById('btnCallNext')?.addEventListener('click', actions.callNext);
        document.getElementById('btnNotify')?.addEventListener('click', actions.notify);
        document.getElementById('btnComplete')?.addEventListener('click', actions.complete);
        document.getElementById('btnSkip')?.addEventListener('click', actions.skip);
        document.getElementById('btnTransfer')?.addEventListener('click', actions.transfer);
        
        // Bind modal buttons
        document.getElementById('confirmSkipBtn')?.addEventListener('click', actions.confirmSkip);
        document.getElementById('cancelSkipBtn')?.addEventListener('click', () => document.getElementById('skip-modal').classList.add('hidden'));
        document.getElementById('closeSkipModalBtn')?.addEventListener('click', () => document.getElementById('skip-modal').classList.add('hidden'));
        document.getElementById('cancelTransferBtn')?.addEventListener('click', () => document.getElementById('transfer-modal').classList.add('hidden'));
        document.getElementById('closeTransferModalBtn')?.addEventListener('click', () => document.getElementById('transfer-modal').classList.add('hidden'));
        
        // Bind header toggle button
        document.getElementById('btnToggleOnline')?.addEventListener('click', actions.toggleOnline);
        
        // Initial fetch
        fetchData();
        
        // Polling
        setInterval(fetchData, CONFIG.pollInterval);
        
        // Visibility change handler
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) fetchData();
        });
        
        console.log('[INIT] Counter panel ready');
    });
})();
</script>
@endpush
@endsection