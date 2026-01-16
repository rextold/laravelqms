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
    <script>
        // Global AJAX setup for CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar-link {
            transition: all 0.3s ease;
        }
        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .sidebar-link.active {
            background: rgba(255, 255, 255, 0.15);
            border-left: 4px solid #fff;
        }
        .external-link {
            background: rgba(255, 255, 255, 0.08);
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        .external-link:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        .external-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            font-size: 0.65rem;
        }
        /* Top navbar quick buttons styling */
        .nav-quick {
            background: rgba(102, 126, 234, 0.10);
            border: 1px solid rgba(118, 75, 162, 0.30);
            color: #374151;
            transition: all 0.2s ease;
        }
        .nav-quick:hover {
            background: rgba(102, 126, 234, 0.18);
            border-color: rgba(118, 75, 162, 0.45);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
        }
        .nav-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            background: rgba(102, 126, 234, 0.25);
            border-radius: 50%;
            font-size: 0.60rem;
            margin-left: 0.5rem;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .sidebar nav::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .sidebar nav {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        /* Collapsed sidebar styles */
        .sidebar {
            transition: width 0.3s ease;
        }
        .sidebar.collapsed {
            width: 5rem;
        }
        .sidebar.collapsed .sidebar-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        .sidebar.collapsed .logo-text {
            display: none;
        }
        .sidebar.collapsed .user-info-text {
            display: none;
        }
        .sidebar.collapsed .sidebar-link {
            justify-content: center;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        .sidebar.collapsed .external-label {
            display: none;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.mobile-open {
                transform: translateX(0);
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50" data-organization-code="{{ request()->route('organization_code') ?? (auth()->user() && auth()->user()->organization ? auth()->user()->organization->organization_code : '') }}">
    @if(auth()->check() && !request()->is('monitor*') && !request()->is('kiosk*'))
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar w-64 gradient-bg text-white flex-shrink-0 fixed h-full z-50 md:relative">
            <div class="h-full flex flex-col">
                @php
                    // Get organization code from URL or from user's organization, fallback to default
                    $orgCode = request()->route('organization_code');
                    if (!$orgCode && auth()->user() && auth()->user()->organization) {
                        $orgCode = auth()->user()->organization->organization_code;
                    }
                    if (!$orgCode) {
                        // Fallback to first organization code in DB if still missing
                        $org = \App\Models\Organization::first();
                        $orgCode = $org ? $org->organization_code : 'default';
                    }
                    $canAccountSettings = auth()->check() && !auth()->user()->isSuperAdmin() && !empty($orgCode);
                @endphp
            <div class="h-full flex flex-col">
                <!-- Logo/Brand -->
                <div class="p-6 border-b border-white border-opacity-20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center org-logo overflow-hidden" data-org-logo>
                                @if(isset($organization) && $organization->setting && $organization->setting->company_logo)
                                    <img src="{{ asset('storage/' . $organization->setting->company_logo) }}" alt="Logo" class="w-full h-full object-contain p-1" data-org-logo>
                                @else
                                    <i class="fas fa-calendar-check text-2xl"></i>
                                @endif
                            </div>
                            <div class="logo-text">
                                <h1 class="text-xl font-bold" data-org-name>{{ $organization->organization_name ?? 'QMS Admin' }}</h1>
                                <p class="text-xs opacity-75">Management System</p>
                            </div>
                        </div>
                        <button id="sidebarToggle" class="text-white hover:bg-white hover:bg-opacity-10 p-2 rounded transition-all">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>

                <!-- User Info -->
                <div class="p-4 border-b border-white border-opacity-20">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="flex-1 min-w-0 user-info-text">
                            <p class="text-sm font-semibold truncate">{{ auth()->user()->display_name ?? auth()->user()->username }}</p>
                            <p class="text-xs opacity-75 capitalize">{{ auth()->user()->role }}</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-4">
                    @auth
                        @if(auth()->user()->isSuperAdmin())
                            <!-- SuperAdmin Menu: Only Users and Organizations -->
                            <a href="{{ route('superadmin.dashboard') }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-home w-5"></i>
                                <span class="ml-3 sidebar-text">Dashboard</span>
                            </a>
                            <a href="{{ route('superadmin.organizations.index') }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('superadmin.organizations.*') ? 'active' : '' }}">
                                <i class="fas fa-building w-5"></i>
                                <span class="ml-3 sidebar-text">Organizations</span>
                            </a>
                            <a href="{{ route('superadmin.users.index') }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
                                <i class="fas fa-users w-5"></i>
                                <span class="ml-3 sidebar-text">Users</span>
                            </a>
                        @elseif(auth()->user()->isAdmin())
                            <!-- Admin Menu: Full organization management -->
                            <a href="{{ route('admin.dashboard', ['organization_code' => $orgCode]) }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-home w-5"></i>
                                <span class="ml-3 sidebar-text">Dashboard</span>
                            </a>
                            <a href="{{ route('admin.organization-settings.edit', ['organization_code' => $orgCode]) }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('admin.organization-settings.*') ? 'active' : '' }}">
                                <i class="fas fa-building w-5"></i>
                                <span class="ml-3 sidebar-text">Organization Settings</span>
                            </a>
                            <a href="{{ route('admin.users.index', ['organization_code' => $orgCode]) }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="fas fa-users w-5"></i>
                                <span class="ml-3 sidebar-text">Manage Counters/Teller</span>
                            </a>
                            <a href="{{ route('admin.videos.index', ['organization_code' => $orgCode]) }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('admin.videos.*') ? 'active' : '' }}">
                                <i class="fas fa-video w-5"></i>
                                <span class="ml-3 sidebar-text">Videos & Display</span>
                            </a>
                            <a href="{{ route('admin.marquee.index', ['organization_code' => $orgCode]) }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('admin.marquee.*') ? 'active' : '' }}">
                                <i class="fas fa-scroll w-5"></i>
                                <span class="ml-3 sidebar-text">Marquee</span>
                            </a>
                            <!-- External Links Section -->
                            <div class="px-6 py-4 mt-6">
                                <div class="flex items-center gap-2 mb-3">
                                    <i class="fas fa-link text-xs opacity-60"></i>
                                    <p class="text-xs opacity-60 uppercase tracking-wider font-semibold">Quick Access</p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <a href="{{ route('monitor.index', ['organization_code' => $orgCode]) }}" target="_blank" class="external-link sidebar-link flex items-center justify-between px-4 py-3 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-tv w-5"></i>
                                            <span class="ml-1 sidebar-text text-sm font-medium">Monitor</span>
                                        </div>
                                        <span class="external-badge">
                                            <i class="fas fa-external-link-alt"></i>
                                        </span>
                                    </a>
                                    <a href="{{ route('kiosk.index', ['organization_code' => $orgCode]) }}" target="_blank" class="external-link sidebar-link flex items-center justify-between px-4 py-3 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-tablet-alt w-5"></i>
                                            <span class="ml-1 sidebar-text text-sm font-medium">Kiosk</span>
                                        </div>
                                        <span class="external-badge">
                                            <i class="fas fa-external-link-alt"></i>
                                        </span>
                                    </a>
                                </div>
                            </div>                          @elseif(auth()->user()->isCounter())
                              <a href="{{ route('counter.panel', ['organization_code' => $orgCode]) }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('counter.panel') ? 'active' : '' }}">
                                  <i class="fas fa-phone-alt w-5"></i>
                                  <span class="ml-3 sidebar-text">Service Panel</span>
                              </a>
                            <a href="{{ route('counter.panel', ['organization_code' => $orgCode]) }}" class="sidebar-link flex items-center px-6 py-3">
                                <i class="fas fa-phone w-5"></i>
                                <span class="ml-3 sidebar-text">Service Station</span>
                                <i class="fas fa-external-link-alt ml-auto text-xs external-label"></i>
                            </a>
                        @endif
                    @endauth
                </nav>

                <!-- Empty space to push logout to bottom -->
                <div class="flex-1"></div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm z-10">
                <div class="px-6 py-4 flex items-center justify-between">
                    <button onclick="toggleSidebar()" class="md:hidden text-gray-600 hover:text-gray-900">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    @php($defaultPageTitle = (auth()->check() && auth()->user()->isCounter()) ? 'Counter Panel' : 'Dashboard')
                    <h2 class="text-2xl font-bold text-gray-800">@yield('page-title', $defaultPageTitle)</h2>
                    <div class="flex items-center space-x-4">
                        @if(auth()->check() && auth()->user()->isAdmin() && !empty($orgCode))
                        <a href="{{ route('monitor.index', ['organization_code' => $orgCode]) }}" target="_blank" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium nav-quick">
                            <i class="fas fa-tv mr-2"></i>
                            Monitor
                            <span class="nav-badge"><i class="fas fa-external-link-alt"></i></span>
                        </a>
                        <a href="{{ route('kiosk.index', ['organization_code' => $orgCode]) }}" target="_blank" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium nav-quick">
                            <i class="fas fa-tablet-alt mr-2"></i>
                            Kiosk
                            <span class="nav-badge"><i class="fas fa-external-link-alt"></i></span>
                        </a>
                        @endif
                        <!-- User Profile Dropdown -->
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-user-circle text-xl"></i>
                                <span class="hidden md:inline font-medium">{{ auth()->user()->display_name ?? auth()->user()->username }}</span>
                                <i class="fas fa-chevron-down text-sm hidden md:inline"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 mt-0 w-48 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <!-- Account Settings -->
                                @if($canAccountSettings)
                                    <a href="{{ route('account.settings', ['organization_code' => $orgCode]) }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 border-b border-gray-200 rounded-t-lg">
                                        <i class="fas fa-user-cog w-4 mr-3 text-blue-600"></i>
                                        <span>Account Settings</span>
                                    </a>
                                @endif

                                <!-- Logout -->
                                <a href="#" onclick="handleLogout(event)" class="w-full flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 rounded-b-lg transition-colors">
                                    <i class="fas fa-sign-out-alt w-4 mr-3 text-red-600"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        }

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

        // Sidebar collapse/expand functionality
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        // Load saved state from localStorage
        const sidebarState = localStorage.getItem('sidebarCollapsed');
        if (sidebarState === 'true') {
            sidebar.classList.add('collapsed');
        }
        
        // Toggle sidebar on button click
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('collapsed');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });

        // Handle logout with GET method
        function handleLogout(event) {
            event.preventDefault();
            // Simply redirect to the logout route using GET method
            window.location.href = '{{ route('logout') }}';
        }
    </script>
    {{-- <script src="{{ asset('js/settings-sync.js') }}"></script> --}}
    @else
    <main>
        @yield('content')
    </main>
    @endif    <!-- Authentication Error Modal -->
    @include('components.auth-error-modal')

    @stack('scripts')
</body>
</html>