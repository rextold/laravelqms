/**
 * Counter Real-time Updates
 * Handles real-time updates for counter status and queue information
 */

class CounterRealtime {
    constructor() {
        this.updateInterval = 5000; // 5 seconds
        this.pollInterval = null;
        this.init();
    }

    init() {
        // Start polling for updates
        this.fetchCounterData();
        this.pollInterval = setInterval(() => this.fetchCounterData(), this.updateInterval);
    }

    async fetchCounterData() {
        try {
            // Get organization code from URL
            const pathParts = window.location.pathname.split('/').filter(p => p);
            const orgCode = pathParts[0];
            
            if (!orgCode) {
                console.warn('No organization code found in URL');
                return;
            }

            const response = await fetch(`/${orgCode}/kiosk/counters`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                console.warn('Failed to fetch counter data, status:', response.status);
                return;
            }

            const data = await response.json();
            this.updateCounterDisplay(data);
        } catch (error) {
            console.warn('Counter data fetch error:', error.message);
        }
    }

    updateCounterDisplay(data) {
        // Update counter buttons if they exist
        const counterButtons = document.querySelectorAll('[data-counter-id]');
        
        counterButtons.forEach(button => {
            const counterId = button.getAttribute('data-counter-id');
            const counter = data.find(c => c.id == counterId);
            
            if (counter) {
                // Update button state based on counter status
                if (counter.is_online) {
                    button.classList.remove('opacity-50', 'cursor-not-allowed');
                    button.classList.add('hover:scale-105');
                    button.disabled = false;
                } else {
                    button.classList.add('opacity-50', 'cursor-not-allowed');
                    button.classList.remove('hover:scale-105');
                    button.disabled = true;
                }
                
                // Update counter info if elements exist
                const counterName = button.querySelector('.counter-name');
                const counterStatus = button.querySelector('.counter-status');
                
                if (counterName) {
                    counterName.textContent = counter.display_name || `Counter ${counter.counter_number}`;
                }
                
                if (counterStatus) {
                    counterStatus.textContent = counter.is_online ? 'Online' : 'Offline';
                    counterStatus.className = `counter-status text-sm ${counter.is_online ? 'text-green-600' : 'text-red-600'}`;
                }
            }
        });
    }

    destroy() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }
}

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    if (!window.counterRealtime) {
        window.counterRealtime = new CounterRealtime();
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.counterRealtime) {
        window.counterRealtime.destroy();
    }
});