@extends('layouts.app')

@section('title', 'Marquee Management')
@section('page-title', 'Marquee Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Marquee Management</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Create Marquee -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Create New Marquee</h2>
        <form id="marqueeForm" onsubmit="createMarquee(event)">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-semibold mb-2">Text *</label>
                    <textarea id="marqueeText" name="text" rows="2" required 
                              class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Speed (10-200)</label>
                    <input id="marqueeSpeed" type="number" name="speed" value="50" min="10" max="200" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                    <button type="submit" class="mt-2 w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Create
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Marquees List -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Marquee Messages</h2>
        <div id="marqueesList" class="space-y-4">
            @foreach($marquees as $marquee)
            <div class="border rounded p-4 {{ $marquee->is_active ? 'border-green-500 bg-green-50' : '' }}">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="text-lg">{{ $marquee->text }}</p>
                        <p class="text-sm text-gray-600 mt-1">Speed: {{ $marquee->speed }}</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="toggleActive({{ $marquee->id }})" 
                                class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-toggle-{{ $marquee->is_active ? 'on' : 'off' }}"></i>
                            {{ $marquee->is_active ? 'Active' : 'Inactive' }}
                        </button>
                        <button type="button" 
                                data-marquee-id="{{ $marquee->id }}"
                                data-marquee-text="{{ addslashes($marquee->text) }}"
                                data-delete-url="{{ route('admin.marquee.destroy', ['organization_code' => request()->route('organization_code'), 'marquee' => $marquee->id]) }}"
                                onclick="openDeleteMarqueeModal(this)"
                                class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Delete Marquee Modal -->
<div id="delete-marquee-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="delete-marquee-modal-content">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    <h3 class="text-xl font-bold text-white">Delete Marquee</h3>
                </div>
                <button onclick="closeModal('delete-marquee-modal')" class="text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-2">Are you sure you want to delete this marquee?</p>
            <p class="text-sm text-gray-500 mb-4">Text: <strong id="delete-marquee-text"></strong></p>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            <button onclick="closeModal('delete-marquee-modal')" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">Cancel</button>
            <button type="button" onclick="confirmDeleteMarquee()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">
                <i class="fas fa-trash mr-2"></i>Delete Marquee
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const organizationCode = '{{ request()->route("organization_code") }}';

function createMarquee(event) {
    event.preventDefault();
    
    const form = document.getElementById('marqueeForm');
    const text = document.getElementById('marqueeText').value;
    const speed = document.getElementById('marqueeSpeed').value;
    
    fetch(`/${organizationCode}/admin/marquee`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ text, speed })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reset form
            form.reset();
            document.getElementById('marqueeSpeed').value = '50';
            
            // Reload marquees list
            loadMarquees();
            
            // Show success message
            showSuccessMessage('Marquee created successfully');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating marquee');
    });
}

function loadMarquees() {
    fetch(`/${organizationCode}/admin/marquee/list`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('marqueesList');
        container.innerHTML = data.marquees.map(marquee => `
            <div class="border rounded p-4 ${marquee.is_active ? 'border-green-500 bg-green-50' : ''}">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="text-lg">${escapeHtml(marquee.text)}</p>
                        <p class="text-sm text-gray-600 mt-1">Speed: ${marquee.speed}</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="toggleActive(${marquee.id})" 
                                class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-toggle-${marquee.is_active ? 'on' : 'off'}"></i>
                            ${marquee.is_active ? 'Active' : 'Inactive'}
                        </button>
                        <button type="button" 
                                data-marquee-id="${marquee.id}"
                                data-marquee-text="${escapeHtml(marquee.text)}"
                                data-delete-url="/${organizationCode}/admin/marquee/${marquee.id}"
                                onclick="openDeleteMarqueeModal(this)"
                                class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    })
    .catch(error => console.error('Error loading marquees:', error));
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showSuccessMessage(message) {
    const alert = document.createElement('div');
    alert.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 fixed top-4 right-4 z-50';
    alert.textContent = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}

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

function openDeleteMarqueeModal(button) {
    const marqueeId = button.dataset.marqueeId;
    const marqueeText = button.dataset.marqueeText;
    const deleteUrl = button.dataset.deleteUrl;
    
    console.log('Opening delete marquee modal with:', { marqueeId, marqueeText, deleteUrl });
    
    document.getElementById('delete-marquee-text').textContent = marqueeText;
    window.currentDeleteUrl = deleteUrl;
    window.currentDeleteId = marqueeId;
    openModal('delete-marquee-modal');
}

function confirmDeleteMarquee() {
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
    
    console.log('Deleting marquee from URL:', url);
    
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
        closeModal('delete-marquee-modal');
        showSuccessMessage('Marquee deleted successfully');
        loadMarquees();
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Error deleting marquee: ' + error.message);
    });
}

// Close modal on outside click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('delete-marquee-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal('delete-marquee-modal');
            }
        });
    }
});

function toggleActive(marqueeId) {
    fetch(`/${organizationCode}/admin/marquee/${marqueeId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadMarquees();
        }
    });
}
</script>
@endpush
@endsection
