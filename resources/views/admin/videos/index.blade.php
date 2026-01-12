@extends('layouts.app')

@section('title', 'Video & Display Management')
@section('page-title', 'Video & Display Management')

@section('content')
<div class="min-h-screen bg-gray-900 text-white">
    <!-- Top Bar -->
    <div class="bg-gray-950 border-b border-gray-800 px-4 py-3 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="fas fa-video text-gray-400"></i>
                <h1 class="text-lg font-bold">Video Control</h1>
                <span class="flex items-center space-x-1 px-2 py-0.5 rounded bg-gray-800">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="text-xs font-semibold text-gray-300">Live</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="max-w-7xl mx-auto p-4 space-y-4">
        
        <!-- Video Player Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            
            <!-- Player & Controls (Left 2/3) -->
            <div class="lg:col-span-2 space-y-4">
                
                <!-- Player -->
                <div class="bg-gray-800 rounded-lg overflow-hidden border border-gray-700">
                    <!-- Player Controls -->
                    <div class="bg-gray-900 p-3 space-y-2">
                        
                        <!-- Volume Control -->
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-volume-down text-gray-400 text-xs"></i>
                            <input type="range" id="volumeSlider" min="0" max="100" value="{{ $control->volume }}" 
                                   class="flex-1 h-1 bg-gray-700 rounded-full appearance-none cursor-pointer" 
                                   oninput="updateVolumeDisplay(this.value)" onchange="updateVolume(this.value)">
                            <span id="volumeValue" class="text-xs text-gray-400 w-7 text-right">{{ $control->volume }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Now Playing Info -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-3 space-y-2">
                    <p class="text-xs font-semibold text-gray-400 uppercase">Now Playing</p>
                    <p id="nowPlayingTitle" class="text-sm font-semibold text-white truncate">—</p>
                    <p id="nowPlayingType" class="text-xs text-gray-400">—</p>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-3 gap-2">
                    <button onclick="toggleRepeat()" id="repeatBtn" class="bg-gray-800 hover:bg-gray-700 rounded p-2 text-center transition border border-gray-700" title="Repeat Mode">
                        <i class="fas fa-repeat text-sm block mb-1"></i>
                        <span class="text-xs font-semibold">Off</span>
                    </button>
                    <button onclick="toggleShuffle()" id="shuffleBtn" class="bg-gray-800 hover:bg-gray-700 rounded p-2 text-center transition border border-gray-700" title="Shuffle">
                        <i class="fas fa-shuffle text-sm block mb-1"></i>
                        <span class="text-xs font-semibold">Off</span>
                    </button>
                    <button onclick="toggleSequence()" id="sequenceBtn" class="bg-gray-800 hover:bg-gray-700 rounded p-2 text-center transition border border-gray-700 border-blue-600" title="Sequence">
                        <i class="fas fa-list-ol text-sm block mb-1"></i>
                        <span class="text-xs font-semibold">On</span>
                    </button>
                </div>

            </div>

            <!-- Right Sidebar (1/3) -->
            <div class="space-y-4">
                
                <!-- Bell Control -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-3 space-y-2">
                    <p class="text-xs font-semibold text-gray-400 uppercase flex items-center">
                        <i class="fas fa-bell mr-2"></i>Bell
                    </p>
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <input type="range" id="bellVolumeSlider" min="0" max="100" value="{{ $control->bell_volume ?? 100 }}" 
                                   class="flex-1 h-1 bg-gray-700 rounded-full appearance-none cursor-pointer"
                                   oninput="updateBellVolumeDisplay(this.value)" onchange="updateBellVolume(this.value)">
                            <span id="bellVolumeValue" class="text-xs text-gray-400 w-7 text-right">{{ $control->bell_volume ?? 100 }}%</span>
                        </div>
                        <div class="text-xs text-gray-500 truncate">{{ $control->bell_sound_path ? basename($control->bell_sound_path) : 'Default' }}</div>
                        <div class="flex space-x-1">
                            <button type="button" onclick="document.getElementById('bellSoundInput').click()" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-1 rounded text-xs font-semibold transition">
                                <i class="fas fa-upload mr-1"></i>Upload
                            </button>
                            @if($control->bell_sound_path)
                                <button type="button" onclick="resetBellSoundModal()" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-1 rounded text-xs font-semibold transition">
                                    <i class="fas fa-undo mr-1"></i>Reset
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Monitor Stats -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-3 space-y-2">
                    <p class="text-xs font-semibold text-gray-400 uppercase flex items-center">
                        <i class="fas fa-tv mr-2"></i>Monitor
                    </p>
                    <div id="monitorStats" class="space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Counters:</span>
                            <span id="counterCount" class="font-semibold text-white">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Waiting:</span>
                            <span id="waitingCount" class="font-semibold text-white">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Served:</span>
                            <span id="servedCount" class="font-semibold text-white">0</span>
                        </div>
                    </div>
                </div>

                <!-- Playlist Queue -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden flex flex-col max-h-96">
                    <div class="bg-gray-900 px-3 py-2 border-b border-gray-700">
                        <p class="text-xs font-semibold text-gray-400 uppercase">Queue</p>
                    </div>
                    <div id="playlistContainer" class="flex-1 overflow-y-auto divide-y divide-gray-700">
                        <div class="text-center py-6 text-gray-500 text-xs">
                            <i class="fas fa-spinner animate-spin mr-1"></i>Loading...
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Add Video & Library (Bottom) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            
            <!-- Add Video Form -->
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-3 space-y-3">
                <p class="text-xs font-semibold text-gray-400 uppercase flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>Add Video
                </p>
                
                @if ($errors->any())
                    <div class="bg-red-900 border border-red-700 text-red-200 px-2 py-1 rounded text-xs">
                        <p class="font-semibold mb-1">Errors:</p>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.videos.store', ['organization_code' => request()->route('organization_code')]) }}" method="POST" enctype="multipart/form-data" id="videoForm">
                    @csrf
                    
                    <div class="flex space-x-1 bg-gray-900 p-1 rounded w-fit mb-2">
                        <label class="flex items-center px-2 py-1 rounded cursor-pointer bg-gray-800 text-xs font-semibold" id="fileLabel">
                            <input type="radio" name="video_type" value="file" checked onchange="toggleVideoType()" class="w-3 h-3 mr-1">
                            <i class="fas fa-file-video mr-1"></i>File
                        </label>
                        <label class="flex items-center px-2 py-1 rounded cursor-pointer text-xs font-semibold" id="youtubeLabel">
                            <input type="radio" name="video_type" value="youtube" onchange="toggleVideoType()" class="w-3 h-3 mr-1">
                            <i class="fab fa-youtube mr-1"></i>YouTube
                        </label>
                    </div>

                    <div class="space-y-2">
                        <input type="text" name="title" value="{{ old('title') }}" required placeholder="Title" 
                               class="w-full px-2 py-1 text-xs bg-gray-900 border border-gray-700 rounded text-white placeholder-gray-600 focus:border-gray-600 focus:outline-none">

                        <div id="fileInput">
                            <input type="file" name="video" accept="video/*" id="videoFile" placeholder="Video file"
                                   class="w-full px-2 py-1 text-xs bg-gray-900 border border-gray-700 rounded text-gray-300 focus:border-gray-600 focus:outline-none">
                        </div>

                        <div id="youtubeInput" style="display: none;">
                            <input type="url" name="youtube_url" id="youtubeUrl" value="{{ old('youtube_url') }}" 
                                   placeholder="https://youtube.com/watch?v=..." 
                                   class="w-full px-2 py-1 text-xs bg-gray-900 border border-gray-700 rounded text-white placeholder-gray-600 focus:border-gray-600 focus:outline-none">
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-1.5 rounded text-xs font-semibold transition">
                            <i class="fas fa-upload mr-1"></i>Add
                        </button>

                        <div id="uploadProgress" class="hidden space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-400"><i class="fas fa-spinner animate-spin mr-1"></i>Uploading...</span>
                                <span id="uploadPercent" class="font-semibold">0%</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-1 overflow-hidden">
                                <div id="uploadBar" class="bg-blue-600 h-1 rounded-full transition-all" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Video Library -->
            <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden flex flex-col max-h-96">
                <div class="bg-gray-900 px-3 py-2 border-b border-gray-700 sticky top-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase">Library</p>
                </div>
                <div class="flex-1 overflow-y-auto divide-y divide-gray-700" id="videos-list">
                    @forelse($videos as $video)
                        <div class="px-2 py-1.5 hover:bg-gray-700 transition flex items-center justify-between group"
                             data-video-row
                             data-video-id="{{ $video->id }}"
                             data-video-type="{{ $video->isYoutube() ? 'youtube' : 'file' }}"
                             data-video-title="{{ addslashes($video->title) }}"
                             data-video-file="{{ $video->isFile() ? asset('storage/'.$video->file_path) : '' }}"
                             data-video-youtube="{{ $video->isYoutube() ? $video->youtube_embed_url : '' }}"
                             data-video-filename="{{ addslashes($video->filename) }}">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-white truncate">{{ $video->title }}</p>
                                <p class="text-[11px] text-gray-400">{{ $video->isYoutube() ? 'YouTube' : 'File' }}</p>
                            </div>
                            <div class="flex items-center space-x-2 flex-shrink-0 ml-1">
                                <span class="playing-indicator w-2 h-2 rounded-full bg-blue-500 opacity-0 transition"></span>
                                <div class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition flex-shrink-0">
                                    <button type="button" class="text-gray-400 hover:text-white p-1 text-xs"
                                            onclick="openEditVideo({{ $video->id }}, '{{ addslashes($video->title) }}')" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button type="button" class="text-gray-400 hover:text-white p-1 text-xs"
                                            onclick="addToPlaylist({{ $video->id }})" title="Add">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button type="button" class="text-gray-400 hover:text-white p-1 text-xs"
                                            onclick="toggleActive({{ $video->id }}, '{{ URL::route('admin.videos.toggle', ['organization_code' => request()->route('organization_code'), 'video' => $video->id]) }}')"
                                            title="Toggle">
                                        <i class="fas fa-toggle-{{ $video->is_active ? 'on' : 'off' }}"></i>
                                    </button>
                                    <button type="button" class="text-gray-400 hover:text-white p-1 text-xs"
                                            onclick="deleteVideoModal({{ $video->id }}, '{{ addslashes($video->filename) }}')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-500 text-xs">No videos</div>
                    @endforelse
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

<!-- Reset Bell Modal -->
<div id="reset-bell-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-gray-800 rounded-lg border border-gray-700 max-w-sm w-full mx-4 overflow-hidden">
        <div class="bg-gray-900 px-4 py-3 border-b border-gray-700">
            <h3 class="text-sm font-bold flex items-center">
                <i class="fas fa-bell mr-2"></i>Reset Bell Sound
            </h3>
        </div>
        <div class="px-4 py-3 space-y-3">
            <p class="text-xs text-gray-400">Reset to default bell sound?</p>
        </div>
        <div class="bg-gray-900 px-4 py-2 border-t border-gray-700 flex justify-end gap-2">
            <button onclick="closeModalReset('reset-bell-modal')" class="px-3 py-1 text-gray-400 hover:text-white text-xs font-semibold transition">Cancel</button>
            <button onclick="confirmResetBell()" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold transition">
                <i class="fas fa-check mr-1"></i>Reset
            </button>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="delete-video-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-gray-800 rounded-lg border border-gray-700 max-w-sm w-full mx-4 overflow-hidden">
        <div class="bg-gray-900 px-4 py-3 border-b border-gray-700">
            <h3 class="text-sm font-bold flex items-center">
                <i class="fas fa-trash mr-2"></i>Delete Video
            </h3>
        </div>
        <div class="px-4 py-3 space-y-2">
            <p class="text-xs text-gray-400">Confirm deletion?</p>
            <p class="text-xs text-gray-500 font-mono bg-gray-900 p-2 rounded border border-gray-700"><strong id="delete-video-name"></strong></p>
        </div>
        <div class="bg-gray-900 px-4 py-2 border-t border-gray-700 flex justify-end gap-2">
            <button onclick="closeModalVideo('delete-video-modal')" class="px-3 py-1 text-gray-400 hover:text-white text-xs font-semibold transition">Cancel</button>
            <button type="button" onclick="confirmDeleteVideo()" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-semibold transition">
                <i class="fas fa-trash mr-1"></i>Delete
            </button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-video-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-gray-800 rounded-lg border border-gray-700 max-w-sm w-full mx-4 overflow-hidden">
        <div class="bg-gray-900 px-4 py-3 border-b border-gray-700">
            <h3 class="text-sm font-bold flex items-center">
                <i class="fas fa-pen mr-2"></i>Edit Video
            </h3>
        </div>
        <div class="px-4 py-3 space-y-2">
            <label class="text-[11px] text-gray-400">Title</label>
            <input id="edit-video-title" class="w-full px-2 py-1 text-xs bg-gray-900 border border-gray-700 rounded text-white placeholder-gray-600 focus:border-gray-600 focus:outline-none" />
        </div>
        <div class="bg-gray-900 px-4 py-2 border-t border-gray-700 flex justify-end gap-2">
            <button onclick="closeModalVideo('edit-video-modal')" class="px-3 py-1 text-gray-400 hover:text-white text-xs font-semibold transition">Cancel</button>
            <button type="button" onclick="confirmEditVideo()" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold transition">
                <i class="fas fa-check mr-1"></i>Save
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let isPlaying = {{ $control->is_playing ? 'true' : 'false' }};
const orgCode = '{{ request()->route("organization_code") }}';
let currentPlaylist = [];
let nowPlayingVideo = null;
let isLoadingPlaylist = false;
let isLoadingMonitor = false;
let repeatMode = 'off';
let isShuffle = false;
let isSequence = true;

const throttle = (func, limit) => {
    let inThrottle;
    return (...args) => {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
};

document.addEventListener('DOMContentLoaded', function() {
    updatePlayButton();
    loadPlaylist();
    refreshMonitorData();
    
    // Combined sync every 2s to reduce API calls
    setInterval(() => syncPlaylistAndControl(), 2000);
    
    // Monitor updates every 3s
    setInterval(() => refreshMonitorData(), 3000);
});

function loadPlaylist() {
    if (isLoadingPlaylist) return;
    isLoadingPlaylist = true;
    
    fetch(`/${orgCode}/admin/playlist`, { cache: 'no-store', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                nowPlayingVideo = d.now_playing || null;
                currentPlaylist = d.playlist || [];
                renderPlaylist(d.playlist);
                updatePlaylistHighlight();
                updatePlaylistControlUI(d.control);
                updateNowPlayingDisplay(d.now_playing);
                if (d?.control && typeof d.control.is_playing !== 'undefined') {
                    isPlaying = !!d.control.is_playing;
                    updatePlayButton();
                }
            }
            isLoadingPlaylist = false;
        })
        .catch(() => { isLoadingPlaylist = false; });
}

function syncPlaylistAndControl() {
    if (isLoadingPlaylist) return;
    isLoadingPlaylist = true;
    fetch(`/${orgCode}/admin/playlist`, { cache: 'no-store', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                if (d.now_playing) {
                    nowPlayingVideo = d.now_playing;
                    updateNowPlayingDisplay(d.now_playing);
                    updatePlaylistHighlight();
                } else {
                    updateNowPlayingDisplay(null);
                }
                currentPlaylist = d.playlist || [];
                renderPlaylist(d.playlist);
                if (d?.control?.is_playing !== undefined) {
                    isPlaying = !!d.control.is_playing;
                    updatePlayButton();
                }
            }
        })
        .catch(() => {})
        .finally(() => { isLoadingPlaylist = false; });
}

