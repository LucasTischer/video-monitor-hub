<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProcessorVideoController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $this->authorizeProcessor($request);

        $validated = $request->validate([
            'camera_id' => ['required', 'integer', 'exists:cameras,id'],
            'filename' => ['required', 'string', 'max:255'],
            'path' => ['required', 'string', 'max:255'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'motion_detected' => ['boolean'],
            'metadata' => ['nullable', 'array'],
        ]);

        $video = Video::create($validated);

        return response()->json([
            'data' => [
                'id' => $video->id,
                'camera_id' => $video->camera_id,
                'filename' => $video->filename,
                'path' => $video->path,
            ],
        ], 201);
    }

    private function authorizeProcessor(Request $request): void
    {
        $expectedToken = config('processor.api_token');

        abort_if(blank($expectedToken), 503, 'Processor API token is not configured.');
        abort_unless(hash_equals($expectedToken, (string) $request->bearerToken()), 401);
    }
}
