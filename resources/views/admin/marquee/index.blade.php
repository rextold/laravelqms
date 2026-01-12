@extends('layouts.app')

@section('title', 'Marquee Management')
@section('page-title', 'Marquee Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Marquee Settings</h1>
            <p class="text-sm text-gray-600 mt-1">Manage the scrolling message shown at the bottom of the monitor screen.</p>
        </div>
        <a href="{{ route('admin.dashboard', ['organization_code' => request()->route('organization_code')]) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg transition font-semibold">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>

    <div id="pageToast" class="fixed top-4 right-4 z-50"></div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Create / Edit -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Create New Message</h2>
                <span class="text-xs text-gray-500">Text max 1000 chars</span>
            </div>

            <div id="marqueeFormError" class="hidden mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

            <form id="marqueeForm" onsubmit="createMarquee(event)">
                @csrf
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Message *</label>
                    <textarea id="marqueeText" name="text" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500"
                              placeholder="Enter your announcement..."></textarea>
                    <div class="flex items-center justify-between mt-2">
                        <p class="text-xs text-gray-500">Tip: Keep it short and clear for customers.</p>
                        <p class="text-xs text-gray-500"><span id="textCount">0</span>/1000</p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-gray-800 font-semibold mb-2">Speed</label>
                        <div class="flex items-center gap-3">
                            <input id="marqueeSpeed" type="range" min="10" max="200" value="50" class="w-full">
                            <input id="marqueeSpeedNumber" type="number" min="10" max="200" value="50" class="w-24 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-center">
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Higher = faster (applies on monitor refresh).</p>
                    </div>
                    <button id="btnCreate" type="submit" class="w-full inline-flex items-center justify-center bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition font-semibold">
                        <i class="fas fa-plus mr-2"></i>Create
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview / Active -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Preview</h2>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-900 text-white px-4 py-3 text-sm font-semibold flex items-center justify-between">
                    <span><i class="fas fa-tv mr-2"></i>Monitor Marquee</span>
                    <span id="activeBadge" class="text-xs px-2 py-1 rounded bg-gray-700">No active</span>
                </div>
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3">
                    <div class="bg-black bg-opacity-35 rounded-md px-3 py-2 overflow-hidden">
                        <div class="whitespace-nowrap" style="will-change: transform;">
                            <span id="previewText" class="inline-block text-white font-semibold">Type a message to preview…</span>
                        </div>
                    </div>
                    <div class="text-xs text-white/80 mt-2 flex items-center justify-between">
                        <span>Speed: <span id="previewSpeed">50</span></span>
                        <span class="opacity-80">Shown at bottom of monitor</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- List -->
    <div class="bg-white rounded-xl shadow p-6 mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Messages</h2>
            <button type="button" onclick="loadMarquees()" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg transition font-semibold">
                <i class="fas fa-rotate mr-2"></i>Refresh
            </button>
        </div>

        <div id="marqueesList" class="space-y-3">
            @foreach($marquees as $marquee)
                <div class="border rounded-xl p-4 {{ $marquee->is_active ? 'border-green-400 bg-green-50' : 'border-gray-200' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-gray-900 truncate">{{ $marquee->text }}</p>
                                @if($marquee->is_active)
                                    <span class="text-xs px-2 py-1 rounded bg-green-600 text-white">Active</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-600 mt-2 flex items-center gap-4">
                                <span><i class="fas fa-gauge-high mr-1"></i>Speed: {{ $marquee->speed }}</span>
                                <span><i class="fas fa-clock mr-1"></i>{{ $marquee->created_at?->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="toggleActive({{ $marquee->id }})" class="text-blue-700 hover:text-blue-900 font-semibold text-sm">
                                <i class="fas fa-toggle-{{ $marquee->is_active ? 'on' : 'off' }} mr-1"></i>
                                {{ $marquee->is_active ? 'Active' : 'Set Active' }}
                            </button>
                            <button type="button"
                                    data-marquee-id="{{ $marquee->id }}"
                                    data-marquee-text="{{ e($marquee->text) }}"
                                    data-marquee-speed="{{ $marquee->speed }}"
                                    onclick="openEditMarqueeModal(this)"
                                    class="text-gray-700 hover:text-gray-900">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button type="button"
                                    data-marquee-id="{{ $marquee->id }}"
                                    data-marquee-text="{{ e($marquee->text) }}"
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

<!-- Edit Marquee Modal -->
<div id="edit-marquee-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="edit-marquee-modal-content">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-pen text-white text-xl"></i>
                    <h3 class="text-xl font-bold text-white">Edit Marquee</h3>
                </div>
                <button onclick="closeModal('edit-marquee-modal')" class="text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
            </div>
        </div>

        <div class="px-6 py-4">
            <div id="editMarqueeError" class="hidden mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>
            <div>
                <label class="block text-gray-800 font-semibold mb-2">Message *</label>
                <textarea id="editMarqueeText" rows="3" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500"></textarea>
                <div class="flex items-center justify-between mt-2">
                    <p class="text-xs text-gray-500">Max 1000 chars</p>
                    <p class="text-xs text-gray-500"><span id="editTextCount">0</span>/1000</p>
                </div>
            </div>

            <div class="mt-5">
                <label class="block text-gray-800 font-semibold mb-2">Speed</label>
                <div class="flex items-center gap-3">
                    <input id="editMarqueeSpeed" type="range" min="10" max="200" value="50" class="w-full">
                    <input id="editMarqueeSpeedNumber" type="number" min="10" max="200" value="50" class="w-24 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-center">
                </div>
            </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            <button onclick="closeModal('edit-marquee-modal')" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">Cancel</button>
            <button id="btnEditSave" type="button" onclick="confirmEditMarquee()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                <i class="fas fa-save mr-2"></i>Save Changes
            </button>
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

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
}

function showToast(type, message) {
    const container = document.getElementById('pageToast');
    if (!container) return;
    const bg = type === 'success' ? 'bg-green-100 border-green-400 text-green-800' : 'bg-red-100 border-red-400 text-red-800';
    const el = document.createElement('div');
    el.className = `${bg} border px-4 py-3 rounded shadow-lg mb-2 max-w-md`;
    el.textContent = message;
    container.appendChild(el);
    setTimeout(() => {
        el.style.transition = 'opacity 250ms ease';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 260);
    }, 3000);
}

function setError(containerId, message) {
    const el = document.getElementById(containerId);
    if (!el) return;
    if (!message) {
        el.classList.add('hidden');
        el.textContent = '';
        return;
    }
    el.textContent = message;
    el.classList.remove('hidden');
}

function syncSpeed(rangeId, numberId, onChange) {
    const rangeEl = document.getElementById(rangeId);
    const numberEl = document.getElementById(numberId);
    if (!rangeEl || !numberEl) return;

    const clamp = (v) => {
        const n = parseInt(v, 10);
        if (Number.isNaN(n)) return 50;
        return Math.min(200, Math.max(10, n));
    };

    const apply = (v) => {
        const n = clamp(v);
        rangeEl.value = String(n);
        numberEl.value = String(n);
        if (typeof onChange === 'function') onChange(n);
    };

    rangeEl.addEventListener('input', () => apply(rangeEl.value));
    numberEl.addEventListener('input', () => apply(numberEl.value));
    apply(rangeEl.value);
}

function updatePreviewFromForm() {
    const txt = document.getElementById('marqueeText')?.value || '';
    const speed = document.getElementById('marqueeSpeed')?.value || '50';
    const previewText = document.getElementById('previewText');
    const previewSpeed = document.getElementById('previewSpeed');
    const textCount = document.getElementById('textCount');
    if (previewText) previewText.textContent = txt.trim() ? txt : 'Type a message to preview…';
    if (previewSpeed) previewSpeed.textContent = speed;
    if (textCount) textCount.textContent = String(Math.min(1000, txt.length));
}

function createMarquee(event, retry) {
    event.preventDefault();

    setError('marqueeFormError', null);
    const form = document.getElementById('marqueeForm');
    const btn = document.getElementById('btnCreate');
    const text = document.getElementById('marqueeText').value;
    const speed = document.getElementById('marqueeSpeed').value;

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
    }

    fetch(`/${organizationCode}/admin/marquee`, {
        method: 'POST',
        credentials: 'same-origin',
        cache: 'no-store',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ text, speed })
    })
    .then(async response => {
        if (response.status === 403 && !retry) {
            // Try to refresh CSRF token and retry once
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute('content', '{{ csrf_token() }}');
            return createMarquee(event, true);
        }
        const data = await response.json().catch(() => null);
        if (!response.ok) {
            if (data && typeof data === 'object') {
                const msg = data?.message || (data?.errors ? Object.values(data.errors).flat().join(' ') : null);
                throw new Error(msg || `Failed to create marquee (HTTP ${response.status}).`);
            }
            throw new Error(`Failed to create marquee (HTTP ${response.status}). Please refresh the page and try again.`);
        }
        return data || {};
    })
    .then(data => {
        if (data && typeof data === 'object' && data.success) {
            form.reset();
            document.getElementById('marqueeSpeed').value = '50';
            document.getElementById('marqueeSpeedNumber').value = '50';
            updatePreviewFromForm();
            loadMarquees();
            showToast('success', 'Marquee created successfully');
        } else if (data && typeof data === 'object') {
            throw new Error(data.message || 'Failed to create marquee.');
        } else {
            throw new Error('Server error: invalid response. Please refresh and try again.');
        }
    })
    .catch(error => {
        setError('marqueeFormError', error.message);
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus mr-2"></i>Create';
        }
    });
}

