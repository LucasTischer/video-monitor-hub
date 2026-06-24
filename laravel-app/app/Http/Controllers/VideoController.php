<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(Request $request): View
    {
        $videos = Video::query()
            ->visibleTo($request->user())
            ->with('camera')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('videos.index', [
            'videos' => $videos,
        ]);
    }

    public function show(Video $video): View
    {
        $this->authorize('view', $video);

        $video->load('camera');

        return view('videos.show', [
            'video' => $video,
        ]);
    }

    public function destroy(Request $request, Video $video): RedirectResponse
    {
        $this->authorize('delete', $video);

        $video->delete();

        return redirect()
            ->route('videos.index', $request->only('page'))
            ->with('status', 'Video recording deleted successfully.');
    }
}
