<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Queue Management System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>
<body class="bg-gray-100">
    @if(auth()->check() && !request()->is('monitor*') && !request()->is('kiosk*'))
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-bold">QMS</h1>
                    @auth
                        <span class="text-sm">{{ auth()->user()->display_name ?? auth()->user()->username }}</span>
                    @endauth
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-200">Dashboard</a>
                            <a href="{{ route('admin.users.index') }}" class="hover:text-blue-200">Users</a>
                            <a href="{{ route('admin.videos.index') }}" class="hover:text-blue-200">Videos</a>
                            <a href="{{ route('admin.marquee.index') }}" class="hover:text-blue-200">Marquee</a>
                            <a href="{{ route('monitor.index') }}" target="_blank" class="hover:text-blue-200">Monitor</a>
                        @elseif(auth()->user()->isCounter())
                            <a href="{{ route('counter.dashboard') }}" class="hover:text-blue-200">Dashboard</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="hover:text-blue-200">Logout</button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    @endif

    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
