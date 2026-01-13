<!-- Authentication Error Modal Component -->
<div id="auth-error-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="auth-error-modal-content">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    <h3 class="text-xl font-bold text-white">Authentication Error</h3>
                </div>
                <button onclick="closeAuthErrorModal()" class="text-white hover:text-gray-200 text-2xl leading-none">
                    &times;
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-6">
            <div class="text-center">
                <div class="mb-4">
                    <i class="fas fa-user-times text-red-500 text-4xl mb-3"></i>
                </div>
                <h4 class="text-lg font-semibold text-gray-800 mb-2">Session Expired</h4>
                <p class="text-gray-600 mb-4">
                    Your session has expired or you are not authenticated. Please refresh the page to continue.
                </p>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                        <p class="text-sm text-yellow-800">
                            This usually happens after being inactive for a while or when your login session expires.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-center space-x-3 border-t border-gray-200">
            <button onclick="refreshPage()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition flex items-center">
                <i class="fas fa-sync-alt mr-2"></i>
                Refresh Page
            </button>
        </div>
    </div>
</div>

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