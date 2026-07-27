<?php

use App\Enums\ApplicationState;
use App\Enums\MessageStatus;
use App\Enums\ServerMemberStatus;
use App\Enums\UserStatus;
use App\Enums\VoiceCallStatus;
use App\Enums\VoiceParticipantState;
use App\Enums\WhiteboardSyncState;
use App\Models\Call;
use App\Models\Message;
use App\Models\User;
use App\Models\Whiteboard;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

uses(TestCase::class);

test('UserStatus state machine transitions correctly', function () {
    Event::fake();

    $user = new User([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'status' => UserStatus::Offline,
    ]);

    expect($user->status)->toBe(UserStatus::Offline)
        ->and($user->transitionStatusTo(UserStatus::Online))->toBeTrue()
        ->and($user->status)->toBe(UserStatus::Online)
        ->and($user->transitionStatusTo(UserStatus::Idle))->toBeTrue()
        ->and($user->status)->toBe(UserStatus::Idle);

    // Valid transition: Offline -> Online

    // Valid transition: Online -> Idle
});

test('UserStatus blocks invalid transitions', function () {
    $user = new User([
        'status' => UserStatus::Offline,
    ]);

    // Invalid transition: Offline -> Idle directly
    expect(fn () => $user->transitionStatusTo(UserStatus::Idle))
        ->toThrow(InvalidArgumentException::class);
});

test('VoiceCallStatus transitions correctly', function () {
    $call = new Call(['status' => VoiceCallStatus::Idle]);

    expect($call->transitionStatusTo(VoiceCallStatus::Connecting))->toBeTrue()
        ->and($call->status)->toBe(VoiceCallStatus::Connecting)
        ->and($call->transitionStatusTo(VoiceCallStatus::Active))->toBeTrue()
        ->and($call->status)->toBe(VoiceCallStatus::Active)
        ->and($call->transitionStatusTo(VoiceCallStatus::Ended))->toBeTrue()
        ->and($call->status)->toBe(VoiceCallStatus::Ended);

});

test('MessageStatus transitions correctly', function () {
    $message = new Message(['status' => MessageStatus::Sent]);

    expect($message->transitionStatusTo(MessageStatus::Delivered))->toBeTrue()
        ->and($message->status)->toBe(MessageStatus::Delivered)
        ->and($message->transitionStatusTo(MessageStatus::Edited))->toBeTrue()
        ->and($message->status)->toBe(MessageStatus::Edited);

});

test('WhiteboardSyncState transitions correctly', function () {
    $whiteboard = new Whiteboard(['sync_status' => WhiteboardSyncState::Synced]);

    expect($whiteboard->transitionSyncStatusTo(WhiteboardSyncState::Dirty))->toBeTrue()
        ->and($whiteboard->sync_status)->toBe(WhiteboardSyncState::Dirty)
        ->and($whiteboard->transitionSyncStatusTo(WhiteboardSyncState::Saving))->toBeTrue()
        ->and($whiteboard->sync_status)->toBe(WhiteboardSyncState::Saving)
        ->and($whiteboard->transitionSyncStatusTo(WhiteboardSyncState::Synced))->toBeTrue()
        ->and($whiteboard->sync_status)->toBe(WhiteboardSyncState::Synced);

    // Test failure and retry lifecycle: Synced -> Dirty -> Saving -> SaveFailed -> Saving -> Synced
    expect($whiteboard->transitionSyncStatusTo(WhiteboardSyncState::Dirty))->toBeTrue()
        ->and($whiteboard->transitionSyncStatusTo(WhiteboardSyncState::Saving))->toBeTrue()
        ->and($whiteboard->transitionSyncStatusTo(WhiteboardSyncState::SaveFailed))->toBeTrue()
        ->and($whiteboard->sync_status)->toBe(WhiteboardSyncState::SaveFailed)
        ->and($whiteboard->transitionSyncStatusTo(WhiteboardSyncState::Saving))->toBeTrue()
        ->and($whiteboard->transitionSyncStatusTo(WhiteboardSyncState::Synced))->toBeTrue();

    // Test invalid direct jump: Synced -> Saving
    expect(fn () => $whiteboard->transitionSyncStatusTo(WhiteboardSyncState::Saving))
        ->toThrow(InvalidArgumentException::class);
});

test('Other state machine enums validate transitions', function () {
    expect(VoiceParticipantState::Disconnected->canTransitionTo(VoiceParticipantState::Joining))->toBeTrue()
        ->and(VoiceParticipantState::Disconnected->canTransitionTo(VoiceParticipantState::Muted))->toBeFalse()
        ->and(ApplicationState::Initializing->canTransitionTo(ApplicationState::Ready))->toBeTrue()
        ->and(ApplicationState::Unauthenticated->canTransitionTo(ApplicationState::Ready))->toBeFalse()
        ->and(ServerMemberStatus::Active->canTransitionTo(ServerMemberStatus::Muted))->toBeTrue();

});
