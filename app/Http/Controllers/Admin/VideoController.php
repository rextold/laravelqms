<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoControl;
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
        $videos = Video::orderBy('order')->get();
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
                'order' => $validated['order'] ?? Video::max('order') + 1,
                'is_active' => true,
            ];

            if ($validated['video_type'] === 'file' && $request->hasFile('video')) {
                $file = $request->file('video');
                
                \Log::info('Video file upload attempted', [
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
                
                $path = $file->store('videos', 'public');
                
                \Log::info('Video file stored successfully', ['path' => $path]);
                
                $data['file_path'] = $path;
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

    public function destroy(Video $video)
    {
        if ($video->isFile() && $video->file_path) {
            Storage::disk('public')->delete($video->file_path);
        }
        $video->delete();

        return redirect()->route('admin.videos.index', ['organization_code' => request()->route('organization_code')])
            ->with('success', 'Video deleted successfully.');
    }

    public function updateControl(Request $request)
    {
        $validated = $request->validate([
            'is_playing' => 'required|boolean',
            'volume' => 'required|integer|min:0|max:100',
            'bell_volume' => 'nullable|integer|min:0|max:100',
            'current_video_id' => 'nullable|exists:videos,id',
        ]);

        $control = VideoControl::getCurrent();
        $control->update($validated);

        return response()->json(['success' => true]);
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
}
