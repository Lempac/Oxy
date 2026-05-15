<?php

namespace App\Http\Controllers;

use App\Models\Whiteboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WhiteboardController extends Controller
{
    public function saveState(Request $request, Whiteboard $whiteboard)
    {
        $request->validate([
            'state' => 'required|string',
        ]);

        // Authorization check: User must be a member of the server the whiteboard belongs to
        $server = $whiteboard->channel->server;
        if (!$server->users()->where('user_id', $request->user()->id)->exists()) {
            abort(403);
        }

        $whiteboard->update([
            'state' => $request->input('state'),
        ]);

        return response()->json(['success' => true]);
    }
}
