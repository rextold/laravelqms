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

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Video Controls -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Display Controls</h2>
            <div id="controlStatus" class="flex items-center space-x-2 text-sm">
                <i class="fas fa-circle text-green-500 animate-pulse"></i>
                <span class="text-gray-600">Live Control</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold mb-3">Video Controls</h3>
                <div class="space-y-3">
                    <button onclick="togglePlay()" id="playBtn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full transition-all">
                        <i class="fas" id="playIcon"></i> <span id="playText"></span>
                    </button>
                    <div class="flex items-center">
                        <label class="mr-2 w-32">Video Volume:</label>
                        <input type="range" id="volumeSlider" min="0" max="100" value="{{ $control->volume }}" 
                               class="flex-1" oninput="updateVolumeDisplay(this.value)" onchange="updateVolume(this.value)">
                        <span id="volumeValue" class="ml-2 w-12 font-semibold">{{ $control->volume }}%</span>
                    </div>
                    <div id="volumeUpdateStatus" class="text-xs text-green-600 hidden">
                        <i class="fas fa-check-circle"></i> Volume updated
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-3">Notification Bell</h3>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <label class="mr-2 w-32">Bell Volume:</label>
                        <input type="range" id="bellVolumeSlider" min="0" max="100" value="{{ $control->bell_volume ?? 100 }}" 
                               class="flex-1" oninput="updateBellVolumeDisplay(this.value)" onchange="updateBellVolume(this.value)">
                        <span id="bellVolumeValue" class="ml-2 w-12 font-semibold">{{ $control->bell_volume ?? 100 }}%</span>
                    </div>
                    <div id="bellVolumeUpdateStatus" class="text-xs text-green-600 hidden">
                        <i class="fas fa-check-circle"></i> Bell volume updated
                    </div>
                    <form action="{{ route('admin.videos.upload-bell', ['company_code' => request()->route('company_code')]) }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                        @csrf
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">
                                Current: {{ $control->bell_sound_path ? basename($control->bell_sound_path) : 'Default Bell' }}
                            </label>
                            <input type="file" name="bell_sound" accept="audio/*" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                            <small class="text-gray-500">Upload custom bell sound (MP3, WAV, OGG - Max 2MB)</small>
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                            @if($control->bell_sound_path)
                                <button type="button" onclick="resetBellSound()" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">
                                    <i class="fas fa-undo"></i> Reset to Default
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Video -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Add New Video</h2>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <div class="font-semibold mb-2">Please fix the following errors:</div>
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.videos.store', ['company_code' => request()->route('company_code')]) }}" method="POST" enctype="multipart/form-data" id="videoForm">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Video Type *</label>
                <div class="flex space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="video_type" value="file" checked onchange="toggleVideoType()" class="form-radio">
                        <span class="ml-2">Upload File</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="video_type" value="youtube" onchange="toggleVideoType()" class="form-radio">
                        <span class="ml-2">YouTube Video</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div id="fileInput">
                    <label class="block text-gray-700 font-semibold mb-2">Video File * (MP4, AVI, MOV, WMV - Max {{ $maxUploadLabel }})</label>
                    <input type="file" name="video" accept="video/*" id="videoFile"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 @error('video') border-red-500 @enderror">
                    @error('video')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <div id="fileValidation" class="text-xs text-gray-500 mt-1"></div>
                    <div id="uploadProgress" class="mt-2 hidden">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-semibold text-gray-700">Uploading...</span>
                            <span id="uploadPercent" class="text-sm font-semibold text-blue-600">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="uploadBar" class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>
                    <div id="uploadStatus" class="text-xs mt-1"></div>
                </div>
                <div id="youtubeInput" style="display: none;">
                    <label class="block text-gray-700 font-semibold mb-2">YouTube URL *</label>
                    <input type="url" name="youtube_url" id="youtubeUrl" value="{{ old('youtube_url') }}" placeholder="https://www.youtube.com/watch?v=..."
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 @error('youtube_url') border-red-500 @enderror">
                    <small class="text-gray-500">Paste any YouTube video URL</small>
                    @error('youtube_url')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full md:w-auto" id="submitBtn">
                        <i class="fas fa-plus"></i> Add Video
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
                        <th class="text-left py-3 px-4">Type</th>
                        <th class="text-left py-3 px-4">Status</th>
                        <th class="text-left py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody id="videos-list">
                    @foreach($videos as $video)
                    <tr class="border-b hover:bg-gray-50" data-id="{{ $video->id }}">
                        <td class="py-3 px-4">{{ $video->order }}</td>
                        <td class="py-3 px-4">
                            <div>
                                {{ $video->title }}
                                @if($video->isYoutube())
                                    <a href="{{ $video->youtube_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 ml-2">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @if($video->isYoutube())
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">
                                    <i class="fab fa-youtube"></i> YouTube
                                </span>
                            @else
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                    <i class="fas fa-file-video"></i> File
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="status-badge {{ $video->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} px-2 py-1 rounded text-sm">
                                {{ $video->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex space-x-3 items-center">
                                <button type="button" class="text-purple-600 hover:text-purple-800"
                                        onclick="previewVideo(this)"
                                        data-type="{{ $video->isYoutube() ? 'youtube' : 'file' }}"
                                        data-title="{{ $video->title }}"
                                        data-file="{{ $video->isFile() ? asset('storage/'.$video->file_path) : '' }}"
                                        data-youtube="{{ $video->isYoutube() ? $video->youtube_embed_url : '' }}">
                                    <i class="fas fa-play-circle"></i>
                                </button>
                                <button onclick="toggleActive({{ $video->id }})" class="text-blue-600 hover:text-blue-800" title="Toggle Active">
                                    <i class="fas fa-toggle-on"></i>
                                </button>
                                <button type="button" 
                                        data-video-id="{{ $video->id }}"
                                        data-video-filename="{{ addslashes($video->filename) }}"
                                        data-delete-url="{{ route('admin.videos.destroy', ['company_code' => request()->route('company_code'), 'video' => $video->id]) }}"
                                        onclick="openDeleteVideoModal(this)"
                                        class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 relative">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <div>
                    <p class="text-xs uppercase text-gray-500">Preview</p>
                    <h3 id="previewTitle" class="text-lg font-semibold text-gray-800"></h3>
                </div>
                <button onclick="closePreview()" class="text-gray-500 hover:text-gray-800 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-4 space-y-4">
                <div id="filePreview" class="hidden">
                    <video id="filePlayer" controls class="w-full rounded-lg bg-black" preload="metadata"></video>
                </div>
                <div id="youtubePreview" class="hidden">
                    <div class="aspect-video bg-black rounded-lg overflow-hidden">
                        <iframe id="ytPlayer" class="w-full h-full" src="" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="playPreview()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-play mr-2"></i>Play
                    </button>
                    <button onclick="pausePreview()" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        <i class="fas fa-pause mr-2"></i>Pause
                    </button>
                    <button onclick="closePreview()" class="text-gray-600 hover:text-gray-900 ml-auto">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let isPlaying = {{ $control->is_playing ? 'true' : 'false' }};
let currentPreview = { type: null, fileSrc: null, youtubeSrc: null };

// Video file validation
document.getElementById('videoFile')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const validationDiv = document.getElementById('fileValidation');
    const maxSize = 500 * 1024 * 1024; // 500MB
    
    if (!file) {
        validationDiv.innerHTML = '';
        return;
    }
    
    let validation = '';
    
    // File size check
    if (file.size > maxSize) {
        validation += '<span class="text-red-600"><i class="fas fa-times-circle"></i> File size exceeds 500MB limit</span>';
    } else {
        validation += '<span class="text-green-600"><i class="fas fa-check-circle"></i> Size: ' + (file.size / (1024*1024)).toFixed(2) + 'MB</span>';
    }
    
    // File type check
    const validTypes = ['video/mp4', 'video/x-msvideo', 'video/quicktime', 'video/x-ms-wmv'];
    if (!validTypes.includes(file.type)) {
        validation += '<br><span class="text-orange-600"><i class="fas fa-exclamation-circle"></i> Format: ' + (file.type || 'Unknown') + ' (MP4 recommended)</span>';
    } else {
        validation += '<br><span class="text-green-600"><i class="fas fa-check-circle"></i> Format: Valid</span>';
    }
    
    validationDiv.innerHTML = validation;
});