function syncPlaylistQuick() {
    syncPlaylistAndControl();
}

function renderPlaylist(playlist) {
    const container = document.getElementById('playlistContainer');
    
    if (!playlist || playlist.length === 0) {
        container.innerHTML = '<div class="text-center py-6 text-gray-500 text-xs"><p>Empty queue</p></div>';
        return;
    }

    const fragment = document.createDocumentFragment();
    playlist.forEach((item, idx) => {
        const div = document.createElement('div');
        const isActive = nowPlayingVideo && item.video_id === nowPlayingVideo.id;
        div.className = 'px-2 py-1.5 transition cursor-pointer flex items-center justify-between group border-l-2';
        if (isActive) {
            div.classList.add('bg-gray-700', 'border-blue-500');
        } else {
            div.classList.add('hover:bg-gray-700', 'border-transparent');
        }
        div.dataset.videoId = item.video_id;
        div.innerHTML = `
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-white truncate">${idx + 1}. ${item.title}</p>
            </div>
            <div class="flex items-center space-x-2 flex-shrink-0 ml-1">
                <span class="playing-indicator w-2 h-2 rounded-full bg-blue-500 ${isActive ? 'opacity-100' : 'opacity-0'} transition"></span>
                <div class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition flex-shrink-0">
                    <button type="button" class="text-gray-400 hover:text-white p-1 text-xs playNowBtn ${isActive && isPlaying ? 'hidden' : ''}" title="Play">
                        <i class="fas fa-play"></i>
                    </button>
                    <button type="button" class="text-gray-400 hover:text-white p-1 text-xs playingBtn ${isActive && isPlaying ? '' : 'hidden'}" title="Playing">
                        <i class="fas fa-pause"></i>
                    </button>
                    <button type="button" class="text-gray-400 hover:text-white p-1 text-xs stopBtn" title="Stop">
                        <i class="fas fa-stop"></i>
                    </button>
                    <button type="button" class="text-gray-400 hover:text-white p-1 text-xs removeBtn" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        div.querySelector('.playNowBtn').addEventListener('click', () => playNow(item.video_id));
        div.querySelector('.playingBtn')?.addEventListener('click', () => pausePlayback());
        div.querySelector('.stopBtn').addEventListener('click', () => stopPlayback());
        div.querySelector('.removeBtn').addEventListener('click', () => removeFromPlaylist(item.video_id));
        
        // Double-click to play video
        div.addEventListener('dblclick', () => playNow(item.video_id));
        
        fragment.appendChild(div);
    });
    
    container.innerHTML = '';
    container.appendChild(fragment);
}

function updatePlaylistHighlight() {
    const rows = document.querySelectorAll('#playlistContainer [data-video-id]');
    rows.forEach(row => {
        const vid = parseInt(row.dataset.videoId, 10);
        const active = nowPlayingVideo && vid === nowPlayingVideo.id;
        row.classList.toggle('bg-gray-700', active);
        row.classList.toggle('border-blue-500', active);
        row.classList.toggle('border-transparent', !active);
        row.classList.toggle('hover:bg-gray-700', !active);
        const indicator = row.querySelector('.playing-indicator');
        if (indicator) {
            indicator.classList.toggle('opacity-100', active);
            indicator.classList.toggle('opacity-0', !active);
        }
    });
}

function refreshMonitorData() {
    if (isLoadingMonitor) return;
    isLoadingMonitor = true;
    
    fetch(`/${orgCode}/monitor/data`)
        .then(r => r.json())
        .then(d => {
            const activeCounters = d.counters?.length || 0;
            const waitingQueues = d.waiting_queues?.reduce((sum, q) => sum + q.queues.length, 0) || 0;
            
            document.getElementById('counterCount').textContent = activeCounters;
            document.getElementById('waitingCount').textContent = waitingQueues;
            document.getElementById('servedCount').textContent = d.served_today || 0;
            isLoadingMonitor = false;
        })
        .catch(() => { isLoadingMonitor = false; });
}

function updateNowPlayingDisplay(nowPlaying) {
    if (nowPlaying && nowPlaying.title) {
        nowPlayingVideo = nowPlaying;
        document.getElementById('nowPlayingTitle').textContent = nowPlaying.title;
        document.getElementById('nowPlayingType').textContent = nowPlaying.video_type === 'youtube' ? 'YouTube' : 'File';
        
        if (nowPlaying.video_type === 'file' && nowPlaying.file_path) {
            const player = document.getElementById('videoPlayer');
            const ytPlayer = document.getElementById('youtubePlayer');
            player.src = '/storage/' + nowPlaying.file_path;
            player.classList.remove('hidden');
            ytPlayer.classList.add('hidden');
            document.getElementById('playerContainer').style.display = 'none';
            if (isPlaying) player.play();
        } else if (nowPlaying.video_type === 'youtube' && nowPlaying.youtube_embed_url) {
            const ytPlayer = document.getElementById('youtubePlayer');
            const player = document.getElementById('videoPlayer');
            ytPlayer.src = nowPlaying.youtube_embed_url;
            ytPlayer.classList.remove('hidden');
            player.classList.add('hidden');
            document.getElementById('playerContainer').style.display = 'none';
        }
        
        document.getElementById('currentVideo').textContent = nowPlaying.title;
        isPlaying = true;
        updatePlayButton();
        updatePlaylistHighlight();
    } else {
        nowPlayingVideo = null;
        document.getElementById('nowPlayingTitle').textContent = '—';
        document.getElementById('nowPlayingType').textContent = '—';
        document.getElementById('videoPlayer').classList.add('hidden');
        document.getElementById('youtubePlayer').classList.add('hidden');
        document.getElementById('playerContainer').style.display = 'flex';
        document.getElementById('currentVideo').textContent = '—';
        isPlaying = false;
        updatePlayButton();
        updatePlaylistHighlight();
    }
}

function updatePlaylistControlUI(control) {
    if (control) {
        repeatMode = control.repeat_mode || 'off';
        isShuffle = control.is_shuffle || false;
        isSequence = control.is_sequence !== false;
        updateRepeatBtn();
        updateShuffleBtn();
        updateSequenceBtn();
    }
}

function updateRepeatBtn() {
    const btn = document.getElementById('repeatBtn');
    const modes = { 'off': 'Off', 'one': 'One', 'all': 'All' };
    btn.innerHTML = `<i class="fas fa-repeat text-sm block mb-1"></i><span class="text-xs font-semibold">${modes[repeatMode] || 'Off'}</span>`;
}

function updateShuffleBtn() {
    const btn = document.getElementById('shuffleBtn');
    btn.innerHTML = `<i class="fas fa-shuffle text-sm block mb-1"></i><span class="text-xs font-semibold">${isShuffle ? 'On' : 'Off'}</span>`;
    if (isShuffle) btn.classList.add('border-blue-600');
    else btn.classList.remove('border-blue-600');
}

function updateSequenceBtn() {
    const btn = document.getElementById('sequenceBtn');
    btn.innerHTML = `<i class="fas fa-list-ol text-sm block mb-1"></i><span class="text-xs font-semibold">${isSequence ? 'On' : 'Off'}</span>`;
    if (isSequence) btn.classList.add('border-blue-600');
    else btn.classList.remove('border-blue-600');
}

function toggleRepeat() {
    const modes = ['off', 'one', 'all'];
    const idx = modes.indexOf(repeatMode);
    repeatMode = modes[(idx + 1) % modes.length];
    updateRepeatBtn();
    updatePlaylistControl();
}

function toggleShuffle() {
    isShuffle = !isShuffle;
    updateShuffleBtn();
    updatePlaylistControl();
}

function toggleSequence() {
    isSequence = !isSequence;
    updateSequenceBtn();
    updatePlaylistControl();
}

function updatePlaylistControl() {
    fetch(`/${orgCode}/admin/playlist/control`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ repeat_mode: repeatMode, is_shuffle: isShuffle, is_sequence: isSequence })
    }).catch(() => {});
}

function togglePlay() {
    isPlaying = !isPlaying;
    updatePlayButton();
    updateControl();
}

function updatePlayButton() {
    const btn = document.getElementById('playBtn');
    const icon = document.getElementById('playIcon');
    if (!btn || !icon) return;
    if (isPlaying) {
        icon.className = 'fas fa-pause text-lg';
        btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        btn.classList.remove('bg-gray-700', 'hover:bg-gray-600');
    } else {
        icon.className = 'fas fa-play text-lg';
        btn.classList.add('bg-gray-700', 'hover:bg-gray-600');
        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
    }
}

function updateControl() {
    const updateThrottled = throttle(() => {
        fetch(`/${orgCode}/admin/videos/control`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                is_playing: isPlaying,
                volume: document.getElementById('volumeSlider').value,
                bell_volume: document.getElementById('bellVolumeSlider').value
            })
        }).catch(() => {});
    }, 300);
    
    updateThrottled();
}

function updateVolume(value) {
    updateControl();
}

function updateVolumeDisplay(value) {
    document.getElementById('volumeValue').textContent = value + '%';
}

function updateBellVolume(value) {
    updateControl();
}

function updateBellVolumeDisplay(value) {
    document.getElementById('bellVolumeValue').textContent = value + '%';
}

function addToPlaylist(videoId) {
    fetch(`/${orgCode}/admin/playlist/add`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ video_id: videoId })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            loadPlaylist();
            showToast('Added to playlist', 'success');
        }
    })
    .catch(e => showToast('Error: ' + e.message, 'error'));
}

function toggleActive(videoId, toggleUrl) {
    fetch(toggleUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            const row = document.querySelector(`[data-video-row][data-video-id="${videoId}"]`);
            if (row) {
                const icon = row.querySelector('button[title="Toggle"] i');
                if (icon) icon.className = `fas fa-toggle-${d.is_active ? 'on' : 'off'}`;
            }
            showToast(`Video ${d.is_active ? 'activated' : 'deactivated'}`, 'success');
        } else {
            showToast('Error toggling video', 'error');
        }
    })
    .catch(() => showToast('Error toggling video', 'error'));
}

function removeFromPlaylist(videoId) {
    fetch(`/${orgCode}/admin/playlist/remove`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ video_id: videoId })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) loadPlaylist();
    })
    .catch(() => {});
}

function playNow(videoId) {
    isPlaying = true;
    updatePlayButton();
    updateControl();
    syncPlaylistAndControl();
    
    fetch(`/${orgCode}/admin/playlist/now-playing`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ video_id: videoId })
    }).catch(() => {});
}

function pausePlayback() {
    isPlaying = false;
    updatePlayButton();
    updateControl();
}

function stopPlayback() {
    isPlaying = false;
    updatePlayButton();
    updateControl();
    // Clear current video
    fetch(`/${orgCode}/admin/videos/control`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            is_playing: false,
            volume: document.getElementById('volumeSlider').value,
            bell_volume: document.getElementById('bellVolumeSlider').value,
            current_video_id: null
        })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) loadPlaylist();
    })
    .catch(() => {});
}

function nextVideo() {
    const currentIdx = currentPlaylist.findIndex(v => nowPlayingVideo && v.video_id === nowPlayingVideo.id);
    if (currentIdx !== -1 && currentIdx < currentPlaylist.length - 1) {
        playNow(currentPlaylist[currentIdx + 1].video_id);
    } else if (repeatMode === 'all' && currentPlaylist.length > 0) {
        playNow(currentPlaylist[0].video_id);
    }
}

document.getElementById('videoForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const progressDiv = document.getElementById('uploadProgress');
    const uploadBar = document.getElementById('uploadBar');
    const uploadPercent = document.getElementById('uploadPercent');
    const submitBtn = document.querySelector('#videoForm button[type="submit"]');
    
    progressDiv.classList.remove('hidden');
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', (evt) => {
        if (evt.lengthComputable) {
            const pct = (evt.loaded / evt.total) * 100;
            uploadPercent.textContent = Math.round(pct) + '%';
            uploadBar.style.width = pct + '%';
        }
    });
    
    xhr.addEventListener('load', () => {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    uploadPercent.textContent = '100%';
                    uploadBar.style.width = '100%';
                    document.getElementById('videoForm').reset();
                    setTimeout(() => {
                        progressDiv.classList.add('hidden');
                        uploadBar.style.width = '0%';
                        submitBtn.disabled = false;
                        showToast('Video uploaded', 'success');
                        loadPlaylist();
                    }, 800);
                }
            } catch (error) {
                location.reload();
            }
        }
    });
    
    xhr.addEventListener('error', () => {
        submitBtn.disabled = false;
        showToast('Upload failed', 'error');
    });
    
    xhr.open('POST', this.action, true);
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    xhr.send(formData);
});

function toggleVideoType() {
    const type = document.querySelector('input[name="video_type"]:checked').value;
    document.getElementById('fileInput').style.display = type === 'file' ? 'block' : 'none';
    document.getElementById('youtubeInput').style.display = type === 'youtube' ? 'block' : 'none';
}

function deleteVideoModal(videoId, filename) {
    document.getElementById('delete-video-name').textContent = filename;
    window.currentDeleteUrl = `/${orgCode}/admin/videos/${videoId}`;
    window.currentVideoId = videoId;
    document.getElementById('delete-video-modal').classList.remove('hidden');
}

function openEditVideo(videoId, title) {
    window.currentEditUrl = `/${orgCode}/admin/videos/${videoId}`;
    window.currentEditVideoId = videoId;
    const input = document.getElementById('edit-video-title');
    input.value = title || '';
    document.getElementById('edit-video-modal').classList.remove('hidden');
}

function confirmEditVideo() {
    const title = document.getElementById('edit-video-title').value.trim();
    if (!title) {
        showToast('Title is required', 'error');
        return;
    }

    fetch(window.currentEditUrl, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ title })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success && d.video) {
            const row = document.querySelector(`[data-video-row][data-video-id="${window.currentEditVideoId}"]`);
            if (row) {
                const titleEl = row.querySelector('p.text-xs.font-semibold.text-white');
                if (titleEl) titleEl.textContent = d.video.title;
            }
            closeModalVideo('edit-video-modal');
            showToast('Video updated', 'success');
        } else {
            showToast('Error updating video', 'error');
        }
    })
    .catch(() => showToast('Error updating video', 'error'));
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
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            closeModalVideo('delete-video-modal');
            showToast('Video deleted', 'success');
            // remove from library list
            const row = document.querySelector(`[data-video-row][data-video-id="${window.currentVideoId}"]`);
            if (row) {
                row.style.opacity = '0';
                row.style.transition = 'opacity 200ms ease';
                setTimeout(() => row.remove(), 200);
            }
            loadPlaylist();
        } else {
            showToast('Error: ' + (d.error || 'Unknown'), 'error');
        }
    })
    .catch(e => showToast('Error: ' + e.message, 'error'));
}

function resetBellSoundModal() {
    document.getElementById('reset-bell-modal').classList.remove('hidden');
}

function confirmResetBell() {
    fetch(`/${orgCode}/admin/videos/reset-bell`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(() => {
        closeModalReset('reset-bell-modal');
        showToast('Bell reset', 'success');
        setTimeout(() => location.reload(), 500);
    })
    .catch(() => showToast('Error resetting bell', 'error'));
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-3 py-2 rounded text-xs font-semibold text-white max-w-xs z-[9999] transition-all ${
        type === 'success' ? 'bg-green-600' : 'bg-red-600'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(400px)';
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

document.getElementById('delete-video-modal')?.addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeModalVideo('delete-video-modal');
});

document.getElementById('reset-bell-modal')?.addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeModalReset('reset-bell-modal');
});
</script>
@endpush

@endsection
