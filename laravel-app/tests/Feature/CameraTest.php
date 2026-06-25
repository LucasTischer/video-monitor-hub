<?php

use App\Models\Camera;
use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Carbon;

test('guests cannot access cameras', function () {
    $this->get('/cameras')->assertRedirect('/login');
});

test('authenticated users can view their cameras', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Camera::create([
        'user_id' => $user->id,
        'name' => 'Front Gate',
        'stream_url' => 'http://camera.local/front',
        'location' => 'Entrance',
        'is_active' => true,
    ]);

    Camera::create([
        'user_id' => $otherUser->id,
        'name' => 'Other Camera',
        'stream_url' => 'http://camera.local/other',
        'location' => 'Office',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/cameras')
        ->assertOk()
        ->assertSee('Front Gate')
        ->assertDontSee('Other Camera');
});

test('authenticated users can view cameras shared with them', function () {
    $user = User::factory()->create();
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $sharedCamera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Shared Camera',
        'stream_url' => 'http://camera.local/shared',
        'is_active' => true,
    ]);

    Camera::create([
        'user_id' => $otherUser->id,
        'name' => 'Unshared Camera',
        'stream_url' => 'http://camera.local/unshared',
        'is_active' => true,
    ]);

    $sharedCamera->sharedUsers()->attach($user->id, [
        'role' => 'viewer',
    ]);

    $this->actingAs($user)
        ->get('/cameras')
        ->assertOk()
        ->assertSee('Shared Camera')
        ->assertDontSee('Unshared Camera');
});

test('authenticated users can create cameras', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/cameras', [
            'name' => 'Back Yard',
            'stream_url' => 'http://camera.local/back-yard',
            'location' => 'Garden',
            'is_active' => '1',
            'motion_detection_enabled' => '1',
            'record_after_motion_seconds' => '5',
            'pre_motion_buffer_seconds' => '2',
            'recording_resolution_height' => '720',
            'recording_fps' => '15',
            'monitoring_starts_at' => '08:00',
            'monitoring_ends_at' => '18:00',
            'recording_retention_days' => '30',
        ])
        ->assertRedirect('/cameras');

    $camera = Camera::where('user_id', $user->id)
        ->where('name', 'Back Yard')
        ->first();

    expect(substr($camera->monitoring_starts_at, 0, 5))->toBe('08:00')
        ->and(substr($camera->monitoring_ends_at, 0, 5))->toBe('18:00');

    $this->assertDatabaseHas('cameras', [
        'user_id' => $user->id,
        'name' => 'Back Yard',
        'stream_url' => 'http://camera.local/back-yard',
        'location' => 'Garden',
        'is_active' => true,
        'motion_detection_enabled' => true,
        'record_after_motion_seconds' => 5,
        'pre_motion_buffer_seconds' => 2,
        'recording_resolution_height' => 720,
        'recording_fps' => 15,
        'recording_retention_days' => 30,
    ]);
});

test('camera creation requires valid input', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/cameras', [
            'name' => '',
            'stream_url' => 'not-a-url',
        ])
        ->assertSessionHasErrors(['name', 'stream_url']);
});

test('camera creation validates recording retention', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/cameras', [
            'name' => 'Back Yard',
            'stream_url' => 'http://camera.local/back-yard',
            'recording_retention_days' => '0',
        ])
        ->assertSessionHasErrors(['recording_retention_days']);
});

test('authenticated users can update their cameras', function () {
    $user = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Old Name',
        'stream_url' => 'http://camera.local/old',
        'location' => 'Old Location',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch("/cameras/{$camera->id}", [
            'name' => 'Updated Camera',
            'stream_url' => 'http://camera.local/updated',
            'location' => 'Updated Location',
            'is_active' => '0',
            'motion_detection_enabled' => '0',
            'record_after_motion_seconds' => '10',
            'pre_motion_buffer_seconds' => '5',
            'recording_resolution_height' => '',
            'recording_fps' => '30',
            'monitoring_starts_at' => '22:00',
            'monitoring_ends_at' => '06:00',
            'recording_retention_days' => '7',
        ])
        ->assertRedirect('/cameras');

    $camera->refresh();

    expect(substr($camera->monitoring_starts_at, 0, 5))->toBe('22:00')
        ->and(substr($camera->monitoring_ends_at, 0, 5))->toBe('06:00');

    $this->assertDatabaseHas('cameras', [
        'id' => $camera->id,
        'name' => 'Updated Camera',
        'stream_url' => 'http://camera.local/updated',
        'location' => 'Updated Location',
        'is_active' => false,
        'motion_detection_enabled' => false,
        'record_after_motion_seconds' => 10,
        'pre_motion_buffer_seconds' => 5,
        'recording_resolution_height' => null,
        'recording_fps' => 30,
        'recording_retention_days' => 7,
    ]);
});

