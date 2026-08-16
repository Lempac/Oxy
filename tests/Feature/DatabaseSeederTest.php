<?php

use App\Enums\ChannelType;
use App\Models\Channel;
use App\Models\Server;
use App\Models\ServerInvite;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

test('database seeder seeds test users, servers, channels, and roles idempotently', function () {
    // Run seeder first time
    $this->seed(DatabaseSeeder::class);

    // Verify test users exist
    $testUser = User::where('nickname', 'testuser')->first();
    $moderator = User::where('nickname', 'moderator')->first();
    $member = User::where('nickname', 'member')->first();

    expect($testUser)->not->toBeNull()
        ->and($moderator)->not->toBeNull()
        ->and($member)->not->toBeNull();

    expect(Hash::check('password', $testUser->password))->toBeTrue()
        ->and(Hash::check('password', $moderator->password))->toBeTrue()
        ->and(Hash::check('password', $member->password))->toBeTrue();

    // Verify Oxy HQ server
    $hqServer = Server::where('slug', 'oxy-hq')->first();
    expect($hqServer)->not->toBeNull()
        ->and($hqServer->name)->toBe('Oxy HQ')
        ->and($hqServer->defaultRole)->not->toBeNull()
        ->and($hqServer->defaultRole->name)->toBe('Member');

    setPermissionsTeamId($hqServer->id);
    expect($testUser->fresh()->hasRole('Owner'))->toBeTrue()
        ->and($moderator->fresh()->hasRole('Moderator'))->toBeTrue()
        ->and($member->fresh()->hasRole('Member'))->toBeTrue();

    // Verify Oxy HQ channels
    $hqChannels = $hqServer->channels;
    expect($hqChannels->pluck('slug')->toArray())->toContain(
        'announcements',
        'general',
        'random',
        'community-lounge',
        'project-canvas'
    );

    $canvasChannel = Channel::where('server_id', $hqServer->id)->where('slug', 'project-canvas')->first();
    expect($canvasChannel->type)->toBe(ChannelType::Whiteboard)
        ->and($canvasChannel->whiteboard)->not->toBeNull();

    $loungeChannel = Channel::where('server_id', $hqServer->id)->where('slug', 'community-lounge')->first();
    expect($loungeChannel->type)->toBe(ChannelType::Voice)
        ->and($loungeChannel->calls()->count())->toBeGreaterThan(0);

    // Verify Community Hangout server
    $communityServer = Server::where('slug', 'community-hangout')->first();
    expect($communityServer)->not->toBeNull()
        ->and($communityServer->defaultRole)->not->toBeNull()
        ->and($communityServer->defaultRole->name)->toBe('Member');

    setPermissionsTeamId($communityServer->id);
    expect($moderator->fresh()->hasRole('Owner'))->toBeTrue()
        ->and($member->fresh()->hasRole('Moderator'))->toBeTrue()
        ->and($testUser->fresh()->hasRole('Member'))->toBeTrue();

    // Verify invites
    $hqInvite = ServerInvite::where('code', 'OXY-PREVIEW')->first();
    expect($hqInvite)->not->toBeNull()
        ->and($hqInvite->server_id)->toBe($hqServer->id)
        ->and($hqInvite->isValid())->toBeTrue();

    $communityInvite = ServerInvite::where('code', 'OXY-COMMUNITY')->first();
    expect($communityInvite)->not->toBeNull()
        ->and($communityInvite->server_id)->toBe($communityServer->id)
        ->and($communityInvite->isValid())->toBeTrue();

    // Run seeder second time to ensure idempotency
    $this->seed(DatabaseSeeder::class);

    expect(User::where('nickname', 'testuser')->count())->toBe(1)
        ->and(Server::where('slug', 'oxy-hq')->count())->toBe(1)
        ->and(Server::where('slug', 'community-hangout')->count())->toBe(1);
});
