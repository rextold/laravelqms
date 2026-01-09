@extends('layouts.app')

@section('title', auth()->user()->isSuperAdmin() ? 'Add Admin' : 'Add Counter')
@section('page-title', auth()->user()->isSuperAdmin() ? 'Add Admin' : 'Add Counter')

@section('content')
@php
    $routePrefix = auth()->user()->isSuperAdmin() ? 'superadmin' : 'admin';
@endphp
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Create New User</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ auth()->user()->isSuperAdmin() ? route('superadmin.users.store') : route('admin.users.store', ['organization_code' => request()->route('organization_code')]) }}" method="POST" class="bg-white p-6 rounded-lg shadow">
            @csrf

            <div class="mb-4">
                <label for="username" class="block text-gray-700 font-semibold mb-2">Username *</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                       required>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-semibold mb-2">Password *</label>
                <input type="password" id="password" name="password" 
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                       required>
            </div>

            <div class="mb-4">
                <label for="role" class="block text-gray-700 font-semibold mb-2">Role *</label>
                <select id="role" name="role" 
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                        required onchange="toggleCounterFields(this.value)">
                    <option value="">Select Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>
                            {{ ucfirst($role) }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(auth()->user()->isSuperAdmin() && $organizations->isNotEmpty())
            <div class="mb-4">
                <label for="organization_id" class="block text-gray-700 font-semibold mb-2">Organization *</label>
                <select id="organization_id" name="organization_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                        required>
                    <option value="">Select Organization</option>
                    @foreach($organizations as $organization)
                        <option value="{{ $organization->id }}" {{ old('organization_id') == $organization->id ? 'selected' : '' }}>
                            {{ $organization->organization_name }} ({{ $organization->organization_code }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div id="counter-fields" style="display: {{ old('role') === 'counter' ? 'block' : 'none' }}">
                <div class="mb-4">
                    <label for="display_name" class="block text-gray-700 font-semibold mb-2">
                        Display Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="display_name" name="display_name" value="{{ old('display_name') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 @error('display_name') border-red-500 @enderror">
                    @error('display_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="counter_number" class="block text-gray-700 font-semibold mb-2">
                        Counter Number <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="counter_number" name="counter_number" value="{{ old('counter_number') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 @error('counter_number') border-red-500 @enderror"
                           min="1">
                    @error('counter_number')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="priority_code" class="block text-gray-700 font-semibold mb-2">Priority Code (Short Code)</label>
                    <input type="text" id="priority_code" name="priority_code" value="{{ old('priority_code') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 @error('priority_code') border-red-500 @enderror"
                           placeholder="e.g., C1">
                    <p class="text-sm text-gray-500 mt-1">Used as the prefix for priority numbers (e.g., C1-1001)</p>
                    @error('priority_code')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="short_description" class="block text-gray-700 font-semibold mb-2">Short Description</label>
                    <input type="text" id="short_description" name="short_description" value="{{ old('short_description') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 @error('short_description') border-red-500 @enderror">
                    @error('short_description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ auth()->user()->isSuperAdmin() ? route('superadmin.users.index') : route('admin.users.index', ['organization_code' => request()->route('organization_code')]) }}" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleCounterFields(role) {
    const counterFields = document.getElementById('counter-fields');
    const displayName = document.getElementById('display_name');
    const counterNumber = document.getElementById('counter_number');
    
    if (role === 'counter') {
        counterFields.style.display = 'block';
        // Make fields required
        displayName.setAttribute('required', 'required');
        counterNumber.setAttribute('required', 'required');
    } else {
        counterFields.style.display = 'none';
        // Remove required attribute and clear values
        displayName.removeAttribute('required');
        counterNumber.removeAttribute('required');
        displayName.value = '';
        counterNumber.value = '';
        document.getElementById('priority_code').value = '';
        document.getElementById('short_description').value = '';
    }
}

// On page load, ensure proper state
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    if (roleSelect.value === 'counter') {
        toggleCounterFields('counter');
    }
});
</script>
@endpush
@endsection
