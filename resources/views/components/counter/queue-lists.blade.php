@props(['waitingQueues' => [], 'skippedQueues' => []])

<!-- Queue Lists -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Waiting Queue -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-clock text-blue-500 mr-2"></i>
                Waiting Queue
                <span id="waitingCount" class="ml-2 bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded-full">0</span>
            </h3>
            <div class="text-sm text-gray-500">
                <span id="waitingTotalTime">--:--</span> total wait
            </div>
        </div>
        <div class="p-4">
            <div id="waitingList" class="space-y-2 max-h-64 overflow-y-auto">
                <div class="text-center text-gray-500 py-8">No waiting queues</div>
            </div>
        </div>
    </div>

    <!-- Skipped Queue -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-forward text-orange-500 mr-2"></i>
                Skipped Queue
                <span id="skippedCount" class="ml-2 bg-orange-100 text-orange-800 text-sm px-2 py-1 rounded-full">0</span>
            </h3>
            <div class="text-sm text-gray-500">
                <span id="skippedTotalTime">--:--</span> total skip time
            </div>
        </div>
        <div class="p-4">
            <div id="skippedList" class="space-y-2 max-h-64 overflow-y-auto">
                <div class="text-center text-gray-500 py-8">No skipped queues</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Enhanced queue list management with CSRF validation
function renderLists(data) {
    const waitingList = document.getElementById('waitingList');
    const skippedList = document.getElementById('skippedList');
    const waitingCount = document.getElementById('waitingCount');
    const skippedCount = document.getElementById('skippedCount');
    const waitingTotalTime = document.getElementById('waitingTotalTime');
    const skippedTotalTime = document.getElementById('skippedTotalTime');
    
    // Validate CSRF before processing
    if (!window.CounterSecurity || !window.CounterSecurity.validateToken()) {
        console.warn('CSRF validation failed - skipping queue list update');
        return;
    }
    
    // Update waiting queue
    if (data.waiting && data.waiting.length > 0) {
        waitingCount.textContent = data.waiting.length;
        waitingTotalTime.textContent = formatTotalTime(data.waiting);
        
        waitingList.innerHTML = data.waiting.map(queue => `
            <div class="queue-item bg-gray-50 rounded-lg p-3 hover:bg-gray-100 transition-colors">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="queue-number-small bg-blue-100 text-blue-800 rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm mr-3">
                            ${queue.queue_number}
                        </div>
                        <div>
                            <div class="font-medium text-gray-800">${queue.customer_name || 'Anonymous'}</div>
                            <div class="text-sm text-gray-500">Wait: ${formatTime(queue.waiting_time)}</div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400">${formatTime(queue.created_at)}</div>
                </div>
            </div>
        `).join('');
    } else {
        waitingCount.textContent = '0';
        waitingTotalTime.textContent = '--:--';
        waitingList.innerHTML = '<div class="text-center text-gray-500 py-8">No waiting queues</div>';
    }
    
    // Update skipped queue
    if (data.skipped && data.skipped.length > 0) {
        skippedCount.textContent = data.skipped.length;
        skippedTotalTime.textContent = formatTotalTime(data.skipped);
        
        skippedList.innerHTML = data.skipped.map(queue => `
            <div class="queue-item bg-orange-50 rounded-lg p-3 hover:bg-orange-100 transition-colors">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="queue-number-small bg-orange-100 text-orange-800 rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm mr-3">
                            ${queue.queue_number}
                        </div>
                        <div>
                            <div class="font-medium text-gray-800">${queue.customer_name || 'Anonymous'}</div>
                            <div class="text-sm text-gray-500">Skipped: ${formatTime(queue.skipped_time)}</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="recallQueue(${queue.id})" 
                                class="px-3 py-1 bg-orange-500 hover:bg-orange-600 text-white rounded text-sm font-medium transition-colors"
                                title="Recall this queue">
                            <i class="fas fa-redo mr-1"></i>Recall
                        </button>
                        <div class="text-xs text-gray-400">${formatTime(queue.created_at)}</div>
                    </div>
                </div>
            </div>
        `).join('');
    } else {
        skippedCount.textContent = '0';
        skippedTotalTime.textContent = '--:--';
        skippedList.innerHTML = '<div class="text-center text-gray-500 py-8">No skipped queues</div>';
    }
    
    // Update dock recall button visibility
    const dockRecallBtn = document.getElementById('dockRecallBtn');
    if (dockRecallBtn) {
        if (data.skipped && data.skipped.length > 0) {
            dockRecallBtn.classList.remove('hidden');
            dockRecallBtn.title = `Recall Skipped Queue (${data.skipped.length} available)`;
        } else {
            dockRecallBtn.classList.add('hidden');
        }
    }
}

// Utility functions
function formatTime(timestamp) {
    if (!timestamp) return '--:--';
    
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins === 1) return '1 min';
    if (diffMins < 60) return `${diffMins} mins`;
    
    const hours = Math.floor(diffMins / 60);
    const mins = diffMins % 60;
    
    if (hours === 1) return `1h ${mins}m`;
    return `${hours}h ${mins}m`;
}

function formatTotalTime(queues) {
    if (!queues || queues.length === 0) return '--:--';
    
    const totalMs = queues.reduce((sum, queue) => {
        const created = new Date(queue.created_at);
        return sum + (new Date() - created);
    }, 0);
    
    const totalMins = Math.floor(totalMs / 60000);
    const hours = Math.floor(totalMins / 60);
    const mins = totalMins % 60;
    
    if (hours > 0) return `${hours}h ${mins}m`;
    return `${mins}m`;
}
</script>
@endpush