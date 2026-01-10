@extends('layouts.app')

@section('title', 'Video & Display Management')
@section('page-title', 'Video & Display Management')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-6 py-8 h-full">
    <!-- Main Content (Left) -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Display Controls Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-600">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Display Controls</h2>
                    <p class="text-sm text-gray-500 mt-1">Manage video playback and audio settings</p>
                </div>
                <div id="controlStatus" class="flex items-center space-x-2 px-4 py-2 rounded-full bg-green-100">
                    <i class="fas fa-circle text-green-500 animate-pulse"></i>
                    <span class="text-sm font-semibold text-green-700">Live Control</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Video Controls -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-video text-blue-600 mr-2"></i>Video Control
                    </h3>
                    <div class="space-y-4">
                        <button onclick="togglePlay()" id="playBtn" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 font-semibold flex items-center justify-center transition-all shadow-md">
                            <i class="fas" id="playIcon"></i> <span id="playText" class="ml-2"></span>
                        </button>
                        <div class="flex items-center space-x-4">
                            <label class="text-gray-700 font-medium w-24">Volume:</label>
                            <input type="range" id="volumeSlider" min="0" max="100" value="{{ $control->volume }}" 
                                   class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" oninput="updateVolumeDisplay(this.value)" onchange="updateVolume(this.value)">
                            <span id="volumeValue" class="font-bold text-gray-700 w-12 text-right">{{ $control->volume }}%</span>
                        </div>
                        <div id="volumeUpdateStatus" class="text-xs text-green-600 hidden flex items-center">
                            <i class="fas fa-check-circle mr-1"></i> Updated
                        </div>
                    </div>
                </div>

                <!-- Bell Settings -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-bell text-amber-600 mr-2"></i>Notification Bell
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <label class="text-gray-700 font-medium w-24">Volume:</label>
                            <input type="range" id="bellVolumeSlider" min="0" max="100" value="{{ $control->bell_volume ?? 100 }}" 
                                   class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" oninput="updateBellVolumeDisplay(this.value)" onchange="updateBellVolume(this.value)">
                            <span id="bellVolumeValue" class="font-bold text-gray-700 w-12 text-right">{{ $control->bell_volume ?? 100 }}%</span>
                        </div>
                        <div id="bellVolumeUpdateStatus" class="text-xs text-green-600 hidden flex items-center">
                            <i class="fas fa-check-circle mr-1"></i> Updated
                        </div>
                        <div class="bg-gray-50 rounded p-3">
                            <p class="text-xs text-gray-600 mb-2">Current: <strong>{{ $control->bell_sound_path ? basename($control->bell_sound_path) : 'Default Bell' }}</strong></p>
                            <div class="flex space-x-2">
                                <button type="button" onclick="document.getElementById('bellSoundInput').click()" class="text-xs bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 flex-1">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                                @if($control->bell_sound_path)
                                    <button type="button" onclick="resetBellSoundModal()" class="text-xs bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700 flex-1">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bell Upload Form (hidden) -->
            <form action="{{ route('admin.videos.upload-bell', ['organization_code' => request()->route('organization_code')]) }}" method="POST" enctype="multipart/form-data" id="bellForm" class="hidden">
                @csrf
                <input type="file" id="bellSoundInput" name="bell_sound" accept="audio/*" onchange="document.getElementById('bellForm').submit()">
            </form>
        </div>

        <!-- Add Video Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-600">
            <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                <i class="fas fa-plus-circle text-green-600 mr-2"></i>Add New Video
            </h2>
            <p class="text-sm text-gray-500 mb-6">Upload from file or paste YouTube link</p>

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <div class="font-semibold mb-2">Errors:</div>
                    <ul class="list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.videos.store', ['organization_code' => request()->route('organization_code')]) }}" method="POST" enctype="multipart/form-data" id="videoForm">
                @csrf
                <!-- Video Type Selector -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-3">Video Source *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-600 transition" id="fileLabel">
                            <input type="radio" name="video_type" value="file" checked onchange="toggleVideoType()" class="w-4 h-4">
                            <span class="ml-3"><i class="fas fa-file-video text-blue-600 mr-2"></i><strong>Upload File</strong></span>
                        </label>
                        <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-red-600 transition" id="youtubeLabel">
                            <input type="radio" name="video_type" value="youtube" onchange="toggleVideoType()" class="w-4 h-4">
                            <span class="ml-3"><i class="fab fa-youtube text-red-600 mr-2"></i><strong>YouTube</strong></span>
                        </label>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Video Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g., Welcome Video"
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-600 focus:outline-none transition @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- File Input -->
                    <div id="fileInput">
                        <label class="block text-gray-700 font-semibold mb-2">Video File * (Max {{ $maxUploadLabel }})</label>
                        <input type="file" name="video" accept="video/*" id="videoFile"
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-600 focus:outline-none transition @error('video') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">MP4, AVI, MOV, WMV</p>
                        @error('video')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <div id="fileValidation" class="text-xs mt-2 space-y-1"></div>
                    </div>

                    <!-- YouTube URL -->
                    <div id="youtubeInput" style="display: none;">
                        <label class="block text-gray-700 font-semibold mb-2">YouTube URL *</label>
                        <input type="url" name="youtube_url" id="youtubeUrl" value="{{ old('youtube_url') }}" placeholder="https://www.youtube.com/watch?v=..."
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-600 focus:outline-none transition @error('youtube_url') border-red-500 @enderror">
                        @error('youtube_url')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-2 rounded-lg hover:from-green-700 hover:to-green-800 font-semibold flex items-center justify-center transition-all shadow-md" id="submitBtn">
                            <i class="fas fa-upload mr-2"></i> Add Video
                        </button>
                    </div>
                </div>

                <!-- Upload Progress -->
                <div id="uploadProgress" class="hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-700"><i class="fas fa-spinner animate-spin mr-2"></i>Uploading...</span>
                        <span id="uploadPercent" class="text-sm font-semibold text-blue-600">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div id="uploadBar" class="bg-gradient-to-r from-blue-600 to-blue-700 h-3 rounded-full transition-all duration-200" style="width: 0%"></div>
                    </div>
                    <div id="uploadStatus" class="text-xs mt-2 text-gray-600"></div>
                </div>
            </form>
        </div>

        <!-- Videos List Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-600">
            <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                <i class="fas fa-list text-purple-600 mr-2"></i>Uploaded Videos
            </h2>
            <p class="text-sm text-gray-500 mb-6">Manage and organize your video library</p>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-300 bg-gray-50">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Order</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Title</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Type</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="videos-list" class="divide-y divide-gray-200">
                        @forelse($videos as $video)
                        <tr class="hover:bg-gray-50 transition" data-id="{{ $video->id }}">
                            <td class="py-3 px-4 text-gray-700 font-semibold">{{ $video->order }}</td>
                            <td class="py-3 px-4 text-gray-800">
                                {{ $video->title }}
                                @if($video->isYoutube())
                                    <a href="{{ $video->youtube_url }}" target="_blank" class="text-red-600 hover:text-red-800 ml-2 inline-block">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($video->isYoutube())
                                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        <i class="fab fa-youtube mr-1"></i>YouTube
                                    </span>
                                @else
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        <i class="fas fa-file-video mr-1"></i>File
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="status-badge {{ $video->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} px-3 py-1 rounded-full text-xs font-semibold">
                                    {{ $video->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex justify-center space-x-3">
                                    <button type="button" class="text-purple-600 hover:text-purple-800 hover:scale-110 transition" title="Preview"
                                            onclick="previewVideo(this)"
                                            data-type="{{ $video->isYoutube() ? 'youtube' : 'file' }}"
                                            data-title="{{ $video->title }}"
                                            data-file="{{ $video->isFile() ? asset('storage/'.$video->file_path) : '' }}"
                                            data-youtube="{{ $video->isYoutube() ? $video->youtube_embed_url : '' }}">
                                        <i class="fas fa-play-circle text-lg"></i>
                                    </button>
                                    <button onclick="toggleActive({{ $video->id }})" class="text-blue-600 hover:text-blue-800 hover:scale-110 transition" title="Toggle">
                                        <i class="fas fa-toggle-on text-lg"></i>
                                    </button>
                                    <button type="button" 
                                            data-video-id="{{ $video->id }}"
                                            data-video-filename="{{ addslashes($video->filename) }}"
                                            data-delete-url="{{ route('admin.videos.destroy', ['organization_code' => request()->route('organization_code'), 'video' => $video->id]) }}"
                                            onclick="openDeleteVideoModal(this)"
                                            class="text-red-600 hover:text-red-800 hover:scale-110 transition" title="Delete">
                                        <i class="fas fa-trash text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">
                                <i class="fas fa-video text-4xl mb-2 opacity-20"></i>
                                <p>No videos yet. Start by adding your first video!</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Live Monitor Panel (Right) -->
    <div class="lg:col-span-1 sticky top-24 h-fit space-y-6">
        <!-- Monitor Header -->
        <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-2xl font-bold">Live Monitor</h2>
                <i class="fas fa-tv text-3xl opacity-80"></i>
            </div>
            <p class="text-indigo-100 text-sm">Real-time display controls</p>
        </div>

        <!-- Counter Status -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-indigo-600">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-users text-indigo-600 mr-2"></i>Active Counters
            </h3>
            <div id="countersContainer" class="space-y-3 max-h-64 overflow-y-auto">
                <div class="text-gray-500 text-center py-4">
                    <i class="fas fa-spinner animate-spin mr-2"></i>Loading...
                </div>
            </div>
        </div>

        <!-- Current Queue Display -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-600">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-queue-list text-green-600 mr-2"></i>Queue Info
            </h3>
            <div id="queueContainer" class="space-y-3 max-h-64 overflow-y-auto">
                <div class="text-gray-500 text-center py-4">
                    <i class="fas fa-spinner animate-spin mr-2"></i>Loading...
                </div>
            </div>
        </div>

        <!-- Auto-refresh Toggle -->
        <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" id="autoRefresh" checked class="w-4 h-4 text-blue-600 rounded">
                <span class="ml-2 text-sm font-semibold text-gray-700">Auto-refresh every 3s</span>
            </label>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl mx-4 relative">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500">Preview</p>
                <h3 id="previewTitle" class="text-xl font-bold text-gray-800"></h3>
            </div>
            <button onclick="closePreview()" class="text-gray-500 hover:text-gray-800 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div id="filePreview" class="hidden">
                <video id="filePlayer" controls class="w-full rounded-lg bg-black max-h-96" preload="metadata"></video>
            </div>
            <div id="youtubePreview" class="hidden">
                <div class="aspect-video bg-black rounded-lg overflow-hidden">
                    <iframe id="ytPlayer" class="w-full h-full" src="" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </div>
            </div>
            <div class="flex gap-3">
                <button onclick="playPreview()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-semibold">
                    <i class="fas fa-play mr-2"></i>Play
                </button>
                <button onclick="pausePreview()" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 font-semibold">
                    <i class="fas fa-pause mr-2"></i>Pause
                </button>
                <button onclick="closePreview()" class="ml-auto text-gray-600 hover:text-gray-900 px-6 py-2">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Video Modal -->
<div id="delete-video-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 rounded-t-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Delete Video
                </h3>
                <button onclick="closeModalVideo('delete-video-modal')" class="text-white hover:text-gray-200 text-2xl">&times;</button>
            </div>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-2">Delete this video?</p>
            <p class="text-sm text-gray-500 font-mono bg-gray-50 p-2 rounded mb-4"><strong id="delete-video-name"></strong></p>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-sm text-yellow-800"><i class="fas fa-info-circle mr-2"></i>This cannot be undone.</p>
            </div>
        </div>
        <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end gap-3 border-t">
            <button onclick="closeModalVideo('delete-video-modal')" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold">Cancel</button>
            <button type="button" onclick="confirmDeleteVideo()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold">
                <i class="fas fa-trash mr-2"></i>Delete
            </button>
        </div>
    </div>
</div>

<!-- Reset Bell Modal -->
<div id="reset-bell-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 rounded-t-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-bell mr-2"></i>Reset Bell Sound
                </h3>
                <button onclick="closeModalReset('reset-bell-modal')" class="text-white hover:text-gray-200 text-2xl">&times;</button>
            </div>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-4">Reset to default bell sound?</p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-sm text-blue-800"><i class="fas fa-info-circle mr-2"></i>Your custom bell will be replaced with the system default.</p>
            </div>
        </div>
        <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end gap-3 border-t">
            <button onclick="closeModalReset('reset-bell-modal')" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold">Cancel</button>
            <button onclick="confirmResetBell()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                <i class="fas fa-check mr-2"></i>Reset
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let isPlaying = {{ $control->is_playing ? 'true' : 'false' }};
let currentPreview = { type: null, fileSrc: null, youtubeSrc: null };
let autoRefreshInterval = null;
const orgCode = '{{ request()->route("organization_code") }}';

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updatePlayButton();
    refreshMonitorData();
    
    document.getElementById('autoRefresh').addEventListener('change', function(e) {
        if (e.target.checked) {
            startAutoRefresh();
        } else {
            stopAutoRefresh();
        }
    });
    
    startAutoRefresh();
});

