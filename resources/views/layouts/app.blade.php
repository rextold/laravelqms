<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Queue Management System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
<body class="bg-gray-50">
    @if(auth()->check() && !request()->is('monitor*') && !request()->is('kiosk*'))
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar w-64 gradient-bg text-white flex-shrink-0 fixed h-full z-50 md:relative">
            <div class="h-full flex flex-col">
                <!-- Logo/Brand -->
                <div class="p-6 border-b border-white border-opacity-20">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-check text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold">QMS Admin</h1>
                            <p class="text-xs opacity-75">Management System</p>
                        </div>
                    </div>
                </div>

                <!-- User Info -->
                <div class="p-4 border-b border-white border-opacity-20">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="flex-1 min-w-0">
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
                                <span class="ml-3">Dashboard</span>
                            </a>
                            <a href="{{ route('superadmin.companies.index') }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('superadmin.companies.*') ? 'active' : '' }}">
                                <i class="fas fa-building w-5"></i>
                                <span class="ml-3">Organizations</span>
                            </a>
                            <a href="{{ route('superadmin.users.index') }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
                                <i class="fas fa-users w-5"></i>
                                <span class="ml-3">Users</span>
                            </a>
                        @elseif(auth()->user()->isAdmin())
                            <!-- Admin Menu: Full organization management -->
                            <a href="{{ route('admin.dashboard', ['company_code' => request()->route('company_code')]) }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-home w-5"></i>
                                <span class="ml-3">Dashboard</span>
                            </a>
                            <a href="{{ route('admin.company-settings.edit', ['company_code' => request()->route('company_code')]) }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('admin.company-settings.*') ? 'active' : '' }}">
                                <i class="fas fa-building w-5"></i>
                                <span class="ml-3">Organization Settings</span>
                            </a>
                            <a href="{{ route('admin.users.index', ['company_code' => request()->route('company_code')]) }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="fas fa-users w-5"></i>
                                <span class="ml-3">Users</span>
                            </a>
                            <a href="{{ route('admin.videos.index', ['company_code' => request()->route('company_code')]) }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('admin.videos.*') ? 'active' : '' }}">
                                <i class="fas fa-video w-5"></i>
                                <span class="ml-3">Videos & Display</span>
                            </a>
                            <a href="{{ route('admin.marquee.index', ['company_code' => request()->route('company_code')]) }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('admin.marquee.*') ? 'active' : '' }}">
                                <i class="fas fa-scroll w-5"></i>
                                <span class="ml-3">Marquee</span>
                            </a>
                            <div class="px-6 py-2 mt-4">
                                <p class="text-xs opacity-50 uppercase tracking-wider">External</p>
                            </div>
                            <a href="{{ route('monitor.index', ['company_code' => request()->route('company_code')]) }}" target="_blank" class="sidebar-link flex items-center px-6 py-3">
                                <i class="fas fa-tv w-5"></i>
                                <span class="ml-3">Monitor Display</span>
                                <i class="fas fa-external-link-alt ml-auto text-xs"></i>
                            </a>
                            <a href="{{ route('kiosk.index', ['company_code' => request()->route('company_code')]) }}" target="_blank" class="sidebar-link flex items-center px-6 py-3">
                                <i class="fas fa-tablet-alt w-5"></i>
                                <span class="ml-3">Kiosk</span>
                                <i class="fas fa-external-link-alt ml-auto text-xs"></i>
                            </a>
                        @elseif(auth()->user()->isCounter())
                            <a href="{{ route('counter.dashboard', ['company_code' => request()->route('company_code')]) }}" class="sidebar-link flex items-center px-6 py-3 {{ request()->routeIs('counter.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-chart-line w-5"></i>
                                <span class="ml-3">Dashboard</span>
                            </a>
                            <a href="{{ route('counter.panel', ['company_code' => request()->route('company_code')]) }}" target="_blank" class="sidebar-link flex items-center px-6 py-3">
                                <i class="fas fa-phone w-5"></i>
                                <span class="ml-3">Service Station</span>
                                <i class="fas fa-external-link-alt ml-auto text-xs"></i>
                            </a>
                        @endif
                    @endauth
                </nav>

                <!-- Logout -->
                <div class="p-4 border-t border-white border-opacity-20">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="sidebar-link flex items-center w-full px-4 py-3 rounded-lg hover:bg-red-500 hover:bg-opacity-20">
                            <i class="fas fa-sign-out-alt w-5"></i>
                            <span class="ml-3">Logout</span>
                        </button>
                    </form>
                </div>
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
                        <!-- Placeholder for future actions -->
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
    </script>
    @else
    <main>
        @yield('content')
    </main>
    @endif

    @stack('scripts')
</body>
</html>
