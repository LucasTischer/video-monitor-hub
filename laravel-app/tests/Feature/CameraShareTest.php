<?php

use App\Models\Camera;
use App\Models\User;

test('camera owners can share cameras with another user', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create(['email' => 'viewer@example.com']);
    $camera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Front Door',
        'stream_url' => 'http://camera.local/front-door',
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->post(route('cameras.shares.store', $camera), [
            'email' => 'viewer@example.com',
            'role' => 'viewer',
        ])
        ->assertRedirect(route('cameras.show', $camera));

    $this->assertDatabaseHas('camera_shares', [
        'camera_id' => $camera->id,
        'user_id' => $viewer->id,
        'role' => 'viewer',
    ]);
});

test('camera sharing validates duplicate and owner shares', function () {
    $owner = User::factory()->create(['email' => 'owner@example.com']);
    $viewer = User::factory()->create(['email' => 'viewer@example.com']);
    $camera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Front Door',
        'stream_url' => 'http://camera.local/front-door',
        'is_active' => true,
    ]);

    $camera->sharedUsers()->attach($viewer->id, ['role' => 'viewer']);

    $this->actingAs($owner)
        ->post(route('cameras.shares.store', $camera), [
            'email' => 'owner@example.com',
            'role' => 'viewer',
        ])
        ->assertSessionHasErrors('email');

    $this->actingAs($owner)
        ->post(route('cameras.shares.store', $camera), [
            'email' => 'viewer@example.com',
            'role' => 'editor',
        ])
        ->assertSessionHasErrors('email');
});

test('camera managers can update and remove shared access', function () {
    $owner = User::factory()->create();
    $manager = User::factory()->create();
    $viewer = User::factory()->create();
    $camera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Front Door',
        'stream_url' => 'http://camera.local/front-door',
        'is_active' => true,
    ]);

    $camera->sharedUsers()->attach($manager->id, ['role' => 'manager']);
    $camera->sharedUsers()->attach($viewer->id, ['role' => 'viewer']);

    $this->actingAs($manager)
        ->patch(route('cameras.shares.update', [$camera, $viewer]), [
            'role' => 'editor',
        ])
        ->assertRedirect(route('cameras.show', $camera));

    $this->assertDatabaseHas('camera_shares', [
        'camera_id' => $camera->id,
        'user_id' => $viewer->id,
        'role' => 'editor',
    ]);

    $this->actingAs($manager)
        ->delete(route('cameras.shares.destroy', [$camera, $viewer]))
        ->assertRedirect(route('cameras.show', $camera));

    $this->assertDatabaseMissing('camera_shares', [
        'camera_id' => $camera->id,
        'user_id' => $viewer->id,
    ]);
});

test('camera viewers cannot manage shared access', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create(['email' => 'viewer@example.com']);
    $target = User::factory()->create(['email' => 'target@example.com']);
    $camera = Camera::create([
        'user_id' => $owner->id,
        'name' => 'Front Door',
        'stream_url' => 'http://camera.local/front-door',
        'is_active' => true,
    ]);

    $camera->sharedUsers()->attach($viewer->id, ['role' => 'viewer']);

    $this->actingAs($viewer)
        ->post(route('cameras.shares.store', $camera), [
            'email' => 'target@example.com',
            'role' => 'viewer',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('camera_shares', [
        'camera_id' => $camera->id,
        'user_id' => $target->id,
    ]);
});
