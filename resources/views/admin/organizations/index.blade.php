@extends('layouts.app')

@section('title', 'Manage Organizations')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Manage Organizations</h1>
        <div class="flex items-center gap-3">
            <button id="resetAllBtn" onclick="confirmResetAll()" class="bg-yellow-50 text-yellow-700 px-4 py-2 rounded hover:bg-yellow-100">
                <i class="fas fa-redo mr-2"></i>Reset All Sequences
            </button>
            <a href="{{ route('superadmin.organizations.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add Organization
            </a>
        </div>
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
                    <th class="text-left py-3 px-4">Organization Code</th>
                    <th class="text-left py-3 px-4">Organization Name</th>
                    <th class="text-left py-3 px-4">Users Count</th>
                    <th class="text-left py-3 px-4">Status</th>
                    <th class="text-left py-3 px-4">Created Date</th>
                    <th class="text-left py-3 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($organizations as $organization)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4">
                        <span class="font-semibold text-blue-600">{{ $organization->organization_code }}</span>
                    </td>
                    <td class="py-3 px-4">{{ $organization->organization_name }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded text-sm bg-gray-100 text-gray-800">
                            {{ $organization->users()->count() }} users
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        @if($organization->is_active)
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Active</span>
                        @else
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">Inactive</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">{{ $organization->created_at->format('M d, Y') }}</td>
                    <td class="py-3 px-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('superadmin.organizations.edit', $organization->id) }}" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($organization->users()->count() == 0)
                                <button type="button" 
                                        data-org-id="{{ $organization->id }}"
                                        data-org-name="{{ $organization->organization_name }}"
                                        data-delete-url="{{ route('superadmin.organizations.destroy', ['organization' => $organization->id]) }}"
                                        onclick="openDeleteOrgModal(this)"
                                        class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @else
                            <span class="text-gray-400" title="Cannot delete organization with users">
                                <i class="fas fa-trash"></i>
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 px-4 text-center text-gray-500">
                        No organizations found. Create your first organization to get started.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Organization Modal -->
<div id="delete-org-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="delete-org-modal-content">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    <h3 class="text-xl font-bold text-white">Delete Organization</h3>
                </div>
                <button onclick="closeModal('delete-org-modal')" class="text-white hover:text-gray-200 text-2xl leading-none">
                    &times;
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-2">Are you sure you want to delete this organization?</p>
            <p class="text-sm text-gray-500 mb-4">Organization: <strong id="delete-org-name"></strong></p>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            <button onclick="closeModal('delete-org-modal')" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">
                Cancel
            </button>
            <button type="button" onclick="confirmDeleteOrg()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">
                <i class="fas fa-trash mr-2"></i>Delete Organization
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

function openDeleteOrgModal(button) {
    const orgId = button.dataset.orgId;
    const orgName = button.dataset.orgName;
    const deleteUrl = button.dataset.deleteUrl;
    
    console.log('Opening delete org modal with:', { orgId, orgName, deleteUrl });
    
    document.getElementById('delete-org-name').textContent = orgName;
    window.currentDeleteUrl = deleteUrl;
    window.currentDeleteId = orgId;
    openModal('delete-org-modal');
}

function confirmDeleteOrg() {
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
    
    console.log('Deleting organization from URL:', url);
    
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
        closeModal('delete-org-modal');
        alert('Organization deleted successfully');
        window.location.reload();
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Error deleting organization: ' + error.message);
    });
}

// Close modal on outside click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('delete-org-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal('delete-org-modal');
            }
        });
    }
});

function confirmResetAll() {
    if (!confirm('Reset queue sequences for ALL organizations? This cannot be undone. Proceed?')) return;

    const btn = document.getElementById('resetAllBtn');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Resetting...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('{{ route('superadmin.organizations.reset-sequences') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
    }).then(async (res) => {
        if (!res.ok) throw new Error('Server error ' + res.status);
        const data = await res.json();
        alert(data.message || 'Reset completed');
        window.location.reload();
    }).catch(err => {
        console.error('Reset all sequences failed', err);
        alert('Failed to reset sequences: ' + err.message);
    }).finally(() => {
        btn.disabled = false;
        btn.innerHTML = orig;
    });
}
</script>
@endpush
@endsection