function startAutoRefresh() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshInterval = setInterval(refreshMonitorData, 3000);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
}

function refreshMonitorData() {
    if (!document.getElementById('autoRefresh').checked) return;
    
    fetch(`/${orgCode}/monitor/data`)
        .then(response => response.json())
        .then(data => {
            updateCountersDisplay(data.counters);
            updateQueuesDisplay(data.waiting_queues);
        })
        .catch(error => console.error('Monitor refresh failed:', error));
}

function updateCountersDisplay(counters) {
    const container = document.getElementById('countersContainer');
    
    if (!counters || counters.length === 0) {
        container.innerHTML = '<div class="text-gray-500 text-center py-4">No active counters</div>';
        return;
    }
    
    container.innerHTML = counters.map(item => {
        const counter = item.counter;
        const queue = item.queue;
        const statusColor = queue ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
        const statusText = queue ? `Serving: #${queue.queue_number}` : 'Idle';
        
        return `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                <div>
                    <p class="font-semibold text-gray-800">Counter ${counter.counter_number}</p>
                    <p class="text-xs text-gray-500">${counter.display_name}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColor}">
                    ${statusText}
                </span>
            </div>
        `;
    }).join('');
}

function updateQueuesDisplay(queues) {
    const container = document.getElementById('queueContainer');
    
    if (!queues || queues.length === 0) {
        container.innerHTML = '<div class="text-gray-500 text-center py-4">No waiting queues</div>';
        return;
    }
    
    container.innerHTML = queues.map(group => {
        const queueNumbers = group.queues.map(q => `#${q.queue_number}`).join(', ');
        return `
            <div class="border-l-4 border-indigo-600 bg-indigo-50 p-3 rounded">
                <p class="text-sm font-semibold text-gray-800">${group.counter_number}</p>
                <p class="text-xs text-gray-600">Waiting: ${queueNumbers}</p>
            </div>
        `;
    }).join('');
}

