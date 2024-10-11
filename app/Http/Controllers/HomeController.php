<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use App\Models\Server;
use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
class HomeController extends Controller
{
    public function home(Request $request): Response
    {
        return Inertia::render('Home')->with(['servers' => $request->user()->servers]);
    }

    public function select(int $server, int $channel = null, int $message = null): Response {
        return Inertia::render('Home', [
            'selected_server' => Server::find($server),
            'selected_channel' => Channel::find($channel),
            'selected_message' => Message::find($message),
            'channels' => Server::find($server)?->channels,
            'messages' => is_null($channel) ? null : Message::findMany(['channel_id' => $channel])
        ]);
    }
}
