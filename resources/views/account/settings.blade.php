@extends('layouts.app')

@section('title', 'Account Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Account Settings</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Account Details -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Account Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="px-4 py-2 bg-gray-50 rounded border border-gray-200">{{ $user->username }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <div class="px-4 py-2 bg-gray-50 rounded border border-gray-200">{{ $user->email ?? 'Not set' }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <div class="px-4 py-2 bg-gray-50 rounded border border-gray-200">
                        <span class="px-2 py-1 rounded text-sm
                            @if($user->role === 'superadmin') bg-purple-100 text-purple-800
                            @elseif($user->role === 'admin') bg-blue-100 text-blue-800
                            @else bg-green-100 text-green-800
                            @endif">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                </div>
                @if($user->organization)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Organization</label>
                    <div class="px-4 py-2 bg-gray-50 rounded border border-gray-200">{{ $user->organization->organization_name }}</div>
                </div>
                @endif
                @if($user->role === 'counter')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
                    <div class="px-4 py-2 bg-gray-50 rounded border border-gray-200">{{ $user->display_name ?? 'Not set' }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Counter Number</label>
                    <div class="px-4 py-2 bg-gray-50 rounded border border-gray-200">{{ $user->counter_number ?? 'Not set' }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Change Password</h2>
            <form action="{{ route('account.update-password', ['organization_code' => request()->route('organization_code')]) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" 
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                           required>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password *</label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                           required>
                    <p class="text-sm text-gray-500 mt-1">Minimum 6 characters</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" 
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                           required>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                        <i class="fas fa-key mr-2"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
