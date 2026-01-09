@extends('layouts.app')

@section('title', auth()->user()->isSuperAdmin() ? 'Manage Admins' : 'Manage Counters')
@section('page-title', auth()->user()->isSuperAdmin() ? 'Manage Admins' : 'Manage Counters')

@section('content')
@php
    $isSuperAdmin = auth()->user()->isSuperAdmin();
    $authUser = auth()->user();
    $orgCode = request()->route('organization_code') ?? $authUser->organization?->organization_code;
@endphp

<div class="container mx-auto px-4 py-8">
    @if(!$orgCode && !$isSuperAdmin)
        <div class="bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 rounded-lg p-4 mb-6 shadow-md">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-4"></i>
                <div>
                    <p class="font-bold text-red-800">Organization Error</p>
                    <p class="text-red-700 text-sm">Your account is not properly assigned to an organization. Please contact the system administrator.</p>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-xl shadow-xl p-6 mb-8">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                    <i class="fas {{ $isSuperAdmin ? 'fa-user-tie' : 'fa-users' }} text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white">{{ $isSuperAdmin ? 'Manage Admins' : 'Manage Counters' }}</h1>
                    <p class="text-blue-100 mt-1">{{ $users->count() }} total {{ $isSuperAdmin ? 'administrator' : 'counter' }}{{ $users->count() !== 1 ? 's' : '' }}</p>
                </div>
            </div>
            <a href="{{ $isSuperAdmin ? route('superadmin.users.create') : route('admin.users.create', ['organization_code' => $orgCode]) }}" 
               class="inline-flex items-center px-6 py-3 bg-white text-blue-600 rounded-lg hover:shadow-2xl hover:scale-105 transition-all duration-200 font-semibold">
                <i class="fas fa-plus mr-2"></i>{{ $isSuperAdmin ? 'Add Admin' : 'Add Counter' }}
            </a>
        </div>
    </div>

    <!-- Users Grid/List -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        @if($users->isEmpty())
            <div class="p-16 text-center">
                <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-gray-400 text-5xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No {{ $isSuperAdmin ? 'Admins' : 'Counters' }} Found</h3>
                <p class="text-gray-500 mb-6">Get started by creating your first {{ $isSuperAdmin ? 'administrator' : 'counter' }}</p>
                <a href="{{ $isSuperAdmin ? route('superadmin.users.create') : route('admin.users.create', ['organization_code' => $orgCode]) }}" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:shadow-lg transition font-semibold">
                    <i class="fas fa-plus mr-2"></i>Create {{ $isSuperAdmin ? 'Admin' : 'Counter' }}
                </a>
            </div>
        @else
            <!-- Table View -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <th class="text-left py-4 px-6 font-bold text-gray-700 uppercase text-xs tracking-wider">
                                <i class="fas fa-user mr-2 text-blue-600"></i>User
                            </th>
                            <th class="text-left py-4 px-6 font-bold text-gray-700 uppercase text-xs tracking-wider">
                                <i class="fas fa-shield-alt mr-2 text-purple-600"></i>Role
                            </th>
                            @if($isSuperAdmin)
                            <th class="text-left py-4 px-6 font-bold text-gray-700 uppercase text-xs tracking-wider">
                                <i class="fas fa-building mr-2 text-indigo-600"></i>Organization
                            </th>
                            @endif
                            <th class="text-left py-4 px-6 font-bold text-gray-700 uppercase text-xs tracking-wider">
                                <i class="fas fa-id-card mr-2 text-green-600"></i>Display Info
                            </th>
                            <th class="text-left py-4 px-6 font-bold text-gray-700 uppercase text-xs tracking-wider">
                                <i class="fas fa-hashtag mr-2 text-orange-600"></i>Counter
                            </th>
                            <th class="text-left py-4 px-6 font-bold text-gray-700 uppercase text-xs tracking-wider">
                                <i class="fas fa-circle mr-2 text-green-600"></i>Status
                            </th>
                            <th class="text-left py-4 px-6 font-bold text-gray-700 uppercase text-xs tracking-wider">
                                <i class="fas fa-cog mr-2 text-gray-600"></i>Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                        <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md group-hover:scale-110 transition-transform">
                                        {{ strtoupper(substr($user->username, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $user->username }}</p>
                                        @if($user->email)
                                            <p class="text-xs text-gray-500 flex items-center mt-0.5">
                                                <i class="fas fa-envelope mr-1"></i>{{ $user->email }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-sm
                                    @if($user->role === 'superadmin') bg-gradient-to-r from-red-500 to-red-600 text-white
                                    @elseif($user->role === 'admin') bg-gradient-to-r from-blue-500 to-blue-600 text-white
                                    @else bg-gradient-to-r from-green-500 to-green-600 text-white
                                    @endif">
                                    <i class="fas {{ $user->role === 'superadmin' ? 'fa-crown' : ($user->role === 'admin' ? 'fa-user-shield' : 'fa-user') }} mr-1.5"></i>
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            @if($isSuperAdmin)
                            <td class="py-4 px-6">
                                @if($user->organization)
                                    <div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-building mr-1.5"></i>{{ $user->organization->organization_name }}
                                        </span>
                                        <p class="text-xs text-gray-500 mt-1 flex items-center">
                                            <i class="fas fa-code mr-1"></i>{{ $user->organization->organization_code }}
                                        </p>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">No organization</span>
                                @endif
                            </td>
                            @endif
                            <td class="py-4 px-6">
                                @if($user->display_name)
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-id-badge text-green-600"></i>
                                        <span class="text-gray-900 font-medium">{{ $user->display_name }}</span>
                                    </div>
                                    @if($user->short_description)
                                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($user->short_description, 40) }}</p>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">Not set</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($user->counter_number)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow">
                                            {{ $user->counter_number }}
                                        </div>
                                        @if($user->priority_code)
                                            <span class="px-2 py-0.5 bg-orange-100 text-orange-800 rounded text-xs font-semibold">
                                                {{ $user->priority_code }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">N/A</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($user->isCounter())
                                    @if($user->is_online)
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                            <span class="w-2 h-2 mr-2 rounded-full bg-green-600 animate-pulse"></span>
                                            Online
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                            <span class="w-2 h-2 mr-2 rounded-full bg-gray-400"></span>
                                            Offline
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">N/A</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-2">
                                    @can('update', $user)
                                        <a href="{{ $isSuperAdmin ? route('superadmin.users.edit', $user) : route('admin.users.edit', ['organization_code' => $orgCode, 'user' => $user]) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white rounded-lg transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md"
                                           title="Edit {{ $user->username }}">
                                            <i class="fas fa-edit mr-1.5"></i>
                                            Edit
                                        </a>
                                    @endcan
                                    
                                    @can('delete', $user)
                                        <button type="button"
                                                onclick="confirmDeleteUser('{{ $user->id }}', '{{ $user->username }}', '{{ $user->role }}', '{{ $isSuperAdmin ? route('superadmin.users.destroy', $user) : route('admin.users.destroy', ['organization_code' => $orgCode, 'user' => $user]) }}')"
                                                class="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 hover:bg-red-600 hover:text-white rounded-lg transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md"
                                                title="Delete {{ $user->username }}">
                                            <i class="fas fa-trash mr-1.5"></i>
                                            Delete
                                        </button>
                                    @endcan
                                    
                                    @cannot('update', $user)
                                        @cannot('delete', $user)
                                            <span class="text-gray-400 text-sm italic flex items-center">
                                                <i class="fas fa-lock mr-1"></i>No access
                                            </span>
                                        @endcannot
                                    @endcannot
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 transform transition-all scale-95" id="modalContent">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-600 via-red-600 to-red-700 px-6 py-5 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white" id="deleteModalTitle">Delete User</h3>
                </div>
                <button type="button" onclick="closeDeleteModal()" class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white hover:bg-opacity-10 rounded-lg">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Body -->
        <div class="p-8">
            <p class="text-gray-700 text-lg mb-6" id="deleteModalMessage">Are you sure you want to delete this user?</p>
            
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-5 mb-6 border border-gray-200">
                <div class="flex items-center space-x-4 mb-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md">
                        <span id="deleteUserInitials"></span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-600 mb-1">Username</p>
                        <p class="text-gray-900 font-bold text-xl" id="deleteUsername"></p>
                    </div>
                </div>
                <div id="deleteRoleBadge" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold"></div>
            </div>
            
            <div class="bg-gradient-to-r from-red-50 to-orange-50 border-l-4 border-red-500 rounded-lg p-5">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-red-900 font-bold mb-1 text-sm">⚠️ Warning: Permanent Action</p>
                        <p class="text-red-800 text-sm leading-relaxed">This action cannot be undone. All data associated with this user will be permanently deleted from the system.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="bg-gray-50 px-8 py-5 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            <button type="button" 
                    onclick="closeDeleteModal()" 
                    class="px-6 py-3 text-gray-700 bg-white border-2 border-gray-300 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition-all font-semibold">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
            <button type="button" 
                    onclick="submitDelete()" 
                    class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-xl transition-all font-semibold shadow-lg hover:shadow-xl">
                <i class="fas fa-trash mr-2"></i>Delete User
            </button>
        </div>
    </div>
</div>

<!-- Hidden form for delete submission -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
let currentDeleteUrl = '';
let currentDeleteRole = '';

function confirmDeleteUser(userId, username, role, deleteUrl) {
    currentDeleteUrl = deleteUrl;
    currentDeleteRole = role;
    
    // Set username and initials
    document.getElementById('deleteUsername').textContent = username;
    document.getElementById('deleteUserInitials').textContent = username.substring(0, 2).toUpperCase();
    
    // Set role badge with appropriate styling
    const roleBadge = document.getElementById('deleteRoleBadge');
    let badgeClass = '';
    let roleIcon = '';
    let roleText = '';
    
    if (role === 'superadmin') {
        badgeClass = 'bg-gradient-to-r from-red-500 to-red-600 text-white';
        roleIcon = 'fa-crown';
        roleText = 'SuperAdmin';
    } else if (role === 'admin') {
        badgeClass = 'bg-gradient-to-r from-blue-500 to-blue-600 text-white';
        roleIcon = 'fa-user-shield';
        roleText = 'Admin';
    } else {
        badgeClass = 'bg-gradient-to-r from-green-500 to-green-600 text-white';
        roleIcon = 'fa-user';
        roleText = 'Counter';
    }
    
    roleBadge.className = `inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold ${badgeClass}`;
    roleBadge.innerHTML = `<i class="fas ${roleIcon} mr-1.5"></i>${roleText}`;
    
    // Update modal title and message based on role
    const modalTitle = role === 'admin' ? 'Delete Admin' : (role === 'counter' ? 'Delete Counter' : 'Delete User');
    const modalMessage = `Are you sure you want to delete this ${roleText.toLowerCase()}?`;
    
    document.getElementById('deleteModalTitle').textContent = modalTitle;
    document.getElementById('deleteModalMessage').textContent = modalMessage;
    
    // Show modal with animation
    const modal = document.getElementById('deleteModal');
    const modalContent = document.getElementById('modalContent');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    
    // Trigger animation
    setTimeout(() => {
        modalContent.style.transform = 'scale(1)';
    }, 10);
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    const modalContent = document.getElementById('modalContent');
    
    // Animate out
    modalContent.style.transform = 'scale(0.95)';
    
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        currentDeleteUrl = '';
        currentDeleteRole = '';
    }, 200);
}

function submitDelete() {
    if (!currentDeleteUrl) {
        showToast('error', 'Error: No delete URL specified.');
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

// Show flash messages as toast notifications
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        showToast('success', '{{ session('success') }}');
    @endif
    
    @if(session('error'))
        showToast('error', '{{ session('error') }}');
    @endif
});

function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 transform transition-all duration-300 translate-x-0 max-w-md`;
    
    const bgColor = type === 'success' ? 'from-green-500 to-green-600' : 'from-red-500 to-red-600';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    toast.innerHTML = `
        <div class="bg-gradient-to-r ${bgColor} text-white px-6 py-4 rounded-xl shadow-2xl">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas ${icon} text-xl"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="font-semibold">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-white hover:text-gray-200 hover:bg-white hover:bg-opacity-10 rounded-lg p-2 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(400px)';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}
</script>
@endpush
@endsection
