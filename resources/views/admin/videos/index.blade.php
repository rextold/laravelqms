@extends('layouts.app')

@section('title', 'Video & Display Management')
@section('page-title', 'Video & Display Management')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
    <!-- Header Bar -->
    <div class="bg-gray-900/80 backdrop-blur-sm border-b border-gray-700/50 px-6 py-4 sticky top-0 z-20">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-video text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">Video & Display Control</h1>
                    <p class="text-xs text-gray-400">Manage videos displayed on the customer monitor</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <span id="connectionStatus" class="flex items-center space-x-2 px-3 py-1.5 rounded-full bg-gray-800/50 border border-gray-700/50">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="text-xs font-medium text-gray-300">Connected</span>
                </span>
                <a href="{{ route('monitor.index', ['organization_code' => request()->route('organization_code')]) }}" 
                   target="_blank"
                   class="px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg text-sm font-medium transition flex items-center space-x-2 border border-gray-700/50">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Open Monitor</span>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-6 space-y-6">
        
        <!-- Main Playback Control Panel -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700/50 overflow-hidden shadow-xl">
            <div class="p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    
                    <!-- Now Playing Info -->
                    <div class="flex items-center space-x-4 flex-1 min-w-0">
                        <div id="nowPlayingThumbnail" class="w-16 h-16 bg-gray-700 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden">
                            <i class="fas fa-film text-gray-500 text-2xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Now Playing on Monitor</p>
                            <h2 id="nowPlayingTitle" class="text-lg font-bold text-white truncate">No video selected</h2>
                            <p id="nowPlayingType" class="text-sm text-gray-400 flex items-center space-x-2">
                                <span class="inline-flex items-center">
                                    <i class="fas fa-circle text-gray-600 text-xs mr-1"></i>
                                    <span>—</span>
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Playback Controls -->
                    <div class="flex items-center space-x-3">
                        <button id="prevBtn" type="button" onclick="prevVideo()" 
                                class="w-12 h-12 bg-gray-700/50 hover:bg-gray-600 rounded-xl flex items-center justify-center transition transform hover:scale-105">
                            <i class="fas fa-step-backward text-gray-300"></i>
                        </button>
                        <button id="playBtn" type="button" onclick="togglePlay()" 
                                class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 rounded-2xl flex items-center justify-center transition transform hover:scale-105 shadow-lg shadow-blue-500/30">
                            <i id="playIcon" class="fas fa-play text-white text-xl ml-1"></i>
                        </button>
                        <button id="nextBtn" type="button" onclick="nextVideo()" 
                                class="w-12 h-12 bg-gray-700/50 hover:bg-gray-600 rounded-xl flex items-center justify-center transition transform hover:scale-105">
                            <i class="fas fa-step-forward text-gray-300"></i>
                        </button>
                        <button id="stopBtn" type="button" onclick="stopPlayback()" 
                                class="w-12 h-12 bg-gray-700/50 hover:bg-red-600 rounded-xl flex items-center justify-center transition transform hover:scale-105">
                            <i class="fas fa-stop text-gray-300"></i>
                        </button>
                    </div>

                    <!-- Volume Control -->
                    <div class="flex items-center space-x-3 lg:min-w-[200px]">
                        <button onclick="toggleMute()" class="w-10 h-10 bg-gray-700/50 hover:bg-gray-600 rounded-lg flex items-center justify-center transition">
                            <i id="volumeIcon" class="fas fa-volume-up text-gray-300"></i>
                        </button>
                        <div class="flex-1">
                            <input type="range" id="volumeSlider" min="0" max="100" value="{{ $control->volume }}" 
                                   class="w-full h-2 bg-gray-700 rounded-full appearance-none cursor-pointer accent-blue-500" 
                                   oninput="updateVolumeDisplay(this.value)" onchange="updateVolume(this.value)">
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>0</span>
                                <span id="volumeValue" class="font-medium text-gray-400">{{ $control->volume }}%</span>
                                <span>100</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Video Selector -->
                <div class="mt-6 pt-6 border-t border-gray-700/50">
                    <div class="flex items-center space-x-4">
                        <label class="text-sm font-medium text-gray-400">Quick Select:</label>
                        <select id="setNowSelect" class="flex-1 max-w-md bg-gray-700/50 text-sm text-white px-4 py-2.5 rounded-xl border border-gray-600/50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            <option value="">Choose a video to play...</option>
                            @foreach($videos as $v)
                                <option value="{{ $v->id }}" data-type="{{ $v->isYoutube() ? 'youtube' : 'file' }}">
                                    {{ $v->title }} ({{ $v->isYoutube() ? 'YouTube' : 'File' }})
                                </option>
                            @endforeach
                        </select>
                        <button type="button" onclick="setNowPlayingFromSelect()" 
                                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 rounded-xl text-sm font-semibold transition shadow-lg shadow-blue-500/20">
                            <i class="fas fa-play mr-2"></i>Play Now
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            
            <!-- Left Column: Playlist & Controls -->
            <div class="xl:col-span-2 space-y-6">
                
                <!-- Playlist Queue -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700/50 overflow-hidden shadow-xl">
                    <div class="bg-gray-900/50 px-6 py-4 border-b border-gray-700/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-purple-500/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-list text-purple-400"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">Playback Queue</h3>
                                <p class="text-xs text-gray-400" id="playlistCount">0 videos in queue</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="toggleRepeat()" id="repeatBtn" 
                                    class="px-3 py-1.5 bg-gray-700/50 hover:bg-gray-600 rounded-lg text-xs font-medium transition flex items-center space-x-2" 
                                    title="Repeat Mode">
                                <i class="fas fa-repeat"></i>
                                <span>Off</span>
                            </button>
                            <button onclick="toggleShuffle()" id="shuffleBtn" 
                                    class="px-3 py-1.5 bg-gray-700/50 hover:bg-gray-600 rounded-lg text-xs font-medium transition flex items-center space-x-2" 
                                    title="Shuffle">
                                <i class="fas fa-shuffle"></i>
                                <span>Off</span>
                            </button>
                            <button onclick="clearPlaylist()" 
                                    class="px-3 py-1.5 bg-gray-700/50 hover:bg-red-600 rounded-lg text-xs font-medium transition" 
                                    title="Clear Queue">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div id="playlistContainer" class="max-h-[400px] overflow-y-auto">
                        <div class="text-center py-12 text-gray-500">
                            <i class="fas fa-spinner animate-spin text-2xl mb-3"></i>
                            <p class="text-sm">Loading playlist...</p>
                        </div>
                    </div>
                </div>

                <!-- Add Video Section -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700/50 overflow-hidden shadow-xl">
                    <div class="bg-gray-900/50 px-6 py-4 border-b border-gray-700/50">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-plus text-green-400"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">Add New Video</h3>
                                <p class="text-xs text-gray-400">Upload a video file or add a YouTube link</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        @if ($errors->any())
                            <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl mb-4">
                                <div class="flex items-start space-x-2">
                                    <i class="fas fa-exclamation-circle mt-0.5"></i>
                                    <div>
                                        <p class="font-semibold text-sm mb-1">Upload Error</p>
                                        <ul class="text-xs space-y-0.5">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('admin.videos.store', ['organization_code' => request()->route('organization_code')]) }}" 
                              method="POST" enctype="multipart/form-data" id="videoForm">
                            @csrf
                            
                            <!-- Video Type Toggle -->
                            <div class="flex space-x-2 mb-6">
                                <label id="fileLabel" class="flex-1 cursor-pointer">
                                    <input type="radio" name="video_type" value="file" checked onchange="toggleVideoType()" class="sr-only peer">
                                    <div class="p-4 bg-gray-700/30 rounded-xl border-2 border-gray-600/50 peer-checked:border-blue-500 peer-checked:bg-blue-500/10 transition text-center">
                                        <i class="fas fa-file-video text-2xl mb-2 text-gray-400 peer-checked:text-blue-400"></i>
                                        <p class="font-semibold text-sm">Upload File</p>
                                        <p class="text-xs text-gray-500">MP4, AVI, MOV, WMV</p>
                                    </div>
                                </label>
                                <label id="youtubeLabel" class="flex-1 cursor-pointer">
                                    <input type="radio" name="video_type" value="youtube" onchange="toggleVideoType()" class="sr-only peer">
                                    <div class="p-4 bg-gray-700/30 rounded-xl border-2 border-gray-600/50 peer-checked:border-red-500 peer-checked:bg-red-500/10 transition text-center">
                                        <i class="fab fa-youtube text-2xl mb-2 text-gray-400 peer-checked:text-red-400"></i>
                                        <p class="font-semibold text-sm">YouTube Link</p>
                                        <p class="text-xs text-gray-500">Paste video URL</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Title Input -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-400 mb-2">Video Title</label>
                                <input type="text" name="title" value="{{ old('title') }}" required 
                                       placeholder="Enter a descriptive title..." 
                                       class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600/50 rounded-xl text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition">
                            </div>

                            <!-- File Upload -->
                            <div id="fileInput" class="mb-4">
                                <label class="block text-sm font-medium text-gray-400 mb-2">Video File</label>
                                <div class="relative">
                                    <input type="file" name="video" accept="video/*" id="videoFile"
                                           class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600/50 border-dashed rounded-xl text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-500 file:text-white hover:file:bg-blue-400 focus:outline-none transition cursor-pointer">
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Maximum file size: {{ $maxUploadLabel ?? '128MB' }}
                                </p>
                            </div>

                            <!-- YouTube URL -->
                            <div id="youtubeInput" class="mb-4 hidden">
                                <label class="block text-sm font-medium text-gray-400 mb-2">YouTube URL</label>
                                <input type="url" name="youtube_url" id="youtubeUrl" value="{{ old('youtube_url') }}" 
                                       placeholder="https://www.youtube.com/watch?v=..." 
                                       class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600/50 rounded-xl text-white placeholder-gray-500 focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none transition">
                                <p class="text-xs text-gray-500 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Supports youtube.com and youtu.be links
                                </p>
                            </div>

                            <!-- Upload Progress -->
                            <div id="uploadProgress" class="hidden mb-4">
                                <div class="bg-gray-700/50 rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm text-gray-400 flex items-center">
                                            <i class="fas fa-cloud-upload-alt animate-bounce mr-2"></i>
                                            Uploading video...
                                        </span>
                                        <span id="uploadPercent" class="text-sm font-bold text-blue-400">0%</span>
                                    </div>
                                    <div class="w-full bg-gray-600 rounded-full h-2 overflow-hidden">
                                        <div id="uploadBar" class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" id="uploadBtn"
                                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white py-3 rounded-xl font-semibold transition shadow-lg shadow-blue-500/20 flex items-center justify-center space-x-2">
                                <i class="fas fa-upload"></i>
                                <span>Add Video</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Library & Settings -->
            <div class="space-y-6">
                
                <!-- Video Library -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700/50 overflow-hidden shadow-xl">
                    <div class="bg-gray-900/50 px-6 py-4 border-b border-gray-700/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-photo-video text-blue-400"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white">Video Library</h3>
                                    <p class="text-xs text-gray-400">{{ $videos->count() }} videos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="max-h-[350px] overflow-y-auto" id="videos-list">
                        @forelse($videos as $video)
                            <div class="px-4 py-3 hover:bg-gray-700/30 transition flex items-center justify-between group border-b border-gray-700/30 last:border-b-0"
                                 data-video-row
                                 data-video-id="{{ $video->id }}"
                                 data-video-type="{{ $video->isYoutube() ? 'youtube' : 'file' }}"
                                 data-video-title="{{ addslashes($video->title) }}"
                                 data-video-file="{{ $video->isFile() ? asset('storage/'.$video->file_path) : '' }}"
                                 data-video-youtube="{{ $video->isYoutube() ? $video->youtube_embed_url : '' }}"
                                 data-video-filename="{{ addslashes($video->filename) }}">
                                <div class="flex items-center space-x-3 flex-1 min-w-0">
                                    <div class="w-10 h-10 bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                        @if($video->isYoutube())
                                            <i class="fab fa-youtube text-red-400"></i>
                                        @else
                                            <i class="fas fa-file-video text-blue-400"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-white truncate">{{ $video->title }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $video->isYoutube() ? 'YouTube' : 'Uploaded File' }}
                                            @if(!$video->is_active)
                                                <span class="ml-2 text-yellow-500">(Inactive)</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition">
                                    <button type="button" onclick="playNow({{ $video->id }})" 
                                            class="w-8 h-8 bg-green-500/20 hover:bg-green-500 text-green-400 hover:text-white rounded-lg flex items-center justify-center transition" 
                                            title="Play Now">
                                        <i class="fas fa-play text-xs"></i>
                                    </button>
                                    <button type="button" onclick="addToPlaylist({{ $video->id }})" 
                                            class="w-8 h-8 bg-blue-500/20 hover:bg-blue-500 text-blue-400 hover:text-white rounded-lg flex items-center justify-center transition" 
                                            title="Add to Queue">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                    <button type="button" onclick="openEditVideo({{ $video->id }}, '{{ addslashes($video->title) }}')" 
                                            class="w-8 h-8 bg-gray-600/50 hover:bg-gray-500 text-gray-400 hover:text-white rounded-lg flex items-center justify-center transition" 
                                            title="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>
                                    <button type="button" onclick="toggleActive({{ $video->id }}, '{{ URL::route('admin.videos.toggle', ['organization_code' => request()->route('organization_code'), 'video' => $video->id]) }}')" 
                                            class="w-8 h-8 bg-gray-600/50 hover:bg-yellow-500 text-gray-400 hover:text-white rounded-lg flex items-center justify-center transition" 
                                            title="Toggle Active">
                                        <i class="fas fa-toggle-{{ $video->is_active ? 'on text-green-400' : 'off' }} text-xs"></i>
                                    </button>
                                    <button type="button" onclick="deleteVideoModal({{ $video->id }}, '{{ addslashes($video->filename) }}')" 
                                            class="w-8 h-8 bg-gray-600/50 hover:bg-red-500 text-gray-400 hover:text-white rounded-lg flex items-center justify-center transition" 
                                            title="Delete">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-gray-500">
                                <i class="fas fa-video-slash text-3xl mb-3 opacity-50"></i>
                                <p class="text-sm">No videos in library</p>
                                <p class="text-xs text-gray-600 mt-1">Upload a video to get started</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Bell Sound Settings -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700/50 overflow-hidden shadow-xl">
                    <div class="bg-gray-900/50 px-6 py-4 border-b border-gray-700/50">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-bell text-yellow-400"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">Notification Bell</h3>
                                <p class="text-xs text-gray-400">Queue call notification sound</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Bell Volume -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-gray-400">Bell Volume</label>
                                <span id="bellVolumeValue" class="text-sm font-medium text-gray-300">{{ $control->bell_volume ?? 100 }}%</span>
                            </div>
                            <input type="range" id="bellVolumeSlider" min="0" max="100" value="{{ $control->bell_volume ?? 100 }}" 
                                   class="w-full h-2 bg-gray-700 rounded-full appearance-none cursor-pointer accent-yellow-500"
                                   oninput="updateBellVolumeDisplay(this.value)" onchange="updateBellVolume(this.value)">
                        </div>

                        <!-- Current Bell Sound -->
                        <div class="bg-gray-700/30 rounded-xl p-3">
                            <p class="text-xs text-gray-500 mb-1">Current Sound</p>
                            <p class="text-sm text-gray-300 truncate">
                                <i class="fas fa-music mr-2 text-yellow-400"></i>
                                {{ $control->bell_sound_path ? basename($control->bell_sound_path) : 'Default Bell Sound' }}
                            </p>
                        </div>

                        <!-- Bell Actions -->
                        <div class="flex space-x-2">
                            <button type="button" onclick="document.getElementById('bellSoundInput').click()" 
                                    class="flex-1 bg-gray-700/50 hover:bg-gray-600 text-white py-2.5 rounded-xl text-sm font-medium transition flex items-center justify-center space-x-2">
                                <i class="fas fa-upload"></i>
                                <span>Upload Custom</span>
                            </button>
                            @if($control->bell_sound_path)
                                <button type="button" onclick="resetBellSoundModal()" 
                                        class="flex-1 bg-gray-700/50 hover:bg-gray-600 text-white py-2.5 rounded-xl text-sm font-medium transition flex items-center justify-center space-x-2">
                                    <i class="fas fa-undo"></i>
                                    <span>Reset</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Monitor Status -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700/50 overflow-hidden shadow-xl">
                    <div class="bg-gray-900/50 px-6 py-4 border-b border-gray-700/50">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-cyan-500/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tv text-cyan-400"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">Monitor Status</h3>
                                <p class="text-xs text-gray-400">Real-time queue statistics</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-user-tie text-green-400"></i>
                                </div>
                                <p id="counterCount" class="text-2xl font-bold text-white">0</p>
                                <p class="text-xs text-gray-500">Counters</p>
                            </div>
                            <div class="text-center">
                                <div class="w-12 h-12 bg-yellow-500/20 rounded-xl flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-clock text-yellow-400"></i>
                                </div>
                                <p id="waitingCount" class="text-2xl font-bold text-white">0</p>
                                <p class="text-xs text-gray-500">Waiting</p>
                            </div>
                            <div class="text-center">
                                <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-check-circle text-blue-400"></i>
                                </div>
                                <p id="servedCount" class="text-2xl font-bold text-white">0</p>
                                <p class="text-xs text-gray-500">Served</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Forms -->
