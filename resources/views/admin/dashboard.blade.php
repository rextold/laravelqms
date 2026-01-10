@extends('layouts.app')

@section('title', auth()->user()->isSuperAdmin() ? 'SuperAdmin Dashboard' : 'Admin Dashboard')
@section('page-title', auth()->user()->isSuperAdmin() ? 'SuperAdmin Dashboard' : 'Admin Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    @php
        $isSuperAdmin = auth()->user()->isSuperAdmin();
    @endphp

    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-2xl shadow-xl p-8 mb-8 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-4xl font-bold mb-2">
                    Welcome back, {{ auth()->user()->username }}! 👋
                </h1>
                <p class="text-blue-100 text-lg">
                    @if($isSuperAdmin)
                        Manage all organizations, admins, and system settings from here.
                    @else
                        Manage your organization's counters and monitor queue activity.
                    @endif
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm text-blue-100 mb-1">{{ now()->format('l') }}</p>
                <p class="text-2xl font-bold">{{ now()->format('F j, Y') }}</p>
                <p class="text-sm text-blue-100">{{ now()->format('g:i A') }}</p>
            </div>
        </div>
    </div>

    @if($isSuperAdmin)
        <!-- SuperAdmin Dashboard -->
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Organizations -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-transform">
                            <i class="fas fa-building text-white text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-gray-800">{{ $organizationsCount ?? $companiesCount }}</p>
                        </div>
                    </div>
                    <h3 class="text-gray-600 font-semibold mb-1">Total Organizations</h3>
                    <div class="flex items-center text-sm text-green-600">
                        <i class="fas fa-check-circle mr-1"></i>
                        <span>Active organizations</span>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
            </div>

            <!-- Total Admins -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-transform">
                            <i class="fas fa-user-tie text-white text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-gray-800">{{ $adminsCount }}</p>
                        </div>
                    </div>
                    <h3 class="text-gray-600 font-semibold mb-1">Organization Admins</h3>
                    <div class="flex items-center text-sm text-purple-600">
                        <i class="fas fa-users-cog mr-1"></i>
                        <span>Organization administrators</span>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-purple-500 to-purple-600"></div>
            </div>

            <!-- Total Counters -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-transform">
                            <i class="fas fa-desktop text-white text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-gray-800">{{ $countersCount }}</p>
                        </div>
                    </div>
                    <h3 class="text-gray-600 font-semibold mb-1">Total Counters</h3>
                    <div class="flex items-center text-sm text-indigo-600">
                        <i class="fas fa-users mr-1"></i>
                        <span>All service counters</span>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-indigo-500 to-indigo-600"></div>
            </div>

            <!-- Total Users -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-transform">
                            <i class="fas fa-users text-white text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-gray-800">{{ $usersCount }}</p>
                        </div>
                    </div>
                    <h3 class="text-gray-600 font-semibold mb-1">Total Users</h3>
                    <div class="flex items-center text-sm text-pink-600">
                        <i class="fas fa-user-check mr-1"></i>
                        <span>Admins & Counters</span>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-pink-500 to-pink-600"></div>
            </div>
        </div>

        <!-- Queue Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-md p-6 text-white">
                <div class="flex items-center justify-between mb-3">
                    <i class="fas fa-ticket-alt text-4xl opacity-80"></i>
                    <div class="text-right">
                        <p class="text-5xl font-bold">{{ $todayQueues }}</p>
                    </div>
                </div>
                <h3 class="text-emerald-100 text-sm font-semibold uppercase tracking-wide mb-1">Today's Queues</h3>
                <p class="text-emerald-200 text-xs">Total processed today</p>
            </div>

            <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-md p-6 text-white">
                <div class="flex items-center justify-between mb-3">
                    <i class="fas fa-clock text-4xl opacity-80"></i>
                    <div class="text-right">
                        <p class="text-5xl font-bold">{{ $waitingQueues }}</p>
                    </div>
                </div>
                <h3 class="text-amber-100 text-sm font-semibold uppercase tracking-wide mb-1">Waiting</h3>
                <p class="text-amber-200 text-xs">In queue now</p>
            </div>

            <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl shadow-md p-6 text-white">
                <div class="flex items-center justify-between mb-3">
                    <i class="fas fa-user-clock text-4xl opacity-80"></i>
                    <div class="text-right">
                        <p class="text-5xl font-bold">{{ $servingNow }}</p>
                    </div>
                </div>
                <h3 class="text-cyan-100 text-sm font-semibold uppercase tracking-wide mb-1">Serving Now</h3>
                <p class="text-cyan-200 text-xs">Currently being served</p>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-md p-6 text-white">
                <div class="flex items-center justify-between mb-3">
                    <i class="fas fa-check-circle text-4xl opacity-80"></i>
                    <div class="text-right">
                        <p class="text-5xl font-bold">{{ $completedToday }}</p>
                    </div>
                </div>
                <h3 class="text-green-100 text-sm font-semibold uppercase tracking-wide mb-1">Completed</h3>
                <p class="text-green-200 text-xs">Finished today</p>
            </div>
        </div>

        <!-- Organization Admins & Top Organizations -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Organization Admins List -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-tie text-white"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">Organization Admins</h2>
                                <p class="text-xs text-gray-500">{{ $admins->count() }} total administrators</p>
                            </div>
                        </div>
                        <a href="{{ route('superadmin.users.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition font-medium text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Admin
                        </a>
                    </div>
                </div>
                <div class="p-6 max-h-96 overflow-y-auto">
                    @forelse($admins as $admin)
                        <div class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-lg mb-3 transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($admin->username, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $admin->username }}</p>
                                    <p class="text-xs text-gray-500">
                                        <i class="fas fa-building mr-1"></i>
                                        {{ $admin->organization?->organization_name ?? 'No Organization' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('superadmin.users.edit', $admin->id) }}" 
                                   class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        onclick="confirmDelete('{{ $admin->id }}', '{{ $admin->username }}', '{{ route('superadmin.users.destroy', $admin->id) }}')"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="fas fa-user-slash text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 font-medium">No admins found</p>
                            <p class="text-gray-400 text-sm">Add an administrator to get started</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Top Organizations -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-bar text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Top Organizations</h2>
                            <p class="text-xs text-gray-500">By counter count</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    @forelse($topOrganizations as $index => $org)
                        <div class="flex items-center justify-between p-4 {{ $index === 0 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200' : 'bg-gray-50' }} rounded-lg mb-3 hover:shadow-md transition">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 {{ $index === 0 ? 'bg-gradient-to-br from-yellow-400 to-yellow-500' : 'bg-gray-200' }} rounded-full flex items-center justify-center font-bold {{ $index === 0 ? 'text-white' : 'text-gray-600' }}">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $org->organization_name }}</p>
                                    <p class="text-xs text-gray-500">
                                        <i class="fas fa-code mr-1"></i>{{ $org->organization_code }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-bold text-blue-600">{{ $org->users_count }}</p>
                                <p class="text-xs text-gray-500">counters</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="fas fa-building text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 font-medium">No organizations found</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    @else
        <!-- Regular Admin Dashboard -->
        
        <!-- Organization Info Banner -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8 border-l-4 border-blue-600">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center space-x-4">
                    @if($organization->setting?->logo_url)
                        <img src="{{ $organization->setting->logo_url }}" alt="Logo" class="h-16 w-16 object-contain">
                    @else
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-building text-white text-2xl"></i>
                        </div>
                    @endif
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ $organization->organization_name }}</h2>
                        <p class="text-gray-600">{{ $organization->organization_code }}</p>
                        @if($organization->setting)
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                @if($organization->setting->company_phone)
                                    <span><i class="fas fa-phone mr-1"></i>{{ $organization->setting->company_phone }}</span>
                                @endif
                                @if($organization->setting->company_email)
                                    <span><i class="fas fa-envelope mr-1"></i>{{ $organization->setting->company_email }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                <a href="{{ route('admin.organization-settings.edit', ['organization_code' => $organization->organization_code]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                    <i class="fas fa-cog mr-2"></i>Organization Settings
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Online Counters -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-transform relative">
                            <i class="fas fa-users text-white text-2xl"></i>
                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full animate-ping"></span>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-gray-800">{{ $onlineCounters->count() }}</p>
                        </div>
                    </div>
                    <h3 class="text-gray-600 font-semibold mb-1">Online Now</h3>
                    <div class="flex items-center text-sm text-green-600">
                        <i class="fas fa-circle mr-1 text-xs animate-pulse"></i>
                        <span>Active counters</span>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-green-500 to-green-600"></div>
            </div>

            <!-- Total Counters -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-transform">
                            <i class="fas fa-desktop text-white text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-gray-800">{{ $counters->count() }}</p>
                        </div>
                    </div>
                    <h3 class="text-gray-600 font-semibold mb-1">Total Counters</h3>
                    <div class="flex items-center text-sm text-blue-600">
                        <i class="fas fa-users mr-1"></i>
                        <span>All service points</span>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
            </div>

            <!-- Today's Queues -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-transform">
                            <i class="fas fa-ticket-alt text-white text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-gray-800">{{ $todayQueues }}</p>
                        </div>
                    </div>
                    <h3 class="text-gray-600 font-semibold mb-1">Today's Queues</h3>
                    <div class="flex items-center text-sm text-purple-600">
                        <i class="fas fa-calendar-day mr-1"></i>
                        <span>Processed today</span>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-purple-500 to-purple-600"></div>
            </div>

            <!-- Waiting -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-transform">
                            <i class="fas fa-clock text-white text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-gray-800">{{ $waitingQueues }}</p>
                        </div>
                    </div>
                    <h3 class="text-gray-600 font-semibold mb-1">Waiting</h3>
                    <div class="flex items-center text-sm text-amber-600">
                        <i class="fas fa-hourglass-half mr-1"></i>
                        <span>In queue now</span>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-amber-500 to-amber-600"></div>
            </div>
        </div>

        <!-- Additional Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Serving Now -->
            <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl shadow-md p-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-cyan-100 text-sm font-semibold uppercase tracking-wide mb-2">Currently Serving</p>
                        <p class="text-6xl font-bold mb-2">{{ $servingNow }}</p>
                        <p class="text-cyan-100">Active service sessions</p>
                    </div>
                    <i class="fas fa-user-clock text-cyan-200 text-7xl opacity-20"></i>
                </div>
            </div>

            <!-- Completed Today -->
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-md p-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-emerald-100 text-sm font-semibold uppercase tracking-wide mb-2">Completed Today</p>
                        <p class="text-6xl font-bold mb-2">{{ $completedToday }}</p>
                        <p class="text-emerald-100">Successfully processed</p>
                    </div>
                    <i class="fas fa-check-circle text-emerald-200 text-7xl opacity-20"></i>
                </div>
            </div>
        </div>

        <!-- Counters Overview -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-desktop text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Counter Status</h2>
                            <p class="text-xs text-gray-500">{{ $counters->count() }} total counters</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.index', ['organization_code' => $organization->organization_code]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium text-sm">
                        <i class="fas fa-plus mr-2"></i>Add Counter
                    </a>
                </div>
            </div>
            <div class="p-6">
                @if($counters->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($counters as $counter)
                            <div class="p-4 border-2 {{ $onlineCounters->contains('id', $counter->id) ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }} rounded-lg hover:shadow-md transition">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                                            {{ $counter->counter_number }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800">{{ $counter->display_name }}</p>
                                            <p class="text-xs text-gray-500">{{ $counter->username }}</p>
                                        </div>
                                    </div>
                                    @if($onlineCounters->contains('id', $counter->id))
                                        <span class="px-2 py-1 bg-green-500 text-white text-xs font-semibold rounded-full flex items-center">
                                            <span class="w-2 h-2 bg-white rounded-full mr-1 animate-pulse"></span>
                                            Online
                                        </span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-400 text-white text-xs font-semibold rounded-full">Offline</span>
                                    @endif
                                </div>
                                @if($counter->short_description)
                                    <p class="text-sm text-gray-600">{{ $counter->short_description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-desktop text-gray-300 text-5xl mb-4"></i>
                        <p class="text-gray-500 font-medium mb-2">No counters found</p>
                        <p class="text-gray-400 text-sm mb-4">Add counters to start managing your queue system</p>
                        <a href="{{ route('admin.users.create', ['organization_code' => $organization->organization_code]) }}" 
                           class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                            <i class="fas fa-plus mr-2"></i>Add Your First Counter
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

@if($isSuperAdmin)
<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4 rounded-t-xl">
            <h3 class="text-xl font-bold text-white flex items-center">
                <i class="fas fa-exclamation-triangle mr-3"></i>Delete Admin
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-700 mb-4">Are you sure you want to delete this admin?</p>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-gray-700">
                    <strong>Admin:</strong> <span id="deleteUsername" class="font-semibold"></span>
                </p>
                <p class="text-xs text-gray-600 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    This action cannot be undone.
                </p>
            </div>
        </div>
        <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3">
            <button type="button" 
                    onclick="closeDeleteModal()" 
                    class="px-5 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
            <button type="button" 
                    onclick="submitDelete()" 
                    class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-medium">
                <i class="fas fa-trash mr-2"></i>Delete Admin
            </button>
        </div>
    </div>
</div>

<!-- Hidden delete form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
let currentDeleteUrl = '';

function confirmDelete(id, username, deleteUrl) {
    currentDeleteUrl = deleteUrl;
    document.getElementById('deleteUsername').textContent = username;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteModal').classList.remove('flex');
    document.body.style.overflow = '';
    currentDeleteUrl = '';
}

function submitDelete() {
    if (!currentDeleteUrl) {
        alert('Error: No delete URL specified.');
        return;
    }
    
    const form = document.getElementById('deleteForm');
    form.action = currentDeleteUrl;
    form.submit();
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});

// Close modal on backdrop click
document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endpush
@endif
@endsection
