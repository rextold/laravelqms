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
            <div class="max-w-7xl mx-auto px-2 sm:px-4 md:px-6 lg:px-8">
                <div class="flex items-center justify-between h-14 sm:h-16">
                    <!-- Left side - Organization Info -->
                    <div class="flex items-center space-x-2 sm:space-x-4 min-w-0 flex-1">
                        @php
                            $logoUrl = null;
                            if (isset($organization)) {
                                // Try multiple logo sources
                                if ($organization->setting && $organization->setting->organization_logo) {
                                    $logoUrl = asset('storage/' . $organization->setting->organization_logo);
                                } elseif ($organization->organization_logo) {
                                    $logoUrl = asset('storage/' . $organization->organization_logo);
                                } elseif (isset($settings) && $settings->organization_logo) {
                                    $logoUrl = asset('storage/' . $settings->organization_logo);
                                }
                            }
                            $orgName = $organization->organization_name ?? 'Queue Management System';
                        @endphp
                        
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $orgName }}" class="h-8 sm:h-10 w-auto rounded-lg shadow-sm flex-shrink-0">
                        @else
                            <div class="h-8 sm:h-10 w-8 sm:w-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-building text-white text-sm sm:text-base"></i>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h1 class="text-sm sm:text-xl font-bold text-gray-900 truncate">{{ $orgName }}</h1>
                            <p class="text-xs sm:text-sm text-gray-600 truncate">Counter {{ $counter->counter_number ?? '' }} - {{ $counter->display_name ?? 'Service Station' }}</p>
                        </div>
                    </div>
                    
                    <!-- Right side - Time and Controls -->
                    <div class="flex items-center space-x-1 sm:space-x-2 md:space-x-4 flex-shrink-0">
                        <!-- Time display - hidden on very small screens -->
                        <div class="text-right hidden sm:block">
                            <div class="text-sm sm:text-lg font-semibold text-gray-700" id="headerTime"></div>
                            <div class="text-xs text-gray-500" id="headerDate"></div>
                        </div>
                        
                        <!-- Online/Offline Status Toggle Button -->
                        <button id="btnToggleOnline" type="button" 
                                class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-all duration-300 flex items-center" 
                                title="{{ $counter->is_online ?? false ? 'Online - Click to go offline' : 'Offline - Click to go online' }}">
                            <i id="statusIcon" class="fas fa-circle {{ $counter->is_online ?? false ? 'text-green-500' : 'text-red-500' }}" style="font-size: 0.5rem;"></i>
                            <span id="statusLabel" class="text-xs font-semibold {{ $counter->is_online ?? false ? 'text-green-600' : 'text-red-600' }} ml-1 sm:ml-2 hidden sm:inline">{{ $counter->is_online ?? false ? 'Online' : 'Offline' }}</span>
                        </button>
                        
                        <!-- Minimize/Restore button - hidden on mobile -->
                        <button id="btnToggleMinimize" type="button" 
                                class="hidden sm:flex px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors" title="Minimize">
                            <i class="fas fa-window-minimize"></i>
                        </button>
                        
                        <!-- Logout button -->
                        <a href="#" id="logoutBtn" 
                           class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 transition-colors" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>                </div>
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

        // Auto-logout after 30 minutes of inactivity for counters
        let inactivityTimer;
        let autoLogoutTimer;
        const INACTIVITY_TIMEOUT = 30 * 60 * 1000; // 30 minutes
        const WARNING_TIME = 5 * 60 * 1000; // Show warning 5 minutes before logout
        
        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            clearTimeout(autoLogoutTimer);
            
            // Reset CSRF refresh timer (25 minutes)
            inactivityTimer = setTimeout(refreshCSRFToken, 1500000);
            
            // Reset auto-logout timer (30 minutes)
            autoLogoutTimer = setTimeout(() => {
                showAutoLogoutWarning();
            }, INACTIVITY_TIMEOUT - WARNING_TIME);
        }
        
        function showAutoLogoutWarning() {
            // Create warning modal
            const modal = document.createElement('div');
            modal.id = 'autoLogoutModal';
            modal.className = 'fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 backdrop-blur-sm';
            modal.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all animate-fadeInScale">
                    <div class="bg-gradient-to-r from-red-500 to-orange-500 px-6 py-4 rounded-t-xl">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-clock text-white text-2xl"></i>
                            <h2 class="text-xl font-bold text-white">Session Timeout Warning</h2>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <p class="text-gray-700">
                            You have been inactive for 25 minutes. Your session will automatically logout in <span id="countdown" class="font-bold text-red-600">5:00</span> minutes for security reasons.
                        </p>
                        <div class="flex space-x-3">
                            <button onclick="extendSession()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                                <i class="fas fa-check mr-2"></i>Stay Logged In
                            </button>
                            <button onclick="logoutNow()" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout Now
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Start countdown
            let timeLeft = WARNING_TIME / 1000; // 5 minutes in seconds
            const countdownElement = document.getElementById('countdown');
            const countdownInterval = setInterval(() => {
                timeLeft--;
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    logoutNow();
                }
            }, 1000);
            
            // Store interval for cleanup
            modal.dataset.countdownInterval = countdownInterval;
        }
        
        function extendSession() {
            const modal = document.getElementById('autoLogoutModal');
            if (modal) {
                clearInterval(modal.dataset.countdownInterval);
                modal.remove();
            }
            resetInactivityTimer();
        }
        
        function logoutNow() {
            const modal = document.getElementById('autoLogoutModal');
            if (modal) {
                clearInterval(modal.dataset.countdownInterval);
                modal.remove();
            }
            window.location.href = '{{ route('logout') }}';
        }
        
        ['click', 'keypress', 'scroll', 'mousemove', 'touchstart', 'touchmove'].forEach(event => {
            document.addEventListener(event, resetInactivityTimer, { passive: true });
        });
        
        resetInactivityTimer();

        // Handle logout with simple GET redirect (route handles authentication check)
        function handleLogout(event) {
            event.preventDefault();
            
            // Show confirmation dialog
            if (confirm('Are you sure you want to logout?')) {
                // Simple GET redirect - no CSRF token needed
                window.location.href = '{{ route('logout') }}';
            }
        }

        // Bind logout button click handler
        document.addEventListener('DOMContentLoaded', function() {
            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', handleLogout);
                console.log('[LAYOUT] Logout button listener attached');
            } else {
                console.warn('[LAYOUT] Logout button not found in DOM');
            }
            
            // Note: Online/offline toggle button is handled in call.blade.php
            // to ensure proper state management with the counter panel
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