function loadMarquees() {
    fetch(`/${organizationCode}/admin/marquee/list`, {
        credentials: 'same-origin',
        cache: 'no-store',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('marqueesList');

        const active = (data.marquees || []).find(m => !!m.is_active);
        const activeBadge = document.getElementById('activeBadge');
        if (activeBadge) {
            if (active) {
                activeBadge.textContent = 'Active';
                activeBadge.className = 'text-xs px-2 py-1 rounded bg-green-600 text-white';
            } else {
                activeBadge.textContent = 'No active';
                activeBadge.className = 'text-xs px-2 py-1 rounded bg-gray-700 text-white';
            }
        }

        container.innerHTML = (data.marquees || []).map(marquee => `
            <div class="border rounded-xl p-4 ${marquee.is_active ? 'border-green-400 bg-green-50' : 'border-gray-200'}">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-gray-900 truncate">${escapeHtml(marquee.text)}</p>
                            ${marquee.is_active ? '<span class="text-xs px-2 py-1 rounded bg-green-600 text-white">Active</span>' : ''}
                        </div>
                        <div class="text-xs text-gray-600 mt-2 flex items-center gap-4">
                            <span><i class="fas fa-gauge-high mr-1"></i>Speed: ${marquee.speed ?? ''}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="toggleActive(${marquee.id})" class="text-blue-700 hover:text-blue-900 font-semibold text-sm">
                            <i class="fas fa-toggle-${marquee.is_active ? 'on' : 'off'} mr-1"></i>
                            ${marquee.is_active ? 'Active' : 'Set Active'}
                        </button>
                        <button type="button"
                                data-marquee-id="${marquee.id}"
                                data-marquee-text="${escapeHtml(marquee.text)}"
                                data-marquee-speed="${marquee.speed ?? 50}"
                                onclick="openEditMarqueeModal(this)"
                                class="text-gray-700 hover:text-gray-900">
                            <i class="fas fa-pen"></i>
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
        `).join('') || `
            <div class="text-center text-gray-500 py-10">
                <i class="fas fa-inbox text-3xl opacity-40"></i>
                <p class="mt-3">No marquee messages yet.</p>
            </div>
        `;
    })
    .catch(error => console.error('Error loading marquees:', error));
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// showSuccessMessage removed (replaced by showToast)

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

    document.getElementById('delete-marquee-text').textContent = marqueeText;
    window.currentDeleteUrl = deleteUrl;
    window.currentDeleteId = marqueeId;
    openModal('delete-marquee-modal');
}

