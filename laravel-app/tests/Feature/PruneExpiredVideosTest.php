<?php

use App\Models\Camera;
use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;

test('prune expired videos deletes old database rows and files', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Front Door',
        'stream_url' => 'http://camera.local/front-door',
        'is_active' => true,
        'recording_retention_days' => 7,
    ]);

    Storage::disk('public')->put('videos/expired.webm', 'expired video');

    $expiredVideo = Video::create([
        'camera_id' => $camera->id,
        'filename' => 'expired.webm',
        'path' => '/storage/videos/expired.webm',
        'started_at' => now()->subDays(8),
    ]);

    $this->artisan('videos:prune-expired')
        ->expectsOutput('Deleted 1 expired video recording(s).')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('videos', [
        'id' => $expiredVideo->id,
    ]);

    Storage::disk('public')->assertMissing('videos/expired.webm');
});

test('prune expired videos keeps recordings inside retention window', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Front Door',
        'stream_url' => 'http://camera.local/front-door',
        'is_active' => true,
        'recording_retention_days' => 7,
    ]);

    Storage::disk('public')->put('videos/fresh.webm', 'fresh video');

    $freshVideo = Video::create([
        'camera_id' => $camera->id,
        'filename' => 'fresh.webm',
        'path' => '/storage/videos/fresh.webm',
        'started_at' => now()->subDays(6),
    ]);

    $this->artisan('videos:prune-expired')
        ->expectsOutput('Deleted 0 expired video recording(s).')
        ->assertExitCode(0);

    $this->assertDatabaseHas('videos', [
        'id' => $freshVideo->id,
    ]);

    Storage::disk('public')->assertExists('videos/fresh.webm');
});

test('prune expired videos ignores cameras without retention settings', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $user->id,
        'name' => 'Front Door',
        'stream_url' => 'http://camera.local/front-door',
        'is_active' => true,
        'recording_retention_days' => null,
    ]);

    Storage::disk('public')->put('videos/kept.webm', 'kept video');

    $keptVideo = Video::create([
        'camera_id' => $camera->id,
        'filename' => 'kept.webm',
        'path' => '/storage/videos/kept.webm',
        'started_at' => now()->subDays(365),
    ]);

    $this->artisan('videos:prune-expired')
        ->expectsOutput('Deleted 0 expired video recording(s).')
        ->assertExitCode(0);

    $this->assertDatabaseHas('videos', [
        'id' => $keptVideo->id,
    ]);

    Storage::disk('public')->assertExists('videos/kept.webm');
});
