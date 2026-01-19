@extends('layouts.app')

@section('title', 'Video & Display Management')
@section('page-title', 'Video & Display Management')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
    <!-- Header Bar -->
    <div class="bg-gray-900/80 backdrop-blur-sm border-b border-gray-700/50 px-6 py-4 sticky top-0 z-40">
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
let isPlaying = {{ $control->is_playing ? 'true' : 'false' }};
let isMuted = false;
const orgCode = '{{ request()->route("organization_code") }}';
let currentPlaylist = [];
let nowPlayingVideo = null;
let isLoadingPlaylist = false;
let isLoadingMonitor = false;
let repeatMode = '{{ $control->repeat_mode ?? 'off' }}';
let isShuffle = {{ $control->is_shuffle ? 'true' : 'false' }};
let isSequence = {{ $control->is_sequence !== false ? 'true' : 'false' }};
let previousVolume = {{ $control->volume }};

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
    updateRepeatBtn();
    updateShuffleBtn();
    loadPlaylist();
    refreshMonitorData();
    
    setInterval(() => syncPlaylistAndControl(), 2000);
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
                nowPlayingVideo = d.now_playing || null;
                currentPlaylist = d.playlist || [];
                renderPlaylist(d.playlist);
                updateNowPlayingDisplay(d.now_playing);
                if (d?.control?.is_playing !== undefined) {
                    isPlaying = !!d.control.is_playing;
                    updatePlayButton();
                }
            }
        })
        .catch(() => {})
        .finally(() => { isLoadingPlaylist = false; });
}