// Form submission with progress tracking
document.getElementById('videoForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const videoType = document.querySelector('input[name="video_type"]:checked').value;
    const fileInput = document.getElementById('videoFile');
    const youtubeUrl = document.getElementById('youtubeUrl');
    
    // Validation
    let isValid = true;
    let errors = [];
    
    const title = document.querySelector('input[name="title"]').value.trim();
    if (!title) {
        isValid = false;
        errors.push('Title is required');
    }
    
    if (videoType === 'file') {
        if (!fileInput.files.length) {
            isValid = false;
            errors.push('Please select a video file');
        } else {
            const file = fileInput.files[0];
            const maxSize = 500 * 1024 * 1024; // 500MB
            if (file.size > maxSize) {
                isValid = false;
                errors.push('File size exceeds 500MB limit');
            }
        }
    } else {
        if (!youtubeUrl.value.trim()) {
            isValid = false;
            errors.push('YouTube URL is required');
        }
    }
    
    if (!isValid) {
        alert('Validation errors:\n' + errors.join('\n'));
        return;
    }
    
    // If using file upload, show progress
    if (videoType === 'file') {
        const progressDiv = document.getElementById('uploadProgress');
        const uploadBar = document.getElementById('uploadBar');
        const uploadPercent = document.getElementById('uploadPercent');
        const uploadStatus = document.getElementById('uploadStatus');
        const submitBtn = document.getElementById('submitBtn');
        
        progressDiv.classList.remove('hidden');
        uploadStatus.innerHTML = '<span class="text-blue-600"><i class="fas fa-spinner animate-spin"></i> Preparing upload...</span>';
        submitBtn.disabled = true;
        
        const formData = new FormData(this);
        const xhr = new XMLHttpRequest();
        
        // Progress event
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                uploadPercent.textContent = Math.round(percentComplete) + '%';
                uploadBar.style.width = percentComplete + '%';
                
                if (percentComplete < 100) {
                    uploadStatus.innerHTML = '<span class="text-blue-600"><i class="fas fa-spinner animate-spin"></i> Uploading... ' + Math.round(percentComplete) + '%</span>';
                }
            }
        });
        
        // Load event
        xhr.addEventListener('load', function() {
            if (xhr.status === 200 || xhr.status === 302) {
                uploadPercent.textContent = '100%';
                uploadBar.style.width = '100%';
                uploadStatus.innerHTML = '<span class="text-green-600"><i class="fas fa-check-circle"></i> Upload complete! Processing...</span>';
                setTimeout(() => {
                    this.submit();
                }, 1000);
            } else {
                uploadStatus.innerHTML = '<span class="text-red-600"><i class="fas fa-exclamation-circle"></i> Upload failed</span>';
                submitBtn.disabled = false;
            }
        });
        
        // Error event
        xhr.addEventListener('error', function() {
            uploadStatus.innerHTML = '<span class="text-red-600"><i class="fas fa-times-circle"></i> Upload error</span>';
            submitBtn.disabled = false;
        });
        
        xhr.open('POST', this.action, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        xhr.send(formData);
    } else {
        this.submit();
    }
});

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

    // Reset
    filePlayer.pause();
    filePlayer.removeAttribute('src');
    ytPlayer.src = '';

    if (type === 'file' && file) {
        fileWrap.classList.remove('hidden');
        ytWrap.classList.add('hidden');
        filePlayer.src = file;
        currentPreview = { type: 'file', fileSrc: file, youtubeSrc: null };
        filePlayer.play();
    } else if (type === 'youtube' && youtube) {
        fileWrap.classList.add('hidden');
        ytWrap.classList.remove('hidden');
        const autoplayUrl = addOrReplaceParam(addOrReplaceParam(youtube, 'enablejsapi', 1), 'autoplay', 1);
        ytPlayer.src = autoplayUrl;
        currentPreview = { type: 'youtube', fileSrc: null, youtubeSrc: youtube };
    }

    titleEl.textContent = title || 'Preview';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePreview() {
    const modal = document.getElementById('previewModal');
    const filePlayer = document.getElementById('filePlayer');
    const ytPlayer = document.getElementById('ytPlayer');
    filePlayer.pause();
    ytPlayer.src = '';
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function playPreview() {
    const filePlayer = document.getElementById('filePlayer');
    const ytPlayer = document.getElementById('ytPlayer');
    if (currentPreview.type === 'file' && currentPreview.fileSrc) {
        filePlayer.play();
    } else if (currentPreview.type === 'youtube' && currentPreview.youtubeSrc) {
        ytPlayer.src = addOrReplaceParam(addOrReplaceParam(currentPreview.youtubeSrc, 'enablejsapi', 1), 'autoplay', 1);
    }
}

function pausePreview() {
    const filePlayer = document.getElementById('filePlayer');
    const ytPlayer = document.getElementById('ytPlayer');
    if (currentPreview.type === 'file') {
        filePlayer.pause();
    } else if (currentPreview.type === 'youtube') {
        ytPlayer.src = addOrReplaceParam(addOrReplaceParam(currentPreview.youtubeSrc, 'enablejsapi', 1), 'autoplay', 0);
    }
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

updatePlayButton();

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
    fetch('{{ route('admin.videos.control', ['company_code' => request()->route('company_code')]) }}', {
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
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Pulse the control status indicator
            const statusEl = document.getElementById('controlStatus');
            statusEl.classList.add('scale-110');
            setTimeout(() => statusEl.classList.remove('scale-110'), 200);
        }
    })
    .catch(error => {
        console.error('Control update failed:', error);
    });
}

