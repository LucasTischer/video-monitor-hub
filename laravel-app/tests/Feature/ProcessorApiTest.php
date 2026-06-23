<?php

use App\Models\Camera;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    config(['processor.api_token' => 'test-processor-token']);
    config(['app.timezone' => 'America/Sao_Paulo']);
    Carbon::setTestNow(Carbon::parse('2026-06-23 10:00:00', 'America/Sao_Paulo'));
});

afterEach(function () {
    Carbon::setTestNow();
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
        'record_after_motion_seconds' => 5,
        'pre_motion_buffer_seconds' => 2,
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
        ->assertJsonPath('data.0.record_after_motion_seconds', 5)
        ->assertJsonPath('data.0.pre_motion_buffer_seconds', 2)
        ->assertJsonPath('data.0.timezone', 'America/Sao_Paulo')
        ->assertJsonMissing(['name' => 'Inactive Camera'])
        ->assertJsonMissing(['name' => 'Detection Disabled Camera']);
});

test('processor includes cameras without a monitoring window', function () {
    $user = User::factory()->create();

    Camera::create([
        'user_id' => $user->id,
        'name' => 'Always On Camera',
        'stream_url' => 'http://camera.local/always-on',
        'is_active' => true,
        'motion_detection_enabled' => true,
        'monitoring_starts_at' => null,
        'monitoring_ends_at' => null,
    ]);

    $this->withToken('test-processor-token')
        ->getJson('/api/processor/cameras')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Always On Camera');
});

test('processor filters cameras by same-day monitoring windows', function () {
    $user = User::factory()->create();

    Camera::create([
        'user_id' => $user->id,
        'name' => 'Business Hours Camera',
        'stream_url' => 'http://camera.local/business-hours',
        'is_active' => true,
        'motion_detection_enabled' => true,
        'monitoring_starts_at' => '08:00',
        'monitoring_ends_at' => '18:00',
    ]);

    Camera::create([
        'user_id' => $user->id,
        'name' => 'Afternoon Camera',
        'stream_url' => 'http://camera.local/afternoon',
        'is_active' => true,
        'motion_detection_enabled' => true,
        'monitoring_starts_at' => '13:00',
        'monitoring_ends_at' => '18:00',
    ]);

    $this->withToken('test-processor-token')
        ->getJson('/api/processor/cameras')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Business Hours Camera')
        ->assertJsonMissing(['name' => 'Afternoon Camera']);
});

test('processor filters cameras by overnight monitoring windows', function () {
    $user = User::factory()->create();

    Carbon::setTestNow(Carbon::parse('2026-06-23 23:00:00', 'America/Sao_Paulo'));

    Camera::create([
        'user_id' => $user->id,
        'name' => 'Night Camera',
        'stream_url' => 'http://camera.local/night',
        'is_active' => true,
        'motion_detection_enabled' => true,
        'monitoring_starts_at' => '22:00',
        'monitoring_ends_at' => '06:00',
    ]);

    Camera::create([
        'user_id' => $user->id,
        'name' => 'Early Morning Camera',
        'stream_url' => 'http://camera.local/early-morning',
        'is_active' => true,
        'motion_detection_enabled' => true,
        'monitoring_starts_at' => '02:00',
        'monitoring_ends_at' => '06:00',
    ]);

    $this->withToken('test-processor-token')
        ->getJson('/api/processor/cameras')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Night Camera')
        ->assertJsonMissing(['name' => 'Early Morning Camera']);
});

test('processor uses database timezone setting when filtering monitoring windows', function () {
    $user = User::factory()->create();

    AppSetting::create([
        'timezone' => 'Asia/Tokyo',
    ]);

    Carbon::setTestNow(Carbon::parse('2026-06-23 00:30:00', 'UTC'));

    Camera::create([
        'user_id' => $user->id,
        'name' => 'Tokyo Morning Camera',
        'stream_url' => 'http://camera.local/tokyo-morning',
        'is_active' => true,
        'motion_detection_enabled' => true,
        'monitoring_starts_at' => '09:00',
        'monitoring_ends_at' => '10:00',
    ]);

    Camera::create([
        'user_id' => $user->id,
        'name' => 'Sao Paulo Morning Camera',
        'stream_url' => 'http://camera.local/sao-paulo-morning',
        'is_active' => true,
        'motion_detection_enabled' => true,
        'monitoring_starts_at' => '21:00',
        'monitoring_ends_at' => '22:00',
    ]);

    $this->withToken('test-processor-token')
        ->getJson('/api/processor/cameras')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Tokyo Morning Camera')
        ->assertJsonMissing(['name' => 'Sao Paulo Morning Camera']);
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
