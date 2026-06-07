<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProcessorCameraController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeProcessor($request);

        $cameras = Camera::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name', 'stream_url', 'location']);

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