function resetBellSound() {
    openResetBellModal();
        fetch('{{ route('admin.videos.reset-bell', ['company_code' => request()->route('company_code')]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            location.reload();
        });
}

function toggleActive(videoId) {
    const companyCode = '{{ request()->route("company_code") }}';
    fetch(`/${companyCode}/admin/videos/${videoId}/toggle`, {
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

<!-- Delete Video Modal -->
<div id="delete-video-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="delete-video-modal-content">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    <h3 class="text-xl font-bold text-white">Delete Video</h3>
                </div>
                <button onclick="closeModalVideo('delete-video-modal')" class="text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-2">Are you sure you want to delete this video?</p>
            <p class="text-sm text-gray-500 mb-4">Filename: <strong id="delete-video-name"></strong></p>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            <button onclick="closeModalVideo('delete-video-modal')" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">Cancel</button>
            <button type="button" onclick="confirmDeleteVideo()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">
                <i class="fas fa-trash mr-2"></i>Delete Video
            </button>
        </div>
    </div>
</div>

<!-- Reset Bell Modal -->
<div id="reset-bell-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="reset-bell-modal-content">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-volume-up text-white text-xl"></i>
                    <h3 class="text-xl font-bold text-white">Reset Bell Sound?</h3>
                </div>
                <button onclick="closeModalReset('reset-bell-modal')" class="text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-4">Are you sure you want to reset the bell sound to default?</p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Note:</strong> This will replace the current custom bell sound with the system default.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            <button onclick="closeModalReset('reset-bell-modal')" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">Cancel</button>
            <button onclick="confirmResetBell()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                <i class="fas fa-check mr-2"></i>Reset Bell
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openModalVideo(modalId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(modalId + '-content');
    
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
}

function closeModalVideo(modalId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(modalId + '-content');
    
    if (modal) {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
}

function closeModalReset(modalId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(modalId + '-content');
    
    if (modal) {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
}

function openDeleteVideoModal(button) {
    const videoId = button.dataset.videoId;
    const videoFilename = button.dataset.videoFilename;
    const deleteUrl = button.dataset.deleteUrl;
    
    console.log('Opening delete video modal with:', { videoId, videoFilename, deleteUrl });
    
    document.getElementById('delete-video-name').textContent = videoFilename;
    window.currentDeleteUrl = deleteUrl;
    window.currentDeleteId = videoId;
    openModalVideo('delete-video-modal');
}

function confirmDeleteVideo() {
    const url = window.currentDeleteUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (!csrfToken) {
        alert('Security token not found. Please refresh the page.');
        console.error('CSRF token not found');
        return;
    }
    
    if (!url) {
        alert('Delete URL not found. Please refresh the page.');
        console.error('Delete URL not set');
        return;
    }
    
    console.log('Deleting video from URL:', url);
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })\n    .then(response => {\n        console.log('Response status:', response.status);\n        if (!response.ok && response.status !== 204) {\n            return response.text().then(text => {\n                console.error('Server response:', text);\n                throw new Error('Server returned ' + response.status);\n            });\n        }\n        return response;\n    })\n    .then(response => {\n        closeModalVideo('delete-video-modal');\n        alert('Video deleted successfully');\n        window.location.reload();\n    })\n    .catch(error => {\n        console.error('Delete error:', error);\n        alert('Error deleting video: ' + error.message);\n    });\n}

function openResetBellModal() {
    const modal = document.getElementById('reset-bell-modal');
    const content = document.getElementById('reset-bell-modal-content');
    
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
}

function confirmResetBell() {
    closeModalReset('reset-bell-modal');
    fetch('{{ route('admin.videos.reset-bell', ['company_code' => request()->route('company_code')]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        location.reload();
    });
}

// Close modals on outside click
document.addEventListener('DOMContentLoaded', function() {
    ['delete-video-modal', 'reset-bell-modal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    if (modalId === 'delete-video-modal') closeModalVideo(modalId);
                    else closeModalReset(modalId);
                }
            });
        }
    });
});
</script>
@endpush

@endsection
