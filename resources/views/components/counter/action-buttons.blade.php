@props(['disabled' => true])

<!-- Action Buttons -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 max-w-4xl mx-auto">
    <button type="button" id="btnCallNext" onclick="callNext(this)" 
            class="counter-btn flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl font-semibold shadow-sm" 
            {{ $disabled ? 'disabled' : '' }}>
        <i class="fas fa-bell mr-2"></i>
        Call Next
    </button>
    
    <button type="button" id="btnNotify" onclick="return notifyCustomer(this, event);" 
            class="counter-btn flex items-center justify-center px-4 py-3 bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl font-semibold shadow-sm" 
            {{ $disabled ? 'disabled' : '' }}>
        <i class="fas fa-bell mr-2"></i>
        Notify
    </button>
    
    <button type="button" id="btnComplete" onclick="moveToNext(this)" 
            class="counter-btn flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl font-semibold shadow-sm" 
            {{ $disabled ? 'disabled' : '' }}>
        <i class="fas fa-check-circle mr-2"></i>
        Complete
    </button>
    
    <button type="button" id="btnSkip" onclick="skipCurrent()" 
            class="counter-btn flex items-center justify-center px-4 py-3 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl font-semibold shadow-sm" 
            {{ $disabled ? 'disabled' : '' }}>
        <i class="fas fa-forward mr-2"></i>
        Skip
    </button>
    
    <button type="button" id="btnTransfer" onclick="openTransferModal()" 
            class="counter-btn flex items-center justify-center px-4 py-3 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl font-semibold shadow-sm" 
            {{ $disabled ? 'disabled' : '' }}>
        <i class="fas fa-exchange-alt mr-2"></i>
        Transfer
    </button>
</div>

@push('scripts')
<script>
// Enhanced button state management with CSRF validation
function updateButtonStates(isOnline) {
    const buttons = ['btnCallNext', 'btnNotify', 'btnComplete', 'btnSkip', 'btnTransfer'];
    
    // Validate CSRF token before enabling buttons
    if (isOnline && (!window.CounterSecurity || !window.CounterSecurity.validateToken())) {
        console.warn('CSRF token validation failed - keeping buttons disabled');
        isOnline = false;
    }
    
    buttons.forEach(buttonId => {
        const button = document.getElementById(buttonId);
        if (button) {
            button.disabled = !isOnline;
            
            // Add visual feedback for disabled state
            if (!isOnline) {
                button.classList.add('opacity-50', 'cursor-not-allowed');
                button.classList.remove('hover:shadow-md');
            } else {
                button.classList.remove('opacity-50', 'cursor-not-allowed');
                button.classList.add('hover:shadow-md');
            }
        }
    });
}

// CSRF-aware action functions
function callNext(btnEl) {
    if (!window.CounterSecurity || !window.CounterSecurity.validateToken()) {
        alert('Security token missing. Please refresh the page.');
        return;
    }
    
    return runActionWithCooldown(btnEl, () =>
        makeCounterRequest('call-next')
            .then((data) => {
                if (data.success) {
                    playNotificationSound();
                    fetchData();
                } else if (data.message) {
                    throw new Error(data.message);
                } else {
                    throw new Error('Failed to call next');
                }
            })
    );
}

function notifyCustomer(btnEl, event) {
    if (event) event.preventDefault();
    
    if (!window.CounterSecurity || !window.CounterSecurity.validateToken()) {
        alert('Security token missing. Please refresh the page.');
        return;
    }
    
    return runActionWithCooldown(btnEl, () =>
        makeCounterRequest('notify')
            .then((data) => {
                if (data && data.success) {
                    playNotificationSound();
                    fetchData();
                } else if (data && data.message) {
                    throw new Error(data.message);
                } else {
                    throw new Error('Notification failed');
                }
            })
    );
}

function moveToNext(btnEl) {
    if (!window.CounterSecurity || !window.CounterSecurity.validateToken()) {
        alert('Security token missing. Please refresh the page.');
        return;
    }
    
    return runActionWithCooldown(btnEl, () =>
        makeCounterRequest('move-next')
            .then((data) => {
                if (data.success) {
                    fetchData();
                } else if (data.message) {
                    throw new Error(data.message);
                } else {
                    throw new Error('Failed to move to next');
                }
            })
    );
}

function skipCurrent() {
    if (!window.CounterSecurity || !window.CounterSecurity.validateToken()) {
        alert('Security token missing. Please refresh the page.');
        return;
    }
    
    openSkipModal();
}

function openTransferModal() {
    if (!window.CounterSecurity || !window.CounterSecurity.validateToken()) {
        alert('Security token missing. Please refresh the page.');
        return;
    }
    
    // Existing transfer modal logic
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
        <button type="button" onclick="confirmTransfer(${counter.id})" class="queue-item hover:bg-blue-50 cursor-pointer">
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
</script>
@endpush