<form action="{{ route('admin.videos.upload-bell', ['organization_code' => request()->route('organization_code')]) }}" method="POST" enctype="multipart/form-data" id="bellForm" class="hidden">
    @csrf
    <input type="file" id="bellSoundInput" name="bell_sound" accept="audio/*" onchange="document.getElementById('bellForm').submit()">
</form>

<!-- Modals -->
<!-- Delete Video Modal -->
<div id="delete-video-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-gray-800 rounded-2xl border border-gray-700/50 max-w-md w-full overflow-hidden shadow-2xl transform transition-all">
        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
            <h3 class="text-lg font-bold text-white flex items-center">
                <i class="fas fa-trash mr-3"></i>Delete Video
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-300 mb-4">Are you sure you want to delete this video? This action cannot be undone.</p>
            <div class="bg-gray-900/50 rounded-xl p-4 border border-gray-700/50">
                <p class="text-sm text-gray-400 mb-1">Video:</p>
                <p class="font-semibold text-white" id="delete-video-name"></p>
            </div>
        </div>
        <div class="bg-gray-900/50 px-6 py-4 flex justify-end space-x-3">
            <button onclick="closeModalVideo('delete-video-modal')" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-xl text-sm font-medium transition">
                Cancel
            </button>
            <button type="button" onclick="confirmDeleteVideo()" class="px-4 py-2 bg-red-600 hover:bg-red-500 rounded-xl text-sm font-medium transition flex items-center space-x-2">
                <i class="fas fa-trash"></i>
                <span>Delete</span>
            </button>
        </div>
    </div>
