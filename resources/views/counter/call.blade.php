@extends('layouts.app')

@section('title', 'Service Station')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <!-- Side Menu Overlay -->
    <div id="sideMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300" onclick="toggleSideMenu()"></div>

    <!-- Side Menu -->
    <div id="sideMenu" class="fixed left-0 top-0 h-full w-80 bg-white shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out z-50">
        <div class="flex flex-col h-full">
            <!-- Side Menu Header -->
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-phone-alt text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold">Counter Panel</h2>
                            <p class="text-sm text-blue-100">Counter {{ $counter->counter_number }}</p>
                        </div>
                    </div>
                    <button onclick="toggleSideMenu()" class="text-white hover:text-blue-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Online Status -->
                <div class="flex items-center justify-between bg-white bg-opacity-10 rounded-lg p-3">
                    <span class="text-sm font-medium">Status</span>
                    <div class="flex items-center space-x-2">
                        <div id="sideMenuStatus" class="w-2 h-2 bg-green-400 rounded-full"></div>
                        <span id="sideMenuStatusText" class="text-sm">Online</span>
                    </div>
                </div>
            </div>

            <!-- Side Menu Navigation -->
            <div class="flex-1 overflow-y-auto">
                <!-- Quick Actions -->
                <div class="p-4 border-b border-gray-100">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Actions</h3>
                    <div class="space-y-2">
                        <button onclick="callNext(document.getElementById('btnCallNext')); toggleSideMenu();" 
                                class="w-full flex items-center space-x-3 px-4 py-3 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-colors">
                            <i class="fas fa-bell"></i>
                            <span class="font-medium">Call Next</span>
                        </button>
                        <button onclick="moveToNext(document.getElementById('btnComplete')); toggleSideMenu();" 
                                class="w-full flex items-center space-x-3 px-4 py-3 bg-green-50 hover:bg-green-100 text-green-700 rounded-lg transition-colors">
                            <i class="fas fa-check-circle"></i>
                            <span class="font-medium">Complete</span>
                        </button>
                        <button onclick="openSkipModal(); toggleSideMenu();" 
                                class="w-full flex items-center space-x-3 px-4 py-3 bg-orange-50 hover:bg-orange-100 text-orange-700 rounded-lg transition-colors">
                            <i class="fas fa-forward"></i>
                            <span class="font-medium">Skip</span>
                        </button>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="p-4 border-b border-gray-100">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Stats</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-2xl font-bold text-gray-900" id="sideMenuServed">0</div>
                            <div class="text-xs text-gray-600">Served Today</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-2xl font-bold text-gray-900" id="sideMenuWaiting">0</div>
                            <div class="text-xs text-gray-600">In Queue</div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Navigation</h3>
                    <nav class="space-y-1">
                        <a href="/{{ $organization->organization_code ?? 'admin' }}/dashboard" 
                           class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <i class="fas fa-chart-bar"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="/{{ $organization->organization_code ?? 'admin' }}/account/settings" 
                           class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                        <a href="/{{ $organization->organization_code ?? 'admin' }}/logout" 
                           class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Side Menu Footer -->
            <div class="p-4 border-t border-gray-100 bg-gray-50">
                <div class="text-xs text-gray-500 text-center">
                    Queue Management System
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Header -->
    <div id="panelHeader" class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <button onclick="toggleSideMenu()" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors" title="Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    @if($settings->logo_url)
                        <img src="{{ $settings->logo_url }}" alt="Organization Logo" class="h-10 w-auto">
                    @endif
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-phone-alt text-white text-sm"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ $organization->organization_name ?? 'QMS' }}</h1>
                            <p class="text-sm text-gray-600">Counter {{ $counter->counter_number }} - {{ $counter->display_name }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-lg font-semibold text-gray-700" id="headerTime"></div>
                        <div class="text-xs text-gray-500" id="headerDate"></div>
                    </div>
                    <button id="btnToggleMinimize" type="button" onclick="toggleMinimize()" 
                            class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors" title="Minimize">
                        <i class="fas fa-window-minimize"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Service Panel -->
    <div id="panelMain" class="flex-1 p-6">
        <div class="max-w-6xl mx-auto">
            <!-- Current Queue Display -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-6">
                <div class="text-center">
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-user-check mr-2"></i>
                            NOW SERVING
                        </span>
                    </div>
                    <div id="currentNumber" class="text-8xl font-extrabold text-gray-900 mb-6 tracking-wider">---</div>
                    
                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 max-w-4xl mx-auto">
                        <button type="button" id="btnCallNext" onclick="callNext(this)" 
                                class="flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl font-semibold transition-all duration-200 shadow-sm hover:shadow-md" disabled>
                            <i class="fas fa-bell mr-2"></i>
                            Call Next
                        </button>
                        
                        <button type="button" id="btnNotify" onclick="return notifyCustomer(this, event);" 
                                class="flex items-center justify-center px-4 py-3 bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl font-semibold transition-all duration-200 shadow-sm hover:shadow-md" disabled>
                            <i class="fas fa-bell mr-2"></i>
                            Notify
                        </button>
                        
                        <button type="button" id="btnComplete" onclick="moveToNext(this)" 
                                class="flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl font-semibold transition-all duration-200 shadow-sm hover:shadow-md" disabled>
                            <i class="fas fa-check-circle mr-2"></i>
                            Complete
                        </button>
                        
                        <button type="button" id="btnSkip" onclick="skipCurrent()" 
                                class="flex items-center justify-center px-4 py-3 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl font-semibold transition-all duration-200 shadow-sm hover:shadow-md" disabled>
                            <i class="fas fa-forward mr-2"></i>
                            Skip
                        </button>
                        
                        <button type="button" id="btnTransfer" onclick="openTransferModal()" 
                                class="flex items-center justify-center px-4 py-3 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl font-semibold transition-all duration-200 shadow-sm hover:shadow-md" disabled>
                            <i class="fas fa-exchange-alt mr-2"></i>
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
                        <span id="waitingCount" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                            0
                        </span>
                    </div>
                    <div id="waitingList" class="space-y-2 max-h-80 overflow-y-auto">
                        <!-- Waiting queue items will be populated here -->
                    </div>
                </div>

                <!-- Skipped Queue -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-clock text-orange-600 mr-2"></i>
                            Skipped Queue
                        </h3>
                        <span id="skippedCount" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            0
                        </span>
                    </div>
                    <div id="skippedList" class="space-y-2 max-h-80 overflow-y-auto">
                        <!-- Skipped queue items will be populated here -->
                    </div>
                </div>
            </div>

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
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 group relative">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-indigo-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-600">Efficiency</p>
                            <p class="text-xl font-bold text-gray-900" id="efficiency">100%</p>
                        </div>
                    </div>
                    <!-- Keyboard shortcut hint -->
                    <div class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="bg-gray-800 text-white text-xs px-2 py-1 rounded shadow-lg">
                            <div class="font-semibold mb-1">Keyboard Shortcuts:</div>
                            <div>N = Call Next</div>
                            <div>C = Complete</div>
                            <div>S = Skip</div>
                            <div>T = Transfer</div>
                            <div>M = Menu</div>
                        </div>
                    </div>
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
    
    const headerTime = document.getElementById('headerTime');
    const headerDate = document.getElementById('headerDate');
    if (headerTime) headerTime.textContent = timeStr;
    if (headerDate) headerDate.textContent = dateStr;
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
// SIDE MENU FUNCTIONS
// ============================================================

function toggleSideMenu() {
    const sideMenu = document.getElementById('sideMenu');
    const overlay = document.getElementById('sideMenuOverlay');
    
    if (sideMenu.classList.contains('-translate-x-full')) {
        // Open menu
        sideMenu.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        setTimeout(() => {
            overlay.classList.add('opacity-100');
        }, 10);
    } else {
        // Close menu
        sideMenu.classList.add('-translate-x-full');
        overlay.classList.remove('opacity-100');
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 300);
    }
}

