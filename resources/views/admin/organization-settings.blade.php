@extends('layouts.app')

@section('title', $organization->organization_name . ' - Settings')
@section('page-title', 'Organization Settings')

@section('content')
<div class="container mx-auto px-4 py-8" id="settingsContainer">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-cog text-blue-600 mr-3"></i>Organization Settings
                </h1>
                <p class="text-gray-600 mt-2">Manage your organization's information, branding, and display preferences</p>
            </div>
            @if($settings->organization_logo)
                <img src="{{ asset('storage/' . $settings->organization_logo) }}" alt="Logo" class="h-16 object-contain">
            @endif
        </div>
    </div>

    <!-- Session Messages (Server-side) -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded fixed top-4 right-4 z-50 max-w-md shadow-lg" id="sessionSuccess">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded fixed top-4 right-4 z-50 max-w-md shadow-lg" id="sessionError">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- AJAX Messages (Client-side) -->
    document.getElementById('organizationSettingsForm').addEventListener('submit', async function(e) {

    <!-- Main Form -->
    <form id="organizationSettingsForm" method="POST">
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
                <!-- Organization Name (Required) -->
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Organization Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="organization_name" 
                        id="organizationName"
                        value="{{ old('organization_name', $organization->organization_name) }}" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                        required>
                </div>

                <!-- Phone Number (Optional) -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Phone Number</label>
                    <input 
                        type="text" 
                        name="organization_phone" 
                        id="organizationPhone"
                        value="{{ old('organization_phone', $settings->organization_phone) }}" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="+1 (555) 123-4567">
                </div>

                <!-- Email Address (Optional) -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Email Address</label>
                    <input 
                        type="email" 
                        name="organization_email" 
                        id="organizationEmail"
                        value="{{ old('organization_email', $settings->organization_email) }}" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="contact@example.com">
                </div>

                <!-- Address (Optional) -->
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-semibold mb-2">Address</label>
                    <textarea 
                        name="organization_address" 
                        id="organizationAddress"
                        rows="3" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="Enter full address">{{ old('organization_address', $settings->organization_address) }}</textarea>
                </div>

                <!-- Queue Number Format (Optional) -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Queue Number Format</label>
                    <select 
                        name="queue_number_digits" 
                        id="queueDigits"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <option value="3" {{ $settings->queue_number_digits == 3 ? 'selected' : '' }}>3 digits (001)</option>
                        <option value="4" {{ $settings->queue_number_digits == 4 ? 'selected' : '' }}>4 digits (0001)</option>
                        <option value="5" {{ $settings->queue_number_digits == 5 ? 'selected' : '' }}>5 digits (00001)</option>
                        <option value="6" {{ $settings->queue_number_digits == 6 ? 'selected' : '' }}>6 digits (000001)</option>
                    </select>
                    <p class="text-sm text-gray-500 mt-2">Example: YYYYMMDD-CC-<code id="queueExample" class="bg-gray-100 px-2 py-1 rounded">{{ str_repeat('0', $settings->queue_number_digits) }}</code></p>
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
                @if($settings->organization_logo)
                <div>
                    <label class="block text-gray-700 font-semibold mb-3">Current Logo</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 bg-gray-50 text-center">
                        <img src="{{ asset('storage/' . $settings->organization_logo) }}" alt="Organization Logo" class="max-h-32 mx-auto mb-4 object-contain">
                    </div>
                </div>
                @endif

                <div>
                    <label class="block text-gray-700 font-semibold mb-3">Upload Logo</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 bg-gray-50 hover:bg-gray-100 transition">
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                            <div class="mb-3">
                                <label for="logoUpload" class="cursor-pointer inline-flex items-center px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                                    <i class="fas fa-folder-open mr-2"></i>Choose File
                                </label>
                                <input 
                                    type="file" 
                                    name="logo" 
                                    id="logoUpload"
                                    accept="image/*" 
                                    class="hidden"
                                    onchange="previewLogo(this)">
                            </div>
                            <p class="text-sm text-gray-600">PNG, JPG, GIF up to 2MB</p>
                            <p class="text-xs text-gray-500 mt-1">Recommended: 400x400px</p>
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
                <p class="text-gray-600 mt-1 text-sm">Customize the color scheme for your organization</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Primary Color -->
                <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                    <label class="block text-gray-800 font-semibold">Primary</label>
                    <p class="text-xs text-gray-500 mt-1">Main buttons, headers</p>

                    <input type="hidden" name="primary_color" id="primaryColorValue" value="{{ old('primary_color', $settings->primary_color) }}">

                    <div class="mt-4 flex items-center gap-3">
                        <input type="color" id="primaryColor" value="{{ old('primary_color', $settings->primary_color) }}" class="h-10 w-12 p-0 border border-gray-300 rounded-lg bg-white cursor-pointer">
                        <input type="text" id="primaryColorHex" value="{{ old('primary_color', $settings->primary_color) }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white font-mono text-sm" placeholder="#1D4ED8" inputmode="text" autocomplete="off">
                    </div>
                    <div class="mt-3 h-10 rounded-lg border border-gray-300" id="primarySwatch" style="background: {{ old('primary_color', $settings->primary_color) }}"></div>
                </div>

                <!-- Secondary Color -->
                <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                    <label class="block text-gray-800 font-semibold">Secondary</label>
                    <p class="text-xs text-gray-500 mt-1">Gradients, accents</p>

                    <input type="hidden" name="secondary_color" id="secondaryColorValue" value="{{ old('secondary_color', $settings->secondary_color) }}">

                    <div class="mt-4 flex items-center gap-3">
                        <input type="color" id="secondaryColor" value="{{ old('secondary_color', $settings->secondary_color) }}" class="h-10 w-12 p-0 border border-gray-300 rounded-lg bg-white cursor-pointer">
                        <input type="text" id="secondaryColorHex" value="{{ old('secondary_color', $settings->secondary_color) }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white font-mono text-sm" placeholder="#7C3AED" inputmode="text" autocomplete="off">
                    </div>
                    <div class="mt-3 h-10 rounded-lg border border-gray-300" id="secondarySwatch" style="background: {{ old('secondary_color', $settings->secondary_color) }}"></div>
                </div>

                <!-- Accent Color -->
                <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                    <label class="block text-gray-800 font-semibold">Accent</label>
                    <p class="text-xs text-gray-500 mt-1">Highlights, badges</p>

                    <input type="hidden" name="accent_color" id="accentColorValue" value="{{ old('accent_color', $settings->accent_color) }}">

                    <div class="mt-4 flex items-center gap-3">
                        <input type="color" id="accentColor" value="{{ old('accent_color', $settings->accent_color) }}" class="h-10 w-12 p-0 border border-gray-300 rounded-lg bg-white cursor-pointer">
                        <input type="text" id="accentColorHex" value="{{ old('accent_color', $settings->accent_color) }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white font-mono text-sm" placeholder="#10B981" inputmode="text" autocomplete="off">
                    </div>
                    <div class="mt-3 h-10 rounded-lg border border-gray-300" id="accentSwatch" style="background: {{ old('accent_color', $settings->accent_color) }}"></div>
                </div>

                <!-- Text Color -->
                <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                    <label class="block text-gray-800 font-semibold">Text</label>
                    <p class="text-xs text-gray-500 mt-1">On colored backgrounds</p>

                    <input type="hidden" name="text_color" id="textColorValue" value="{{ old('text_color', $settings->text_color) }}">

                    <div class="mt-4 flex items-center gap-3">
                        <input type="color" id="textColor" value="{{ old('text_color', $settings->text_color) }}" class="h-10 w-12 p-0 border border-gray-300 rounded-lg bg-white cursor-pointer">
                        <input type="text" id="textColorHex" value="{{ old('text_color', $settings->text_color) }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white font-mono text-sm" placeholder="#FFFFFF" inputmode="text" autocomplete="off">
                    </div>
                    <div class="mt-3 h-10 rounded-lg border border-gray-300" id="textSwatch" style="background: {{ old('text_color', $settings->text_color) }}"></div>
                </div>
            </div>

            <!-- Live Preview -->
            <div class="border-2 border-gray-200 rounded-lg p-6 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Live Preview</h3>
                <div id="brandPreview" class="rounded-lg p-8 shadow-lg" style="background: linear-gradient(135deg, {{ $settings->primary_color }}, {{ $settings->secondary_color }});">
                    <h3 id="previewTitle" class="text-3xl font-bold mb-3" style="color: {{ $settings->text_color }}">{{ $organization->organization_name }}</h3>
                    <p id="previewText" class="text-lg mb-4" style="color: {{ $settings->text_color }}; opacity: 0.9;">This is how your brand colors will appear</p>
                    <div class="flex gap-3">
                        <button id="previewButton" type="button" class="px-6 py-3 rounded-lg font-semibold" style="background: {{ $settings->accent_color }}; color: white;">Sample Button</button>
                        <div id="previewCard" class="px-6 py-3 rounded-lg font-semibold bg-white bg-opacity-20" style="color: {{ $settings->text_color }}">Info Card</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.dashboard', ['organization_code' => request()->route('organization_code')]) }}" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg transition font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
                <div class="flex items-center space-x-3">
                    <button type="reset" class="inline-flex items-center px-6 py-3 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 rounded-lg transition font-semibold">
                        <i class="fas fa-undo mr-2"></i>Reset
                    </button>
                    <button type="submit" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-700 hover:to-blue-800 rounded-lg transition font-semibold shadow-md hover:shadow-lg">
                        <i class="fas fa-save mr-2"></i>Save Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
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

function normalizeHexColor(value) {
    if (value == null) return null;
    const v = String(value).trim();
    if (!v) return null;
    const raw = v.startsWith('#') ? v.slice(1) : v;
    if (!/^[0-9a-fA-F]{6}$/.test(raw)) return null;
    return `#${raw.toUpperCase()}`;
}

function bindColorControl(colorInputId, hexInputId, hiddenInputId, swatchId) {
    const colorEl = document.getElementById(colorInputId);
    const hexEl = document.getElementById(hexInputId);
    const hiddenEl = document.getElementById(hiddenInputId);
    const swatchEl = document.getElementById(swatchId);
    if (!colorEl || !hexEl || !hiddenEl) return;

    const apply = (hex) => {
        const normalized = normalizeHexColor(hex);
        if (!normalized) return false;
        colorEl.value = normalized;
        hexEl.value = normalized;
        hiddenEl.value = normalized;
        if (swatchEl) swatchEl.style.background = normalized;
        updatePreview();
        return true;
    };

    colorEl.addEventListener('change', function() { apply(this.value); });

    hexEl.addEventListener('blur', function() {
        if (!apply(this.value)) {
            // revert to last good value
            this.value = hiddenEl.value;
        }
    });

    hexEl.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.blur();
        }
    });

    // Initialize sync
    apply(hiddenEl.value || colorEl.value || hexEl.value);
}

