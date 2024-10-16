<?php

use App\Enums\ChannelType;
use App\Models\Channel;
use App\Models\Server;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('home page is displayed', function () {
    $user = User::factory()->create();
    $response = $this
        ->actingAs($user)
        ->get(route('home'));

    $response->assertOk();
});

test('home select server', function () {
    $user = User::factory()->has(Server::factory()->hasChannels(random_int(1, 2)))->create();
    $this->actingAs($user);

    $response = $this
        ->get(route('home.server', ['server' => $user->servers->first()->id]));

    $response->assertOk();
});

test('home returns all channels', function () {
    $user = User::factory()->has(Server::factory()->has(Channel::factory()->count(random_int(1, 3))->state(['type' => ChannelType::Text])))->create();
    $this->actingAs($user);

    $response = $this
        ->get(route('home.text', ['server' => $user->servers->first()->id]));
    $response->assertInertia(fn(Assert $page) => $page
            ->has('channels', $user->servers->first()->channels->count())
        );
});

test('home select channel', function () {
    $user = User::factory()->has(Server::factory()->has(Channel::factory()->count(random_int(1, 3))->state(['type' => ChannelType::Text])))->create();
    $server = $user->servers->first();
    $selectedChannel = $server->channels->random();
    $this->actingAs($user);

    $response = $this
        ->get(route('home.channel', ['server' => $server->id, 'channel' => $selectedChannel->id]));
    $response->assertInertia(fn(Assert $page) => $page
            ->has('channels', $user->servers->first()->channels->count())
            ->has('selected_channel')->where('selected_channel.id', $selectedChannel->id)
        );
});