// Video management functions
document.getElementById('videoFile')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const validationDiv = document.getElementById('fileValidation');
    
    if (!file) {
        validationDiv.innerHTML = '';
        return;
    }
    
    let validation = '';
    const maxSize = 500 * 1024 * 1024;
    
    if (file.size > maxSize) {
        validation += '<span class="text-red-600"><i class="fas fa-times-circle"></i> File too large</span>';
    } else {
        validation += '<span class="text-green-600"><i class="fas fa-check-circle"></i> Size: ' + (file.size / (1024*1024)).toFixed(2) + 'MB</span>';
    }
    
    const validTypes = ['video/mp4', 'video/x-msvideo', 'video/quicktime', 'video/x-ms-wmv'];
    if (!validTypes.includes(file.type)) {
        validation += '<br><span class="text-orange-600"><i class="fas fa-exclamation-circle"></i> Format: ' + (file.type || 'Unknown') + '</span>';
    }
    
    validationDiv.innerHTML = validation;
});

document.getElementById('videoForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const videoType = document.querySelector('input[name="video_type"]:checked').value;
    const progressDiv = document.getElementById('uploadProgress');
    const uploadBar = document.getElementById('uploadBar');
    const uploadPercent = document.getElementById('uploadPercent');
    const uploadStatus = document.getElementById('uploadStatus');
    const submitBtn = document.getElementById('submitBtn');
    
    progressDiv.classList.remove('hidden');
    uploadStatus.innerHTML = '<span class="text-blue-600"><i class="fas fa-spinner animate-spin"></i> Preparing...</span>';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            uploadPercent.textContent = Math.round(percentComplete) + '%';
            uploadBar.style.width = percentComplete + '%';
        }
    });
    
    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success && response.video) {
                    uploadPercent.textContent = '100%';
                    uploadBar.style.width = '100%';
                    uploadStatus.innerHTML = '<span class="text-green-600"><i class="fas fa-check-circle"></i> Added!</span>';
                    appendVideoToTable(response.video);
                    
                    setTimeout(() => {
                        document.getElementById('videoForm').reset();
                        progressDiv.classList.add('hidden');
                        uploadBar.style.width = '0%';
                        submitBtn.disabled = false;
                    }, 1000);
                }
            } catch (error) {
                uploadStatus.innerHTML = '<span class="text-red-600"><i class="fas fa-exclamation-circle"></i> Error</span>';
                submitBtn.disabled = false;
            }
        }
    });
    
    xhr.open('POST', this.action, true);
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.send(formData);
});

