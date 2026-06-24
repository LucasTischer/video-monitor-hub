<?php

use App\Models\Camera;
use App\Models\User;
use App\Models\Video;

test('guests cannot access videos', function () {
    $this->get('/videos')->assertRedirect('/login');
});

test('authenticated users can view their videos', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Front Gate',
        'stream_url' => 'http://camera.local/front',
        'is_active' => true,
    ]);

    $otherCamera = Camera::create([
        'user_id' => $otherUser->id,
        'name' => 'Private Office',
        'stream_url' => 'http://camera.local/private',
        'is_active' => true,
    ]);

    Video::create([
        'camera_id' => $camera->id,
        'filename' => 'front-gate-motion.mp4',
        'path' => '/storage/videos/front-gate-motion.mp4',
        'started_at' => now(),
        'duration_seconds' => 20,
    ]);

    Video::create([
        'camera_id' => $otherCamera->id,
        'filename' => 'private-office-motion.mp4',
        'path' => '/storage/videos/private-office-motion.mp4',
    ]);

    $this->actingAs($user)
        ->get('/videos')
        ->assertOk()
        ->assertSee('Front Gate')
        ->assertSee('front-gate-motion.mp4')
        ->assertDontSee('Private Office')
        ->assertDontSee('private-office-motion.mp4');
});

test('authenticated users can view videos from cameras shared with them', function () {
    $user = User::factory()->create();
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $sharedCamera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Shared Hallway',
        'stream_url' => 'http://camera.local/shared-hallway',
        'is_active' => true,
    ]);

    $unsharedCamera = Camera::create([
        'user_id' => $otherUser->id,
        'name' => 'Unshared Hallway',
        'stream_url' => 'http://camera.local/unshared-hallway',
        'is_active' => true,
    ]);

    Video::create([
        'camera_id' => $sharedCamera->id,
        'filename' => 'shared-hallway-motion.mp4',
        'path' => '/storage/videos/shared-hallway-motion.mp4',
    ]);

    Video::create([
        'camera_id' => $unsharedCamera->id,
        'filename' => 'unshared-hallway-motion.mp4',
        'path' => '/storage/videos/unshared-hallway-motion.mp4',
    ]);

    $sharedCamera->sharedUsers()->attach($user->id, [
        'role' => 'viewer',
    ]);

    $this->actingAs($user)
        ->get('/videos')
        ->assertOk()
        ->assertSee('Shared Hallway')
        ->assertSee('shared-hallway-motion.mp4')
        ->assertDontSee('Unshared Hallway')
        ->assertDontSee('unshared-hallway-motion.mp4');
});

test('video list is paginated with the latest visible recordings first', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Front Gate',
        'stream_url' => 'http://camera.local/front',
        'is_active' => true,
    ]);

    $otherCamera = Camera::create([
        'user_id' => $otherUser->id,
        'name' => 'Private Office',
        'stream_url' => 'http://camera.local/private',
        'is_active' => true,
    ]);

    for ($index = 1; $index <= 16; $index++) {
        Video::create([
            'camera_id' => $camera->id,
            'filename' => sprintf('front-gate-motion-%02d.webm', $index),
            'path' => sprintf('/storage/videos/front-gate-motion-%02d.webm', $index),
            'started_at' => now()->subMinutes(16 - $index),
        ]);
    }

    Video::create([
        'camera_id' => $otherCamera->id,
        'filename' => 'private-office-motion.webm',
        'path' => '/storage/videos/private-office-motion.webm',
        'started_at' => now()->addMinute(),
    ]);

    $this->actingAs($user)
        ->get('/videos')
        ->assertOk()
        ->assertSee('front-gate-motion-16.webm')
        ->assertSee('front-gate-motion-02.webm')
        ->assertDontSee('front-gate-motion-01.webm')
        ->assertDontSee('private-office-motion.webm');
});

test('video list can show the second page of visible recordings', function () {
    $user = User::factory()->create();
    $owner = User::factory()->create();

    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Front Gate',
        'stream_url' => 'http://camera.local/front',
        'is_active' => true,
    ]);

    $sharedCamera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Shared Lobby',
        'stream_url' => 'http://camera.local/shared-lobby',
        'is_active' => true,
    ]);

    $sharedCamera->sharedUsers()->attach($user->id, [
        'role' => 'viewer',
    ]);

    for ($index = 1; $index <= 15; $index++) {
        Video::create([
            'camera_id' => $camera->id,
            'filename' => sprintf('front-gate-motion-%02d.webm', $index),
            'path' => sprintf('/storage/videos/front-gate-motion-%02d.webm', $index),
            'started_at' => now()->subMinutes(15 - $index),
        ]);
    }

    Video::create([
        'camera_id' => $sharedCamera->id,
        'filename' => 'shared-lobby-motion.webm',
        'path' => '/storage/videos/shared-lobby-motion.webm',
        'started_at' => now()->subHour(),
    ]);

    $this->actingAs($user)
        ->get('/videos?page=2')
        ->assertOk()
        ->assertSee('shared-lobby-motion.webm')
        ->assertDontSee('front-gate-motion-15.webm');
});

