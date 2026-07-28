<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/profile', [
            'nickname' => 'NewNickname',
            'light_theme' => 'oxy',
            'dark_theme' => 'dark',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('NewNickname', $user->nickname);
});

test('profile theme can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/profile', [
            'nickname' => 'TestUser',
            'light_theme' => 'cyberpunk',
            'dark_theme' => 'synthwave',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $refreshed = $user->refresh();
    $this->assertSame('cyberpunk', $refreshed->light_theme->value);
    $this->assertSame('synthwave', $refreshed->dark_theme->value);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
