@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-users text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Online Counters</p>
                    <p class="text-2xl font-bold">{{ $onlineCounters->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-desktop text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Total Counters</p>
                    <p class="text-2xl font-bold">{{ $counters->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-list text-purple-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Total Queues Today</p>
                    <p class="text-2xl font-bold">{{ \App\Models\Queue::whereDate('created_at', today())->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Counter Status</h2>
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
