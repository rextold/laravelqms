@extends('layouts.app')

@section('title', 'Dashboard & Reports')

@section('content')
<div class="container mx-auto px-4 py-8">

    <!-- ONLINE / OFFLINE BUTTON -->
    <div class="flex justify-end items-center mb-6">
        <button
            type="button"
            id="onlineBtn"
            onclick="toggleOnline()"
            class="px-4 py-2 rounded {{ $counter->is_online ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white flex items-center"
        >
            <i class="fas fa-power-off mr-2"></i>
            <span id="onlineText">{{ $counter->is_online ? 'Go Offline' : 'Go Online' }}</span>
            <span id="onlineSpinner" class="hidden ml-2">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        </button>
    </div>

    <!-- STATS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-gradient-to-br from-yellow-400 to-orange-500 p-6 rounded-xl shadow-lg">
            <div class="text-orange-100 text-sm">Waiting</div>
            <div id="waitingCount" class="text-4xl font-bold text-white">{{ $stats['waiting'] }}</div>
        </div>

        <div class="bg-gradient-to-br from-green-400 to-green-600 p-6 rounded-xl shadow-lg">
            <div class="text-green-100 text-sm">Completed Today</div>
            <div id="completedCount" class="text-4xl font-bold text-white">{{ $stats['completed_today'] }}</div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-6 rounded-xl shadow-lg">
            <div class="text-blue-100 text-sm">Current Queue</div>
            <div id="currentQueue" class="text-3xl font-bold text-white">
                {{ $stats['current_queue'] ? explode('-', $stats['current_queue']->queue_number)[1] ?? $stats['current_queue']->queue_number : 'None' }}
            </div>
        </div>
    </div>

    <!-- REPORTS -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Reports & Analytics</h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="font-semibold mb-4">Completions by Hour</h3>
                <canvas id="hourlyChart"></canvas>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="font-semibold mb-4">Weekly Trend</h3>
                <canvas id="weeklyChart"></canvas>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="font-semibold mb-4">Avg Wait Time</h3>
                <canvas id="waitTimeChart"></canvas>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="font-semibold mb-4">Queue Distribution</h3>
                <canvas id="peakHoursChart"></canvas>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

@push('scripts')
<script>
let isOnline = {{ $counter->is_online ? 'true' : 'false' }};
const REFRESH_MS = 3000;
let refreshInFlight = false;

/* =========================
   AUTO REFRESH DASHBOARD
========================= */
function refreshDashboardData() {
    if (refreshInFlight) return;
    refreshInFlight = true;

    fetch('{{ route('counter.data', ['organization_code' => request()->route('organization_code')]) }}', {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
    })
    .then(r => r.ok ? r.json() : Promise.reject())
    .then(data => {
        if (!data?.success) return;

        const stats = data.stats || {};
        document.getElementById('waitingCount').textContent = stats.waiting ?? 0;
        document.getElementById('completedCount').textContent = stats.completed_today ?? 0;
        document.getElementById('currentQueue').textContent =
            data.current_queue ? formatQueue(data.current_queue.queue_number) : 'None';

        if (typeof data.is_online === 'boolean') {
            isOnline = data.is_online;
            updateOnlineButton();
        }
    })
    .finally(() => refreshInFlight = false);
}

setInterval(refreshDashboardData, REFRESH_MS);

/* =========================
   ONLINE / OFFLINE TOGGLE
========================= */
function toggleOnline() {
    const btn = document.getElementById('onlineBtn');
    const spinner = document.getElementById('onlineSpinner');

    btn.disabled = true;
    spinner.classList.remove('hidden');

    fetch('{{ route('counter.toggle-online', ['organization_code' => request()->route('organization_code')]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(r => r.ok ? r.json() : Promise.reject())
    .then(data => {
        if (!data.success) throw new Error();
        isOnline = data.is_online;
        updateOnlineButton();
    })
    .catch(() => alert('Failed to update status'))
    .finally(() => {
        btn.disabled = false;
        spinner.classList.add('hidden');
    });
}

function updateOnlineButton() {
    const btn = document.getElementById('onlineBtn');
    const text = document.getElementById('onlineText');

    btn.className = `px-4 py-2 rounded text-white flex items-center ${
        isOnline ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'
    }`;

    text.textContent = isOnline ? 'Go Offline' : 'Go Online';
}

/* =========================
   HELPERS
========================= */
function formatQueue(q) {
    if (!q) return 'None';
    const parts = String(q).split('-');
    return parts[parts.length - 1] || q;
}

/* =========================
   CHARTS
========================= */
document.addEventListener('DOMContentLoaded', () => {

    new Chart(document.getElementById('hourlyChart'), {
        type: 'bar',
        data: {
            labels: [...Array(24).keys()].map(h => `${h}:00`),
            datasets: [{
                data: {{ json_encode($analyticsData['hourly']) }},
                backgroundColor: 'rgba(59,130,246,0.7)'
            }]
        }
    });

    new Chart(document.getElementById('weeklyChart'), {
        type: 'line',
        data: {
            labels: {{ json_encode($analyticsData['weekly_days']) }},
            datasets: [{
                data: {{ json_encode($analyticsData['weekly']) }},
                borderColor: 'rgba(34,197,94,1)',
                fill: true
            }]
        }
    });

    new Chart(document.getElementById('waitTimeChart'), {
        type: 'bar',
        data: {
            labels: {{ json_encode($analyticsData['weekly_days']) }},
            datasets: [{
                data: {{ json_encode($analyticsData['wait_time']) }},
                backgroundColor: 'rgba(251,146,60,0.7)'
            }]
        }
    });

    new Chart(document.getElementById('peakHoursChart'), {
        type: 'doughnut',
        data: {
            labels: ['Peak', 'Morning', 'Evening', 'Night'],
            datasets: [{
                data: {{ json_encode($analyticsData['peak_hours']) }}
            }]
        }
    });

});
</script>
@endpush
@endsection