function confirmDeleteMarquee() {
    const url = window.currentDeleteUrl;
    const csrfToken = getCsrfToken();
    
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
    
    fetch(url, {
        method: 'DELETE',
        credentials: 'same-origin',
        cache: 'no-store',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data?.message || 'Failed to delete marquee.');
            }).catch(() => {
                throw new Error('Failed to delete marquee.');
            });
        }
        return response.json().catch(() => ({}));
    })
    .then(() => {
        closeModal('delete-marquee-modal');
        showToast('success', 'Marquee deleted successfully');
        loadMarquees();
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('error', 'Error deleting marquee: ' + error.message);
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
        credentials: 'same-origin',
        cache: 'no-store',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.is_active ? 'Marquee activated' : 'Marquee deactivated');
            loadMarquees();
        }
    });
}

function openEditMarqueeModal(button) {
    setError('editMarqueeError', null);
    const id = button.dataset.marqueeId;
    const text = button.dataset.marqueeText || '';
    const speed = button.dataset.marqueeSpeed || '50';

    window.currentEditId = id;
    document.getElementById('editMarqueeText').value = text;
    document.getElementById('editTextCount').textContent = String(Math.min(1000, text.length));
    document.getElementById('editMarqueeSpeed').value = speed;
    document.getElementById('editMarqueeSpeedNumber').value = speed;
    openModal('edit-marquee-modal');
}

