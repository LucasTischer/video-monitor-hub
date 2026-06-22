<?php

use App\Models\Camera;
use App\Models\User;
use App\Models\Video;

test('guests are redirected away from the dashboard', function () {
    $this->get('/dashboard')
        ->assertRedirect('/login');
});

test('dashboard displays the authenticated users cameras and videos', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Front Gate',
        'stream_url' => 'http://camera.local/front',
        'location' => 'Entrance',
        'is_active' => true,
    ]);

    Camera::create([
        'user_id' => $otherUser->id,
        'name' => 'Private Office',
        'stream_url' => 'http://camera.local/private',
        'location' => 'Office',
        'is_active' => true,
    ]);

    Video::create([
        'camera_id' => $camera->id,
        'filename' => 'front-gate-motion.mp4',
        'path' => 'videos/front-gate-motion.mp4',
        'started_at' => now(),
        'duration_seconds' => 12,
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Front Gate')
        ->assertSee('Entrance')
        ->assertSee('front-gate-motion.mp4')
        ->assertDontSee('Private Office');
});

test('dashboard displays cameras and videos shared with the authenticated user', function () {
    $user = User::factory()->create();
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $sharedCamera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Shared Porch',
        'stream_url' => 'http://camera.local/shared-porch',
        'location' => 'Porch',
        'is_active' => true,
    ]);

    $unsharedCamera = Camera::create([
        'user_id' => $otherUser->id,
        'name' => 'Private Storage',
        'stream_url' => 'http://camera.local/private-storage',
        'location' => 'Storage',
        'is_active' => true,
    ]);

    Video::create([
        'camera_id' => $sharedCamera->id,
        'filename' => 'shared-porch-motion.mp4',
        'path' => 'videos/shared-porch-motion.mp4',
        'started_at' => now(),
        'duration_seconds' => 9,
    ]);

    Video::create([
        'camera_id' => $unsharedCamera->id,
        'filename' => 'private-storage-motion.mp4',
        'path' => 'videos/private-storage-motion.mp4',
        'started_at' => now(),
        'duration_seconds' => 7,
    ]);

    $sharedCamera->sharedUsers()->attach($user->id, [
        'role' => 'viewer',
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Shared Porch')
        ->assertSee('Porch')
        ->assertSee('shared-porch-motion.mp4')
        ->assertDontSee('Private Storage')
        ->assertDontSee('private-storage-motion.mp4');
});
