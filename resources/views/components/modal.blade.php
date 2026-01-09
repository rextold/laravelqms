<!-- Reusable Modal Component -->
<div id="{{ $modalId ?? 'modal' }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="{{ $modalId ?? 'modal' }}-content">
        <!-- Header -->
        <div class="bg-gradient-to-r {{ $headerGradient ?? 'from-blue-500 to-blue-600' }} px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    @if(isset($icon))
                        <i class="{{ $icon }} text-white text-xl"></i>
                    @endif
                    <h3 class="text-xl font-bold text-white">{{ $title ?? 'Confirm Action' }}</h3>
                </div>
                <button onclick="closeModal('{{ $modalId ?? 'modal' }}')" class="text-white hover:text-gray-200 text-2xl leading-none">
                    &times;
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
            {{ $slot }}
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            @if(isset($cancelText))
                <button onclick="closeModal('{{ $modalId ?? 'modal' }}')" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">
                    {{ $cancelText ?? 'Cancel' }}
                </button>
            @endif
            
            @if(isset($confirmText))
                <button onclick="{{ $confirmAction ?? 'closeModal(\'' . ($modalId ?? 'modal') . '\')' }}" class="px-4 py-2 {{ $confirmButtonClass ?? 'bg-red-600 hover:bg-red-700' }} text-white rounded-lg font-semibold transition">
                    {{ $confirmText }}
                </button>
            @endif
        </div>
    </div>
</div>

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

// Close modal on outside click
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[id$="-modal"]').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
});
</script>
