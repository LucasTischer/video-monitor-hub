<?php

use App\Models\Camera;
use App\Models\User;

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

test('authenticated users can create cameras', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/cameras', [
            'name' => 'Back Yard',
            'stream_url' => 'http://camera.local/back-yard',
            'location' => 'Garden',
            'is_active' => '1',
        ])
        ->assertRedirect('/cameras');

    $this->assertDatabaseHas('cameras', [
        'user_id' => $user->id,
        'name' => 'Back Yard',
        'stream_url' => 'http://camera.local/back-yard',
        'location' => 'Garden',
        'is_active' => true,
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
        ])
        ->assertRedirect('/cameras');

    $this->assertDatabaseHas('cameras', [
        'id' => $camera->id,
        'name' => 'Updated Camera',
        'stream_url' => 'http://camera.local/updated',
        'location' => 'Updated Location',
        'is_active' => false,
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
