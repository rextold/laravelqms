@extends('layouts.app')

@section('title', 'Counter Dashboard')
@section('page-title', 'Counter Dashboard - Analytics & Performance')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-end items-center mb-6">
        <button type="button" id="onlineBtn" 
                class="px-4 py-2 rounded {{ $counter->is_online ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white flex items-center"
                onclick="toggleOnline()">
            <i class="fas fa-power-off mr-2"></i>
            <span id="onlineText">{{ $counter->is_online ? 'Go Offline' : 'Go Online' }}</span>
            <span id="onlineSpinner" class="hidden ml-2"><i class="fas fa-spinner fa-spin"></i></span>
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-gradient-to-br from-yellow-400 to-orange-500 p-6 rounded-xl shadow-lg transform hover:scale-105 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-orange-100 text-sm font-medium mb-1">Waiting</div>
                    <div class="text-4xl font-bold text-white" id="waitingCount">{{ $stats['waiting'] }}</div>
                    <div class="text-orange-100 text-xs mt-2"><i class="fas fa-clock"></i> In Queue</div>
                </div>
                <div class="p-4 bg-white bg-opacity-20 rounded-full">
                    <i class="fas fa-hourglass-half text-white text-3xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-green-400 to-green-600 p-6 rounded-xl shadow-lg transform hover:scale-105 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-green-100 text-sm font-medium mb-1">Completed Today</div>
                    <div class="text-4xl font-bold text-white" id="completedCount">{{ $stats['completed_today'] }}</div>
                    <div class="text-green-100 text-xs mt-2"><i class="fas fa-check-circle"></i> Served</div>
                </div>
                <div class="p-4 bg-white bg-opacity-20 rounded-full">
                    <i class="fas fa-check-double text-white text-3xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-6 rounded-xl shadow-lg transform hover:scale-105 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-blue-100 text-sm font-medium mb-1">Current Queue</div>
                    @php
                        $formatCounterQueue = function ($queueNumber, $counterNumber) {
                            if (!$queueNumber) return 'None';
                            $parts = explode('-', $queueNumber);
                            $suffix = count($parts) ? end($parts) : $queueNumber;
                            return $suffix ?: $queueNumber;
                        };
                    @endphp
                    <div class="text-3xl font-bold text-white" id="currentQueue">
                        {{ $stats['current_queue'] ? $formatCounterQueue($stats['current_queue']->queue_number, $counter->counter_number) : 'None' }}
                    </div>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Status Indicator -->
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full {{ $counter->is_online ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></div>
                        <span class="text-sm font-medium {{ $counter->is_online ? 'text-green-700' : 'text-red-700' }}">
                            {{ $counter->is_online ? 'Online' : 'Offline' }}
                        </span>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('counter.panel', ['organization_code' => request()->route('organization_code')]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-phone-alt mr-2"></i>
                            Service Panel
                        </a>
                        
                        <button id="onlineBtn" type="button" onclick="toggleOnlineStatus()"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors
                                {{ $counter->is_online ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-green-600 hover:bg-green-700 text-white' }}">
                            <i class="fas {{ $counter->is_online ? 'fa-power-off' : 'fa-plug' }} mr-2"></i>
                            <span id="onlineText">{{ $counter->is_online ? 'Go Offline' : 'Go Online' }}</span>
                            <span id="onlineSpinner" class="hidden ml-2"><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">In Queue</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['waiting'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-amber-600"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">Waiting customers</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Completed Today</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_today'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-double text-green-600"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">Today's completed</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Now Serving</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['current_number'] ?? '---' }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-check text-blue-600"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">Active queue</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Served</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_served'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">All time</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Avg Wait</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['avg_wait_time'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-cyan-600"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">Minutes today</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Avg Service</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['avg_service_time'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-stopwatch text-orange-600"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">Minutes today</div>
            </div>
        </div>


        <!-- Analytics Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Hourly Performance Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                        Hourly Completions
                    </h3>
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium">Today</span>
                </div>
                <div class="h-64">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>

            <!-- Weekly Trend Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-chart-line text-green-600 mr-2"></i>
                        Weekly Trend
                    </h3>
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-medium">7 Days</span>
                </div>
                <div class="h-64">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Bottom Analytics Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Wait Time Analysis -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-hourglass-end text-orange-600 mr-2"></i>
                        Average Wait Time
                    </h3>
                    <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-medium">Minutes</span>
                </div>
                <div class="h-64">
                    <canvas id="waitTimeChart"></canvas>
                </div>
            </div>

            <!-- Peak Hours Distribution -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-chart-pie text-purple-600 mr-2"></i>
                        Peak Hours Distribution
                    </h3>
                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full font-medium">30 Days</span>
                </div>
                <div class="h-64">
                    <canvas id="peakHoursChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Service Panel Preview -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-tachometer-alt text-indigo-600 mr-2"></i>
                    Quick Service Panel
                </h3>
                <a href="{{ route('counter.panel', ['organization_code' => request()->route('organization_code')]) }}" 
                   class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    Open Full Panel →
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Current Status -->
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-3xl font-bold text-gray-900 mb-2">{{ $stats['current_number'] ?? '---' }}</div>
                    <div class="text-sm text-gray-600">Now Serving</div>
                </div>
                
                <!-- Waiting Queue -->
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-3xl font-bold text-blue-600 mb-2">{{ $stats['waiting'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Waiting Customers</div>
                </div>
                
                <!-- Action Button -->
                <div class="flex items-center justify-center">
                    <button onclick="window.location.href='{{ route('counter.panel', ['organization_code' => request()->route('organization_code')]) }}'" 
                            class="w-full px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg transition-all transform hover:scale-105">
                        <i class="fas fa-phone-alt mr-2"></i>
                        Go to Service Panel
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="mt-8 p-4 bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between text-sm text-gray-600">
                <span><i class="fas fa-info-circle mr-2 text-blue-600"></i>Dashboard refreshes automatically</span>
                <span id="updateCounter" class="font-mono text-xs text-gray-400">Last updated: just now</span>
            </div>
        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

    <script>
        function toggleOnline() {
            const btn = document.getElementById('onlineBtn');
            const text = document.getElementById('onlineText');
            const spinner = document.getElementById('onlineSpinner');
            btn.disabled = true;
            spinner.classList.remove('hidden');
            fetch("{{ route('counter.toggle-online', ['organization_code' => request()->route('organization_code')]) }}", {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    text.textContent = data.is_online ? 'Go Offline' : 'Go Online';
                    btn.classList.toggle('bg-green-600', !data.is_online);
                    btn.classList.toggle('hover:bg-green-700', !data.is_online);
                    btn.classList.toggle('bg-red-600', data.is_online);
                    btn.classList.toggle('hover:bg-red-700', data.is_online);
                }
            })
            .catch(() => {})
            .finally(() => {
                btn.disabled = false;
                spinner.classList.add('hidden');
            });
        }
    </script>
        // swallow errors to avoid breaking the interval
    });
}

function toggleOnline() {
    const btn = document.getElementById('onlineBtn');
    const spinner = document.getElementById('onlineSpinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    fetch('{{ route('counter.toggle-online', ['organization_code' => request()->route('organization_code')]) }}', {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.ok ? response.json() : Promise.reject(response))
    .then(data => {
        if (data.success) {
            isOnline = data.is_online;
            updateOnlineButton();
            // No hard reload needed; update button instantly
        } else {
            throw new Error('Toggle failed');
        }
    })
    .catch(() => {
        alert('Could not update status. Please try again.');
    })
    .finally(() => {
        btn.disabled = false;
        spinner.classList.add('hidden');
    });
}

function formatDisplayQueue(queueNumber) {
    if (!queueNumber) return 'None';
    const parts = String(queueNumber).split('-');
    const suffix = parts.length ? (parts[parts.length - 1] || queueNumber) : queueNumber;
    return suffix;
}

function updateOnlineButton() {
    const btn = document.getElementById('onlineBtn');
    const text = document.getElementById('onlineText');
    const base = 'px-4 py-2 rounded text-white flex items-center';
    if (isOnline) {
        btn.className = `${base} bg-red-600 hover:bg-red-700`;
        text.textContent = 'Go Offline';
    } else {
        btn.className = `${base} bg-green-600 hover:bg-green-700`;
        text.textContent = 'Go Online';
    }
}

// Initialize Charts
document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
    
    // Prevent form submission and use AJAX instead
    const onlineForm = document.getElementById('onlineForm');
    if (onlineForm) {
        onlineForm.addEventListener('submit', function(e) {
            e.preventDefault();
            toggleOnline();
        });
    }
    // Hourly Completions Chart
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: ['12am', '1am', '2am', '3am', '4am', '5am', '6am', '7am', '8am', '9am', '10am', '11am', '12pm', '1pm', '2pm', '3pm', '4pm', '5pm', '6pm', '7pm', '8pm', '9pm', '10pm', '11pm'],
            datasets: [{
                label: 'Completions',
                data: {{ json_encode($analyticsData['hourly']) }},
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#666' }
                },
                x: {
                    ticks: { color: '#666' }
                }
            }
        }
    });

    // Weekly Trend Chart
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: {{ json_encode($analyticsData['weekly_days']) }},
            datasets: [{
                label: 'Daily Completions',
                data: {{ json_encode($analyticsData['weekly']) }},
                borderColor: 'rgba(34, 197, 94, 1)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: 'rgba(34, 197, 94, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: true, labels: { color: '#666' } }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#666' }
                },
                x: {
                    ticks: { color: '#666' }
                }
            }
        }
    });

    // Average Wait Time Chart
    const waitTimeCtx = document.getElementById('waitTimeChart').getContext('2d');
    new Chart(waitTimeCtx, {
        type: 'bar',
        data: {
            labels: {{ json_encode($analyticsData['weekly_days']) }},
            datasets: [{
                label: 'Average Wait Time (min)',
                data: {{ json_encode($analyticsData['wait_time']) }},
                backgroundColor: [
                    'rgba(251, 146, 60, 0.7)',
                    'rgba(251, 146, 60, 0.7)',
                    'rgba(251, 146, 60, 0.7)',
                    'rgba(251, 146, 60, 0.7)',
                    'rgba(251, 146, 60, 0.7)',
                    'rgba(34, 197, 94, 0.7)',
                    'rgba(34, 197, 94, 0.7)'
                ],
                borderColor: 'rgba(251, 146, 60, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#666' }
                },
                x: {
                    ticks: { color: '#666' }
                }
            }
        }
    });

    // Queue Distribution Chart
    const peakHoursCtx = document.getElementById('peakHoursChart').getContext('2d');
    new Chart(peakHoursCtx, {
        type: 'doughnut',
        data: {
            labels: ['Peak Hours (9am-5pm)', 'Morning (6am-9am)', 'Evening (5pm-9pm)', 'Night (9pm-6am)'],
            datasets: [{
                data: {{ json_encode($analyticsData['peak_hours']) }},
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
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { color: '#666', padding: 15 }
                }
            }
        }
    });
});