function confirmEditMarquee() {
    setError('editMarqueeError', null);
    const id = window.currentEditId;
    const text = document.getElementById('editMarqueeText').value;
    const speed = document.getElementById('editMarqueeSpeed').value;
    const btn = document.getElementById('btnEditSave');
    if (!id) {
        setError('editMarqueeError', 'Missing marquee id. Please refresh the page.');
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    }

    fetch(`/${organizationCode}/admin/marquee/${id}`, {
        method: 'PUT',
        credentials: 'same-origin',
        cache: 'no-store',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ text, speed })
    })
    .then(async response => {
        const data = await response.json().catch(() => null);
        if (!response.ok) {
            if (data && typeof data === 'object') {
                const msg = data?.message || (data?.errors ? Object.values(data.errors).flat().join(' ') : null);
                throw new Error(msg || `Failed to update marquee (HTTP ${response.status}).`);
            }
            throw new Error(`Failed to update marquee (HTTP ${response.status}). Please refresh the page and try again.`);
        }
        return data || {};
    })
    .then(data => {
        if (data.success) {
            closeModal('edit-marquee-modal');
            showToast('success', 'Marquee updated successfully');
            loadMarquees();
        } else {
            throw new Error(data.message || 'Failed to update marquee.');
        }
    })
    .catch(err => {
        setError('editMarqueeError', err.message);
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Changes';
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    syncSpeed('marqueeSpeed', 'marqueeSpeedNumber', (n) => {
        const previewSpeed = document.getElementById('previewSpeed');
        if (previewSpeed) previewSpeed.textContent = String(n);
    });
    syncSpeed('editMarqueeSpeed', 'editMarqueeSpeedNumber');

    const textEl = document.getElementById('marqueeText');
    const editTextEl = document.getElementById('editMarqueeText');
    if (textEl) {
        textEl.addEventListener('input', updatePreviewFromForm);
        updatePreviewFromForm();
    }
    if (editTextEl) {
        editTextEl.addEventListener('input', function() {
            document.getElementById('editTextCount').textContent = String(Math.min(1000, this.value.length));
        });
    }

    // Close modals on outside click
    const deleteModal = document.getElementById('delete-marquee-modal');
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === this) closeModal('delete-marquee-modal');
        });
    }
    const editModal = document.getElementById('edit-marquee-modal');
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === this) closeModal('edit-marquee-modal');
        });
    }

    // Ensure active badge and list are synced
    loadMarquees();
});
</script>
@endpush
@endsection
