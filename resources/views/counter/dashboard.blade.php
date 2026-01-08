@extends('layouts.app')

@section('title', 'Counter Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Counter {{ $counter->counter_number }} - {{ $counter->display_name }}</h1>
        <button onclick="toggleOnline()" id="onlineBtn" 
                class="px-4 py-2 rounded {{ $counter->is_online ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white">
            <i class="fas fa-power-off mr-2"></i>
            <span id="onlineText">{{ $counter->is_online ? 'Go Offline' : 'Go Online' }}</span>
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-gray-500 mb-2">Waiting</div>
            <div class="text-3xl font-bold" id="waitingCount">{{ $stats['waiting'] }}</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-gray-500 mb-2">Completed Today</div>
            <div class="text-3xl font-bold" id="completedCount">{{ $stats['completed_today'] }}</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-gray-500 mb-2">Current Queue</div>
            <div class="text-3xl font-bold" id="currentQueue">
                {{ $stats['current_queue'] ? $stats['current_queue']->queue_number : 'None' }}
            </div>
        </div>
    </div>

    <!-- Current Queue -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Current Queue</h2>
        <div id="currentQueueDisplay" class="text-center">
            @if($stats['current_queue'])
                <div class="text-6xl font-bold text-blue-600 mb-4">
                    {{ $stats['current_queue']->queue_number }}
                </div>
                <div class="space-x-4">
                    <button onclick="moveToNext()" class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700">
                        <i class="fas fa-check mr-2"></i>Complete & Next
                    </button>
                </div>
            @else
                <p class="text-gray-500 text-xl">No queue being served</p>
                <button onclick="callNext()" class="mt-4 bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">
                    <i class="fas fa-bell mr-2"></i>Call Next Queue
                </button>
            @endif
        </div>
    </div>

    <!-- Waiting Queues -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Waiting Queues</h2>
        <div class="space-y-2" id="waitingQueues">
            @forelse($waitingQueues as $queue)
                <div class="flex justify-between items-center p-3 border rounded hover:bg-gray-50" data-queue-id="{{ $queue->id }}">
                    <span class="font-semibold">{{ $queue->queue_number }}</span>
                    <div class="space-x-2">
                        @if($onlineCounters->count() > 0)
                        <select class="border rounded px-2 py-1" id="transfer-{{ $queue->id }}">
                            <option value="">Transfer to...</option>
                            @foreach($onlineCounters as $oc)
                                <option value="{{ $oc->id }}">Counter {{ $oc->counter_number }} - {{ $oc->display_name }}</option>
                            @endforeach
                        </select>
                        <button onclick="transferQueue({{ $queue->id }})" class="bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center">No waiting queues</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
let isOnline = {{ $counter->is_online ? 'true' : 'false' }};

function toggleOnline() {
    fetch('{{ route('counter.toggle-online') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            isOnline = data.is_online;
            updateOnlineButton();
            location.reload();
        }
    });
}

function updateOnlineButton() {
    const btn = document.getElementById('onlineBtn');
    const text = document.getElementById('onlineText');
    if (isOnline) {
        btn.className = 'px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white';
        text.textContent = 'Go Offline';
    } else {
        btn.className = 'px-4 py-2 rounded bg-green-600 hover:bg-green-700 text-white';
        text.textContent = 'Go Online';
    }
}

function callNext() {
    fetch('{{ route('counter.call-next') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'No queues available');
        }
    });
}

function moveToNext() {
    fetch('{{ route('counter.move-next') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function transferQueue(queueId) {
    const select = document.getElementById(`transfer-${queueId}`);
    const toCounterId = select.value;
    
    if (!toCounterId) {
        alert('Please select a counter');
        return;
    }

    fetch('{{ route('counter.transfer') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            queue_id: queueId,
            to_counter_id: toCounterId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Queue transferred successfully');
            location.reload();
        } else {
            alert(data.message || 'Transfer failed');
        }
    });
}

// Auto-refresh every 10 seconds
setInterval(() => {
    location.reload();
}, 10000);
</script>
@endpush
@endsection
