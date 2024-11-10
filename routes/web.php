<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\Api\RoleController;

Route::get('/', fn() => Inertia::render('Welcome'))->name('welcome');
Route::get('manual', fn() => Inertia::render('Manual'))->name('manual');

//Server/home routes
Route::middleware('auth')->group(function () {
    Route::controller(HomeController::class)->prefix('home')->name('home')->group(function () {
        Route::get('/', 'home')->middleware('verified');
        Route::get('/{server}', 'server')->name('.server');
        Route::get('/{server}/text', 'text')->name('.text');
        Route::get('/{server}/text/{channel}', 'channel')->name('.channel');
        Route::get('/{server}/text/{channel}/{message}', 'message')->name('.message');

        Route::get('/{server}/voice', 'voice')->name('.voice');
    });
    //Setting routes
    Route::prefix('settings')->group(function () {
        Route::controller(ServerController::class)->prefix('server')->group(function () {
            Route::get('/{serverId}', 'showSettings')->name('settings.server');
            Route::post('/{id}', 'update')->name('server.update');
            Route::delete('/{id}', 'destroy')->name('server.destroy');
        });
        Route::get('/role/{id}', [RoleController::class, 'showSettings'])->name('settings.role');
    });

    //Profile routes
    Route::controller(ProfileController::class)->prefix('profile')->group(function () {
        Route::get('/', 'edit')->name('profile.edit');
        Route::post('/', 'update')->name('profile.update');
        Route::delete('/', 'destroy')->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';
