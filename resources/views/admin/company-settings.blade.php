@extends('layouts.app')

@section('title', 'Organization Settings')

@section('content')

    <div class="container mx-auto px-6 py-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-lg p-8">
            <form action="{{ route('admin.company-settings.update', ['company_code' => request()->route('company_code')]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Organization Information -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-3">
                        <i class="fas fa-building mr-2 text-blue-600"></i>Organization Information
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Organization Name *</label>
                            <input type="text" name="company_name" value="{{ old('company_name', $settings->company_name) }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Queue Number Digits *</label>
                            <select name="queue_number_digits" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" required>
                                <option value="3" {{ $settings->queue_number_digits == 3 ? 'selected' : '' }}>3 digits (001)</option>
                                <option value="4" {{ $settings->queue_number_digits == 4 ? 'selected' : '' }}>4 digits (0001)</option>
                                <option value="5" {{ $settings->queue_number_digits == 5 ? 'selected' : '' }}>5 digits (00001)</option>
                                <option value="6" {{ $settings->queue_number_digits == 6 ? 'selected' : '' }}>6 digits (000001)</option>
                            </select>
                            <p class="text-sm text-gray-600 mt-1">Format: YYYYMMDD-CC-{{ str_repeat('0', $settings->queue_number_digits) }}</p>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Phone Number</label>
                            <input type="text" name="company_phone" value="{{ old('company_phone', $settings->company_phone) }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Email Address</label>
                            <input type="email" name="company_email" value="{{ old('company_email', $settings->company_email) }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-semibold mb-2">Address</label>
                            <textarea name="company_address" rows="3" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">{{ old('company_address', $settings->company_address) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Logo Upload -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-3">
                        <i class="fas fa-image mr-2 text-blue-600"></i>Organization Logo
                    </h2>
                    
                    @if($settings->logo_url)
                        <div class="mb-4 p-4 bg-gray-50 rounded">
                            <p class="text-sm text-gray-600 mb-2">Current Logo:</p>
                            <img src="{{ $settings->logo_url }}" alt="Organization Logo" class="max-h-32 mb-3 border rounded">
                            <button type="button" onclick="document.getElementById('remove-logo-form').submit();" 
                                    class="text-red-600 hover:text-red-800 text-sm">
                                <i class="fas fa-trash mr-1"></i>Remove Logo
                            </button>
                        </div>
                    @endif

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Upload New Logo</label>
                        <input type="file" name="company_logo" accept="image/*" 
                               class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                        <p class="text-sm text-gray-600 mt-1">Recommended: PNG or SVG with transparent background. Max 2MB.</p>
                    </div>
                </div>

                <!-- Color Scheme -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-3">
                        <i class="fas fa-palette mr-2 text-blue-600"></i>Brand Colors
                    </h2>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Primary Color *</label>
                            <div class="flex items-center space-x-2">
                                <input type="color" name="primary_color" value="{{ old('primary_color', $settings->primary_color) }}" 
                                       class="h-12 w-20 border border-gray-300 rounded cursor-pointer" required>
                                <input type="text" value="{{ $settings->primary_color }}" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded bg-gray-50" readonly>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">Main buttons, headers</p>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Secondary Color *</label>
                            <div class="flex items-center space-x-2">
                                <input type="color" name="secondary_color" value="{{ old('secondary_color', $settings->secondary_color) }}" 
                                       class="h-12 w-20 border border-gray-300 rounded cursor-pointer" required>
                                <input type="text" value="{{ $settings->secondary_color }}" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded bg-gray-50" readonly>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">Gradients, accents</p>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Accent Color *</label>
                            <div class="flex items-center space-x-2">
                                <input type="color" name="accent_color" value="{{ old('accent_color', $settings->accent_color) }}" 
                                       class="h-12 w-20 border border-gray-300 rounded cursor-pointer" required>
                                <input type="text" value="{{ $settings->accent_color }}" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded bg-gray-50" readonly>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">Success, highlights</p>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Text Color *</label>
                            <div class="flex items-center space-x-2">
                                <input type="color" name="text_color" value="{{ old('text_color', $settings->text_color) }}" 
                                       class="h-12 w-20 border border-gray-300 rounded cursor-pointer" required>
                                <input type="text" value="{{ $settings->text_color }}" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded bg-gray-50" readonly>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">On colored backgrounds</p>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="mt-6 p-6 rounded-lg" style="background: linear-gradient(135deg, {{ $settings->primary_color }}, {{ $settings->secondary_color }});">
                        <h3 class="text-2xl font-bold mb-2" style="color: {{ $settings->text_color }}">{{ $settings->company_name }}</h3>
                        <p style="color: {{ $settings->text_color }}; opacity: 0.9;">This is how your brand colors will appear</p>
                        <button type="button" class="mt-3 px-6 py-2 rounded font-semibold" style="background: {{ $settings->accent_color }}; color: white;">
                            Sample Button
                        </button>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.dashboard', ['company_code' => request()->route('company_code')]) }}" class="px-6 py-3 bg-gray-500 text-white rounded hover:bg-gray-600 font-semibold">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">
                        <i class="fas fa-save mr-2"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden form to remove logo -->
    <form id="remove-logo-form" action="{{ route('admin.company-settings.remove-logo', ['company_code' => request()->route('company_code')]) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

@push('scripts')
<script>
    // Update hex values when color picker changes
    document.querySelectorAll('input[type="color"]').forEach(colorInput => {
        colorInput.addEventListener('input', function() {
            this.nextElementSibling.value = this.value;
        });
    });
</script>
@endpush
@endsection
