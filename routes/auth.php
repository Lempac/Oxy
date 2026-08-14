<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('magic-login/{user}', function (Request $request, User $user) {
    if (! $request->hasValidSignature()) {
        abort(401, 'Invalid or expired magic link.');
    }
    Auth::login($user, remember: true);
    $request->session()->regenerate();

    return redirect()->route('home');
})->name('magic-login');

Route::middleware('guest')->group(function () {
    Route::post('register', [RegisteredUserController::class, 'store'])
        ->name('register')
        ->middleware('throttle:10,1');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
