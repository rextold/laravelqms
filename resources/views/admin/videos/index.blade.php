@extends('layouts.app')

@section('title', 'Video Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Video Management</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Video Controls -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Display Controls</h2>
        <div class="flex items-center space-x-4">
            <button onclick="togglePlay()" id="playBtn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas" id="playIcon"></i> <span id="playText"></span>
            </button>
            <div class="flex items-center">
                <label class="mr-2">Volume:</label>
                <input type="range" id="volumeSlider" min="0" max="100" value="{{ $control->volume }}" 
                       class="w-32" onchange="updateVolume(this.value)">
                <span id="volumeValue" class="ml-2">{{ $control->volume }}%</span>
            </div>
        </div>
    </div>

    <!-- Upload Video -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Upload New Video</h2>
        <form action="{{ route('admin.videos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Title *</label>
                    <input type="text" name="title" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Video File * (MP4, AVI, MOV)</label>
                    <input type="file" name="video" accept="video/*" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Upload Video
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Videos List -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Videos</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left py-3 px-4">Order</th>
                        <th class="text-left py-3 px-4">Title</th>
                        <th class="text-left py-3 px-4">Status</th>
                        <th class="text-left py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody id="videos-list">
                    @foreach($videos as $video)
                    <tr class="border-b hover:bg-gray-50" data-id="{{ $video->id }}">
                        <td class="py-3 px-4">{{ $video->order }}</td>
                        <td class="py-3 px-4">{{ $video->title }}</td>
                        <td class="py-3 px-4">
                            <span class="status-badge {{ $video->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} px-2 py-1 rounded text-sm">
                                {{ $video->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex space-x-2">
                                <button onclick="toggleActive({{ $video->id }})" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-toggle-on"></i>
                                </button>
                                <form action="{{ route('admin.videos.destroy', $video) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
let isPlaying = {{ $control->is_playing ? 'true' : 'false' }};

function updatePlayButton() {
    const playIcon = document.getElementById('playIcon');
    const playText = document.getElementById('playText');
    if (isPlaying) {
        playIcon.className = 'fas fa-pause';
        playText.textContent = 'Pause';
    } else {
        playIcon.className = 'fas fa-play';
        playText.textContent = 'Play';
    }
}

updatePlayButton();

function togglePlay() {
    isPlaying = !isPlaying;
    updateControl();
    updatePlayButton();
}

function updateVolume(value) {
    document.getElementById('volumeValue').textContent = value + '%';
    updateControl();
}

function updateControl() {
    fetch('{{ route('admin.videos.control') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            is_playing: isPlaying,
            volume: document.getElementById('volumeSlider').value
        })
    });
}

function toggleActive(videoId) {
    fetch(`/admin/videos/${videoId}/toggle`, {
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
