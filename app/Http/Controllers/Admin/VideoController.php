<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoControl;
use App\Models\PlaylistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    /**
     * Parse php.ini size shorthand (e.g. 2M, 512K, 1G) to bytes.
     */
    private function bytesFromIni(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }
        $last = strtolower($value[strlen($value) - 1]);
        $num = (int)$value;
        switch ($last) {
            case 'g':
                return $num * 1024 * 1024 * 1024;
            case 'm':
                return $num * 1024 * 1024;
            case 'k':
                return $num * 1024;
            default:
                return $num; // already bytes
        }
    }

    /**
     * Format bytes to a human-readable label (KB/MB/GB).
     */
    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / (1024 * 1024 * 1024)) . 'GB';
        }
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024)) . 'MB';
        }
        return max(1, round($bytes / 1024)) . 'KB';
    }

    public function index()
    {
        $orgCode = request()->route('organization_code');
        $organization = \App\Models\Organization::where('organization_code', $orgCode)->firstOrFail();
        
        $videos = Video::where('organization_id', $organization->id)->orderBy('order')->get();
        $control = VideoControl::getCurrent();
        // Compute effective upload limit from php.ini settings
        $uploadBytes = $this->bytesFromIni(ini_get('upload_max_filesize') ?: '0');
        $postBytes = $this->bytesFromIni(ini_get('post_max_size') ?: '0');
        $effectiveBytes = ($uploadBytes && $postBytes) ? min($uploadBytes, $postBytes) : max($uploadBytes, $postBytes);
        $maxUploadLabel = $this->humanSize($effectiveBytes);

        return view('admin.videos.index', compact('videos', 'control', 'maxUploadLabel'));
    }

    public function store(Request $request)
    {
        try {
            // Derive dynamic max (KB) based on php.ini limits
            $uploadBytes = $this->bytesFromIni(ini_get('upload_max_filesize') ?: '0');
            $postBytes = $this->bytesFromIni(ini_get('post_max_size') ?: '0');
            $effectiveBytes = ($uploadBytes && $postBytes) ? min($uploadBytes, $postBytes) : max($uploadBytes, $postBytes);
            $effectiveKB = max(1, (int) floor($effectiveBytes / 1024));

            $orgCode = $request->route('organization_code');
            $organization = \App\Models\Organization::where('organization_code', $orgCode)->firstOrFail();

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'video_type' => 'required|in:file,youtube',
                'video' => 'required_if:video_type,file|nullable|mimes:mp4,avi,mov,wmv|max:' . $effectiveKB,
                'youtube_url' => 'required_if:video_type,youtube|nullable|url',
                'order' => 'nullable|integer',
            ]);

            $data = [
                'title' => $validated['title'],
                'video_type' => $validated['video_type'],
                'order' => $validated['order'] ?? Video::where('organization_id', $organization->id)->max('order') + 1,
                'is_active' => true,
                'organization_id' => $organization->id,
            ];

            // Handle file upload (sync for speed)
            if ($validated['video_type'] === 'file' && $request->hasFile('video')) {
                $file = $request->file('video');
                
                \Log::info('Video file upload started', [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ]);
                
                // Store file synchronously for immediate availability
                $path = $file->storeAs('videos', uniqid() . '_' . $file->getClientOriginalName(), 'public');
                $data['file_path'] = $path;
                
                \Log::info('Video file stored', ['path' => $path]);
            } elseif ($validated['video_type'] === 'youtube') {
                $data['youtube_url'] = $validated['youtube_url'];
            }

            $video = Video::create($data);

            // If AJAX request, return JSON with video data
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Video added successfully.',
                    'video' => [
                        'id' => $video->id,
                        'title' => $video->title,
                        'order' => $video->order,
                        'is_active' => $video->is_active,
                        'video_type' => $video->video_type,
                        'is_youtube' => $video->isYoutube(),
                        'is_file' => $video->isFile(),
                        'youtube_url' => $video->youtube_url,
                        'youtube_embed_url' => $video->youtube_embed_url,
                        'file_path' => $video->file_path ? asset('storage/'.$video->file_path) : null,
                        'filename' => $video->filename,
                    ]
                ]);
            }

            return redirect()->route('admin.videos.index', ['organization_code' => request()->route('organization_code')])
                ->with('success', 'Video added successfully.');
        } catch (\Exception $e) {
            \Log::error('Video upload failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return back()->withInput()->with('error', 'Video upload failed: ' . $e->getMessage());
        }
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'videos' => 'required|array',
            'videos.*.id' => 'required|exists:videos,id',
            'videos.*.order' => 'required|integer',
        ]);

        foreach ($validated['videos'] as $videoData) {
            Video::where('id', $videoData['id'])
                ->update(['order' => $videoData['order']]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleActive(Video $video)
    {
        $video->update(['is_active' => !$video->is_active]);

        return response()->json(['success' => true, 'is_active' => $video->is_active]);
    }

    public function destroy($video)
    {
        try {
            $orgCode = request()->route('organization_code');
            $organization = \App\Models\Organization::where('organization_code', $orgCode)->firstOrFail();
            
            // Always treat as ID since we use whereNumber() in route
            $videoModel = Video::where('id', (int)$video)
                ->where('organization_id', $organization->id)
                ->firstOrFail();
            
            // Remove from all playlists
            PlaylistItem::where('video_id', $videoModel->id)->delete();
            
            // Delete file from storage if it exists
            if ($videoModel->isFile() && $videoModel->file_path) {
                Storage::disk('public')->delete($videoModel->file_path);
            }
            
            $videoModel->delete();

            // Return JSON for AJAX requests
            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Video deleted successfully.']);
            }

            return redirect()->route('admin.videos.index', ['organization_code' => request()->route('organization_code')])
                ->with('success', 'Video deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Video deletion error', [
                'video_id' => $video,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to delete video'], 400);
            }
            
            return redirect()->back()->with('error', 'Failed to delete video: ' . $e->getMessage());
        }
    }
    public function updateControl(Request $request)
    {
        
        $validated = $request->validate([
            'is_playing' => 'required|boolean',
            'volume' => 'required|integer|min:0|max:100',
            'bell_volume' => 'nullable|integer|min:0|max:100',
            'current_video_id' => 'nullable|exists:videos,id',
            'bell_choice' => 'nullable|string',
            'video_muted' => 'nullable|boolean',
            'autoplay' => 'nullable|boolean',
            'loop' => 'nullable|boolean',
        ]);

        $control = VideoControl::getCurrent();

        // Only set columns that actually exist to avoid migration issues
        $columnsToSet = [
            'is_playing', 'volume', 'bell_volume', 'current_video_id',
            'bell_choice', 'video_muted', 'autoplay', 'loop'
        ];

        foreach ($columnsToSet as $col) {
            if (array_key_exists($col, $validated)) {
                // check if column exists on table
                if (\Illuminate\Support\Facades\Schema::hasColumn('video_controls', $col)) {
                    $control->{$col} = $validated[$col];
                } else {
                    // store transient meta if needed (not persisted)
                    $control->{$col} = $validated[$col];
                }
            }
        }

        $control->save();

        // Broadcast update so monitors can receive push updates (if broadcasting configured)
        try {
            event(new \App\Events\VideoControlUpdated($control, []));
        } catch (\Throwable $e) {
            // non-fatal if broadcasting not configured
            \Log::debug('VideoControlUpdated broadcast failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'control' => $control]);
    }
    /**
     * Set specific video to play now on monitor
     */
    public function setNowPlaying(Request $request)
    {
        $validated = $request->validate([
            'video_id' => 'required|exists:videos,id',
        ]);

        $orgCode = $request->route('organization_code');
        $organization = \App\Models\Organization::where('organization_code', $orgCode)->firstOrFail();
        
        // Verify video belongs to this organization
        $video = Video::where('id', $validated['video_id'])
                     ->where('organization_id', $organization->id)
                     ->firstOrFail();

        $control = VideoControl::getCurrent();
        $control->update([
            'current_video_id' => $video->id,
            'is_playing' => true, // Auto-start when manually selected
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Now playing: ' . $video->title,
            'video' => $video
        ]);
    }

    public function unmute(Request $request)
    {
        $validated = $request->validate([
            'seconds' => 'nullable|integer|min:1|max:600'
        ]);
        $orgCode = $request->route('organization_code');
        $organization = \App\Models\Organization::where('organization_code', $orgCode)->firstOrFail();

        $control = VideoControl::getCurrent();

        // If database has video_muted column, unset it
        if (\Illuminate\Support\Facades\Schema::hasColumn('video_controls', 'video_muted')) {
            $control->video_muted = false;
            $control->save();
        }

        $seconds = $validated['seconds'] ?? 10;
        $until = now()->addSeconds($seconds);

        // Persist unmute_until on the control if column exists
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('video_controls', 'unmute_until')) {
                $control->unmute_until = $until;
                $control->save();
            }
        } catch (\Throwable $e) {
            \Log::debug('Failed to persist unmute_until: ' . $e->getMessage());
        }

        // store a transient cache key as fallback so monitor/data can include unmute_until
        try {
            \Illuminate\Support\Facades\Cache::put('video_control_unmute_until_' . $organization->id, $until->toDateTimeString(), $seconds);
        } catch (\Throwable $e) {
            \Log::debug('Cache put failed for unmute_until: ' . $e->getMessage());
        }

        // Broadcast a transient unmute instruction to monitors
        try {
            $meta = ['unmute_seconds' => $seconds, 'unmute_until' => $until->toDateTimeString(), 'organization_id' => $organization->id];
            event(new \App\Events\VideoControlUpdated($control, $meta));
        } catch (\Throwable $e) {
            \Log::debug('VideoControlUpdated unmute broadcast failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'unmute_seconds' => $seconds, 'unmute_until' => $until->toDateTimeString()]);
    }

    public function update(Request $request, Video $video)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $video->update(['title' => $validated['title']]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'video' => $video]);
        }

        return redirect()->back()->with('success', 'Video updated successfully.');
    }
    public function uploadBellSound(Request $request)
    {
        try {
            $request->validate([
                'bell_sound' => 'required|mimes:mp3,wav,ogg|max:2048', // 2MB max (limited by PHP ini)
            ]);

            $control = VideoControl::getCurrent();

            // Delete old custom bell sound if exists
            if ($control->bell_sound_path) {
                Storage::disk('public')->delete($control->bell_sound_path);
            }

            $file = $request->file('bell_sound');
            
            \Log::info('Bell sound upload attempted', [
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
            
            $path = $file->store('sounds', 'public');
            
            \Log::info('Bell sound stored successfully', ['path' => $path]);
            
            $control->update(['bell_sound_path' => $path]);

            return redirect()->route('admin.videos.index', ['organization_code' => request()->route('organization_code')])
                ->with('success', 'Bell sound uploaded successfully.');
        } catch (\Exception $e) {
            \Log::error('Bell sound upload failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return back()->withInput()->with('error', 'Bell sound upload failed: ' . $e->getMessage());
        }
    }

    public function resetBellSound()
    {
        $control = VideoControl::getCurrent();

        // Delete custom bell sound if exists
        if ($control->bell_sound_path) {
            Storage::disk('public')->delete($control->bell_sound_path);
            $control->update(['bell_sound_path' => null]);
        }

        return response()->json(['success' => true, 'message' => 'Bell sound reset to default.']);
    }

    /**
     * Get playlist for the organization
     */
    public function getPlaylist(Request $request)
    {
        $orgCode = $request->route('organization_code');
        $organization = \App\Models\Organization::where('organization_code', $orgCode)->first();
        
        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        // Eager load videos to avoid N+1 queries
        $playlist = PlaylistItem::with('video:id,title,video_type')
            ->where('organization_id', $organization->id)
            ->orderBy('sequence_order')
            ->select('id', 'video_id', 'sequence_order')
            ->get();
        
        // Get now playing video with eager load
        $control = VideoControl::getCurrent();
        $nowPlaying = null;
        if ($control->current_video_id) {
            $nowPlaying = Video::select('id', 'title', 'video_type', 'file_path', 'youtube_url')
                ->find($control->current_video_id);
        }

        return response()->json([
            'success' => true,
            'playlist' => $playlist->map(fn($item) => [
                'id' => $item->id,
                'video_id' => $item->video_id,
                'title' => $item->video->title,
                'type' => $item->video->video_type,
                'is_youtube' => $item->video->isYoutube(),
                'sequence_order' => $item->sequence_order,
            ]),
            'now_playing' => $nowPlaying ? [
                'id' => $nowPlaying->id,
                'title' => $nowPlaying->title,
                'video_type' => $nowPlaying->video_type,
                'file_path' => $nowPlaying->file_path,
                'youtube_url' => $nowPlaying->youtube_url,
                'youtube_embed_url' => $nowPlaying->youtube_embed_url,
            ] : null,
            'control' => [
                'current_video_id' => $control->current_video_id,
                'is_playing' => $control->is_playing,
                'volume' => $control->volume ?? 50,
                'bell_volume' => $control->bell_volume ?? 100,
                'bell_choice' => $control->bell_choice ?? null,
                'video_muted' => $control->video_muted ?? false,
                'autoplay' => $control->autoplay ?? false,
                'loop' => $control->loop ?? false,
                'repeat_mode' => $control->repeat_mode,
                'is_shuffle' => $control->is_shuffle,
                'is_sequence' => $control->is_sequence,
            ]
        ]);
    }

    /**
     * Add video to playlist
     */
    public function addToPlaylist(Request $request)
    {
        $validated = $request->validate([
            'video_id' => 'required|exists:videos,id',
        ]);

        $orgCode = $request->route('organization_code');
        $organization = \App\Models\Organization::where('organization_code', $orgCode)->first();
        
        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        // Check if already in playlist
        $existing = PlaylistItem::where('video_id', $validated['video_id'])
            ->where('organization_id', $organization->id)
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Video already in playlist'], 422);
        }

        PlaylistItem::addToPlaylist($organization->id, $validated['video_id']);

        return response()->json(['success' => true, 'message' => 'Video added to playlist.']);
    }

    /**
     * Remove video from playlist
     */
    public function removeFromPlaylist(Request $request)
    {
        $validated = $request->validate([
            'video_id' => 'required|exists:videos,id',
        ]);

        $orgCode = $request->route('organization_code');
        $organization = \App\Models\Organization::where('organization_code', $orgCode)->first();
        
        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        PlaylistItem::removeFromPlaylist($organization->id, $validated['video_id']);

        return response()->json(['success' => true, 'message' => 'Video removed from playlist.']);
    }

    /**
     * Reorder playlist
     */
    public function reorderPlaylist(Request $request)
    {
        $validated = $request->validate([
            'video_ids' => 'required|array',
            'video_ids.*' => 'required|exists:videos,id',
        ]);

        $orgCode = $request->route('organization_code');
        $organization = \App\Models\Organization::where('organization_code', $orgCode)->first();
        
        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        PlaylistItem::reorderPlaylist($organization->id, $validated['video_ids']);

        return response()->json(['success' => true, 'message' => 'Playlist reordered.']);
    }

    /**
     * Update playlist control settings (repeat, shuffle, sequence)
     */
    public function updatePlaylistControl(Request $request)
    {
        $validated = $request->validate([
            'repeat_mode' => 'nullable|in:off,one,all',
            'is_shuffle' => 'nullable|boolean',
            'is_sequence' => 'nullable|boolean',
        ]);

        $control = VideoControl::getCurrent();
        
        if (isset($validated['repeat_mode'])) {
            $control->repeat_mode = $validated['repeat_mode'];
        }
        if (isset($validated['is_shuffle'])) {
            $control->is_shuffle = $validated['is_shuffle'];
        }
        if (isset($validated['is_sequence'])) {
            $control->is_sequence = $validated['is_sequence'];
        }
        
        $control->save();

        return response()->json([
            'success' => true,
            'control' => [
                'repeat_mode' => $control->repeat_mode,
                'is_shuffle' => $control->is_shuffle,
                'is_sequence' => $control->is_sequence,
            ]
        ]);
    }
}
