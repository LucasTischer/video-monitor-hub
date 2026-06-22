<?php

use App\Models\Camera;
use App\Models\User;

beforeEach(function () {
    config(['processor.api_token' => 'test-processor-token']);
});

test('processor camera endpoint requires a valid token', function () {
    $this->getJson('/api/processor/cameras')
        ->assertUnauthorized();
});

test('processor can list active cameras', function () {
    $user = User::factory()->create();

    Camera::create([
        'user_id' => $user->id,
        'name' => 'Front Gate',
        'stream_url' => 'http://camera.local/front',
        'location' => 'Entrance',
        'is_active' => true,
        'motion_detection_enabled' => true,
    ]);

    Camera::create([
        'user_id' => $user->id,
        'name' => 'Inactive Camera',
        'stream_url' => 'http://camera.local/inactive',
        'is_active' => false,
        'motion_detection_enabled' => true,
    ]);

    Camera::create([
        'user_id' => $user->id,
        'name' => 'Detection Disabled Camera',
        'stream_url' => 'http://camera.local/detection-disabled',
        'is_active' => true,
        'motion_detection_enabled' => false,
    ]);

    $this->withToken('test-processor-token')
        ->getJson('/api/processor/cameras')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Front Gate')
        ->assertJsonMissing(['name' => 'Inactive Camera'])
        ->assertJsonMissing(['name' => 'Detection Disabled Camera']);
});

test('processor can register a video recording', function () {
    $user = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Front Gate',
        'stream_url' => 'http://camera.local/front',
        'is_active' => true,
    ]);

    $this->withToken('test-processor-token')
        ->postJson('/api/processor/videos', [
            'camera_id' => $camera->id,
            'filename' => 'front-gate-motion.avi',
            'path' => '/storage/videos/front-gate-motion.avi',
            'started_at' => '2026-06-07 10:00:00',
            'ended_at' => '2026-06-07 10:00:12',
            'duration_seconds' => 12,
            'motion_detected' => true,
            'metadata' => [
                'source' => 'python-processor',
            ],
        ])
        ->assertCreated()
        ->assertJsonPath('data.camera_id', $camera->id);

    $this->assertDatabaseHas('videos', [
        'camera_id' => $camera->id,
        'filename' => 'front-gate-motion.avi',
        'path' => '/storage/videos/front-gate-motion.avi',
        'duration_seconds' => 12,
        'motion_detected' => true,
    ]);
});

test('processor video endpoint validates payload', function () {
    $this->withToken('test-processor-token')
        ->postJson('/api/processor/videos', [
            'filename' => '',
            'path' => '',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['camera_id', 'filename', 'path']);
});
