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

        <form action="{{ route('superadmin.companies.update', $company->id) }}" method="POST" class="bg-white p-6 rounded-lg shadow">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="company_code" class="block text-gray-700 font-semibold mb-2">Organization Code *</label>
                <input type="text" id="company_code" name="company_code" value="{{ old('company_code', $company->company_code) }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 uppercase"
                       required>
                <p class="text-sm text-gray-500 mt-1">Must be unique. Use only letters, numbers, dashes, and underscores.</p>
            </div>

            <div class="mb-4">
                <label for="company_name" class="block text-gray-700 font-semibold mb-2">Organization Name *</label>
                <input type="text" id="company_name" name="company_name" value="{{ old('company_name', $company->company_name) }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                       required>
            </div>

            <div class="mb-4">
                <div class="bg-gray-50 p-4 rounded">
                    <p class="text-sm text-gray-600">
                        <strong>Users Count:</strong> {{ $company->users()->count() }}
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Created:</strong> {{ $company->created_at->format('M d, Y h:i A') }}
                    </p>
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $company->is_active) ? 'checked' : '' }} class="mr-2">
                    <span class="text-gray-700 font-semibold">Active</span>
                </label>
                <p class="text-sm text-gray-500 mt-1">Only active companies can be accessed by users.</p>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('superadmin.companies.index') }}" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Update Company
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto-uppercase company code
    document.getElementById('company_code').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });
</script>
@endsection