function updateSideMenuStats(data) {
    // Update served today count
    const servedToday = data.served_today || 0;
    const sideMenuServed = document.getElementById('sideMenuServed');
    if (sideMenuServed) sideMenuServed.textContent = servedToday;
    
    // Update waiting count
    const waitingCount = data.waiting_queues ? data.waiting_queues.length : 0;
    const sideMenuWaiting = document.getElementById('sideMenuWaiting');
    if (sideMenuWaiting) sideMenuWaiting.textContent = waitingCount;
    
    // Update online status
    const sideMenuStatus = document.getElementById('sideMenuStatus');
    const sideMenuStatusText = document.getElementById('sideMenuStatusText');
    
    if (sideMenuStatus && sideMenuStatusText) {
        // Check if counter is online (you may need to adjust this based on your data structure)
        const isOnline = data.online_counters && data.online_counters.some(counter => counter.id == COUNTER_ID);
        
        if (isOnline) {
            sideMenuStatus.className = 'w-2 h-2 bg-green-400 rounded-full';
            sideMenuStatusText.textContent = 'Online';
        } else {
            sideMenuStatus.className = 'w-2 h-2 bg-red-400 rounded-full';
            sideMenuStatusText.textContent = 'Offline';
        }
    }
}

// ============================================================
// DATA RENDERING
// ============================================================

