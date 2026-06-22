<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Camera;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $cameras = Camera::query()
            ->visibleTo($user)
            ->withCount('videos')
            ->latest()
            ->get();

        $userVideos = Video::query()
            ->visibleTo($user);

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
