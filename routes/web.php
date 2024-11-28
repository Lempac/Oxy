<?php

use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KanbanBoardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome'))->name('welcome');
Route::get('manual', fn () => Inertia::render('Manual'))->name('manual');

//Server/home routes
Route::middleware('auth')->group(function () {
    Route::controller(HomeController::class)->prefix('home')->name('home')->group(function () {
        Route::get('/', 'home')->middleware('verified');
        Route::get('/{server}', 'server')->name('.server');
        Route::get('/{server}/text', 'text')->name('.text');
        Route::get('/{server}/text/{channel}', 'channel')->name('.text.channel');
        Route::get('/{server}/text/{channel}/{message}', 'message')->name('.text.channel.message');

        Route::get('/{server}/voice', 'voice')->name('.voice');
        Route::get('/{server}/voice/{channel}', 'vchannel')->name('.voice.channel');
    });

    Route::delete('/{id}/leave', [ServerController::class, 'leave'])->name('server.leave');

    //Setting routes
    Route::prefix('settings')->group(function () {
        Route::controller(ServerController::class)->prefix('server')->group(function () {
            Route::get('/{id}', 'showSettings')->name('settings.server');
            Route::post('/{id}', 'update')->name('server.update');
            Route::delete('/{id}', 'destroy')->name('server.destroy');
        });
        Route::get('/role/{id}', [RoleController::class, 'showSettings'])->name('settings.role');
        Route::get('/members/{id}', [RoleController::class, 'showMembers'])->name('settings.members');
    });

    //Profile routes
    Route::controller(ProfileController::class)->prefix('profile')->group(function () {
        Route::get('/', 'edit')->name('profile.edit');
        Route::post('/', 'update')->name('profile.update');
        Route::delete('/', 'destroy')->name('profile.destroy');
    });

    Route::controller(KanbanBoardController::class)->prefix('kanban')->group(function () {
        Route::get('/', 'index')->name('kanban.index');
        Route::get('/create', 'create')->name('kanban.create');
        Route::post('/', 'store')->name('kanban.store');
        Route::get('/{board}', 'show')->name('kanban.show');
        Route::get('/{board}/edit', 'edit')->name('kanban.edit');
        Route::put('/{board}', 'update')->name('kanban.update');
        Route::delete('/{board}', 'destroy')->name('kanban.destroy');
    });
});

require __DIR__.'/auth.php';
