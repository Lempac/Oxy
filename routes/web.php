<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\ServerController;

Route::get('/', fn() => Inertia::render('Welcome'))->name('welcome');
#Server/home routes
Route::middleware('auth')->group(function () {
    Route::controller(HomeController::class)->prefix('home')->group(function () {
        Route::get('/', 'home')->middleware('verified')->name('home');
        Route::get('/{server}', 'server')->name('home.server');
        Route::get('/{server}/text', 'text')->name('home.text');
        Route::get('/{server}/text/{channel}', 'channel')->name('home.channel');
        Route::get('/{server}/text/{channel}/{message}', 'message')->name('home.message');
    });
    #Setting routes
    Route::get('/settings/server/{serverId}', [ServerController::class, 'showSettings'])->name('settings.server');
    Route::post('/settings/server/{id}', [ServerController::class, 'update'])->name('server.update');
    Route::delete('/settings/server/{id}', [ServerController::class, 'destroy'])->name('server.destroy');
    Route::get('/settings/role', fn() => Inertia::render('Settings/Role'))->name('settings.role');
    
    #Profile routes
    Route::controller(ProfileController::class)->prefix('profile')->group(function () {
        Route::get('/', 'edit')->name('profile.edit');
        Route::post('/', 'update')->name('profile.update');
        Route::delete('/', 'destroy')->name('profile.destroy');
    });
});

require __DIR__ . '/auth.php';
