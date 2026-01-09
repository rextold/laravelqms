@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', auth()->user()->isSuperAdmin() ? 'SuperAdmin Dashboard' : 'Admin Dashboard')

@section('content')
<div class="p-6 space-y-6">
    @if(auth()->user()->isSuperAdmin())
        <!-- SuperAdmin Dashboard -->
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-indigo-100 text-xs font-semibold uppercase tracking-wide mb-2">Total</p>
                        <p class="text-5xl font-bold text-white mb-1">{{ $companiesCount ?? 0 }}</p>
                        <p class="text-indigo-100 text-sm">Organizations</p>
                    </div>
                    <div class="p-4 bg-white bg-opacity-20 rounded-2xl backdrop-blur-sm">
                        <i class="fas fa-building text-white text-3xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-cyan-100 text-xs font-semibold uppercase tracking-wide mb-2">Total Admins</p>
                        <p class="text-5xl font-bold text-white mb-1">{{ \App\Models\User::where('role', 'admin')->count() }}</p>
                        <p class="text-cyan-100 text-sm">Company Admins</p>
                    </div>
                    <div class="p-4 bg-white bg-opacity-20 rounded-2xl backdrop-blur-sm">
                        <i class="fas fa-users-cog text-white text-3xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-purple-100 text-xs font-semibold uppercase tracking-wide mb-2">Total Counters</p>
                        <p class="text-5xl font-bold text-white mb-1">{{ \App\Models\User::where('role', 'counter')->count() }}</p>
                        <p class="text-purple-100 text-sm">All Counters</p>
                    </div>
                    <div class="p-4 bg-white bg-opacity-20 rounded-2xl backdrop-blur-sm">
                        <i class="fas fa-users text-white text-3xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-rose-500 to-rose-600 p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-rose-100 text-xs font-semibold uppercase tracking-wide mb-2">Total Users</p>
                        <p class="text-5xl font-bold text-white mb-1">{{ $usersCount ?? 0 }}</p>
                        <p class="text-rose-100 text-sm">Admins & Counters</p>
                    </div>
                    <div class="p-4 bg-white bg-opacity-20 rounded-2xl backdrop-blur-sm">
                        <i class="fas fa-globe text-white text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Management Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-tie text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Organization Admins</h2>
                            <p class="text-xs text-gray-500">Manage all organization administrators</p>
                        </div>
                    </div>
                    <a href="{{ route('superadmin.users.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                        <i class="fas fa-plus mr-2"></i>Add Admin
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase tracking-wider">Username</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase tracking-wider">Organization</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase tracking-wider">Counters</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $admins = \App\Models\User::where('role', 'admin')->with('company')->get();
                        @endphp
                        @forelse($admins as $admin)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                        {{ strtoupper(substr($admin->username, 0, 1)) }}
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $admin->username }}</p>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                                    {{ $admin->company?->company_name ?? 'No Organization' }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-sm text-gray-600">{{ $admin->email ?? '-' }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-2">
                                    <span class="text-lg font-bold text-gray-900">{{ \App\Models\User::where('company_id', $admin->company_id)->where('role', 'counter')->count() }}</span>
                                    <span class="text-xs text-gray-500">counters</span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('superadmin.users.edit', $admin->id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm transition">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button type="button" 
                                            data-admin-id="{{ $admin->id }}"
                                            data-admin-name="{{ $admin->username }}"
                                            data-delete-url="{{ route('superadmin.users.destroy', ['user' => $admin->id]) }}"
                                            onclick="openDeleteAdminModal(this)"
                                            class="text-red-600 hover:text-red-800 font-semibold text-sm transition">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 px-6 text-center">
                                <p class="text-gray-500">No admins found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Company Overview Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Organizations by Counter Count -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-chart-bar text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Top Organizations</h2>
                            <p class="text-xs text-gray-500">By counter count</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    @php
                        $companies = \App\Models\Company::with('users')
                            ->withCount(['users' => function($q) { $q->where('role', 'counter'); }])
                            ->orderBy('users_count', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp
                    @forelse($companies as $company)
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg hover:shadow-md transition">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $company->company_name }}</p>
                            <p class="text-xs text-gray-500">{{ $company->company_code }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-purple-600">{{ $company->users_count }}</p>
                            <p class="text-xs text-gray-500">counters</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 py-8">No organizations found</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Queue Activity -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-list-ul text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Queue Statistics</h2>
                            <p class="text-xs text-gray-500">Today's activity</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-list text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Total Queues</p>
                                <p class="text-xs text-gray-500">Processed today</p>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-blue-600">{{ \App\Models\Queue::whereDate('created_at', today())->count() }}</p>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-orange-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-orange-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Waiting</p>
                                <p class="text-xs text-gray-500">In queue now</p>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-orange-600">{{ \App\Models\Queue::where('status', 'waiting')->count() }}</p>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-emerald-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-emerald-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Completed</p>
                                <p class="text-xs text-gray-500">Today</p>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-emerald-600">{{ \App\Models\Queue::where('status', 'completed')->whereDate('created_at', today())->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- Regular Admin Dashboard -->
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                            <p class="text-emerald-100 text-xs font-semibold uppercase tracking-wide">Online Now</p>
                        </div>
                        <p class="text-5xl font-bold text-white mb-1">{{ $onlineCounters->count() }}</p>
                        <p class="text-emerald-100 text-sm">Active Counters</p>
                    </div>
                    <div class="p-4 bg-white bg-opacity-20 rounded-2xl backdrop-blur-sm">
                        <i class="fas fa-users text-white text-3xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-blue-100 text-xs font-semibold uppercase tracking-wide mb-2">Total</p>
                        <p class="text-5xl font-bold text-white mb-1">{{ $counters->count() }}</p>
                        <p class="text-blue-100 text-sm">All Counters</p>
                    </div>
                    <div class="p-4 bg-white bg-opacity-20 rounded-2xl backdrop-blur-sm">
                        <i class="fas fa-desktop text-white text-3xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-purple-100 text-xs font-semibold uppercase tracking-wide mb-2">Today</p>
                        <p class="text-5xl font-bold text-white mb-1">{{ \App\Models\Queue::whereDate('created_at', today())->count() }}</p>
                        <p class="text-purple-100 text-sm">Queues Processed</p>
                    </div>
                    <div class="p-4 bg-white bg-opacity-20 rounded-2xl backdrop-blur-sm">
                        <i class="fas fa-list text-white text-3xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-orange-500 to-orange-600 p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-orange-100 text-xs font-semibold uppercase tracking-wide mb-2">Waiting</p>
                        <p class="text-5xl font-bold text-white mb-1">{{ \App\Models\Queue::where('status', 'waiting')->count() }}</p>
                        <p class="text-orange-100 text-sm">In Queue</p>
                    </div>
                    <div class="p-4 bg-white bg-opacity-20 rounded-2xl backdrop-blur-sm">
                        <i class="fas fa-clock text-white text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Counter Status Table -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-list-ul text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Counter Status</h2>
                            <p class="text-xs text-gray-500">Real-time monitoring</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <i class="fas fa-circle text-green-500 animate-pulse"></i>
                        <span>Live</span>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase tracking-wider">Counter</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase tracking-wider">Display Name</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase tracking-wider">Queues Today</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($counters as $counter)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold">
                                        {{ $counter->counter_number }}
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-sm font-semibold text-gray-900">{{ $counter->display_name }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-sm text-gray-600">{{ $counter->short_description }}</p>
                            </td>
                            <td class="py-4 px-6">
                                @if($counter->is_online)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        <i class="fas fa-circle text-xs mr-1.5"></i> Online
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        <i class="far fa-circle text-xs mr-1.5"></i> Offline
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-2">
                                    <span class="text-2xl font-bold text-gray-900">{{ $counter->queues()->whereDate('created_at', today())->count() }}</span>
                                    <span class="text-xs text-gray-500">queues</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-admin-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="delete-admin-modal-content">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    <h3 class="text-xl font-bold text-white">Delete Admin</h3>
                </div>
                <button onclick="closeModal('delete-admin-modal')" class="text-white hover:text-gray-200 text-2xl leading-none">
                    &times;
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-2">Are you sure you want to delete this admin?</p>
            <p class="text-sm text-gray-500 mb-4">Admin: <strong id="delete-admin-name"></strong></p>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. All associated data will be preserved.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            <button onclick="closeModal('delete-admin-modal')" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">
                Cancel
            </button>
            <button type="button" onclick="confirmDeleteAdmin()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">
                <i class="fas fa-trash mr-2"></i>Delete Admin
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(modalId + '-content');
    
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(modalId + '-content');
    
    if (modal) {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
}

function openDeleteAdminModal(button) {
    const adminId = button.dataset.adminId;
    const adminName = button.dataset.adminName;
    const deleteUrl = button.dataset.deleteUrl;
    
    console.log('Opening delete modal with:', { adminId, adminName, deleteUrl });
    
    document.getElementById('delete-admin-name').textContent = adminName;
    window.currentDeleteUrl = deleteUrl;
    window.currentDeleteId = adminId;
    openModal('delete-admin-modal');
}

function confirmDeleteAdmin() {
    const url = window.currentDeleteUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (!csrfToken) {
        alert('Security token not found. Please refresh the page.');
        console.error('CSRF token not found');
        return;
    }
    
    if (!url) {
        alert('Delete URL not found. Please refresh the page.');
        console.error('Delete URL not set');
        return;
    }
    
    console.log('Deleting admin from URL:', url);
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok && response.status !== 204) {
            return response.text().then(text => {
                console.error('Server response:', text);
                throw new Error('Server returned ' + response.status);
            });
        }
        return response;
    })
    .then(response => {
        closeModal('delete-admin-modal');
        alert('Admin deleted successfully');
        window.location.reload();
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Error deleting admin: ' + error.message);
    });
}

// Close modal on outside click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('delete-admin-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal('delete-admin-modal');
            }
        });
    }
});
</script>
@endpush
@endsection
