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
