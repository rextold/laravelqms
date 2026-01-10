@extends('layouts.app')

@section('title', 'Edit Organization')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Edit Organization</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('superadmin.organizations.update', $organization->id) }}" method="POST" class="bg-white p-6 rounded-lg shadow">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="organization_code" class="block text-gray-700 font-semibold mb-2">Organization Code *</label>
                <input type="text" id="organization_code" name="organization_code" value="{{ old('organization_code', $organization->organization_code) }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 uppercase"
                       required>
                <p class="text-sm text-gray-500 mt-1">Must be unique. Use only letters, numbers, dashes, and underscores.</p>
            </div>

            <div class="mb-4">
                <label for="organization_name" class="block text-gray-700 font-semibold mb-2">Organization Name *</label>
                <input type="text" id="organization_name" name="organization_name" value="{{ old('organization_name', $organization->organization_name) }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                       required>
            </div>

            <div class="mb-4">
                <div class="bg-gray-50 p-4 rounded">
                    <p class="text-sm text-gray-600">
                        <strong>Users Count:</strong> {{ $organization->users()->count() }}
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Created:</strong> {{ $organization->created_at->format('M d, Y h:i A') }}
                    </p>
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $organization->is_active) ? 'checked' : '' }} class="mr-2">
                    <span class="text-gray-700 font-semibold">Active</span>
                </label>
                <p class="text-sm text-gray-500 mt-1">Only active organizations can be accessed by users.</p>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('superadmin.organizations.index') }}" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Update Organization
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto-uppercase organization code
    document.getElementById('organization_code').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });
</script>
@endsection