function appendVideoToTable(video) {
    const tbody = document.getElementById('videos-list');
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-gray-50 transition';
    tr.dataset.id = video.id;
    
    const typeBadge = video.is_youtube 
        ? '<span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold"><i class="fab fa-youtube mr-1"></i>YouTube</span>'
        : '<span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold"><i class="fas fa-file-video mr-1"></i>File</span>';
    
    const titleCell = video.is_youtube
        ? video.title + ' <a href="' + video.youtube_url + '" target="_blank" class="text-blue-600 hover:text-blue-800 ml-2"><i class="fab fa-youtube"></i></a>'
        : video.title;
    
    const escapedFilename = (video.filename || video.title).replace(/'/g, "\\'");
    
    tr.innerHTML = `
        <td class="py-3 px-4 text-gray-700 font-semibold">${video.order}</td>
        <td class="py-3 px-4 text-gray-800">${titleCell}</td>
        <td class="py-3 px-4">${typeBadge}</td>
        <td class="py-3 px-4">
            <span class="status-badge ${video.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'} px-3 py-1 rounded-full text-xs font-semibold">
                ${video.is_active ? 'Active' : 'Inactive'}
            </span>
        </td>
        <td class="py-3 px-4 text-center">
            <div class="flex justify-center space-x-3">
                <button type="button" class="text-purple-600 hover:text-purple-800 hover:scale-110 transition"
                        onclick="previewVideo(this)"
                        data-type="${video.is_youtube ? 'youtube' : 'file'}"
                        data-title="${video.title}"
                        data-file="${video.is_file ? video.file_path : ''}"
                        data-youtube="${video.is_youtube ? video.youtube_embed_url : ''}">
                    <i class="fas fa-play-circle text-lg"></i>
                </button>
                <button onclick="toggleActive(${video.id})" class="text-blue-600 hover:text-blue-800 hover:scale-110 transition">
                    <i class="fas fa-toggle-on text-lg"></i>
                </button>
                <button type="button" 
                        data-video-id="${video.id}"
                        data-video-filename="${escapedFilename}"
                        data-delete-url="/${orgCode}/admin/videos/${video.id}"
                        onclick="openDeleteVideoModal(this)"
                        class="text-red-600 hover:text-red-800 hover:scale-110 transition">
                    <i class="fas fa-trash text-lg"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.appendChild(tr);
    tr.style.backgroundColor = '#dcfce7';
    setTimeout(() => {
        tr.style.transition = 'background-color 1.5s ease';
        tr.style.backgroundColor = '';
    }, 100);
}

function previewVideo(btn) {
    const modal = document.getElementById('previewModal');
    const titleEl = document.getElementById('previewTitle');
    const fileWrap = document.getElementById('filePreview');
    const ytWrap = document.getElementById('youtubePreview');
    const filePlayer = document.getElementById('filePlayer');
    const ytPlayer = document.getElementById('ytPlayer');

    const type = btn.dataset.type;
    const title = btn.dataset.title;
    const file = btn.dataset.file;
    const youtube = btn.dataset.youtube;

    filePlayer.pause();
    filePlayer.removeAttribute('src');
    ytPlayer.src = '';

    if (type === 'file' && file) {
        fileWrap.classList.remove('hidden');
        ytWrap.classList.add('hidden');
        filePlayer.src = file;
        filePlayer.play();
    } else if (type === 'youtube' && youtube) {
        fileWrap.classList.add('hidden');
        ytWrap.classList.remove('hidden');
        ytPlayer.src = addOrReplaceParam(addOrReplaceParam(youtube, 'enablejsapi', 1), 'autoplay', 1);
    }

    titleEl.textContent = title;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePreview() {
    const modal = document.getElementById('previewModal');
    document.getElementById('filePlayer').pause();
    document.getElementById('ytPlayer').src = '';
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function playPreview() {
    if (document.getElementById('filePlayer').src) {
        document.getElementById('filePlayer').play();
    }
}

function pausePreview() {
    document.getElementById('filePlayer').pause();
}

function addOrReplaceParam(url, key, value) {
    if (!url) return '';
    const re = new RegExp('([?&])' + key + '=.*?(&|$)', 'i');
    const separator = url.indexOf('?') !== -1 ? '&' : '?';
    if (url.match(re)) {
        return url.replace(re, '$1' + key + '=' + value + '$2');
    }
    return url + separator + key + '=' + value;
}

function toggleVideoType() {
    const videoType = document.querySelector('input[name="video_type"]:checked').value;
    const fileInput = document.getElementById('fileInput');
    const youtubeInput = document.getElementById('youtubeInput');
    const videoFile = document.getElementById('videoFile');
    const youtubeUrl = document.getElementById('youtubeUrl');
    
    if (videoType === 'file') {
        fileInput.style.display = 'block';
        youtubeInput.style.display = 'none';
        videoFile.required = true;
        youtubeUrl.required = false;
    } else {
        fileInput.style.display = 'none';
        youtubeInput.style.display = 'block';
        videoFile.required = false;
        youtubeUrl.required = true;
    }
}

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

function togglePlay() {
    isPlaying = !isPlaying;
    updateControl();
    updatePlayButton();
}

function updateVolume(value) {
    document.getElementById('volumeValue').textContent = value + '%';
    updateControl();
    showUpdateStatus('volumeUpdateStatus');
}

function updateVolumeDisplay(value) {
    document.getElementById('volumeValue').textContent = value + '%';
}

function updateBellVolume(value) {
    document.getElementById('bellVolumeValue').textContent = value + '%';
    updateControl();
    showUpdateStatus('bellVolumeUpdateStatus');
}

function updateBellVolumeDisplay(value) {
    document.getElementById('bellVolumeValue').textContent = value + '%';
}

function showUpdateStatus(elementId) {
    const statusEl = document.getElementById(elementId);
    statusEl.classList.remove('hidden');
    setTimeout(() => {
        statusEl.classList.add('hidden');
    }, 2000);
}

function updateControl() {
    fetch(`/${orgCode}/admin/videos/control`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            is_playing: isPlaying,
            volume: document.getElementById('volumeSlider').value,
            bell_volume: document.getElementById('bellVolumeSlider').value
        })
    })
    .catch(error => console.error('Control update failed:', error));
}

function resetBellSoundModal() {
    document.getElementById('reset-bell-modal').classList.remove('hidden');
}

function toggleActive(videoId) {
    fetch(`/${orgCode}/admin/videos/${videoId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) location.reload();
    });
}

function openDeleteVideoModal(button) {
    document.getElementById('delete-video-name').textContent = button.dataset.videoFilename;
    window.currentDeleteUrl = button.dataset.deleteUrl;
    document.getElementById('delete-video-modal').classList.remove('hidden');
}

function closeModalVideo(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function closeModalReset(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function confirmDeleteVideo() {
    fetch(window.currentDeleteUrl, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(() => {
        closeModalVideo('delete-video-modal');
        location.reload();
    })
    .catch(error => {
        alert('Error deleting video: ' + error.message);
    });
}

function confirmResetBell() {
    fetch(`/${orgCode}/admin/videos/reset-bell`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(() => {
        closeModalReset('reset-bell-modal');
        location.reload();
    });
}

document.getElementById('previewModal')?.addEventListener('click', function(e) {
    if (e.target === this) closePreview();
});

document.getElementById('delete-video-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeModalVideo('delete-video-modal');
});

document.getElementById('reset-bell-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeModalReset('reset-bell-modal');
});
</script>
@endpush

@endsection
