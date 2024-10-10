<?php

namespace App\Http\Middleware;

use App\Models\Channel;
use App\Models\Message;
use App\Models\Server;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $auth = [
            'user' => $request->user(),
        ];

//        if ($request->is('home')) {
//            // Get the server, channel, and message from the request
//            $server = $request->route('server');
//            $channel = $request->route('channel');
//            $message = $request->route('message');
//
//            // Add the specific values for the '/home' route
//            $sharedData['auth'] = array_merge($auth['auth'], [
//                'selected_server' => Server::find($server),
//                'selected_channel' => Channel::find($channel),
//                'selected_message' => Message::find($message),
//                'channels' => Server::find($server)?->channels,
//                'messages' => is_null($channel) ? null : Message::where('channel_id', $channel)->get(),
//            ]);
//        }

        return [
            ...parent::share($request),
            'auth' => $auth,
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
