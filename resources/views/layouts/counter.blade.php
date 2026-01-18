<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="QMS">
    <meta name="msapplication-TileColor" content="#667eea">
    <meta name="msapplication-config" content="/browserconfig.xml">
    
    
    <title>@yield('title', 'Queue Management System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script nonce="{{ session('csp_nonce', '') }}">
        // Global AJAX setup for CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style nonce="{{ session('csp_nonce', '') }}">
        /* Counter-specific styles - no sidebar needed */
        .counter-layout {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .counter-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .counter-content {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: calc(100vh - 80px);
        }
        /* Hide any sidebar-related elements */
        .sidebar, #sidebar, .sidebar-toggle {
            display: none !important;
        }
        /* Ensure content takes full width */
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
        }
        /* Responsive font sizes for queue numbers */
        .queue-number {
            font-size: clamp(4rem, 10vw, 8rem);
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.02em;
        }
        /* Button animations */
        .counter-btn {
            transition: all 0.2s ease;
            transform: translateY(0);
        }
        .counter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        .counter-btn:active {
            transform: translateY(0);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        /* Glass morphism effect for cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        /* Status indicators */
        .status-active {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        /* Fullscreen optimization */
        @media (max-width: 768px) {
            .queue-number {
                font-size: clamp(3rem, 15vw, 6rem);
            }
            .counter-btn {
                font-size: 0.875rem;
                padding: 0.75rem 1rem;
            }
        }
        /* Print styles */
        @media print {
            .no-print { display: none !important; }
            .counter-layout { background: white !important; }
        }

        /* Queue list items */
        .queue-item {
            @apply flex items-center justify-between p-3 bg-white rounded-lg shadow-sm border border-gray-200;
        }

        /* Counter buttons */
        .counter-btn {
            @apply transition-all duration-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2;
        }

        .counter-btn:focus {
            @apply ring-blue-500;
        }

        /* Glass cards */
        .glass-card {
            @apply bg-white bg-opacity-90 backdrop-blur-sm border border-white border-opacity-20 rounded-2xl shadow-xl;
        }

        /* Main content area */
        .counter-main {
            @apply min-h-screen bg-gradient-to-br from-gray-50 to-blue-50;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .queue-number {
                @apply text-6xl;
            }
            
            .counter-btn {
                @apply text-sm px-3 py-2;
            }
        }

        @media (max-width: 480px) {
            .queue-number {
                @apply text-5xl;
            }
            
            .grid-cols-2 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }
        /* Focus indicators for accessibility */
        .counter-btn:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .glass-card {
                border: 2px solid #000;
            }
        }
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .counter-btn {
                transition: none;
            }
            .counter-btn:hover {
                transform: none;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="counter-layout" data-organization-code="{{ request()->route('organization_code') ?? (auth()->user() && auth()->user()->organization ? auth()->user()->organization->organization_code : '') }}">
    <!-- Counter Layout - No Sidebar -->
    <div class="min-h-screen">
        <!-- Header -->
        <header class="counter-header">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Left side - Organization Info -->
                    <div class="flex items-center space-x-4">
                        @if(isset($organization) && $organization->setting && $organization->setting->organization_logo)
                            <img src="{{ asset('storage/' . $organization->setting->organization_logo) }}" alt="Organization Logo" class="h-10 w-auto rounded-lg shadow-sm">
                        @endif
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ $organization->organization_name ?? 'Queue Management System' }}</h1>
                            <p class="text-sm text-gray-600">Counter {{ $counter->counter_number ?? '' }} - {{ $counter->display_name ?? 'Service Station' }}</p>
                        </div>
                    </div>
                    
                    <!-- Right side - Time and Controls -->
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <div class="text-lg font-semibold text-gray-700" id="headerTime"></div>
                            <div class="text-xs text-gray-500" id="headerDate"></div>
                        </div>
                        
                        <!-- Minimize/Restore button -->
                        <button id="btnToggleMinimize" type="button" 
                                class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors" title="Minimize">
                            <i class="fas fa-window-minimize"></i>
                        </button>
                        
                        <!-- Logout button -->
                        <a href="#" id="logoutBtn" 
                           class="px-3 py-2 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 transition-colors" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="counter-main counter-content">
            @yield('content')
        </main>
    </div>

    <!-- Minimized dock bar (for easy corner docking) -->
    <div id="panelDock" class="hidden fixed bottom-4 right-4 glass-card rounded-lg px-4 py-3 flex items-center space-x-4 z-40">
        <div class="text-sm font-semibold text-gray-700">Counter {{ $counter->counter_number ?? '' }}</div>
        <div class="flex items-baseline space-x-2">
            <div class="text-xs text-gray-500">Now</div>
            <div id="dockCurrentNumber" class="text-xl font-extrabold text-gray-900">---</div>
        </div>
        <button type="button" id="dockRestoreBtn" class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition" title="Restore">
            <i class="fas fa-window-restore"></i>
        </button>
    </div>

    <!-- Authentication Error Modal -->
    @include('components.auth-error-modal')

    <script nonce="{{ session('csp_nonce', '') }}">
        // Auto-refresh CSRF token every 30 minutes to prevent page expiration
        function refreshCSRFToken() {
            fetch('/refresh-csrf', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.token) {
                    // Update meta tag
                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                    // Update all forms with CSRF token
                    document.querySelectorAll('input[name="_token"]').forEach(input => {
                        input.value = data.token;
                    });
                    console.log('CSRF token refreshed');
                }
            })
            .catch(error => console.error('Failed to refresh CSRF token:', error));
        }

        // Refresh token every 30 minutes (1800000 ms)
        setInterval(refreshCSRFToken, 1800000);

        // Also refresh on user activity after 25 minutes of inactivity
        let inactivityTimer;
        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(refreshCSRFToken, 1500000); // 25 minutes
        }
        
        ['click', 'keypress', 'scroll', 'mousemove'].forEach(event => {
            document.addEventListener(event, resetInactivityTimer, { passive: true });
        });
        
        resetInactivityTimer();

        // Handle logout with POST method (security best practice)
        function handleLogout(event) {
            event.preventDefault();
            
            // Create a temporary form for POST logout
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('logout') }}';
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        // Bind logout button click handler
        document.addEventListener('DOMContentLoaded', function() {
            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', handleLogout);
                console.log('Logout button listener attached');
            } else {
                console.warn('Logout button not found');
            }
        });

        // Minimize/Restore functionality
        function toggleMinimize(minimize = null) {
            const panelMain = document.querySelector('.counter-layout');
            const panelDock = document.getElementById('panelDock');
            
            if (minimize === null) {
                // Toggle state
                minimize = panelMain.style.display !== 'none';
            }
            
            if (minimize) {
                panelMain.style.display = 'none';
                panelDock.classList.remove('hidden');
            } else {
                panelMain.style.display = 'block';
                panelDock.classList.add('hidden');
            }
        }

        // Update time and date
        function updateDateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            });
            const dateString = now.toLocaleDateString('en-US', { 
                weekday: 'short',
                month: 'short',
                day: 'numeric'
            });
            
            document.getElementById('headerTime').textContent = timeString;
            document.getElementById('headerDate').textContent = dateString;
        }

        // Update time every second
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Keyboard shortcuts for counter operations
        document.addEventListener('keydown', function(e) {
            // Only trigger if no input is focused
            if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA') {
                return;
            }
            
            switch(e.key.toLowerCase()) {
                case 'n': // Next
                case 'enter':
                    e.preventDefault();
                    const callNextBtn = document.getElementById('btnCallNext');
                    if (callNextBtn && !callNextBtn.disabled) {
                        callNextBtn.click();
                    }
                    break;
                case 'c': // Complete
                    e.preventDefault();
                    const completeBtn = document.getElementById('btnComplete');
                    if (completeBtn && !completeBtn.disabled) {
                        completeBtn.click();
                    }
                    break;
                case 's': // Skip
                    e.preventDefault();
                    const skipBtn = document.getElementById('btnSkip');
                    if (skipBtn && !skipBtn.disabled) {
                        skipBtn.click();
                    }
                    break;
                case 't': // Transfer
                    e.preventDefault();
                    const transferBtn = document.getElementById('btnTransfer');
                    if (transferBtn && !transferBtn.disabled) {
                        transferBtn.click();
                    }
                    break;
            }
        });

        // Prevent accidental page refresh/back navigation
        window.addEventListener('beforeunload', function(e) {
            // Only warn if there are active operations
            const activeButtons = document.querySelectorAll('button:disabled:not([disabled=""])');
            if (activeButtons.length > 0) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });

        // Handle fullscreen mode
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log('Fullscreen not supported:', err);
                });
            } else {
                document.exitFullscreen();
            }
        }

        // Add fullscreen button functionality
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F11' || (e.ctrlKey && e.shiftKey && e.key === 'F')) {
                e.preventDefault();
                toggleFullscreen();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>