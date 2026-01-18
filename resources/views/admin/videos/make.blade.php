@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h2 class="text-xl font-bold mb-3">Make Video (Slideshow)</h2>

    <div class="bg-gray-800 p-4 rounded border border-gray-700">
        <form id="makeForm" action="{{ route('admin.videos.make.post', ['organization_code' => request()->route('organization_code')]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-3">
                <label class="text-sm text-gray-300">Title</label>
                <input name="title" required class="w-full px-2 py-1 bg-gray-900 border border-gray-700 rounded text-white" />

                <label class="text-sm text-gray-300">Images (multiple)</label>
                <input type="file" name="images[]" accept="image/*" multiple required class="w-full" />

                <label class="text-sm text-gray-300">Optional audio (mp3/wav)</label>
                <input type="file" name="audio" accept="audio/*" class="w-full" />

                <label class="text-sm text-gray-300">Duration per slide (seconds)</label>
                <input type="number" name="duration" value="5" min="1" max="60" class="w-24 px-2 py-1 bg-gray-900 border border-gray-700 rounded text-white" />

                <div id="makeProgress" class="hidden">
                    <div class="text-xs text-gray-400"><span id="makeStatus">Working...</span></div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 px-3 py-1 rounded text-white">Build Video</button>
                    <a href="{{ route('admin.videos.index', ['organization_code' => request()->route('organization_code')]) }}" class="bg-gray-700 px-3 py-1 rounded text-white">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