function renderPlaylist(playlist) {
    const container = document.getElementById('playlistContainer');
    const countEl = document.getElementById('playlistCount');
    
    if (countEl) {
        countEl.textContent = `${playlist?.length || 0} videos in queue`;
    }
    
    if (!playlist || playlist.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-inbox text-3xl mb-3 opacity-50"></i>
                <p class="text-sm">Queue is empty</p>
                <p class="text-xs text-gray-600 mt-1">Add videos from the library</p>
            </div>
        `;
        return;
    }

    const fragment = document.createDocumentFragment();
    playlist.forEach((item, idx) => {
        const div = document.createElement('div');
        const isActive = nowPlayingVideo && item.video_id === nowPlayingVideo.id;
        div.className = `px-4 py-3 flex items-center justify-between group transition cursor-pointer border-l-4 ${
            isActive 
                ? 'bg-blue-500/10 border-blue-500' 
                : 'hover:bg-gray-700/30 border-transparent'
        }`;
        div.dataset.videoId = item.video_id;
        div.innerHTML = `
            <div class="flex items-center space-x-3 flex-1 min-w-0">
                <div class="w-8 h-8 ${isActive ? 'bg-blue-500' : 'bg-gray-700'} rounded-lg flex items-center justify-center flex-shrink-0">
                    ${isActive && isPlaying 
                        ? '<i class="fas fa-volume-up text-white text-xs animate-pulse"></i>' 
                        : `<span class="text-xs font-bold ${isActive ? 'text-white' : 'text-gray-400'}">${idx + 1}</span>`
                    }
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-white truncate">${item.title}</p>
                    <p class="text-xs text-gray-500">${item.is_youtube ? 'YouTube' : 'File'}</p>
                </div>
            </div>
            <div class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition">
                <button type="button" class="playNowBtn w-8 h-8 bg-green-500/20 hover:bg-green-500 text-green-400 hover:text-white rounded-lg flex items-center justify-center transition ${isActive && isPlaying ? 'hidden' : ''}" title="Play">
                    <i class="fas fa-play text-xs"></i>
                </button>
                <button type="button" class="pauseBtn w-8 h-8 bg-yellow-500/20 hover:bg-yellow-500 text-yellow-400 hover:text-white rounded-lg flex items-center justify-center transition ${isActive && isPlaying ? '' : 'hidden'}" title="Pause">
                    <i class="fas fa-pause text-xs"></i>
                </button>
                <button type="button" class="removeBtn w-8 h-8 bg-red-500/20 hover:bg-red-500 text-red-400 hover:text-white rounded-lg flex items-center justify-center transition" title="Remove">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        `;
        
        div.querySelector('.playNowBtn')?.addEventListener('click', (e) => { e.stopPropagation(); playNow(item.video_id); });
        div.querySelector('.pauseBtn')?.addEventListener('click', (e) => { e.stopPropagation(); pausePlayback(); });
        div.querySelector('.removeBtn').addEventListener('click', (e) => { e.stopPropagation(); removeFromPlaylist(item.video_id); });
        div.addEventListener('dblclick', () => playNow(item.video_id));
        
        fragment.appendChild(div);
    });
    
    container.innerHTML = '';
    container.appendChild(fragment);
}

function refreshMonitorData() {
    if (isLoadingMonitor) return;
    isLoadingMonitor = true;
    
    fetch(`/${orgCode}/monitor/data`)
        .then(r => r.json())
        .then(d => {
            const activeCounters = d.counters?.length || 0;
            const waitingQueues = d.waiting_queues?.reduce((sum, q) => sum + (q.queues?.length || 0), 0) || 0;
            
            document.getElementById('counterCount').textContent = activeCounters;
            document.getElementById('waitingCount').textContent = waitingQueues;
            document.getElementById('servedCount').textContent = d.served_today || 0;
            isLoadingMonitor = false;
        })
        .catch(() => { isLoadingMonitor = false; });
}

function updateNowPlayingDisplay(nowPlaying) {
    const titleEl = document.getElementById('nowPlayingTitle');
    const typeEl = document.getElementById('nowPlayingType');
    const thumbEl = document.getElementById('nowPlayingThumbnail');
    
    if (nowPlaying && nowPlaying.title) {
        nowPlayingVideo = nowPlaying;
        titleEl.textContent = nowPlaying.title;
        
        const isYoutube = nowPlaying.video_type === 'youtube';
        typeEl.innerHTML = `
            <span class="inline-flex items-center">
                <i class="${isYoutube ? 'fab fa-youtube text-red-400' : 'fas fa-file-video text-blue-400'} text-xs mr-2"></i>
                <span>${isYoutube ? 'YouTube Video' : 'Uploaded File'}</span>
            </span>
            ${isPlaying ? '<span class="ml-3 px-2 py-0.5 bg-green-500/20 text-green-400 rounded-full text-xs font-medium"><i class="fas fa-circle animate-pulse mr-1"></i>Playing</span>' : ''}
        `;
        
        thumbEl.innerHTML = isYoutube 
            ? '<i class="fab fa-youtube text-red-400 text-2xl"></i>'
            : '<i class="fas fa-play text-blue-400 text-2xl"></i>';
        thumbEl.className = 'w-16 h-16 bg-gray-700 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden';
    } else {
        nowPlayingVideo = null;
        titleEl.textContent = 'No video selected';
        typeEl.innerHTML = '<span class="inline-flex items-center"><i class="fas fa-circle text-gray-600 text-xs mr-1"></i><span>—</span></span>';
        thumbEl.innerHTML = '<i class="fas fa-film text-gray-500 text-2xl"></i>';
    }
}

function setNowPlayingFromSelect() {
    const sel = document.getElementById('setNowSelect');
    if (!sel || !sel.value) {
        showToast('Please select a video first', 'error');
        return;
    }
    playNow(parseInt(sel.value, 10));
}

function prevVideo() {
    const currentIdx = currentPlaylist.findIndex(v => nowPlayingVideo && v.video_id === nowPlayingVideo.id);
    if (currentIdx > 0) {
        playNow(currentPlaylist[currentIdx - 1].video_id);
    } else if (repeatMode === 'all' && currentPlaylist.length > 0) {
        playNow(currentPlaylist[currentPlaylist.length - 1].video_id);
    }
}

function nextVideo() {
    const currentIdx = currentPlaylist.findIndex(v => nowPlayingVideo && v.video_id === nowPlayingVideo.id);
    if (currentIdx !== -1 && currentIdx < currentPlaylist.length - 1) {
        playNow(currentPlaylist[currentIdx + 1].video_id);
    } else if (repeatMode === 'all' && currentPlaylist.length > 0) {
        playNow(currentPlaylist[0].video_id);
    }
}

function updatePlaylistControlUI(control) {
    if (control) {
        repeatMode = control.repeat_mode || 'off';
        isShuffle = control.is_shuffle || false;
        isSequence = control.is_sequence !== false;
        updateRepeatBtn();
        updateShuffleBtn();
    }
}

function updateRepeatBtn() {
    const btn = document.getElementById('repeatBtn');
    const modes = { 'off': 'Off', 'one': 'One', 'all': 'All' };
    const active = repeatMode !== 'off';
    btn.innerHTML = `<i class="fas fa-repeat"></i><span>${modes[repeatMode] || 'Off'}</span>`;
    btn.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center space-x-2 ${
        active ? 'bg-blue-500 text-white' : 'bg-gray-700/50 hover:bg-gray-600'
    }`;
}

function updateShuffleBtn() {
    const btn = document.getElementById('shuffleBtn');
    btn.innerHTML = `<i class="fas fa-shuffle"></i><span>${isShuffle ? 'On' : 'Off'}</span>`;
    btn.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center space-x-2 ${
        isShuffle ? 'bg-purple-500 text-white' : 'bg-gray-700/50 hover:bg-gray-600'
    }`;
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

function toggleMute() {
    const slider = document.getElementById('volumeSlider');
    const icon = document.getElementById('volumeIcon');
    
    if (isMuted) {
        slider.value = previousVolume;
        isMuted = false;
    } else {
        previousVolume = slider.value;
        slider.value = 0;
        isMuted = true;
    }
    
    updateVolumeDisplay(slider.value);
    updateVolume(slider.value);
    
    icon.className = `fas ${isMuted || slider.value == 0 ? 'fa-volume-mute' : slider.value < 50 ? 'fa-volume-down' : 'fa-volume-up'} text-gray-300`;
}

function updatePlayButton() {
    const btn = document.getElementById('playBtn');
    const icon = document.getElementById('playIcon');
    if (!btn || !icon) return;
    
    if (isPlaying) {
        icon.className = 'fas fa-pause text-white text-xl';
        btn.className = 'w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 hover:from-green-400 hover:to-green-500 rounded-2xl flex items-center justify-center transition transform hover:scale-105 shadow-lg shadow-green-500/30';
    } else {
        icon.className = 'fas fa-play text-white text-xl ml-1';
        btn.className = 'w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 rounded-2xl flex items-center justify-center transition transform hover:scale-105 shadow-lg shadow-blue-500/30';
    }
}

function updateControl() {
    fetch(`/${orgCode}/admin/videos/control`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            is_playing: isPlaying,
            volume: document.getElementById('volumeSlider').value,
            bell_volume: document.getElementById('bellVolumeSlider').value,
            current_video_id: nowPlayingVideo ? nowPlayingVideo.id : null
        })
    }).catch(() => {});
}

function updateVolume(value) {
    const icon = document.getElementById('volumeIcon');
    icon.className = `fas ${value == 0 ? 'fa-volume-mute' : value < 50 ? 'fa-volume-down' : 'fa-volume-up'} text-gray-300`;
    isMuted = value == 0;
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
            showToast('Added to queue', 'success');
        } else {
            showToast(d.error || 'Already in queue', 'error');
        }
    })
    .catch(e => showToast('Error: ' + e.message, 'error'));
}

function toggleActive(videoId, toggleUrl) {
    fetch(toggleUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            const row = document.querySelector(`[data-video-row][data-video-id="${videoId}"]`);
            if (row) {
                const icon = row.querySelector('button[title="Toggle Active"] i');
                if (icon) {
                    icon.className = `fas fa-toggle-${d.is_active ? 'on text-green-400' : 'off'} text-xs`;
                }
                const statusText = row.querySelector('.text-yellow-500');
                if (d.is_active && statusText) {
                    statusText.remove();
                } else if (!d.is_active) {
                    const nameEl = row.querySelector('.text-xs.text-gray-500');
                    if (nameEl && !nameEl.querySelector('.text-yellow-500')) {
                        nameEl.innerHTML += ' <span class="ml-2 text-yellow-500">(Inactive)</span>';
                    }
                }
            }
            showToast(`Video ${d.is_active ? 'activated' : 'deactivated'}`, 'success');
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
        if (d.success) {
            loadPlaylist();
            showToast('Removed from queue', 'success');
        }
    })
    .catch(() => {});
}

function clearPlaylist() {
    if (!confirm('Clear all videos from the queue?')) return;
    
    currentPlaylist.forEach(item => {
        fetch(`/${orgCode}/admin/playlist/remove`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ video_id: item.video_id })
        });
    });
    
    setTimeout(() => {
        loadPlaylist();
        showToast('Queue cleared', 'success');
    }, 500);
}

function playNow(videoId) {
    isPlaying = true;
    updatePlayButton();
    
    const row = document.querySelector(`[data-video-row][data-video-id="${videoId}"]`);
    const title = row ? row.dataset.videoTitle || row.querySelector('.font-medium')?.textContent : null;
    nowPlayingVideo = { id: videoId, title: title };
    updateNowPlayingDisplay(nowPlayingVideo);
    
    fetch(`/${orgCode}/admin/playlist/now-playing`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ video_id: videoId })
    })
    .then(() => {
        updateControl();
        syncPlaylistAndControl();
    })
    .catch(() => {});
}

function pausePlayback() {
    isPlaying = false;
    updatePlayButton();
    updateControl();
}

function stopPlayback() {
    isPlaying = false;
    nowPlayingVideo = null;
    updatePlayButton();
    updateNowPlayingDisplay(null);
    
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
    .then(() => loadPlaylist())
    .catch(() => {});
}

// Form submission with progress
document.getElementById('videoForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const progressDiv = document.getElementById('uploadProgress');
    const uploadBar = document.getElementById('uploadBar');
    const uploadPercent = document.getElementById('uploadPercent');
    const submitBtn = document.getElementById('uploadBtn');
    
    progressDiv.classList.remove('hidden');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin"></i><span>Uploading...</span>';
    
    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', (evt) => {
        if (evt.lengthComputable) {
            const pct = Math.round((evt.loaded / evt.total) * 100);
            uploadPercent.textContent = pct + '%';
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
                    
                    setTimeout(() => {
                        progressDiv.classList.add('hidden');
                        uploadBar.style.width = '0%';
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-upload"></i><span>Add Video</span>';
                        document.getElementById('videoForm').reset();
                        showToast('Video added successfully!', 'success');
                        location.reload();
                    }, 800);
                }
            } catch (error) {
                location.reload();
            }
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload"></i><span>Add Video</span>';
            progressDiv.classList.add('hidden');
            showToast('Upload failed. Please try again.', 'error');
        }
    });
    
    xhr.addEventListener('error', () => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-upload"></i><span>Add Video</span>';
        progressDiv.classList.add('hidden');
        showToast('Upload failed. Please try again.', 'error');
    });
    
    xhr.open('POST', this.action, true);
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    xhr.send(formData);
});

function toggleVideoType() {
    const type = document.querySelector('input[name="video_type"]:checked').value;
    document.getElementById('fileInput').classList.toggle('hidden', type !== 'file');
    document.getElementById('youtubeInput').classList.toggle('hidden', type !== 'youtube');
}

function deleteVideoModal(videoId, filename) {
    document.getElementById('delete-video-name').textContent = filename || 'Untitled Video';
    window.currentDeleteUrl = `/${orgCode}/admin/videos/${videoId}`;
    window.currentVideoId = videoId;
    document.getElementById('delete-video-modal').classList.remove('hidden');
}

function openEditVideo(videoId, title) {
    window.currentEditUrl = `/${orgCode}/admin/videos/${videoId}`;
    window.currentEditVideoId = videoId;
    document.getElementById('edit-video-title').value = title || '';
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
        if (d.success) {
            closeModalVideo('edit-video-modal');
            showToast('Video updated', 'success');
            location.reload();
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
        showToast('Bell sound reset to default', 'success');
        setTimeout(() => location.reload(), 500);
    })
    .catch(() => showToast('Error resetting bell', 'error'));
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 px-5 py-3 rounded-xl text-sm font-medium text-white max-w-sm z-[9999] shadow-xl transform translate-x-0 transition-all duration-300 flex items-center space-x-3 ${
        type === 'success' 
            ? 'bg-gradient-to-r from-green-600 to-green-700' 
            : 'bg-gradient-to-r from-red-600 to-red-700'
    }`;
    toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Close modals on backdrop click
document.querySelectorAll('[id$="-modal"]').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    
    switch(e.key) {
        case ' ':
            e.preventDefault();
            togglePlay();
            break;
        case 'ArrowLeft':
            prevVideo();
            break;
        case 'ArrowRight':
            nextVideo();
            break;
        case 'm':
            toggleMute();
            break;
    }
});
</script>
@endpush

@endsection
