import Echo from 'laravel-echo';
window.Pusher = require('pusher-js');

const orgCode = window.orgCode || document.body.dataset.orgCode;
const echo = new Echo({
    broadcaster: 'pusher',
    key: 'YOUR_PUSHER_APP_KEY', // Replace with your .env PUSHER_APP_KEY
    cluster: 'YOUR_PUSHER_APP_CLUSTER', // Replace with your .env PUSHER_APP_CLUSTER
    forceTLS: true
});

echo.channel('organization.' + orgCode + '.counters')
    .listen('CounterStatusUpdated', (e) => {
        // e.counter_id, e.status
        if (e.status === 'online') {
            // Add counter to display
            addCounterToDisplay(e.counter_id);
        } else {
            // Remove counter from display
            removeCounterFromDisplay(e.counter_id);
        }
    });

function addCounterToDisplay(counterId) {
    // Example: fetch counter data and append to grid
    if (document.getElementById('counter-' + counterId)) return; // Already exists
    // Optionally fetch more info via AJAX if needed
    const el = document.createElement('div');
    el.id = 'counter-' + counterId;
    el.className = 'counter-card';
    el.innerHTML = `<div>Counter #${counterId} <span class="text-green-500">Online</span></div>`;
    // Try to append to .monitor-grid or .kiosk-counters
    let grid = document.querySelector('.monitor-grid') || document.querySelector('.kiosk-counters');
    if (grid) grid.appendChild(el);
}

function removeCounterFromDisplay(counterId) {
    const el = document.getElementById('counter-' + counterId);
    if (el && el.parentNode) el.parentNode.removeChild(el);
}