</div>

<!-- Edit Video Modal -->
<div id="edit-video-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-gray-800 rounded-2xl border border-gray-700/50 max-w-md w-full overflow-hidden shadow-2xl transform transition-all">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4">
            <h3 class="text-lg font-bold text-white flex items-center">
                <i class="fas fa-pen mr-3"></i>Edit Video
            </h3>
        </div>
        <div class="p-6">
            <label class="block text-sm font-medium text-gray-400 mb-2">Video Title</label>
            <input id="edit-video-title" placeholder="Enter video title..."
                   class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600/50 rounded-xl text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition" />
        </div>
        <div class="bg-gray-900/50 px-6 py-4 flex justify-end space-x-3">
            <button onclick="closeModalVideo('edit-video-modal')" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-xl text-sm font-medium transition">
                Cancel
            </button>
            <button type="button" onclick="confirmEditVideo()" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded-xl text-sm font-medium transition flex items-center space-x-2">
                <i class="fas fa-check"></i>
                <span>Save Changes</span>
            </button>
        </div>
    </div>
</div>

<!-- Reset Bell Modal -->
<div id="reset-bell-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-gray-800 rounded-2xl border border-gray-700/50 max-w-md w-full overflow-hidden shadow-2xl transform transition-all">
        <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-4">
            <h3 class="text-lg font-bold text-white flex items-center">
                <i class="fas fa-bell mr-3"></i>Reset Bell Sound
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-300">Reset to the default notification bell sound?</p>
        </div>
        <div class="bg-gray-900/50 px-6 py-4 flex justify-end space-x-3">
            <button onclick="closeModalReset('reset-bell-modal')" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-xl text-sm font-medium transition">
                Cancel
            </button>
            <button onclick="confirmResetBell()" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-500 rounded-xl text-sm font-medium transition flex items-center space-x-2">
                <i class="fas fa-undo"></i>
                <span>Reset</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Immutable config ──────────────────────────────────────────────────────────
