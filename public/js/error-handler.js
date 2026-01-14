/**
 * Global Error Handler for 403 Forbidden Suppression
 * Prevents modal dialogs from crashing kiosk, counter, and monitor displays
 */

(function() {
    'use strict';

    // Suppress 403 errors on public displays
    const isPublicDisplay = () => {
        const path = window.location.pathname;
        return path.includes('/kiosk') || path.includes('/monitor') || path.includes('/counter/panel');
    };

    // Override fetch to suppress 403 modals
    const originalFetch = window.fetch;
    let fetchInterceptorActive = false;

    window.fetch = function(...args) {
        return originalFetch.apply(this, args)
            .then(response => {
                // Suppress 403 on public displays
                if (response.status === 403 && isPublicDisplay()) {
                    console.warn('[Error Handler] 403 suppressed on public display:', args[0]);
                    // Return a safe response that won't trigger modals
                    return new Response(
                        JSON.stringify({ success: false, suppressed: true, message: 'Access denied' }),
                        { status: 200, headers: { 'Content-Type': 'application/json' } }
                    );
                }
                return response;
            })
            .catch(error => {
                console.error('[Error Handler] Fetch error:', error);
                if (isPublicDisplay()) {
                    // Don't throw - let display continue
                    return new Response(
                        JSON.stringify({ success: false, error: 'Network error' }),
                        { status: 200, headers: { 'Content-Type': 'application/json' } }
                    );
                }
                throw error;
            });
    };

    // Preserve original fetch properties
    for (const key in originalFetch) {
        if (key !== 'apply' && key !== 'call' && key !== 'bind') {
            try {
                window.fetch[key] = originalFetch[key];
            } catch (e) {}
        }
    }

    // Global error handler for uncaught errors
    window.addEventListener('error', function(event) {
        if (isPublicDisplay()) {
            const message = event.message || '';
            const filename = event.filename || '';
            
            // Suppress 403 related errors
            if (message.includes('403') || message.includes('Forbidden') || filename.includes('error')) {
                console.warn('[Error Handler] Suppressed error on public display:', message);
                event.preventDefault();
                return false;
            }
        }
    }, true);

    // Handle unhandled promise rejections
    window.addEventListener('unhandledrejection', function(event) {
        if (isPublicDisplay()) {
            const reason = event.reason;
            if (reason && (reason.message || reason).toString().includes('403')) {
                console.warn('[Error Handler] Suppressed 403 promise rejection on public display');
                event.preventDefault();
            }
        }
    });

    // Alert override to suppress modal dialogs on public displays
    const originalAlert = window.alert;
    window.alert = function(message) {
        if (isPublicDisplay() && (message.includes('403') || message.includes('Forbidden') || message.includes('error'))) {
            console.warn('[Error Handler] Suppressed alert on public display:', message);
            return;
        }
        return originalAlert.apply(this, arguments);
    };

    // Console error override to catch and suppress 403 errors
    const originalError = console.error;
    console.error = function(...args) {
        const message = args.join(' ');
        if (isPublicDisplay() && message.includes('403')) {
            console.log('[Error Handler] Caught 403 error, suppressing on public display');
            return;
        }
        return originalError.apply(console, args);
    };

    console.log('[Error Handler] Global error suppression initialized for displays');
})();