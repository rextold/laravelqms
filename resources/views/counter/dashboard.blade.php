@extends('layouts.app')

@section('title', 'Counter Dashboard')
@section('page-title', 'Counter Dashboard - Analytics & Performance')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 md:p-8">
    
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-6">
            <!-- Left: Organization Info -->
            <div class="flex items-center gap-4">
                @if($settings->company_logo)
                    <div class="w-14 h-14 bg-white rounded-xl shadow-md p-2 flex items-center justify-center">
                        <img src="{{ asset('storage/' . $settings->company_logo) }}" alt="Organization Logo" class="h-full w-auto object-contain">
                    </div>
                @else
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl shadow-md flex items-center justify-center">
                        <i class="fas fa-building text-white text-2xl"></i>
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900" data-org-name>{{ $organization->organization_name ?? 'QMS' }}</h1>
                    <p class="text-sm md:text-base text-gray-600 flex items-center gap-2">
                        <i class="fas fa-store-alt"></i>
                        Counter #{{ $counter->counter_number }} - {{ $counter->display_name }}
                    </p>
                </div>
            </div>
            
            <!-- Right: Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('counter.panel', ['organization_code' => request()->route('organization_code')]) }}" 
                   class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-semibold flex items-center justify-center gap-2 transition-all shadow-lg transform hover:scale-105 active:scale-95">
                    <i class="fas fa-phone-alt"></i>
                    <span>Service Panel</span>
                </a>
                
                <button id="onlineBtn" type="button" onclick="toggleOnlineStatus()"
                        class="px-6 py-3 rounded-lg font-semibold flex items-center justify-center gap-2 transition-all shadow-lg transform hover:scale-105 active:scale-95 min-w-[180px]"
                        style="background: {{ $counter->is_online ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #22c55e, #16a34a)' }}; color: white;">
                    <i class="fas {{ $counter->is_online ? 'fa-power-off' : 'fa-plug' }}"></i>
                    <span id="onlineText">{{ $counter->is_online ? 'Go Offline' : 'Go Online' }}</span>
                    <span id="onlineSpinner" class="hidden"><i class="fas fa-spinner fa-spin ml-1"></i></span>
                </button>
            </div>
        </div>
        
        <!-- Status Bar -->
        <div class="flex items-center gap-3 p-4 bg-white rounded-lg shadow-sm border-l-4" 
             style="border-left-color: {{ $counter->is_online ? '#22c55e' : '#ef4444' }};">
            <div class="w-3 h-3 rounded-full {{ $counter->is_online ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></div>
            <span class="text-sm font-semibold {{ $counter->is_online ? 'text-green-700' : 'text-red-700' }}">
                {{ $counter->is_online ? '✓ Online & Ready' : '✗ Offline' }}
            </span>
            <span class="text-xs text-gray-500 ml-auto" id="lastUpdate">Updated now</span>
        </div>
    </div>

    <!-- Key Metrics Section -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <x-counter.metric-card 
        title="In Queue"
        value="{{ $stats['waiting'] ?? 0 }}"
        icon="users"
        color="amber"
        tooltip="Waiting customers"
    />
    
    <x-counter.metric-card 
        title="Completed"
        value="{{ $stats['completed_today'] ?? 0 }}"
        icon="check-double"
        color="green"
        tooltip="Today"
    />
    
    <x-counter.metric-card 
        title="Now Serving"
        value="{{ $currentNumber ?? '---' }}"
        icon="user-check"
        color="blue"
        tooltip="Active queue"
    />
    
    <x-counter.metric-card 
        title="Total Served"
        value="{{ $stats['total_served'] ?? 0 }}"
        icon="chart-line"
        color="purple"
        tooltip="All time"
    />

        <!-- Completed Today -->
        <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-green-500 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Completed</p>
                    <p class="text-4xl font-bold text-gray-900" id="completedCount">{{ $stats['completed_today'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-2">Today</p>
                </div>
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-double text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Current Queue -->
        <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-500 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Now Serving</p>
                    <p class="text-4xl font-bold text-gray-900" id="currentQueue">
                        @if($stats['current_queue'])
                            @php 
                                $queueParts = explode('-', $stats['current_queue']->queue_number);
                                $currentNumber = $queueParts[array_key_last($queueParts)];
                            @endphp
                            {{ $currentNumber }}
                        @else
                            ---
                        @endif
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Active queue</p>
                </div>
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-check text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Served -->
        <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-purple-500 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Total Served</p>
                    <p class="text-4xl font-bold text-gray-900" id="totalServed">{{ $stats['total_served'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-2">All time</p>
                </div>
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Hourly Chart -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-chart-bar text-blue-600 mr-2"></i>Hourly Completions
                </h2>
                <span class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-semibold">Today</span>
            </div>
            <div class="h-80 relative">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>

        <!-- Weekly Chart -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-chart-line text-green-600 mr-2"></i>Weekly Trend
                </h2>
                <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold">7 Days</span>
            </div>
            <div class="h-80 relative">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>

        <!-- Wait Time Chart -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-hourglass-end text-orange-600 mr-2"></i>Average Wait Time
                </h2>
                <span class="text-xs bg-orange-100 text-orange-700 px-3 py-1 rounded-full font-semibold">Minutes</span>
            </div>
            <div class="h-80 relative">
                <canvas id="waitTimeChart"></canvas>
            </div>
        </div>

        <!-- Distribution Chart -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-chart-pie text-purple-600 mr-2"></i>Peak Hours Distribution
                </h2>
                <span class="text-xs bg-purple-100 text-purple-700 px-3 py-1 rounded-full font-semibold">30 Days</span>
            </div>
            <div class="h-80 relative">
                <canvas id="peakHoursChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Footer Info -->
    <div class="mt-8 p-4 bg-white rounded-xl shadow-sm border-l-4 border-blue-500">
        <div class="flex items-center justify-between text-sm text-gray-600">
            <span><i class="fas fa-info-circle mr-2 text-blue-600"></i>Dashboard refreshes every 3 seconds</span>
            <span id="updateCounter" class="font-mono text-xs text-gray-400">Refreshing...</span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

@push('scripts')
<script src="{{ asset('js/counter/dashboard.js') }}"></script>
<script>
const dashboard = new DashboardManager({
    orgCode: '{{ request()->route('organization_code') }}',
    counterId: {{ $counter->id }},
    charts: ['hourly', 'weekly', 'waitTime', 'peakHours'],
    refreshInterval: 3000
});

dashboard.initialize();

const REFRESH_INTERVAL = 3000;
let refreshTimer = null;



function updateTimeAgo() {
    const el = document.getElementById('lastUpdate');
    if (el) el.textContent = 'Updated just now';
    
    const counter = document.getElementById('updateCounter');
    if (counter) counter.textContent = `Refresh #${++refreshCount}`;
}

// ===== DATA FETCHING =====
function fetchDashboardData() {
    fetch(`/${ORG_CODE}/counter/data`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (response.status === 403 || response.status === 401) {
            console.warn(`Dashboard returned ${response.status} - using defaults`);
            return null;
        }
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (!data || !data.success) return;
        updateDashboardDisplay(data);
        updateTimeAgo();
    })
    .catch(error => {
        console.error('Dashboard fetch error:', error);
        updateTimeAgo();
    });
}

function updateDashboardDisplay(data) {
    // Update stats
    if (data.stats) {
        const waiting = document.getElementById('waitingCount');
        const completed = document.getElementById('completedCount');
        const current = document.getElementById('currentQueue');
        const total = document.getElementById('totalServed');
        
        if (waiting) waiting.textContent = data.stats.waiting ?? 0;
        if (completed) completed.textContent = data.stats.completed_today ?? 0;
        if (current) current.textContent = data.current_queue ? formatDisplayQueue(data.current_queue.queue_number) : '---';
        if (total) total.textContent = data.stats.total_served ?? 0;
    }
    
    // Update online status
    if (typeof data.online_status === 'boolean') {
        updateOnlineButton(data.online_status);
    }
}

// ===== ONLINE STATUS =====
function updateOnlineButton(isOnline) {
    const btn = document.getElementById('onlineBtn');
    if (!btn) return;
    
    const icon = btn.querySelector('i:first-child');
    const text = document.getElementById('onlineText');
    
    if (isOnline) {
        btn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
        icon.className = 'fas fa-power-off';
        if (text) text.textContent = 'Go Offline';
    } else {
        btn.style.background = 'linear-gradient(135deg, #22c55e, #16a34a)';
        icon.className = 'fas fa-plug';
        if (text) text.textContent = 'Go Online';
    }
}

async function toggleOnlineStatus() {
    const btn = document.getElementById('onlineBtn');
    const spinner = document.getElementById('onlineSpinner');
    
    btn.disabled = true;
    spinner?.classList.remove('hidden');
    
    try {
        const response = await fetch(`/${ORG_CODE}/counter/toggle-online`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        
        if (data.success && typeof data.is_online === 'boolean') {
            updateOnlineButton(data.is_online);
        }
    } catch (error) {
        console.error('Toggle error:', error);
    } finally {
        btn.disabled = false;
        spinner?.classList.add('hidden');
        setTimeout(fetchDashboardData, 500);
    }
}

// ===== CHARTS =====
function initCharts() {
    const hourlyData = @json($analyticsData['hourly'] ?? array_fill(0, 24, 0));
    const weeklyData = @json($analyticsData['weekly'] ?? array_fill(0, 7, 0));
    const weeklyDays = @json($analyticsData['weekly_days'] ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']);
    const waitData = @json($analyticsData['wait_time'] ?? array_fill(0, 7, 0));
    const peakData = @json($analyticsData['peak_hours'] ?? [0, 0, 0, 0]);

    // Hourly Chart
    const hourlyCtx = document.getElementById('hourlyChart')?.getContext('2d');
    if (hourlyCtx) {
        charts.hourly = new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: ['12am', '1am', '2am', '3am', '4am', '5am', '6am', '7am', '8am', '9am', '10am', '11am', '12pm', '1pm', '2pm', '3pm', '4pm', '5pm', '6pm', '7pm', '8pm', '9pm', '10pm', '11pm'],
                datasets: [{
                    label: 'Completions',
                    data: hourlyData,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: 'rgba(59, 130, 246, 0.9)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { color: '#999', font: { size: 12 } } },
                    x: { ticks: { color: '#999', font: { size: 11 } } }
                }
            }
        });
    }

    // Weekly Chart
    const weeklyCtx = document.getElementById('weeklyChart')?.getContext('2d');
    if (weeklyCtx) {
        charts.weekly = new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: weeklyDays,
                datasets: [{
                    label: 'Daily Completions',
                    data: weeklyData,
                    borderColor: 'rgba(34, 197, 94, 1)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: 'rgba(34, 197, 94, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    hoverPointRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, labels: { color: '#666' } } },
                scales: {
                    y: { beginAtZero: true, ticks: { color: '#999', font: { size: 12 } } },
                    x: { ticks: { color: '#999', font: { size: 12 } } }
                }
            }
        });
    }

    // Wait Time Chart
    const waitCtx = document.getElementById('waitTimeChart')?.getContext('2d');
    if (waitCtx) {
        charts.wait = new Chart(waitCtx, {
            type: 'bar',
            data: {
                labels: weeklyDays,
                datasets: [{
                    label: 'Avg Wait (min)',
                    data: waitData,
                    backgroundColor: weeklyData.map((_, i) => i >= 5 ? 'rgba(34, 197, 94, 0.7)' : 'rgba(251, 146, 60, 0.7)'),
                    borderColor: 'rgba(251, 146, 60, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: 'rgba(251, 146, 60, 0.9)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { color: '#999', font: { size: 12 } } },
                    x: { ticks: { color: '#999', font: { size: 12 } } }
                }
            }
        });
    }

    // Distribution Chart
    const peakCtx = document.getElementById('peakHoursChart')?.getContext('2d');
    if (peakCtx) {
        charts.peak = new Chart(peakCtx, {
            type: 'doughnut',
            data: {
                labels: ['Peak (9am-5pm)', 'Morning (6am-9am)', 'Evening (5pm-9pm)', 'Night (9pm-6am)'],
                datasets: [{
                    data: peakData,
                    backgroundColor: [
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(156, 163, 175, 0.8)'
                    ],
                    borderColor: [
                        'rgba(168, 85, 247, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(251, 146, 60, 1)',
                        'rgba(156, 163, 175, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'bottom', labels: { color: '#666', padding: 15, font: { size: 12 } } } }
            }
        });
    }
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', () => {
    updateTimeAgo();
    initCharts();
    fetchDashboardData();
    
    refreshTimer = setInterval(fetchDashboardData, REFRESH_INTERVAL);
    
    // Handle visibility changes
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) fetchDashboardData();
    });
    
    // Cleanup
    window.addEventListener('beforeunload', () => {
        if (refreshTimer) clearInterval(refreshTimer);
    });
});
</script>
@endpush
@endsection