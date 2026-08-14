<?php

use App\Models\User;

test('users can authenticate using nickname and password', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'nickname' => $user->nickname,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'nickname' => $user->nickname,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('users can authenticate using magic login signed route', function () {
    $user = User::factory()->create();

    $signedUrl = URL::temporarySignedRoute(
        'magic-login',
        now()->addMinutes(30),
        ['user' => $user->id]
    );

    $response = $this->get($signedUrl);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('home', absolute: false));
});

test('magic login fails with invalid or expired signature', function () {
    $user = User::factory()->create();

    $invalidUrl = route('magic-login', ['user' => $user->id]).'?signature=invalid';

    $response = $this->get($invalidUrl);

    $response->assertStatus(401);
    $this->assertGuest();
});

test('artisan auth:magic-link outputs valid signed url', function () {
    $this->artisan('auth:magic-link testuser')
        ->assertSuccessful();

    $user = User::where('nickname', 'testuser')->first();
    expect($user)->not->toBeNull();
});