test('camera creation requires a complete monitoring window', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/cameras', [
            'name' => 'Back Yard',
            'stream_url' => 'http://camera.local/back-yard',
            'monitoring_starts_at' => '08:00',
            'monitoring_ends_at' => '',
        ])
        ->assertSessionHasErrors(['monitoring_ends_at']);
});

test('camera creation rejects equal monitoring window times', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/cameras', [
            'name' => 'Back Yard',
            'stream_url' => 'http://camera.local/back-yard',
            'monitoring_starts_at' => '08:00',
            'monitoring_ends_at' => '08:00',
        ])
        ->assertSessionHasErrors(['monitoring_ends_at']);
});

test('camera creation validates motion recording settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/cameras', [
            'name' => 'Back Yard',
            'stream_url' => 'http://camera.local/back-yard',
            'record_after_motion_seconds' => '0',
            'pre_motion_buffer_seconds' => '31',
        ])
        ->assertSessionHasErrors(['record_after_motion_seconds', 'pre_motion_buffer_seconds']);
});

test('camera creation validates recording quality settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/cameras', [
            'name' => 'Back Yard',
            'stream_url' => 'http://camera.local/back-yard',
            'recording_resolution_height' => '999',
            'recording_fps' => '12',
        ])
        ->assertSessionHasErrors(['recording_resolution_height', 'recording_fps']);
});

test('authenticated users can update camera motion detection setting', function () {
    $user = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Old Name',
        'stream_url' => 'http://camera.local/old',
        'is_active' => true,
        'motion_detection_enabled' => true,
    ]);

    $this->actingAs($user)
        ->patch("/cameras/{$camera->id}", [
            'name' => 'Updated Camera',
            'stream_url' => 'http://camera.local/updated',
            'is_active' => '1',
            'motion_detection_enabled' => '0',
        ])
        ->assertRedirect('/cameras');

    $this->assertDatabaseHas('cameras', [
        'id' => $camera->id,
        'motion_detection_enabled' => false,
    ]);
});

test('authenticated users can clear camera recording retention', function () {
    $user = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Old Name',
        'stream_url' => 'http://camera.local/old',
        'is_active' => true,
        'recording_retention_days' => 30,
    ]);

    $this->actingAs($user)
        ->patch("/cameras/{$camera->id}", [
            'name' => 'Updated Camera',
            'stream_url' => 'http://camera.local/updated',
            'is_active' => '1',
            'recording_retention_days' => '',
        ])
        ->assertRedirect('/cameras');

    $this->assertDatabaseHas('cameras', [
        'id' => $camera->id,
        'recording_retention_days' => null,
    ]);
});

test('users cannot update another users camera', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $otherUser->id,
        'name' => 'Private Camera',
        'stream_url' => 'http://camera.local/private',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch("/cameras/{$camera->id}", [
            'name' => 'Changed',
            'stream_url' => 'http://camera.local/changed',
            'is_active' => '1',
        ])
        ->assertNotFound();

    $this->assertDatabaseHas('cameras', [
        'id' => $camera->id,
        'name' => 'Private Camera',
    ]);
});

test('shared viewers can view but not update cameras', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Shared Entry',
        'stream_url' => 'http://camera.local/shared-entry',
        'is_active' => true,
    ]);

    $camera->sharedUsers()->attach($viewer->id, [
        'role' => 'viewer',
    ]);

    $this->actingAs($viewer)
        ->get("/cameras/{$camera->id}")
        ->assertOk()
        ->assertSee('Shared Entry');

    $this->actingAs($viewer)
        ->patch("/cameras/{$camera->id}", [
            'name' => 'Changed',
            'stream_url' => 'http://camera.local/changed',
            'is_active' => '1',
        ])
        ->assertNotFound();
});

