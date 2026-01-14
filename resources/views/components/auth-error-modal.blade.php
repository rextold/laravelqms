<script>
// Authentication Error Modal Functions
function showAuthErrorModal() {
    const modal = document.getElementById('auth-error-modal');
    const content = document.getElementById('auth-error-modal-content');
    
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
}

function closeAuthErrorModal() {
    const modal = document.getElementById('auth-error-modal');
    const content = document.getElementById('auth-error-modal-content');
    
    if (modal) {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
}

function refreshPage() {
    window.location.reload();
}

// Close modal on outside click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('auth-error-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAuthErrorModal();
            }
        });
    }
});

// Global function to handle authentication errors
window.handleAuthError = function(response) {
    // Check if response indicates authentication error
    if (response && (response.status === 401 || response.status === 403)) {
        showAuthErrorModal();
        return true;
    }
    
    // Check for Laravel's redirect to login (302 with login URL)
    if (response && response.status === 302) {
        const locationHeader = response.headers.get('Location');
        if (locationHeader && locationHeader.includes('/login')) {
            showAuthErrorModal();
            return true;
        }
    }
    
    return false;
};

// Enhanced fetch wrapper that automatically handles auth errors
window.authFetch = function(url, options = {}) {
    // Ensure CSRF token is included
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    const defaultOptions = {
        headers: {
            'X-CSRF-TOKEN': csrfToken || '',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...options.headers
        },
        ...options
    };
    
    return fetch(url, defaultOptions)
        .then(response => {
            // Check for authentication errors
            if (handleAuthError(response)) {
                throw new Error('Authentication error');
            }
            return response;
        })
        .catch(error => {
            // Handle network errors that might indicate auth issues
            if (error.message.includes('Authentication error')) {
                throw error;
            }
            
            // For other errors, still check if it might be auth-related
            console.error('Fetch error:', error);
            throw error;
        });
};
</script>