<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Camera;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProcessorCameraController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeProcessor($request);

        $timezone = AppSetting::currentTimezone();

        $cameras = Camera::query()
            ->where('is_active', true)
            ->where('motion_detection_enabled', true)
            ->currentlyMonitorable()
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'stream_url',
                'location',
                'motion_detection_enabled',
                'record_after_motion_seconds',
                'pre_motion_buffer_seconds',
                'recording_resolution_height',
                'recording_fps',
            ])
            ->map(fn (Camera $camera): array => [
                'id' => $camera->id,
                'name' => $camera->name,
                'stream_url' => $camera->stream_url,
                'location' => $camera->location,
                'motion_detection_enabled' => $camera->motion_detection_enabled,
                'record_after_motion_seconds' => $camera->record_after_motion_seconds,
                'pre_motion_buffer_seconds' => $camera->pre_motion_buffer_seconds,
                'recording_resolution_height' => $camera->recording_resolution_height,
                'recording_fps' => $camera->recording_fps,
                'timezone' => $timezone,
            ]);

        return response()->json([
            'data' => $cameras,
        ]);
    }

    private function authorizeProcessor(Request $request): void
    {
        $expectedToken = config('processor.api_token');

        abort_if(blank($expectedToken), 503, 'Processor API token is not configured.');
        abort_unless(hash_equals($expectedToken, (string) $request->bearerToken()), 401);
    }
}
