<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KanbanBoardController;
use App\Http\Controllers\KanbanColumnController;
use App\Http\Controllers\KanbanTaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn() => Inertia::render('Welcome'))->name('welcome');

Route::middleware('auth')->group(function () {
    Route::controller(HomeController::class)->prefix('home')->group(function () {
        Route::get('/', 'home')->middleware('verified')->name('home');
        Route::get('/{server}', 'server')->name('home.server');
        Route::get('/{server}/text', 'text')->name('home.text');
        Route::get('/{server}/text/{channel}', 'channel')->name('home.channel');
        Route::get('/{server}/text/{channel}/{message}', 'message')->name('home.message');
    });

    Route::get('/settings/server', fn() => Inertia::render('Settings/Server'))->name('settings.server');
    Route::get('/settings/role', fn() => Inertia::render('Settings/Role'))->name('settings.role');

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

require __DIR__ . '/auth.php';
