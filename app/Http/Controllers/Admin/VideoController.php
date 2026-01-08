<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::orderBy('order')->get();
        $control = VideoControl::getCurrent();

        return view('admin.videos.index', compact('videos', 'control'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'video' => 'required|mimes:mp4,avi,mov,wmv|max:102400', // 100MB max
            'order' => 'nullable|integer',
        ]);

        $path = $request->file('video')->store('videos', 'public');

        Video::create([
            'title' => $validated['title'],
            'file_path' => $path,
            'order' => $validated['order'] ?? Video::max('order') + 1,
            'is_active' => true,
        ]);

        return redirect()->route('admin.videos.index')
            ->with('success', 'Video uploaded successfully.');
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
        Storage::disk('public')->delete($video->file_path);
        $video->delete();

        return redirect()->route('admin.videos.index')
            ->with('success', 'Video deleted successfully.');
    }

    public function updateControl(Request $request)
    {
        $validated = $request->validate([
            'is_playing' => 'required|boolean',
            'volume' => 'required|integer|min:0|max:100',
            'current_video_id' => 'nullable|exists:videos,id',
        ]);

        $control = VideoControl::getCurrent();
        $control->update($validated);

        return response()->json(['success' => true]);
    }
}