const ORGCODE = @json(request()->route('organization_code'));
const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';

// ── Mutable state ─────────────────────────────────────────────────────────────
let isPlaying  = {{ $control->is_playing ? 'true' : 'false' }};
let isMuted    = false;
let prevVol    = {{ (int)$control->volume }};
let repeatMode = @json($control->repeat_mode ?? 'off');
let isShuffle  = {{ $control->is_shuffle ? 'true' : 'false' }};
let currentList  = [];   // playlist items — synced from server
let nowPlaying   = null; // current video object — synced from server
let syncing      = false;
let syncingStats = false;

// ── Helpers ───────────────────────────────────────────────────────────────────
const $el = id => document.getElementById(id);
const jsonHeaders = () => ({ 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' });
const apiPost = (path, body) =>
    fetch(`/${ORGCODE}${path}`, { method: 'POST', headers: jsonHeaders(), body: JSON.stringify(body) }).then(r => r.json());
const apiDel  = (path) =>
    fetch(`/${ORGCODE}${path}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } }).then(r => r.json());
const apiPut  = (path, body) =>
    fetch(`/${ORGCODE}${path}`, { method: 'PUT',  headers: jsonHeaders(), body: JSON.stringify(body) }).then(r => r.json());
const apiGet  = (path) =>
    fetch(`/${ORGCODE}${path}`, { cache: 'no-store', credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(r => r.json());

// ── Init ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    updatePlayButton();
    updateRepeatBtn();
    updateShuffleBtn();
    syncState();
    syncMonitorStats();
    setInterval(syncState,        2000);
    setInterval(syncMonitorStats, 4000);
});

// ── Single sync function (replaces loadPlaylist + syncPlaylistAndControl) ─────
function syncState() {
    if (syncing) return;
    syncing = true;
    apiGet('/admin/playlist')
        .then(d => {
            if (!d.success) return;
            currentList = d.playlist || [];
            nowPlaying  = d.now_playing || null;
            renderPlaylist(currentList);
            renderNowPlaying(nowPlaying);
            if (d.control) {
                isPlaying  = !!d.control.is_playing;
                repeatMode = d.control.repeat_mode || 'off';
                isShuffle  = !!d.control.is_shuffle;
                updatePlayButton();
                updateRepeatBtn();
                updateShuffleBtn();
            }
        })
        .catch(() => {})
        .finally(() => { syncing = false; });
}

// ── Render playlist (XSS-safe — uses textContent for user data) ───────────────
function renderPlaylist(list) {
    const container = $el('playlistContainer');
    const countEl   = $el('playlistCount');
    if (countEl) countEl.textContent = `${list.length} video${list.length !== 1 ? 's' : ''} in queue`;

    if (!list.length) {
        container.innerHTML = `
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-inbox text-3xl mb-3 opacity-50"></i>
                <p class="text-sm">Queue is empty</p>
                <p class="text-xs text-gray-600 mt-1">Add videos from the library</p>
            </div>`;
        return;
    }

    const frag = document.createDocumentFragment();
    list.forEach((item, idx) => {
        const isActive = nowPlaying && item.video_id === nowPlaying.id;

        // Badge (number or playing indicator)
        const badge = document.createElement('div');
        badge.className = `w-8 h-8 ${isActive ? 'bg-blue-500' : 'bg-gray-700'} rounded-lg flex items-center justify-center flex-shrink-0`;
        if (isActive && isPlaying) {
            badge.innerHTML = '<i class="fas fa-volume-up text-white text-xs animate-pulse"></i>';
        } else {
            const num = document.createElement('span');
            num.className = `text-xs font-bold ${isActive ? 'text-white' : 'text-gray-400'}`;
            num.textContent = idx + 1;
            badge.appendChild(num);
        }

        // Title — textContent prevents XSS
        const titleP = document.createElement('p');
        titleP.className = 'text-sm font-medium text-white truncate';
        titleP.textContent = item.title;

        const typeP = document.createElement('p');
        typeP.className = 'text-xs text-gray-500';
        typeP.textContent = item.is_youtube ? 'YouTube' : 'File';

        const info = document.createElement('div');
        info.className = 'min-w-0 flex-1';
        info.appendChild(titleP);
        info.appendChild(typeP);

        const left = document.createElement('div');
        left.className = 'flex items-center space-x-3 flex-1 min-w-0';
        left.appendChild(badge);
        left.appendChild(info);

        // Action buttons
        const btnPlay = document.createElement('button');
        btnPlay.type = 'button'; btnPlay.title = 'Play';
        btnPlay.className = `playNowBtn w-8 h-8 bg-green-500/20 hover:bg-green-500 text-green-400 hover:text-white rounded-lg flex items-center justify-center transition ${isActive && isPlaying ? 'hidden' : ''}`;
        btnPlay.innerHTML = '<i class="fas fa-play text-xs"></i>';

        const btnPause = document.createElement('button');
        btnPause.type = 'button'; btnPause.title = 'Pause';
        btnPause.className = `pauseBtn w-8 h-8 bg-yellow-500/20 hover:bg-yellow-500 text-yellow-400 hover:text-white rounded-lg flex items-center justify-center transition ${isActive && isPlaying ? '' : 'hidden'}`;
        btnPause.innerHTML = '<i class="fas fa-pause text-xs"></i>';

        const btnRem = document.createElement('button');
        btnRem.type = 'button'; btnRem.title = 'Remove';
        btnRem.className = 'w-8 h-8 bg-red-500/20 hover:bg-red-500 text-red-400 hover:text-white rounded-lg flex items-center justify-center transition';
        btnRem.innerHTML = '<i class="fas fa-times text-xs"></i>';

        const actions = document.createElement('div');
        actions.className = 'flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition';
        actions.appendChild(btnPlay);
        actions.appendChild(btnPause);
        actions.appendChild(btnRem);

        const row = document.createElement('div');
        row.className = `px-4 py-3 flex items-center justify-between group transition cursor-pointer border-l-4 ${
            isActive ? 'bg-blue-500/10 border-blue-500' : 'hover:bg-gray-700/30 border-transparent'
        }`;
        row.dataset.videoId = item.video_id;
        row.appendChild(left);
        row.appendChild(actions);

        btnPlay.addEventListener('click',  e => { e.stopPropagation(); playNow(item.video_id); });
        btnPause.addEventListener('click', e => { e.stopPropagation(); pausePlayback(); });
        btnRem.addEventListener('click',   e => { e.stopPropagation(); removeFromPlaylist(item.video_id); });
        row.addEventListener('dblclick', () => playNow(item.video_id));

        frag.appendChild(row);
    });

    container.innerHTML = '';
    container.appendChild(frag);
}

// ── Now Playing display (XSS-safe) ───────────────────────────────────────────
function renderNowPlaying(video) {
    const titleEl = $el('nowPlayingTitle');
    const typeEl  = $el('nowPlayingType');
    const thumbEl = $el('nowPlayingThumbnail');

    if (!video) {
        titleEl.textContent = 'No video selected';
        thumbEl.innerHTML   = '<i class="fas fa-film text-gray-500 text-2xl"></i>';
        typeEl.innerHTML    = '<span class="inline-flex items-center"><i class="fas fa-circle text-gray-600 text-xs mr-1"></i><span>—</span></span>';
        return;
    }

    const isYT = video.video_type === 'youtube';
    titleEl.textContent = video.title;
    thumbEl.innerHTML   = `<i class="${isYT ? 'fab fa-youtube text-red-400' : 'fas fa-play text-blue-400'} text-2xl"></i>`;

    typeEl.textContent = '';
    const typeSpan = document.createElement('span');
    typeSpan.className = 'inline-flex items-center';
    const icon = document.createElement('i');
    icon.className = `${isYT ? 'fab fa-youtube text-red-400' : 'fas fa-file-video text-blue-400'} text-xs mr-2`;
    const label = document.createElement('span');
    label.textContent = isYT ? 'YouTube Video' : 'Uploaded File';
    typeSpan.appendChild(icon);
    typeSpan.appendChild(label);
    typeEl.appendChild(typeSpan);

    if (isPlaying) {
        const badge = document.createElement('span');
        badge.className = 'ml-3 px-2 py-0.5 bg-green-500/20 text-green-400 rounded-full text-xs font-medium';
        badge.innerHTML = '<i class="fas fa-circle animate-pulse mr-1"></i>';
        const lbl = document.createElement('span');
        lbl.textContent = 'Playing';
        badge.appendChild(lbl);
        typeEl.appendChild(badge);
    }
}

// Alias — kept so any old inline references still work
const updateNowPlayingDisplay = renderNowPlaying;

// ── Monitor stats ─────────────────────────────────────────────────────────────
function syncMonitorStats() {
    if (syncingStats) return;
    syncingStats = true;
    apiGet('/monitor/data')
        .then(d => {
            $el('counterCount').textContent = d.counters?.length ?? 0;
            $el('waitingCount').textContent = d.waiting_queues?.reduce((s, q) => s + (q.queues?.length ?? 0), 0) ?? 0;
            $el('servedCount').textContent  = d.served_today ?? 0;
        })
        .catch(() => {})
        .finally(() => { syncingStats = false; });
}

// ── Playback controls ─────────────────────────────────────────────────────────
function togglePlay() {
    isPlaying = !isPlaying;
    updatePlayButton();
    pushControl();
}

function playNow(videoId) {
    isPlaying = true;
    updatePlayButton();
    // Optimistic display — get video_type from the library row data attribute
    const libRow = document.querySelector(`[data-video-row][data-video-id="${parseInt(videoId, 10)}"]`);
    nowPlaying = {
        id:         parseInt(videoId, 10),
        title:      libRow?.dataset.videoTitle ?? '',
        video_type: libRow?.dataset.videoType  ?? 'file',
    };
    renderNowPlaying(nowPlaying);

    apiPost('/admin/playlist/now-playing', { video_id: videoId })
        .then(() => { pushControl(); syncState(); })
        .catch(() => {});
}

function pausePlayback() {
    isPlaying = false;
    updatePlayButton();
    pushControl();
}

function stopPlayback() {
    isPlaying  = false;
    nowPlaying = null;
    updatePlayButton();
    renderNowPlaying(null);
    // Use inline fetch to explicitly clear current_video_id
    fetch(`/${ORGCODE}/admin/videos/control`, {
        method: 'POST',
        headers: jsonHeaders(),
        body: JSON.stringify({
            is_playing:       false,
            volume:           parseInt($el('volumeSlider').value, 10),
            bell_volume:      parseInt($el('bellVolumeSlider').value, 10),
            current_video_id: null,
        })
    }).catch(() => {});
}

function prevVideo() {
    const idx = currentList.findIndex(v => nowPlaying && v.video_id === nowPlaying.id);
    if (idx > 0) {
        playNow(currentList[idx - 1].video_id);
    } else if (repeatMode === 'all' && currentList.length > 0) {
        playNow(currentList[currentList.length - 1].video_id);
    }
}

function nextVideo() {
    const idx = currentList.findIndex(v => nowPlaying && v.video_id === nowPlaying.id);
    if (idx !== -1 && idx < currentList.length - 1) {
        playNow(currentList[idx + 1].video_id);
    } else if (repeatMode === 'all' && currentList.length > 0) {
        playNow(currentList[0].video_id);
    }
}

// ── Push control state to server ─────────────────────────────────────────────
function pushControl() {
    apiPost('/admin/videos/control', {
        is_playing:       isPlaying,
        volume:           parseInt($el('volumeSlider').value, 10),
        bell_volume:      parseInt($el('bellVolumeSlider').value, 10),
        current_video_id: nowPlaying ? nowPlaying.id : null,
    }).catch(() => {});
}

// ── Volume controls ───────────────────────────────────────────────────────────
function updateVolumeDisplay(value) {
    $el('volumeValue').textContent = value + '%';
    $el('volumeIcon').className = `fas ${value == 0 ? 'fa-volume-mute' : value < 50 ? 'fa-volume-down' : 'fa-volume-up'} text-gray-300`;
}

function updateVolume(value) {
    isMuted = (value == 0);
    pushControl();
}

function toggleMute() {
    const slider = $el('volumeSlider');
    if (isMuted) {
        slider.value = prevVol;
        isMuted = false;
    } else {
        prevVol      = slider.value;
        slider.value = 0;
        isMuted      = true;
    }
    updateVolumeDisplay(slider.value);
    pushControl();
}

function updateBellVolumeDisplay(value) { $el('bellVolumeValue').textContent = value + '%'; }
function updateBellVolume()             { pushControl(); }

// ── Play button UI ────────────────────────────────────────────────────────────
function updatePlayButton() {
    const btn  = $el('playBtn');
    const icon = $el('playIcon');
    if (!btn || !icon) return;
    if (isPlaying) {
        icon.className = 'fas fa-pause text-white text-xl';
        btn.className  = 'w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 hover:from-green-400 hover:to-green-500 rounded-2xl flex items-center justify-center transition transform hover:scale-105 shadow-lg shadow-green-500/30';
    } else {
        icon.className = 'fas fa-play text-white text-xl ml-1';
        btn.className  = 'w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 rounded-2xl flex items-center justify-center transition transform hover:scale-105 shadow-lg shadow-blue-500/30';
    }
}

// ── Repeat / Shuffle ──────────────────────────────────────────────────────────
function updateRepeatBtn() {
    const btn   = $el('repeatBtn');
    const modes = { off: 'Off', one: 'One', all: 'All' };
    btn.innerHTML = `<i class="fas fa-repeat"></i><span>${modes[repeatMode] || 'Off'}</span>`;
    btn.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center space-x-2 ${
        repeatMode !== 'off' ? 'bg-blue-500 text-white' : 'bg-gray-700/50 hover:bg-gray-600'
    }`;
}

function updateShuffleBtn() {
    const btn = $el('shuffleBtn');
    btn.innerHTML = `<i class="fas fa-shuffle"></i><span>${isShuffle ? 'On' : 'Off'}</span>`;
    btn.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center space-x-2 ${
        isShuffle ? 'bg-purple-500 text-white' : 'bg-gray-700/50 hover:bg-gray-600'
    }`;
}

function toggleRepeat() {
    const modes = ['off', 'one', 'all'];
    repeatMode = modes[(modes.indexOf(repeatMode) + 1) % modes.length];
    updateRepeatBtn();
    apiPost('/admin/playlist/control', { repeat_mode: repeatMode, is_shuffle: isShuffle }).catch(() => {});
}

function toggleShuffle() {
    isShuffle = !isShuffle;
    updateShuffleBtn();
    apiPost('/admin/playlist/control', { repeat_mode: repeatMode, is_shuffle: isShuffle }).catch(() => {});
}

// ── Playlist management ───────────────────────────────────────────────────────
function setNowPlayingFromSelect() {
    const sel = $el('setNowSelect');
    if (!sel?.value) { showToast('Please select a video first', 'error'); return; }
    playNow(parseInt(sel.value, 10));
}

function addToPlaylist(videoId) {
    apiPost('/admin/playlist/add', { video_id: videoId })
        .then(d => {
            if (d.success) { syncState(); showToast('Added to queue', 'success'); }
            else showToast(d.error || 'Already in queue', 'error');
        })
        .catch(e => showToast('Error: ' + e.message, 'error'));
}

function removeFromPlaylist(videoId) {
    apiPost('/admin/playlist/remove', { video_id: videoId })
        .then(d => { if (d.success) { syncState(); showToast('Removed from queue', 'success'); } })
        .catch(() => {});
}

function clearPlaylist() {
    if (!confirm('Clear all videos from the queue?')) return;
    apiPost('/admin/playlist/clear', {})
        .then(d => {
            if (d.success) {
                isPlaying  = false;
                nowPlaying = null;
                updatePlayButton();
                renderNowPlaying(null);
                syncState();
                showToast('Queue cleared', 'success');
            } else {
                showToast(d.error || 'Failed to clear', 'error');
            }
        })
        .catch(() => showToast('Error clearing playlist', 'error'));
}

// ── Video library row actions ─────────────────────────────────────────────────
function toggleActive(videoId, toggleUrl) {
    fetch(toggleUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF } })
        .then(r => r.json())
        .then(d => {
            if (!d.success) { showToast('Error toggling video', 'error'); return; }
            const row = document.querySelector(`[data-video-row][data-video-id="${videoId}"]`);
            if (row) {
                const toggleIcon = row.querySelector('button[title="Toggle Active"] i');
                if (toggleIcon) toggleIcon.className = `fas fa-toggle-${d.is_active ? 'on text-green-400' : 'off'} text-xs`;
                const old = row.querySelector('.text-yellow-500');
                if (d.is_active && old) {
                    old.remove();
                } else if (!d.is_active && !old) {
                    const nameEl = row.querySelector('.text-xs.text-gray-500');
                    if (nameEl) {
                        const s = document.createElement('span');
                        s.className = 'ml-2 text-yellow-500';
                        s.textContent = '(Inactive)';
                        nameEl.appendChild(s);
                    }
                }
            }
            showToast(`Video ${d.is_active ? 'activated' : 'deactivated'}`, 'success');
        })
        .catch(() => showToast('Error toggling video', 'error'));
}

// ── Modals ────────────────────────────────────────────────────────────────────
function deleteVideoModal(videoId, filename) {
    $el('delete-video-name').textContent = filename || 'Untitled Video';
    window._deleteVideoId = videoId;
    $el('delete-video-modal').classList.remove('hidden');
}

function confirmDeleteVideo() {
    apiDel(`/admin/videos/${window._deleteVideoId}`)
        .then(d => {
            if (d.success) {
                closeModal('delete-video-modal');
                showToast('Video deleted', 'success');
                const row = document.querySelector(`[data-video-row][data-video-id="${window._deleteVideoId}"]`);
                if (row) { row.style.transition = 'opacity 200ms'; row.style.opacity = '0'; setTimeout(() => row.remove(), 200); }
                syncState();
            } else {
                showToast('Error: ' + (d.error || 'Unknown'), 'error');
            }
        })
        .catch(e => showToast('Error: ' + e.message, 'error'));
}

function openEditVideo(videoId, title) {
    window._editVideoId = videoId;
    $el('edit-video-title').value = title || '';
    $el('edit-video-modal').classList.remove('hidden');
}

function confirmEditVideo() {
    const title = $el('edit-video-title').value.trim();
    if (!title) { showToast('Title is required', 'error'); return; }
    apiPut(`/admin/videos/${window._editVideoId}`, { title })
        .then(d => {
            if (d.success) { closeModal('edit-video-modal'); showToast('Video updated', 'success'); location.reload(); }
            else showToast('Error updating video', 'error');
        })
        .catch(() => showToast('Error updating video', 'error'));
}

function closeModal(id)        { $el(id).classList.add('hidden'); }
const closeModalVideo = closeModal;
const closeModalReset = closeModal;

function resetBellSoundModal() { $el('reset-bell-modal').classList.remove('hidden'); }

function confirmResetBell() {
    apiPost('/admin/videos/reset-bell', {})
        .then(() => { closeModal('reset-bell-modal'); showToast('Bell sound reset to default', 'success'); setTimeout(() => location.reload(), 500); })
        .catch(() => showToast('Error resetting bell', 'error'));
}

// ── Toast (XSS-safe — textContent for message) ────────────────────────────────
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 px-5 py-3 rounded-xl text-sm font-medium text-white max-w-sm z-[9999] shadow-xl transition-all duration-300 flex items-center space-x-3 ${
        type === 'success' ? 'bg-gradient-to-r from-green-600 to-green-700' : 'bg-gradient-to-r from-red-600 to-red-700'
    }`;
    const icon = document.createElement('i');
    icon.className = `fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}`;
    const text = document.createElement('span');
    text.textContent = message; // textContent — no XSS
    toast.appendChild(icon);
    toast.appendChild(text);
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ── Video upload form with XHR progress ──────────────────────────────────────
document.getElementById('videoForm')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const progressDiv = $el('uploadProgress');
    const uploadBar   = $el('uploadBar');
    const uploadPct   = $el('uploadPercent');
    const submitBtn   = $el('uploadBtn');

    progressDiv.classList.remove('hidden');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin"></i><span>Uploading...</span>';

    const xhr = new XMLHttpRequest();
    xhr.upload.addEventListener('progress', evt => {
        if (evt.lengthComputable) {
            const pct = Math.round(evt.loaded / evt.total * 100);
            uploadPct.textContent = pct + '%';
            uploadBar.style.width = pct + '%';
        }
    });
    xhr.addEventListener('load', () => {
        if (xhr.status === 200) {
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.success) {
                    uploadPct.textContent = '100%';
                    uploadBar.style.width = '100%';
                    setTimeout(() => {
                        progressDiv.classList.add('hidden');
                        uploadBar.style.width = '0%';
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-upload"></i><span>Add Video</span>';
                        this.reset();
                        showToast('Video added successfully!', 'success');
                        location.reload();
                    }, 800);
                    return;
                }
            } catch (_) {}
        }
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-upload"></i><span>Add Video</span>';
        progressDiv.classList.add('hidden');
        showToast('Upload failed. Please try again.', 'error');
    });
    xhr.addEventListener('error', () => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-upload"></i><span>Add Video</span>';
        progressDiv.classList.add('hidden');
        showToast('Upload failed. Please try again.', 'error');
    });
    xhr.open('POST', this.action, true);
    xhr.setRequestHeader('X-CSRF-TOKEN', CSRF);
    xhr.send(new FormData(this));
});

function toggleVideoType() {
    const type = document.querySelector('input[name="video_type"]:checked').value;
    $el('fileInput').classList.toggle('hidden',    type !== 'file');
    $el('youtubeInput').classList.toggle('hidden', type !== 'youtube');
}

// ── Modal backdrop click dismissal ───────────────────────────────────────────
document.querySelectorAll('[id$="-modal"]').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); });
});

// ── Keyboard shortcuts ────────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    if      (e.key === ' ')           { e.preventDefault(); togglePlay(); }
    else if (e.key === 'ArrowLeft')   prevVideo();
    else if (e.key === 'ArrowRight')  nextVideo();
    else if (e.key === 'm')           toggleMute();
});
</script>
@endpush

@endsection
