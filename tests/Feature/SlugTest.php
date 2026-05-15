<?php

use App\Models\Channel;
use App\Models\Server;

test('servers are created with a slug', function () {
    $server = Server::factory()->create([
        'name' => 'Test Server Name',
    ]);

    expect($server->slug)->toBe('test-server-name');
});

test('server slugs are unique', function () {
    $server1 = Server::factory()->create([
        'name' => 'Same Name',
    ]);

    $server2 = Server::factory()->create([
        'name' => 'Same Name',
    ]);

    expect($server1->slug)->toBe('same-name');
    expect($server2->slug)->toBe('same-name-1');
});

test('channels are created with a slug unique per server', function () {
    $server = Server::factory()->create([
        'name' => 'Server 1',
    ]);

    $channel1 = Channel::factory()->create([
        'name' => 'General',
        'server_id' => $server->id,
    ]);

    $channel2 = Channel::factory()->create([
        'name' => 'General',
        'server_id' => $server->id,
    ]);

    expect($channel1->slug)->toBe('general');
    expect($channel2->slug)->toBe('general-1');
});
