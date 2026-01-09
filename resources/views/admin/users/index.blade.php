@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
@php
    $routePrefix = auth()->user()->isSuperAdmin() ? 'superadmin' : 'admin';
@endphp
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Manage Users</h1>
        <a href="{{ auth()->user()->isSuperAdmin() ? route('superadmin.users.create') : route('admin.users.create', ['company_code' => request()->route('company_code')]) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add User
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left py-3 px-4">Username</th>
                    <th class="text-left py-3 px-4">Role</th>
                    @if(auth()->user()->isSuperAdmin())
                    <th class="text-left py-3 px-4">Company</th>
                    @endif
                    <th class="text-left py-3 px-4">Display Name</th>
                    <th class="text-left py-3 px-4">Counter #</th>
                    <th class="text-left py-3 px-4">Status</th>
                    <th class="text-left py-3 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4">{{ $user->username }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded text-sm
                            @if($user->role === 'superadmin') bg-red-100 text-red-800
                            @elseif($user->role === 'admin') bg-blue-100 text-blue-800
                            @else bg-green-100 text-green-800
                            @endif">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    @if(auth()->user()->isSuperAdmin())
                    <td class="py-3 px-4">
                        @if($user->company)
                            <span class="px-2 py-1 rounded text-sm bg-purple-100 text-purple-800">
                                {{ $user->company->company_code }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    @endif
                    <td class="py-3 px-4">{{ $user->display_name ?? '-' }}</td>
                    <td class="py-3 px-4">{{ $user->counter_number ?? '-' }}</td>
                    <td class="py-3 px-4">
                        @if($user->isCounter())
                            @if($user->is_online)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Online</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm">Offline</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex space-x-2">
                            <a href="{{ auth()->user()->isSuperAdmin() ? route('superadmin.users.edit', $user->id) : route('admin.users.edit', [request()->route('company_code'), $user->id]) }}" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </a>
                    @if($user->id !== auth()->id())
                            <button type="button" 
                                    data-user-id="{{ $user->id }}" 
                                    data-username="{{ $user->username }}"
                                    data-delete-url="{{ auth()->user()->isSuperAdmin() ? route('superadmin.users.destroy', ['user' => $user->id]) : route('admin.users.destroy', ['company_code' => request()->route('company_code'), 'user' => $user->id]) }}"
                                    onclick="openDeleteUserModal(this)"
                                    class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Delete User Modal -->
<div id="delete-user-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="delete-user-modal-content">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    <h3 class="text-xl font-bold text-white">Delete User</h3>
                </div>
                <button onclick="closeModal('delete-user-modal')" class="text-white hover:text-gray-200 text-2xl leading-none">
                    &times;
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-2">Are you sure you want to delete this user?</p>
            <p class="text-sm text-gray-500 mb-4">User: <strong id="delete-user-name"></strong></p>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            <button onclick="closeModal('delete-user-modal')" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">
                Cancel
            </button>
            <button type="button" onclick="confirmDeleteUser()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">
                <i class="fas fa-trash mr-2"></i>Delete User
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

function openDeleteUserModal(button) {
    const userId = button.dataset.userId;
    const username = button.dataset.username;
    const deleteUrl = button.dataset.deleteUrl;
    
    console.log('Opening delete modal with:', { userId, username, deleteUrl });
    console.log('Delete URL raw:', button.getAttribute('data-delete-url'));
    
    document.getElementById('delete-user-name').textContent = username;
    window.currentDeleteUrl = deleteUrl;
    window.currentDeleteId = userId;
    openModal('delete-user-modal');
}

function confirmDeleteUser() {
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
    
    console.log('Deleting user from URL:', url);
    
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
        closeModal('delete-user-modal');
        alert('User deleted successfully');
        window.location.reload();
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Error deleting user: ' + error.message);
    });
}

// Close modal on outside click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('delete-user-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal('delete-user-modal');
            }
        });
    }
});

</script>
@endpush
@endsection
