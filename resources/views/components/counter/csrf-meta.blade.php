{{-- Enhanced CSRF Meta Tags and Security Headers --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="x-requested-with" content="XMLHttpRequest">

{{-- Content Security Policy headers --}}
@php
    $nonce = base64_encode(random_bytes(16));
    session(['csp_nonce' => $nonce]);
@endphp
<meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'nonce-{{ $nonce }}' https://cdn.tailwindcss.com https://code.jquery.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: https:;">

{{-- Enhanced AJAX Setup with CSRF Protection --}}
<script nonce="{{ $nonce }}">
// Enhanced CSRF Protection for Counter Operations
window.CounterSecurity = {
    token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    
    init: function() {
        this.setupAjaxDefaults();
        this.validateToken();
        this.setupErrorHandling();
    },
    
    setupAjaxDefaults: function() {
        // jQuery AJAX setup (if jQuery is available)
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': this.token,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
        }
        
        // Fetch API defaults
        this.originalFetch = window.fetch;
        window.fetch = (url, options = {}) => {
            if (!options.headers) options.headers = {};
            
            // Add CSRF token to same-origin requests
            if (this.isSameOrigin(url)) {
                options.headers['X-CSRF-TOKEN'] = this.token;
                options.headers['X-Requested-With'] = 'XMLHttpRequest';
            }
            
            return this.originalFetch(url, options);
        };
    },
    
    validateToken: function() {
        if (!this.token) {
            console.warn('CSRF token not found. Security may be compromised.');
            this.showSecurityWarning();
        }
    },
    
    setupErrorHandling: function() {
        window.addEventListener('error', (event) => {
            if (event.message && event.message.includes('CSRF')) {
                this.handleSecurityError();
            }
        });
    },
    
    isSameOrigin: function(url) {
        const urlObj = new URL(url, window.location.origin);
        return urlObj.origin === window.location.origin;
    },
    
    showSecurityWarning: function() {
        console.error('⚠️ SECURITY WARNING: CSRF token is missing or invalid');
    },
    
    handleSecurityError: function() {
        // Show user-friendly error message
        if (confirm('Security session expired. Refresh page to continue?')) {
            window.location.reload();
        }
    },
    
    // Helper method to get headers for manual fetch requests
    getHeaders: function() {
        return {
            'X-CSRF-TOKEN': this.token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };
    }
};

// Initialize security on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    window.CounterSecurity.init();
});
</script>