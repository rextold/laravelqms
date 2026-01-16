@extends('layouts.app')

@section('title', 'Counter Dashboard')
@section('page-title', 'Counter Dashboard - Analytics & Performance')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <!-- Modern Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Left: Logo & Info -->
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        @if($settings->logo_url)
                            <img src="{{ $settings->logo_url }}" alt="Organization Logo" class="h-10 w-auto">
                        @else
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-building text-white text-lg"></i>
                            </div>
                        @endif
                    </div>
                    <div class="hidden md:block">
                        <h1 class="text-lg font-bold text-gray-900">{{ $organization->organization_name ?? 'QMS' }}</h1>
                        <p class="text-sm text-gray-500">Counter #{{ $counter->counter_number }} - {{ $counter->display_name }}</p>
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

@push('scripts')
<script>
// Modern Dashboard JavaScript
class CounterDashboard {
    constructor(config) {
        this.orgCode = config.orgCode;
        this.counterId = config.counterId;
        this.charts = {};
        this.refreshInterval = config.refreshInterval || 3000;
        this.refreshCount = 0;
        
        this.init();
    }

    init() {
        this.initCharts();
        this.startAutoRefresh();
        this.updateTimestamp();
    }

    initCharts() {
        // Hourly Chart
        const hourlyCtx = document.getElementById('hourlyChart');
        if (hourlyCtx) {
            this.charts.hourly = new Chart(hourlyCtx, {
                type: 'line',
                data: {
                    labels: ['8AM', '10AM', '12PM', '2PM', '4PM', '6PM'],
                    datasets: [{
                        label: 'Completions',
                        data: [12, 19, 15, 25, 22, 18],
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#F3F4F6'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Weekly Chart
        const weeklyCtx = document.getElementById('weeklyChart');
        if (weeklyCtx) {
            this.charts.weekly = new Chart(weeklyCtx, {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Total Served',
                        data: [65, 78, 82, 91, 95, 45, 32],
                        backgroundColor: '#10B981',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#F3F4F6'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Wait Time Chart
        const waitTimeCtx = document.getElementById('waitTimeChart');
        if (waitTimeCtx) {
            this.charts.waitTime = new Chart(waitTimeCtx, {
                type: 'line',
                data: {
                    labels: ['8AM', '10AM', '12PM', '2PM', '4PM', '6PM'],
                    datasets: [{
                        label: 'Wait Time (min)',
                        data: [5, 8, 12, 15, 10, 7],
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Minutes'
                            },
                            grid: {
                                color: '#F3F4F6'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Peak Hours Chart
        const peakHoursCtx = document.getElementById('peakHoursChart');
        if (peakHoursCtx) {
            this.charts.peakHours = new Chart(peakHoursCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Morning', 'Afternoon', 'Evening'],
                    datasets: [{
                        data: [35, 45, 20],
                        backgroundColor: [
                            '#8B5CF6',
                            '#A78BFA',
                            '#C4B5FD'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        }
    }

    startAutoRefresh() {
        setInterval(() => {
            this.refreshData();
        }, this.refreshInterval);
    }

    refreshData() {
        // Update timestamp
        this.updateTimestamp();
        this.refreshCount++;
        
        // Update counter
        const counterEl = document.getElementById('updateCounter');
        if (counterEl) {
            counterEl.textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
        }

        // Here you would typically fetch new data from the server
        // For now, we'll just update the timestamp
        console.log(`Dashboard refreshed #${this.refreshCount}`);
    }

    updateTimestamp() {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        const dateString = now.toLocaleDateString();
        
        // Update any time displays if they exist
        const timeEl = document.getElementById('headerTime');
        const dateEl = document.getElementById('headerDate');
        
        if (timeEl) timeEl.textContent = timeString;
        if (dateEl) dateEl.textContent = dateString;
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const dashboard = new CounterDashboard({
        orgCode: '{{ request()->route('organization_code') }}',
        counterId: {{ $counter->id }},
        refreshInterval: 3000
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