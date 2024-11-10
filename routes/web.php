<?php

use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome'))->name('welcome');
//Server/home routes
Route::middleware('auth')->group(function () {
    Route::controller(HomeController::class)->prefix('home')->group(function () {
        Route::get('/', 'home')->middleware('verified')->name('home');
        Route::get('/{server}', 'server')->name('home.server');
        Route::get('/{server}/text', 'text')->name('home.text');
        Route::get('/{server}/text/{channel}', 'channel')->name('home.channel');
        Route::get('/{server}/text/{channel}/{message}', 'message')->name('home.message');
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