function renderLists(data) {
    // Update side menu stats
    updateSideMenuStats(data);
    
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

    // Update waiting count in side menu
    const waitingCount = document.getElementById('waitingCount');
    if (waitingCount) {
        waitingCount.textContent = hasWaitingQueues ? data.waiting_queues.length : '0';
    }

    // Update served today in side menu and main panel
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
                row.className = 'p-3 border rounded flex justify-between items-center';
                row.innerHTML = `<span class="font-semibold">${formatDisplayQueue(w.queue_number)}</span>`;
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
                row.className = 'p-3 border rounded flex justify-between items-center bg-orange-50';
                row.innerHTML = `
                    <span class="font-semibold text-orange-700">${formatDisplayQueue(s.queue_number)}</span>
                    <button type="button" class="bg-blue-600 text-white px-3 py-1 rounded text-sm" onclick="recallQueue(${s.id}, event)">Recall</button>
                `;
                skippedList.appendChild(row);
            });
        }
    }
}

// ============================================================
// FETCH & POLLING
// ============================================================

let counterFetchInFlight = false;
let counterFetchController = null;

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
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        signal: counterFetchController ? counterFetchController.signal : undefined,
    })
        .then(response => {
            // Handle 403 by continuing with cached data
            if (response.status === 403 || response.status === 401) {
                console.warn(`Counter endpoint ${response.status} - using cached data`);
                 if (typeof handleFallbackData === 'function') {
                     handleFallbackData();
                 }
                counterFetchInFlight = false;
                return;
            }
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            return response.json().then(data => {
                if (data.success) {
                    renderLists(data);
                    lastErrorTime = 0;
                } else {
                    console.warn('Counter data response not successful:', data);
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

// ============================================================
// KEYBOARD SHORTCUTS
// ============================================================

document.addEventListener('keydown', function(event) {
    // Only handle shortcuts when not typing in input fields
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
        return;
    }
    
    // Check if side menu is open - close it with Escape
    const sideMenu = document.getElementById('sideMenu');
    if (!sideMenu.classList.contains('-translate-x-full') && event.key === 'Escape') {
        toggleSideMenu();
        return;
    }
    
    // Counter action shortcuts (only when side menu is closed)
    if (sideMenu.classList.contains('-translate-x-full')) {
        switch(event.key.toLowerCase()) {
            case 'n': // Call Next
                event.preventDefault();
                const btnCallNext = document.getElementById('btnCallNext');
                if (btnCallNext && !btnCallNext.disabled) {
                    callNext(btnCallNext);
                }
                break;
            case 'c': // Complete
                event.preventDefault();
                const btnComplete = document.getElementById('btnComplete');
                if (btnComplete && !btnComplete.disabled) {
                    moveToNext(btnComplete);
                }
                break;
            case 's': // Skip
                event.preventDefault();
                const btnSkip = document.getElementById('btnSkip');
                if (btnSkip && !btnSkip.disabled) {
                    openSkipModal();
                }
                break;
            case 't': // Transfer
                event.preventDefault();
                const btnTransfer = document.getElementById('btnTransfer');
                if (btnTransfer && !btnTransfer.disabled) {
                    openTransferModal();
                }
                break;
            case 'm': // Menu
                event.preventDefault();
                toggleSideMenu();
                break;
        }
    }
});

// ============================================================
// INITIALIZATION
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    updateHeaderTime();
    setInterval(updateHeaderTime, 1000);

    // Rapid polling for real-time updates
    setInterval(fetchData, FETCH_INTERVAL);
    fetchData(); // Initial fetch

    // Restore minimized state
    try {
        const saved = localStorage.getItem('counterPanelMinimized');
        if (saved === '1') setMinimized(true);
    } catch (e) {}

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
    
    // Add keyboard shortcut help tooltip
    const header = document.getElementById('panelHeader');
    if (header) {
        header.title = 'Keyboard shortcuts: N (Call Next), C (Complete), S (Skip), T (Transfer), M (Menu), ESC (Close Menu)';
    }
});
</script>
@endpush
@endsection