test('authenticated users can view a video detail page', function () {
    $user = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Garage',
        'stream_url' => 'http://camera.local/garage',
        'is_active' => true,
    ]);
    $video = Video::create([
        'camera_id' => $camera->id,
        'filename' => 'garage-motion.mp4',
        'path' => '/storage/videos/garage-motion.mp4',
        'started_at' => now(),
        'ended_at' => now()->addSeconds(12),
        'duration_seconds' => 12,
        'metadata' => ['trigger' => 'motion'],
    ]);

    $this->actingAs($user)
        ->get("/videos/{$video->id}")
        ->assertOk()
        ->assertSee('garage-motion.mp4')
        ->assertSee('Garage')
        ->assertSee('motion');
});

test('users cannot view another users video', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $otherUser->id,
        'name' => 'Private Office',
        'stream_url' => 'http://camera.local/private',
        'is_active' => true,
    ]);
    $video = Video::create([
        'camera_id' => $camera->id,
        'filename' => 'private-office-motion.mp4',
        'path' => '/storage/videos/private-office-motion.mp4',
    ]);

    $this->actingAs($user)
        ->get("/videos/{$video->id}")
        ->assertNotFound();
});

test('shared camera viewers can view but not delete videos', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Shared Lobby',
        'stream_url' => 'http://camera.local/shared-lobby',
        'is_active' => true,
    ]);
    $video = Video::create([
        'camera_id' => $camera->id,
        'filename' => 'shared-lobby-motion.mp4',
        'path' => '/storage/videos/shared-lobby-motion.mp4',
    ]);

    $camera->sharedUsers()->attach($viewer->id, [
        'role' => 'viewer',
    ]);

    $this->actingAs($viewer)
        ->get("/videos/{$video->id}")
        ->assertOk()
        ->assertSee('shared-lobby-motion.mp4');

    $this->actingAs($viewer)
        ->delete("/videos/{$video->id}")
        ->assertNotFound();

    $this->assertDatabaseHas('videos', [
        'id' => $video->id,
    ]);
});

test('shared camera managers can delete videos', function () {
    $owner = User::factory()->create();
    $manager = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Managed Lobby',
        'stream_url' => 'http://camera.local/managed-lobby',
        'is_active' => true,
    ]);
    $video = Video::create([
        'camera_id' => $camera->id,
        'filename' => 'managed-lobby-motion.mp4',
        'path' => '/storage/videos/managed-lobby-motion.mp4',
    ]);

    $camera->sharedUsers()->attach($manager->id, [
        'role' => 'manager',
    ]);

    $this->actingAs($manager)
        ->delete("/videos/{$video->id}")
        ->assertRedirect('/videos');

    $this->assertDatabaseMissing('videos', [
        'id' => $video->id,
    ]);
});

test('authenticated users can delete their videos', function () {
    $user = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Back Yard',
        'stream_url' => 'http://camera.local/back-yard',
        'is_active' => true,
    ]);
    $video = Video::create([
        'camera_id' => $camera->id,
        'filename' => 'back-yard-motion.mp4',
        'path' => '/storage/videos/back-yard-motion.mp4',
    ]);

    $this->actingAs($user)
        ->delete("/videos/{$video->id}")
        ->assertRedirect('/videos');

    $this->assertDatabaseMissing('videos', [
        'id' => $video->id,
    ]);
});

test('deleting a video from a paginated list redirects back to the same page', function () {
    $user = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Back Yard',
        'stream_url' => 'http://camera.local/back-yard',
        'is_active' => true,
    ]);
    $video = Video::create([
        'camera_id' => $camera->id,
        'filename' => 'back-yard-motion.webm',
        'path' => '/storage/videos/back-yard-motion.webm',
    ]);

    $this->actingAs($user)
        ->delete("/videos/{$video->id}?page=2")
        ->assertRedirect('/videos?page=2');

    $this->assertDatabaseMissing('videos', [
        'id' => $video->id,
    ]);
});

test('users cannot delete another users video', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $otherUser->id,
        'name' => 'Private Office',
        'stream_url' => 'http://camera.local/private',
        'is_active' => true,
    ]);
    $video = Video::create([
        'camera_id' => $camera->id,
        'filename' => 'private-office-motion.mp4',
        'path' => '/storage/videos/private-office-motion.mp4',
    ]);

    $this->actingAs($user)
        ->delete("/videos/{$video->id}")
        ->assertNotFound();

    $this->assertDatabaseHas('videos', [
        'id' => $video->id,
    ]);
});
