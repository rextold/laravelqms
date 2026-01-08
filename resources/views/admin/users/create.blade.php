@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Create User</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.store') }}" method="POST" class="bg-white p-6 rounded-lg shadow">
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
                    <label for="short_description" class="block text-gray-700 font-semibold mb-2">Short Description</label>
                    <input type="text" id="short_description" name="short_description" value="{{ old('short_description') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
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
    counterFields.style.display = role === 'counter' ? 'block' : 'none';
}
</script>
@endpush
@endsection
