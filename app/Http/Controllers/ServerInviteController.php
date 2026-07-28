<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\Server;
use App\Models\ServerInvite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ServerInviteController extends Controller
{
    /**
     * Generate a new invite code for a server.
     */
    public function store(Request $request, Server $server): JsonResponse|RedirectResponse
    {
        setPermissionsTeamId($server->id);

        if (! Auth::user()->servers->contains('id', $server->id)) {
            abort(403, 'Forbidden.');
        }

        if (! Auth::user()->hasPermissionTo('CAN_INVITE') && ! Auth::user()->hasPermissionTo('CAN_MANAGE_SERVER')) {
            abort(403, 'Forbidden.');
        }

        $validated = $request->validate([
            'max_uses' => 'nullable|integer|min:1',
            'expires_in_hours' => 'nullable|integer|min:1',
        ]);

        $expiresAt = isset($validated['expires_in_hours'])
            ? now()->addHours($validated['expires_in_hours'])
            : null;

        $invite = ServerInvite::create([
            'server_id' => $server->id,
            'created_by_user_id' => Auth::id(),
            'code' => ServerInvite::generateCode(),
            'max_uses' => $validated['max_uses'] ?? null,
            'expires_at' => $expiresAt,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Invite created successfully.',
                'invite' => $invite,
            ]);
        }

        return back()->with('invite_code', $invite->code);
    }

    /**
     * Check if an invitation code is valid and return basic server info.
     */
    public function check(string $code): JsonResponse
    {
        $invite = ServerInvite::with('server')->where('code', $code)->first();

        if (! $invite || ! $invite->isValid()) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired invite code.'], 404);
        }

        $server = $invite->server;
        $membersCount = $server->users()->count();
        $onlineCount = $server->users()->whereIn('users.status', [
            UserStatus::Online->value,
            UserStatus::Idle->value,
            UserStatus::DoNotDisturb->value,
        ])->count();

        return response()->json([
            'valid' => true,
            'server' => [
                'name' => $server->name,
                'description' => $server->description,
                'icon' => $server->icon,
                'members_count' => $membersCount,
                'online_count' => $onlineCount,
            ],
        ]);
    }

    /**
     * Join a server using an invitation code (for logged-in users).
     */
    public function join(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'server_code' => 'required|string',
        ]);

        $invite = ServerInvite::where('code', $request->server_code)->first();

        if (! $invite || ! $invite->isValid()) {
            throw ValidationException::withMessages([
                'server_code' => 'The provided server code is invalid or has expired.',
            ]);
        }

        $user = Auth::user();
        $server = $invite->server;

        if ($server && ! $server->users()->where('users.id', $user->id)->exists()) {
            $server->users()->attach($user->id);
            $invite->increment('uses');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Joined server successfully.',
                'server' => $server,
            ]);
        }

        return redirect()->route('home.server', ['server' => $server->slug]);
    }
}
