<?php

use App\Models\AppSetting;
use App\Models\User;

test('admin users can view the settings page', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/settings')
        ->assertOk()
        ->assertSee('Settings')
        ->assertSee('America/Sao_Paulo');
});

test('admin users can update the global timezone setting', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $this->actingAs($admin)
        ->patch('/settings', [
            'timezone' => 'America/New_York',
        ])
        ->assertRedirect('/settings');

    $this->assertDatabaseHas('app_settings', [
        'id' => AppSetting::SINGLETON_ID,
        'timezone' => 'America/New_York',
    ]);
});

test('non admin users cannot manage settings', function () {
    $user = User::factory()->create([
        'is_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/settings')
        ->assertForbidden();

    $this->actingAs($user)
        ->patch('/settings', [
            'timezone' => 'America/New_York',
        ])
        ->assertForbidden();
});

test('settings update validates timezone identifiers', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $this->actingAs($admin)
        ->patch('/settings', [
            'timezone' => 'Not/A_Timezone',
        ])
        ->assertSessionHasErrors(['timezone']);
});
