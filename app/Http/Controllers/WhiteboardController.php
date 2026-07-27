<?php

namespace App\Http\Controllers;

use App\Enums\WhiteboardSyncState;
use App\Events\Whiteboards\WhiteboardStateUpdated;
use App\Models\Whiteboard;
use Illuminate\Http\Request;

class WhiteboardController extends Controller
{
    public function saveState(Request $request, Whiteboard $whiteboard)
    {
        $request->validate([
            'state' => 'required|string',
        ]);

        // Authorization check: User must be a member of the server the whiteboard belongs to
        $server = $whiteboard->channel->server;
        if (! $server->users()->where('user_id', $request->user()->id)->exists()) {
            abort(403);
        }

        $whiteboard->update([
            'state' => $request->input('state'),
        ]);

        // Transition sync status state machine to Synced safely
        $currentStatus = $whiteboard->sync_status ?? WhiteboardSyncState::Uninitialized;
        if ($currentStatus === WhiteboardSyncState::Dirty || $currentStatus === WhiteboardSyncState::SaveFailed) {
            $whiteboard->transitionSyncStatusTo(WhiteboardSyncState::Saving);
        }
        if ($whiteboard->sync_status === WhiteboardSyncState::Saving || $whiteboard->sync_status === WhiteboardSyncState::Uninitialized) {
            $whiteboard->transitionSyncStatusTo(WhiteboardSyncState::Synced);
        }

        broadcast(new WhiteboardStateUpdated($whiteboard))->toOthers();

        return response()->json([
            'success' => true,
            'sync_status' => $whiteboard->sync_status?->value ?? WhiteboardSyncState::Synced->value,
        ]);
    }
}
