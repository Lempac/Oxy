<?php

use App\Models\Server;
use App\Models\ServerInvite;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('new users can register with valid server code', function () {
    $server = Server::factory()->create();
    $invite = ServerInvite::create([
        'server_id' => $server->id,
        'code' => 'TESTCODE',
    ]);

    $response = $this->post('/register', [
        'server_code' => 'TESTCODE',
        'nickname' => 'testuser',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home', absolute: false));

    $user = User::where('nickname', 'testuser')->first();
    $this->assertNotNull($user);
    $this->assertTrue($server->users->contains('id', $user->id));
});

test('registration fails with invalid server code', function () {
    $response = $this->post('/register', [
        'server_code' => 'INVALIDCODE',
        'nickname' => 'testuser',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('server_code');
});

test('new users can register with valid profile icon', function () {
    Storage::fake('public');
    $server = Server::factory()->create();
    ServerInvite::create([
        'server_id' => $server->id,
        'code' => 'TESTCODE',
    ]);

    $file = UploadedFile::fake()->image('avatar.png', 500, 500);

    $response = $this->post('/register', [
        'server_code' => 'TESTCODE',
        'nickname' => 'iconuser',
        'password' => 'password',
        'password_confirmation' => 'password',
        'icon' => $file,
    ]);

    $this->assertAuthenticated();
    $user = User::where('nickname', 'iconuser')->first();
    $this->assertNotNull($user->icon);
});

test('registration rejects icon exceeding dimension limit', function () {
    Storage::fake('public');
    $server = Server::factory()->create();
    ServerInvite::create([
        'server_id' => $server->id,
        'code' => 'TESTCODE',
    ]);

    $file = UploadedFile::fake()->image('large.png', 2000, 1500);

    $response = $this->post('/register', [
        'server_code' => 'TESTCODE',
        'nickname' => 'largeiconuser',
        'password' => 'password',
        'password_confirmation' => 'password',
        'icon' => $file,
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('icon');
});
