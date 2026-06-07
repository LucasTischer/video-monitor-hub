<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $cameras = $user->cameras()
            ->withCount('videos')
            ->latest()
            ->get();

        $userVideos = Video::query()
            ->whereHas('camera', fn ($query) => $query->where('user_id', $user->id));

        $recentVideos = (clone $userVideos)
            ->with('camera')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', [
            'cameras' => $cameras,
            'recentVideos' => $recentVideos,
            'totalCameras' => $cameras->count(),
            'activeCameras' => $cameras->where('is_active', true)->count(),
            'totalVideos' => $userVideos->count(),
        ]);
    }
}
