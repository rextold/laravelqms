@extends('layouts.app')

@section('title', 'Create User')

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

        <form action="{{ auth()->user()->isSuperAdmin() ? route('superadmin.users.store') : route('admin.users.store', ['company_code' => request()->route('company_code')]) }}" method="POST" class="bg-white p-6 rounded-lg shadow">
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

            @if(auth()->user()->isSuperAdmin() && $companies->isNotEmpty())
            <div class="mb-4">
                <label for="company_id" class="block text-gray-700 font-semibold mb-2">Organization *</label>
                <select id="company_id" name="company_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                        required>
                    <option value="">Select Organization</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->company_name }} ({{ $company->company_code }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div id="counter-fields" style="display: {{ old('role') === 'counter' ? 'block' : 'none' }}">
                <div class="mb-4">
                    <label for="display_name" class="block text-gray-700 font-semibold mb-2">Display Name *</label>
                    <input type="text" id="display_name" name="display_name" value="{{ old('display_name') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label for="counter_number" class="block text-gray-700 font-semibold mb-2">Counter Number *</label>
                    <input type="number" id="counter_number" name="counter_number" value="{{ old('counter_number') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label for="priority_code" class="block text-gray-700 font-semibold mb-2">Priority Code (Short Code)</label>
                    <input type="text" id="priority_code" name="priority_code" value="{{ old('priority_code') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                           placeholder="e.g., C1">
                    <p class="text-sm text-gray-500 mt-1">Used as the prefix for priority numbers (e.g., C1-1001)</p>
                </div>

                <div class="mb-4">
                    <label for="short_description" class="block text-gray-700 font-semibold mb-2">Short Description</label>
                    <input type="text" id="short_description" name="short_description" value="{{ old('short_description') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ auth()->user()->isSuperAdmin() ? route('superadmin.users.index') : route('admin.users.index', ['company_code' => request()->route('company_code')]) }}" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
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
    if (role === 'counter') {
        counterFields.style.display = 'block';
    } else {
        // Clear counter fields when switching to non-counter role
        document.getElementById('display_name').value = '';
        document.getElementById('counter_number').value = '';
        document.getElementById('priority_code').value = '';
        document.getElementById('short_description').value = '';
        counterFields.style.display = 'none';
    }
}
</script>
@endpush
@endsection
