@extends('layouts.app')

@section('title', $organization->organization_name . ' - Settings')
@section('page-title', 'Organization Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-cog text-blue-600 mr-3"></i>Organization Settings
                </h1>
                <p class="text-gray-600 mt-2">Manage your organization's information, branding, and display preferences</p>
            </div>
            @if($settings->company_logo)
                <img src="{{ asset('storage/' . $settings->company_logo) }}" alt="Logo" class="h-16 object-contain">
            @endif
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded fixed top-4 right-4 z-50 max-w-md shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded fixed top-4 right-4 z-50 max-w-md shadow-lg">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.organization-settings.update', ['organization_code' => request()->route('organization_code')]) }}" 
          method="POST" enctype="multipart/form-data" id="settingsForm">
        @csrf
        @method('PUT')

        <!-- Organization Information -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-building text-blue-600 mr-2"></i>Organization Information
                </h2>
                <p class="text-gray-600 mt-1 text-sm">Basic information about your organization</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Organization Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="organization_name" 
                           value="{{ old('organization_name', $organization->organization_name) }}" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('organization_name') border-red-500 @enderror" 
                           required>
                    @error('organization_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">
                        Phone Number
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-gray-400">
                            <i class="fas fa-phone"></i>
                        </span>
                        <input type="text" 
                               name="company_phone" 
                               value="{{ old('company_phone', $settings->company_phone) }}" 
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="+1 (555) 123-4567">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-gray-400">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" 
                               name="company_email" 
                               value="{{ old('company_email', $settings->company_email) }}" 
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="contact@example.com">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Address
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-gray-400">
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                        <textarea name="company_address" 
                                  rows="3" 
                                  class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                  placeholder="Enter full address">{{ old('company_address', $settings->company_address) }}</textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">
                        Queue Number Format
                    </label>
                    <select name="queue_number_digits" 
                            id="queueDigits"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <option value="3" {{ $settings->queue_number_digits == 3 ? 'selected' : '' }}>3 digits (001)</option>
                        <option value="4" {{ $settings->queue_number_digits == 4 ? 'selected' : '' }}>4 digits (0001)</option>
                        <option value="5" {{ $settings->queue_number_digits == 5 ? 'selected' : '' }}>5 digits (00001)</option>
                        <option value="6" {{ $settings->queue_number_digits == 6 ? 'selected' : '' }}>6 digits (000001)</option>
                    </select>
                    <p class="text-sm text-gray-500 mt-2 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>Example: <code id="queueExample" class="bg-gray-100 px-2 py-1 rounded">YYYYMMDD-CC-{{ str_repeat('0', $settings->queue_number_digits) }}</code></span>
                    </p>

                    <div class="mt-4 flex items-center gap-3">
                        <div class="text-sm text-gray-600">
                            Last sequence: <span class="font-mono">{{ str_pad($settings->last_queue_sequence ?? 0, $settings->queue_number_digits ?? 4, '0', STR_PAD_LEFT) }}</span>
                            <br>
                            Last reset: <span class="font-mono">{{ $settings->last_queue_sequence_date ?? '—' }}</span>
                        </div>
                        <form method="POST" action="{{ route('admin.organization-settings.reset-sequence', ['organization_code' => request()->route('organization_code')]) }}" onsubmit="return confirm('Reset the queue sequence to 0000? Next ticket will be 0001. Are you sure?');">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-50 text-yellow-700 hover:bg-yellow-600 hover:text-white rounded-lg transition font-medium text-sm">
                                <i class="fas fa-redo mr-2"></i>Reset Sequence
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logo Upload -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-image text-blue-600 mr-2"></i>Organization Logo
                </h2>
                <p class="text-gray-600 mt-1 text-sm">Upload your organization's logo for display across the system</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($settings->company_logo)
                <div>
                    <label class="block text-gray-700 font-semibold mb-3">Current Logo</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 bg-gray-50 text-center">
                        <img src="{{ asset('storage/' . $settings->company_logo) }}" 
                             alt="Organization Logo" 
                             class="max-h-32 mx-auto mb-4 object-contain">
                        <button type="button" 
                                onclick="confirmRemoveLogo()"
                                class="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 hover:bg-red-600 hover:text-white rounded-lg transition font-medium text-sm">
                            <i class="fas fa-trash mr-2"></i>Remove Logo
                        </button>
                    </div>
                </div>
                @endif

                <div class="{{ $settings->company_logo ? '' : 'md:col-span-2' }}">
                    <label class="block text-gray-700 font-semibold mb-3">
                        {{ $settings->company_logo ? 'Upload New Logo' : 'Upload Logo' }}
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 bg-gray-50 hover:bg-gray-100 transition">
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                            <div class="mb-3">
                                <label for="logoUpload" class="cursor-pointer inline-flex items-center px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                                    <i class="fas fa-folder-open mr-2"></i>Choose File
                                </label>
                                <input type="file" 
                                       name="logo" 
                                       id="logoUpload"
                                       accept="image/*" 
                                       class="hidden"
                                       onchange="previewLogo(this)">
                            </div>
                            <p class="text-sm text-gray-600">PNG, JPG, GIF up to 2MB</p>
                            <p class="text-xs text-gray-500 mt-1">Recommended: Transparent background, 400x400px</p>
                        </div>
                        <div id="logoPreview" class="mt-4 hidden">
                            <img id="logoPreviewImage" src="" alt="Preview" class="max-h-32 mx-auto">
                            <p id="logoFileName" class="text-sm text-gray-600 text-center mt-2"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Brand Colors -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-palette text-blue-600 mr-2"></i>Brand Colors
                </h2>
                <p class="text-gray-600 mt-1 text-sm">Customize the color scheme for your organization's displays</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Primary Color -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-3">
                        Primary Color
                    </label>
                    <input type="hidden" name="primary_color" id="primaryColorValue" value="{{ old('primary_color', $settings->primary_color) }}">
                    <div class="relative">
                        <input type="color" 
                               id="primaryColor"
                               value="{{ old('primary_color', $settings->primary_color) }}" 
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="h-24 rounded-lg border-2 border-gray-300 cursor-pointer hover:border-blue-500 transition relative overflow-hidden"
                             style="background: {{ $settings->primary_color }}"
                             onclick="document.getElementById('primaryColor').click()">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <i class="fas fa-palette text-white text-2xl drop-shadow-lg"></i>
                            </div>
                        </div>
                    </div>
                    <input type="text" 
                           id="primaryColorHex"
                           value="{{ $settings->primary_color }}" 
                           class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-center font-mono text-sm" 
                           readonly>
                    <p class="text-xs text-gray-500 mt-1 text-center">Main buttons, headers</p>
                </div>

                <!-- Secondary Color -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-3">
                        Secondary Color
                    </label>
                    <input type="hidden" name="secondary_color" id="secondaryColorValue" value="{{ old('secondary_color', $settings->secondary_color) }}">
                    <div class="relative">
                        <input type="color" 
                               id="secondaryColor"
                               value="{{ old('secondary_color', $settings->secondary_color) }}" 
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="h-24 rounded-lg border-2 border-gray-300 cursor-pointer hover:border-blue-500 transition relative overflow-hidden"
                             style="background: {{ $settings->secondary_color }}"
                             onclick="document.getElementById('secondaryColor').click()">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <i class="fas fa-brush text-white text-2xl drop-shadow-lg"></i>
                            </div>
                        </div>
                    </div>
                    <input type="text" 
                           id="secondaryColorHex"
                           value="{{ $settings->secondary_color }}" 
                           class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-center font-mono text-sm" 
                           readonly>
                    <p class="text-xs text-gray-500 mt-1 text-center">Gradients, accents</p>
                </div>

                <!-- Accent Color -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-3">
                        Accent Color
                    </label>
                    <input type="hidden" name="accent_color" id="accentColorValue" value="{{ old('accent_color', $settings->accent_color) }}">
                    <div class="relative">
                        <input type="color" 
                               id="accentColor"
                               value="{{ old('accent_color', $settings->accent_color) }}" 
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="h-24 rounded-lg border-2 border-gray-300 cursor-pointer hover:border-blue-500 transition relative overflow-hidden"
                             style="background: {{ $settings->accent_color }}"
                             onclick="document.getElementById('accentColor').click()">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <i class="fas fa-star text-white text-2xl drop-shadow-lg"></i>
                            </div>
                        </div>
                    </div>
                    <input type="text" 
                           id="accentColorHex"
                           value="{{ $settings->accent_color }}" 
                           class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-center font-mono text-sm" 
                           readonly>
                    <p class="text-xs text-gray-500 mt-1 text-center">Success, highlights</p>
                </div>

                <!-- Text Color -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-3">
                        Text Color
                    </label>
                    <input type="hidden" name="text_color" id="textColorValue" value="{{ old('text_color', $settings->text_color) }}">
                    <div class="relative">
                        <input type="color" 
                               id="textColor"
                               value="{{ old('text_color', $settings->text_color) }}" 
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="h-24 rounded-lg border-2 border-gray-300 cursor-pointer hover:border-blue-500 transition relative overflow-hidden"
                             style="background: {{ $settings->text_color }}"
                             onclick="document.getElementById('textColor').click()">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <i class="fas fa-font text-gray-800 text-2xl drop-shadow-lg"></i>
                            </div>
                        </div>
                    </div>
                    <input type="text" 
                           id="textColorHex"
                           value="{{ $settings->text_color }}" 
                           class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-center font-mono text-sm" 
                           readonly>
                    <p class="text-xs text-gray-500 mt-1 text-center">On colored backgrounds</p>
                </div>
            </div>

            <!-- Live Preview -->
            <div class="border-2 border-gray-200 rounded-lg p-6 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-eye mr-2 text-blue-600"></i>Live Preview
                </h3>
                <div id="brandPreview" class="rounded-lg p-8 shadow-lg transition-all duration-300" 
                     style="background: linear-gradient(135deg, var(--primary, {{ $settings->primary_color }}), var(--secondary, {{ $settings->secondary_color }}));">
                    <h3 id="previewTitle" class="text-3xl font-bold mb-3" style="color: var(--text, {{ $settings->text_color }})">
                        {{ $organization->organization_name }}
                    </h3>
                    <p id="previewText" class="text-lg mb-4" style="color: var(--text, {{ $settings->text_color }}); opacity: 0.9;">
                        This is how your brand colors will appear across the system
                    </p>
                    <div class="flex items-center space-x-3">
                        <button type="button" id="previewButton" class="px-6 py-3 rounded-lg font-semibold shadow-md hover:shadow-lg transition-all" 
                                style="background: var(--accent, {{ $settings->accent_color }}); color: white;">
                            <i class="fas fa-check mr-2"></i>Sample Button
                        </button>
                        <div class="px-6 py-3 rounded-lg font-semibold bg-white bg-opacity-20 backdrop-blur-sm" 
                             id="previewCard"
                             style="color: var(--text, {{ $settings->text_color }})">
                            <i class="fas fa-info-circle mr-2"></i>Info Card
                        </div>
                    </div>
                </div>
            </div>

            <!-- JavaScript for live preview updates -->
            <script>
                // Update preview when colors change
                function updatePreviewFromColors() {
                    const primary = document.getElementById('primaryColorValue').value;
                    const secondary = document.getElementById('secondaryColorValue').value;
                    const accent = document.getElementById('accentColorValue').value;
                    const text = document.getElementById('textColorValue').value;
                    
                    const preview = document.getElementById('brandPreview');
                    preview.style.background = `linear-gradient(135deg, ${primary}, ${secondary})`;
                    
                    const previewTitle = document.getElementById('previewTitle');
                    if (previewTitle) previewTitle.style.color = text;
                    
                    const previewText = document.getElementById('previewText');
                    if (previewText) previewText.style.color = text;
                    
                    const previewButton = document.getElementById('previewButton');
                    if (previewButton) previewButton.style.background = accent;
                    
                    const previewCard = document.getElementById('previewCard');
                    if (previewCard) previewCard.style.color = text;
                }
                
                // Watch for color changes from inputs
                document.getElementById('primaryColorValue').addEventListener('change', updatePreviewFromColors);
                document.getElementById('secondaryColorValue').addEventListener('change', updatePreviewFromColors);
                document.getElementById('accentColorValue').addEventListener('change', updatePreviewFromColors);
                document.getElementById('textColorValue').addEventListener('change', updatePreviewFromColors);
                
                // Also watch color input elements for real-time updates
                document.getElementById('primaryColor').addEventListener('input', function() {
                    document.getElementById('primaryColorValue').value = this.value;
                    updatePreviewFromColors();
                });
                document.getElementById('secondaryColor').addEventListener('input', function() {
                    document.getElementById('secondaryColorValue').value = this.value;
                    updatePreviewFromColors();
                });
                document.getElementById('accentColor').addEventListener('input', function() {
                    document.getElementById('accentColorValue').value = this.value;
                    updatePreviewFromColors();
                });
                document.getElementById('textColor').addEventListener('input', function() {
                    document.getElementById('textColorValue').value = this.value;
                    updatePreviewFromColors();
                });
            </script>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.dashboard', ['organization_code' => request()->route('organization_code')]) }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg transition font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
                <div class="flex items-center space-x-3">
                    <button type="button" 
                            onclick="document.getElementById('settingsForm').reset(); location.reload();"
                            class="inline-flex items-center px-6 py-3 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 rounded-lg transition font-semibold">
                        <i class="fas fa-undo mr-2"></i>Reset Changes
                    </button>
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-700 hover:to-blue-800 rounded-lg transition font-semibold shadow-md hover:shadow-lg">
                        <i class="fas fa-save mr-2"></i>Save Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Remove Logo Confirmation Modal -->
<div id="removeLogoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4 rounded-t-lg">
            <h3 class="text-xl font-bold text-white flex items-center">
                <i class="fas fa-exclamation-triangle mr-3"></i>Remove Logo
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-700 mb-6">Are you sure you want to remove the organization logo? This action cannot be undone.</p>
            <div class="flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeRemoveLogoModal()" 
                        class="px-5 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition font-medium">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button type="button" 
                        onclick="submitRemoveLogo()" 
                        class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-medium">
                    <i class="fas fa-trash mr-2"></i>Remove Logo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for logo removal -->
<form id="removeLogoForm" action="{{ route('admin.organization-settings.remove-logo', ['organization_code' => request()->route('organization_code')]) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
// Color picker updates
const colorInputs = [
    { picker: 'primaryColor', hex: 'primaryColorHex', preview: 'brandPreview' },
    { picker: 'secondaryColor', hex: 'secondaryColorHex', preview: 'brandPreview' },
    { picker: 'accentColor', hex: 'accentColorHex', preview: 'previewButton' },
    { picker: 'textColor', hex: 'textColorHex', preview: 'previewTitle' }
];

colorInputs.forEach(item => {
    const pickerEl = document.getElementById(item.picker);
    const hexEl = document.getElementById(item.hex);
    
    pickerEl.addEventListener('input', function() {
        hexEl.value = this.value.toUpperCase();
        pickerEl.parentElement.querySelector('div').style.background = this.value;
        updatePreview();
    });
});

function updatePreview() {
    const primary = document.getElementById('primaryColor').value;
    const secondary = document.getElementById('secondaryColor').value;
    const accent = document.getElementById('accentColor').value;
    const text = document.getElementById('textColor').value;
    
    const preview = document.getElementById('brandPreview');
    preview.style.background = `linear-gradient(135deg, ${primary}, ${secondary})`;
    
    document.getElementById('previewTitle').style.color = text;
    document.getElementById('previewText').style.color = text;
    document.getElementById('previewCard').style.color = text;
    document.getElementById('previewButton').style.background = accent;
}

// Queue format example update
document.getElementById('queueDigits').addEventListener('change', function() {
    const digits = parseInt(this.value);
    const zeros = '0'.repeat(digits);
    document.getElementById('queueExample').textContent = `YYYYMMDD-CC-${zeros}`;
});

// Logo preview
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').classList.remove('hidden');
            document.getElementById('logoPreviewImage').src = e.target.result;
            document.getElementById('logoFileName').textContent = input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Remove logo modal
function confirmRemoveLogo() {
    const modal = document.getElementById('removeLogoModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeRemoveLogoModal() {
    const modal = document.getElementById('removeLogoModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

function submitRemoveLogo() {
    document.getElementById('removeLogoForm').submit();
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRemoveLogoModal();
    }
});

// Close modal on backdrop click
document.getElementById('removeLogoModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRemoveLogoModal();
    }
});

// Auto-hide success toast
setTimeout(() => {
    const toast = document.getElementById('successToast');
    if (toast) {
        toast.style.transition = 'opacity 0.5s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 500);
    }
}, 5000);

// Organization name live update in preview
document.querySelector('input[name="organization_name"]')?.addEventListener('input', function() {
    document.getElementById('previewTitle').textContent = this.value || 'Organization Name';
});

// Real-time color preview and update
const primaryColorInput = document.getElementById('primaryColor');
const primaryColorValue = document.getElementById('primaryColorValue');
const primaryColorHex = document.getElementById('primaryColorHex');

const secondaryColorInput = document.getElementById('secondaryColor');
const secondaryColorValue = document.getElementById('secondaryColorValue');
const secondaryColorHex = document.getElementById('secondaryColorHex');

const accentColorInput = document.getElementById('accentColor');
const accentColorValue = document.getElementById('accentColorValue');
const accentColorHex = document.getElementById('accentColorHex');

const textColorInput = document.getElementById('textColor');
const textColorValue = document.getElementById('textColorValue');
const textColorHex = document.getElementById('textColorHex');

function updateColorPreview() {
    const root = document.documentElement;
    root.style.setProperty('--primary', primaryColorValue.value);
    root.style.setProperty('--secondary', secondaryColorValue.value);
    root.style.setProperty('--accent', accentColorValue.value);
    root.style.setProperty('--text', textColorValue.value);
}

// Sync color input to hidden field and hex display
function syncColorInput(colorInput, colorValue, colorHex) {
    colorInput.addEventListener('input', function() {
        colorValue.value = this.value;
        colorHex.value = this.value;
        updateColorPreview();
    });
}

// Initialize color sync
syncColorInput(primaryColorInput, primaryColorValue, primaryColorHex);
syncColorInput(secondaryColorInput, secondaryColorValue, secondaryColorHex);
syncColorInput(accentColorInput, accentColorValue, accentColorHex);
syncColorInput(textColorInput, textColorValue, textColorHex);

// Initial color preview update
updateColorPreview();

// Submit form with AJAX for real-time updates on displays
document.getElementById('settingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = this;
    const orgCode = '{{ request()->route("organization_code") }}';
    
    // Client-side validation: Check organization name is not empty
    const orgNameField = form.querySelector('input[name="organization_name"]');
    if (!orgNameField || !orgNameField.value || orgNameField.value.trim() === '') {
        const errorToast = document.createElement('div');
        errorToast.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded fixed top-4 right-4 z-50 max-w-md shadow-lg';
        errorToast.innerHTML = `
            <ul>
                <li>The organization name field is required.</li>
            </ul>
        `;
        document.body.insertBefore(errorToast, document.body.firstChild);
        setTimeout(() => {
            errorToast.style.transition = 'opacity 0.5s';
            errorToast.style.opacity = '0';
            setTimeout(() => errorToast.remove(), 500);
        }, 5000);
        return;
    }
    
    // Collect all form data properly
    const formData = new FormData(form);
    
    // Debug: Log all form data being sent
    console.log('Form data being sent:');
    console.log('Organization Name Field Value:', orgNameField.value);
    let hasOrgName = false;
    for (let [key, value] of formData.entries()) {
        if (key !== 'logo') { // Don't log file data
            console.log(`  ${key}: ${value}`);
        }
        if (key === 'organization_name') {
            hasOrgName = true;
        }
    }
    console.log('Has organization_name in FormData:', hasOrgName);
    console.log('Organization Name Value from FormData:', formData.get('organization_name'));
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        // Get CSRF token from the form or meta tag
        const csrfToken = document.querySelector('input[name="_token"]')?.value 
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch(
            `/${orgCode}/admin/organization-settings`,
            {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            }
        );

        const data = await response.json();
        
        console.log('Server response:', data);

        if (response.ok && data.success) {
            // Show success message
            const successToast = document.createElement('div');
            successToast.className = 'bg-green-100 border-l-4 border-green-500 text-green-700 px-6 py-4 rounded-lg mb-6 shadow-md fixed top-4 left-4 right-4 z-50';
            successToast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-xl mr-3"></i>
                    <span class="font-semibold">${data.message}</span>
                </div>
            `;
            document.body.insertBefore(successToast, document.body.firstChild);

            setTimeout(() => {
                successToast.style.transition = 'opacity 0.5s';
                successToast.style.opacity = '0';
                setTimeout(() => successToast.remove(), 500);
            }, 3000);
            
            // Broadcast settings update to other pages/tabs
            if (window.settingsSync) {
                window.settingsSync.broadcastUpdate(data.settings);
                window.settingsSync.fetchAndApply(); // Also trigger immediate fetch
            }
            
            // Dispatch custom event for other listeners
            const updatedSettings = {
                organization_name: form.querySelector('input[name="organization_name"]').value,
                primary_color: data.settings.primary_color,
                secondary_color: data.settings.secondary_color,
                accent_color: data.settings.accent_color,
                text_color: data.settings.text_color,
                company_logo: data.settings.company_logo
            };
            
            window.dispatchEvent(new CustomEvent('organizationSettingsUpdated', {
                detail: { settings: updatedSettings }
            }));
        } else {
            // Show error message
            let errorMessage = data.message || 'Failed to update settings';
            
            // If there are validation errors, show them
            if (data.errors) {
                console.error('Validation errors:', data.errors);
                const errorList = Object.values(data.errors)
                    .flat()
                    .map(err => `• ${err}`)
                    .join('<br>');
                
                const errorToast = document.createElement('div');
                errorToast.className = 'bg-red-100 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-lg mb-6 shadow-md fixed top-4 left-4 right-4 z-50 max-w-2xl';
                errorToast.innerHTML = `
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-xl mr-3 mt-1 flex-shrink-0"></i>
                        <div class="flex-1">
                            <p class="font-semibold mb-2">Validation Errors:</p>
                            <div class="text-sm">${errorList}</div>
                        </div>
                    </div>
                `;
                document.body.insertBefore(errorToast, document.body.firstChild);

                setTimeout(() => {
                    errorToast.style.transition = 'opacity 0.5s';
                    errorToast.style.opacity = '0';
                    setTimeout(() => errorToast.remove(), 500);
                }, 8000);
            } else {
                const errorToast = document.createElement('div');
                errorToast.className = 'bg-red-100 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-lg mb-6 shadow-md fixed top-4 left-4 right-4 z-50';
                errorToast.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                        <span class="font-semibold">Error: ${errorMessage}</span>
                    </div>
                `;
                document.body.insertBefore(errorToast, document.body.firstChild);

                setTimeout(() => {
                    errorToast.style.transition = 'opacity 0.5s';
                    errorToast.style.opacity = '0';
                    setTimeout(() => errorToast.remove(), 500);
                }, 5000);
            }
        }
    } catch (error) {
        console.error('Error updating settings:', error);
        
        const errorToast = document.createElement('div');
        errorToast.className = 'bg-red-100 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-lg mb-6 shadow-md fixed top-4 left-4 right-4 z-50';
        errorToast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                <span class="font-semibold">Network Error: ${error.message}</span>
            </div>
        `;
        document.body.insertBefore(errorToast, document.body.firstChild);

        setTimeout(() => {
            errorToast.style.transition = 'opacity 0.5s';
            errorToast.style.opacity = '0';
            setTimeout(() => errorToast.remove(), 500);
        }, 5000);
    } finally {
        // Restore button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>
@endpush
@endsection
