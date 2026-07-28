<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ServerInvite;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'server_code' => ['required', 'string'],
            'nickname' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $invite = ServerInvite::where('code', $request->server_code)->first();

        if (! $invite || ! $invite->isValid()) {
            throw ValidationException::withMessages([
                'server_code' => 'The provided server code is invalid or has expired.',
            ]);
        }

        $iconPath = null;
        if ($request->file('icon')?->isValid()) {
            $file = $request->file('icon');

            if (in_array($file->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'gif', 'webp'])) {

                [$width, $height] = getimagesize($file->getRealPath());

                if ($width > 1920 || $height > 1080) {
                    return redirect()->back()->withErrors(['icon' => 'The image must not exceed 1920x1080 pixels.']);
                }
            }

            $path = $file->store('uploads', 'public');
            $iconPath = Storage::url($path);
        }

        $user = User::create([
            'nickname' => $request->nickname,
            'password' => Hash::make($request->password),
            'icon' => $iconPath,
        ]);

        // Attach user to server and update invite count
        $server = $invite->server;
        if ($server && ! $server->users()->where('users.id', $user->id)->exists()) {
            $server->users()->attach($user->id);
        }
        $invite->increment('uses');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('home', absolute: false));
    }
}
