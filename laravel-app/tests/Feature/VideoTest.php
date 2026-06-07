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