// Color synchronization
bindColorControl('primaryColor', 'primaryColorHex', 'primaryColorValue', 'primarySwatch');
bindColorControl('secondaryColor', 'secondaryColorHex', 'secondaryColorValue', 'secondarySwatch');
bindColorControl('accentColor', 'accentColorHex', 'accentColorValue', 'accentSwatch');
bindColorControl('textColor', 'textColorHex', 'textColorValue', 'textSwatch');

// Update preview
function updatePreview() {
    const primary = document.getElementById('primaryColorValue').value;
    const secondary = document.getElementById('secondaryColorValue').value;
    const accent = document.getElementById('accentColorValue').value;
    const text = document.getElementById('textColorValue').value;
    
    document.getElementById('brandPreview').style.background = `linear-gradient(135deg, ${primary}, ${secondary})`;
    document.getElementById('previewTitle').style.color = text;
    document.getElementById('previewText').style.color = text;
    const previewBtn = document.getElementById('previewButton');
    const previewCard = document.getElementById('previewCard');
    if (previewBtn) previewBtn.style.background = accent;
    if (previewCard) previewCard.style.color = text;
}

// Form submission via AJAX
document.getElementById('settingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const orgCode = '{{ request()->route("organization_code") }}';
    const orgName = document.getElementById('organizationName').value.trim();
    
    // Validate organization name
    if (!orgName) {
        showAjaxMessage('error', 'The organization name field is required.');
        return;
    }
    
    const formData = new FormData(this);
    
    // Debug: Log FormData contents
    console.log('FormData contents:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    
    // Ensure organization_name is in FormData
    if (!formData.has('organization_name')) {
        console.warn('organization_name not found in FormData, adding it');
        formData.set('organization_name', orgName);
    }
    
    // Ensure _method is set to PUT for Laravel
    if (!formData.has('_method')) {
        formData.set('_method', 'PUT');
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const response = await fetch(
            `/${orgCode}/admin/organization-settings`,
            {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            }
        );
        
        const data = await response.json();
        console.log('Server response:', data);
        
        if (response.ok && data.success) {
            showAjaxMessage('success', data.message);
            if (window.settingsSync) {
                window.settingsSync.broadcastUpdate(data.settings);
                window.settingsSync.fetchAndApply();
            }
        } else {
            if (data.errors) {
                const errorList = Object.values(data.errors).flat().join(' ');
                showAjaxMessage('error', errorList);
            } else {
                showAjaxMessage('error', data.message || 'Failed to update settings');
            }
        }
    } catch (error) {
        showAjaxMessage('error', 'Network error: ' + error.message);
        console.error('Submit error:', error);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

function showAjaxMessage(type, message) {
    const container = document.getElementById('ajaxMessage');
    const bgColor = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
    
    container.innerHTML = `
        <div class="${bgColor} border px-4 py-3 rounded fixed top-4 right-4 z-50 max-w-md shadow-lg">
            ${message}
        </div>
    `;
    
    setTimeout(() => {
        const alert = container.querySelector('div');
        if (alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => { alert.remove(); }, 500);
        }
    }, 4000);
}

// Organization name live preview
document.getElementById('organizationName').addEventListener('input', function() {
    document.getElementById('previewTitle').textContent = this.value || '{{ $organization->organization_name }}';
});
</script>
@endsection
