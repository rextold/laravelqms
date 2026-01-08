@extends('layouts.app')

@section('title', 'Marquee Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Marquee Management</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Create Marquee -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Create New Marquee</h2>
        <form action="{{ route('admin.marquee.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-semibold mb-2">Text *</label>
                    <textarea name="text" rows="2" required 
                              class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Speed (10-200)</label>
                    <input type="number" name="speed" value="50" min="10" max="200" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                    <button type="submit" class="mt-2 w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Create
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Marquees List -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Marquee Messages</h2>
        <div class="space-y-4">
            @foreach($marquees as $marquee)
            <div class="border rounded p-4 {{ $marquee->is_active ? 'border-green-500 bg-green-50' : '' }}">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="text-lg">{{ $marquee->text }}</p>
                        <p class="text-sm text-gray-600 mt-1">Speed: {{ $marquee->speed }}</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="toggleActive({{ $marquee->id }})" 
                                class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-toggle-{{ $marquee->is_active ? 'on' : 'off' }}"></i>
                            {{ $marquee->is_active ? 'Active' : 'Inactive' }}
                        </button>
                        <form action="{{ route('admin.marquee.destroy', $marquee) }}" method="POST" class="inline"
                              onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleActive(marqueeId) {
    fetch(`/admin/marquee/${marqueeId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>
@endpush
@endsection