test('camera detail paginates video recordings ten per page', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Garage',
        'stream_url' => 'http://camera.local/garage',
        'is_active' => true,
    ]);
    $otherCamera = Camera::create([
        'user_id' => $otherUser->id,
        'name' => 'Private Office',
        'stream_url' => 'http://camera.local/private',
        'is_active' => true,
    ]);
    $baseTime = Carbon::parse('2026-06-24 12:00:00');

    for ($index = 1; $index <= 11; $index++) {
        Carbon::setTestNow($baseTime->copy()->addMinutes($index));

        Video::create([
            'camera_id' => $camera->id,
            'filename' => sprintf('garage-motion-%02d.webm', $index),
            'path' => sprintf('/storage/videos/garage-motion-%02d.webm', $index),
            'started_at' => now(),
        ]);
    }

    Carbon::setTestNow($baseTime->copy()->addMinutes(12));

    Video::create([
        'camera_id' => $otherCamera->id,
        'filename' => 'private-office-motion.webm',
        'path' => '/storage/videos/private-office-motion.webm',
        'started_at' => now(),
    ]);

    Carbon::setTestNow();

    $this->actingAs($user)
        ->get("/cameras/{$camera->id}")
        ->assertOk()
        ->assertSee('garage-motion-11.webm')
        ->assertSee('garage-motion-02.webm')
        ->assertDontSee('garage-motion-01.webm')
        ->assertDontSee('private-office-motion.webm');

    $this->actingAs($user)
        ->get("/cameras/{$camera->id}?page=2")
        ->assertOk()
        ->assertSee('garage-motion-01.webm')
        ->assertDontSee('garage-motion-11.webm')
        ->assertDontSee('private-office-motion.webm');
});

test('camera detail video actions show delete only when allowed', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Shared Entry',
        'stream_url' => 'http://camera.local/shared-entry',
        'is_active' => true,
    ]);
    Video::create([
        'camera_id' => $camera->id,
        'filename' => 'shared-entry-motion.webm',
        'path' => '/storage/videos/shared-entry-motion.webm',
    ]);

    $camera->sharedUsers()->attach($viewer->id, [
        'role' => 'viewer',
    ]);

    $this->actingAs($owner)
        ->get("/cameras/{$camera->id}")
        ->assertOk()
        ->assertSee('View recording')
        ->assertSee('Delete recording');

    $this->actingAs($viewer)
        ->get("/cameras/{$camera->id}")
        ->assertOk()
        ->assertSee('View recording')
        ->assertDontSee('Delete recording');
});

test('deleting a video from camera detail redirects back to the same camera page', function () {
    $user = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Garage',
        'stream_url' => 'http://camera.local/garage',
        'is_active' => true,
    ]);
    $video = Video::create([
        'camera_id' => $camera->id,
        'filename' => 'garage-motion.webm',
        'path' => '/storage/videos/garage-motion.webm',
    ]);

    $this->actingAs($user)
        ->delete("/videos/{$video->id}?page=2", [
            'redirect_to_camera' => '1',
        ])
        ->assertRedirect("/cameras/{$camera->id}?page=2");

    $this->assertDatabaseMissing('videos', [
        'id' => $video->id,
    ]);
});

test('shared editors can update but not delete cameras', function () {
    $owner = User::factory()->create();
    $editor = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Editable Shared Camera',
        'stream_url' => 'http://camera.local/editable-shared',
        'is_active' => true,
    ]);

    $camera->sharedUsers()->attach($editor->id, [
        'role' => 'editor',
    ]);

    $this->actingAs($editor)
        ->patch("/cameras/{$camera->id}", [
            'name' => 'Updated By Editor',
            'stream_url' => 'http://camera.local/updated-by-editor',
            'is_active' => '1',
        ])
        ->assertRedirect('/cameras');

    $this->actingAs($editor)
        ->delete("/cameras/{$camera->id}")
        ->assertNotFound();

    $this->assertDatabaseHas('cameras', [
        'id' => $camera->id,
        'name' => 'Updated By Editor',
    ]);
});

test('shared managers can delete cameras', function () {
    $owner = User::factory()->create();
    $manager = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Managed Shared Camera',
        'stream_url' => 'http://camera.local/managed-shared',
        'is_active' => true,
    ]);

    $camera->sharedUsers()->attach($manager->id, [
        'role' => 'manager',
    ]);

    $this->actingAs($manager)
        ->delete("/cameras/{$camera->id}")
        ->assertRedirect('/cameras');

    $this->assertDatabaseMissing('cameras', [
        'id' => $camera->id,
    ]);
});

test('authenticated users can delete their cameras', function () {
    $user = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Garage',
        'stream_url' => 'http://camera.local/garage',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->delete("/cameras/{$camera->id}")
        ->assertRedirect('/cameras');

    $this->assertDatabaseMissing('cameras', [
        'id' => $camera->id,
    ]);
});
