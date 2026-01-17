@props(['counter', 'organizationCode'])

<!-- Minimized dock bar (for easy corner docking) -->
<div id="panelDock" class="hidden fixed bottom-4 right-4 bg-white rounded-xl shadow-2xl px-4 py-3 flex items-center space-x-3 z-50 border-2 border-blue-200 min-w-max group" data-organization-code="{{ $organizationCode }}">
    <div class="text-sm font-bold text-gray-800">Counter {{ $counter->counter_number }}</div>
    <div class="flex items-baseline space-x-2 bg-blue-100 px-3 py-2 rounded-lg border border-blue-200">
        <div class="text-xs font-medium text-blue-700">Now</div>
        <div id="dockCurrentNumber" class="text-2xl font-black text-blue-800">---</div>
    </div>
    
    <!-- Quick Recall Button - Visible when minimized -->
    <button type="button" id="dockRecallBtn" onclick="recallFromDock()" 
            class="px-4 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white transition-all duration-200 font-bold text-sm shadow-lg hover:shadow-xl transform hover:scale-105 hidden" 
            title="Recall Skipped Queue (Press 'R')">
        <i class="fas fa-redo mr-2"></i>Recall
    </button>
    
    <button type="button" onclick="toggleMinimize(false)" class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition" title="Restore (Press Space)">
        <i class="fas fa-window-restore"></i>
    </button>
    
    <!-- Keyboard shortcuts hint -->
    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
        Press 'R' to Recall • Space to Restore
        <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
    </div>
</div>

@push('scripts')
<script>
// CSRF token management for counter operations
const CounterCSRF = {
    getToken: function() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    },
    
    getHeaders: function() {
        return {
            'X-CSRF-TOKEN': this.getToken(),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };
    },
    
    validateToken: function() {
        const token = this.getToken();
        if (!token) {
            console.warn('CSRF token not found in meta tag');
            return false;
        }
        return true;
    }
};

// Enhanced recall function with better error handling
function recallFromDock() {
    // Validate CSRF token first
    if (!CounterCSRF.validateToken()) {
        alert('Security token missing. Please refresh the page.');
        return;
    }
    
    // Get the first available skipped queue when in dock mode
    if (lastSuccessfulData && lastSuccessfulData.skipped && lastSuccessfulData.skipped.length > 0) {
        const firstSkipped = lastSuccessfulData.skipped[0];
        if (firstSkipped && firstSkipped.id) {
            recallQueue(firstSkipped.id, null);
        } else {
            alert('No skipped queues available to recall');
        }
    } else {
        alert('No skipped queues available to recall');
    }
}

// Enhanced keyboard shortcuts with CSRF validation
document.addEventListener('keydown', function(e) {
    // Only work when dock is visible (minimized state)
    const dock = document.getElementById('panelDock');
    if (!dock || dock.classList.contains('hidden')) return;
    
    // Validate CSRF token for protected actions
    if (!CounterCSRF.validateToken()) return;
    
    // R key for Recall (when recall button is visible)
    if (e.key === 'r' || e.key === 'R') {
        const recallBtn = document.getElementById('dockRecallBtn');
        if (recallBtn && !recallBtn.classList.contains('hidden')) {
            e.preventDefault();
            recallFromDock();
        }
    }
    // Space bar to restore from dock
    else if (e.key === ' ') {
        e.preventDefault();
        toggleMinimize(false);
    }
});
</script>
@endpush