@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl shadow-lg hover:shadow-2xl transition-all transform hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Online Counters</p>
                    <p class="text-4xl font-bold text-white">{{ $onlineCounters->count() }}</p>
                    <p class="text-green-100 text-xs mt-2"><i class="fas fa-circle animate-pulse"></i> Active Now</p>
                </div>
                <div class="p-4 bg-white bg-opacity-20 rounded-full">
                    <i class="fas fa-users text-white text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl shadow-lg hover:shadow-2xl transition-all transform hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">Total Counters</p>
                    <p class="text-4xl font-bold text-white">{{ $counters->count() }}</p>
                    <p class="text-blue-100 text-xs mt-2"><i class="fas fa-check-circle"></i> Registered</p>
                </div>
                <div class="p-4 bg-white bg-opacity-20 rounded-full">
                    <i class="fas fa-desktop text-white text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl shadow-lg hover:shadow-2xl transition-all transform hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium mb-1">Queues Today</p>
                    <p class="text-4xl font-bold text-white">{{ \App\Models\Queue::whereDate('created_at', today())->count() }}</p>
                    <p class="text-purple-100 text-xs mt-2"><i class="fas fa-calendar-day"></i> {{ date('M d, Y') }}</p>
                </div>
                <div class="p-4 bg-white bg-opacity-20 rounded-full">
                    <i class="fas fa-list text-white text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-list-ul mr-3 text-blue-600"></i> Counter Status
            </h2>
            <span class="text-sm text-gray-500"><i class="fas fa-sync-alt animate-spin"></i> Live</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Counter #</th>
                        <th class="text-left py-3 px-4">Display Name</th>
                        <th class="text-left py-3 px-4">Description</th>
                        <th class="text-left py-3 px-4">Status</th>
                        <th class="text-left py-3 px-4">Queues Today</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($counters as $counter)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4">{{ $counter->counter_number }}</td>
                        <td class="py-3 px-4">{{ $counter->display_name }}</td>
                        <td class="py-3 px-4">{{ $counter->short_description }}</td>
                        <td class="py-3 px-4">
                            @if($counter->is_online)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Online</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm">Offline</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            {{ $counter->queues()->whereDate('created_at', today())->count() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
