@extends('layouts.app')

@section('title', 'Dashboard & Reports')

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
                    <div class="text-blue-100 text-xs mt-2"><i class="fas fa-user-clock"></i> Serving</div>
                </div>
                <div class="p-4 bg-white bg-opacity-20 rounded-full">
                    <i class="fas fa-user-tie text-white text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports & Analytics Section -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Reports & Analytics</h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Daily Completion Chart -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    <i class="fas fa-chart-bar mr-2 text-blue-500"></i>Completions by Hour
                </h3>
                <canvas id="hourlyChart" height="100"></canvas>
            </div>

            <!-- Weekly Trend Chart -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    <i class="fas fa-chart-line mr-2 text-green-500"></i>Weekly Trend
                </h3>
                <canvas id="weeklyChart" height="100"></canvas>
            </div>

            <!-- Average Wait Time -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    <i class="fas fa-hourglass-end mr-2 text-orange-500"></i>Avg Wait Time (Minutes)
                </h3>
                <canvas id="waitTimeChart" height="100"></canvas>
            </div>

            <!-- Peak Hours -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    <i class="fas fa-chart-pie mr-2 text-purple-500"></i>Queue Distribution
                </h3>
                <canvas id="peakHoursChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Overview only: no action panels, lists, or large displays -->
</div>

<!-- No large number styles needed in overview-only dashboard -->
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
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
</script>
@endpush
@endsection
