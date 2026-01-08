@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Manage Users</h1>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add User
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left py-3 px-4">Username</th>
                    <th class="text-left py-3 px-4">Role</th>
                    <th class="text-left py-3 px-4">Display Name</th>
                    <th class="text-left py-3 px-4">Counter #</th>
                    <th class="text-left py-3 px-4">Status</th>
                    <th class="text-left py-3 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4">{{ $user->username }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded text-sm
                            @if($user->role === 'superadmin') bg-red-100 text-red-800
                            @elseif($user->role === 'admin') bg-blue-100 text-blue-800
                            @else bg-green-100 text-green-800
                            @endif">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="py-3 px-4">{{ $user->display_name ?? '-' }}</td>
                    <td class="py-3 px-4">{{ $user->counter_number ?? '-' }}</td>
                    <td class="py-3 px-4">
                        @if($user->isCounter())
                            @if($user->is_online)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Online</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm">Offline</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