// Toggle online status function
function toggleOnlineStatus() {
    const btn = document.getElementById('onlineBtn');
    const spinner = document.getElementById('onlineSpinner');
    const text = document.getElementById('onlineText');
    
    // Show loading state
    btn.disabled = true;
    spinner.classList.remove('hidden');
    
    fetch('{{ route("counter.toggle-online", ["organization_code" => request()->route("organization_code")]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI based on new status
            const isOnline = data.is_online;
            
            // Update button appearance
            btn.className = `inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors ${
                isOnline ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-green-600 hover:bg-green-700 text-white'
            }`;
            
            // Update button text
            text.textContent = isOnline ? 'Go Offline' : 'Go Online';
            
            // Update status indicator
            const statusDot = document.querySelector('.flex.items-center.space-x-2 .w-3.h-3');
            const statusText = document.querySelector('.flex.items-center.space-x-2 span:last-child');
            
            if (statusDot) {
                statusDot.className = `w-3 h-3 rounded-full ${isOnline ? 'bg-green-500 animate-pulse' : 'bg-red-500'}`;
            }
            
            if (statusText) {
                statusText.textContent = isOnline ? 'Online' : 'Offline';
                statusText.className = `text-sm font-medium ${isOnline ? 'text-green-700' : 'text-red-700'}`;
            }
            
            // Show success message
            showNotification(data.message, 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to update status', 'error');
    })
    .finally(() => {
        // Hide loading state
        btn.disabled = false;
        spinner.classList.add('hidden');
    });
}

// Show notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.add('translate-x-0');
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>
@endpush


</script>
@endpush
@endsection
