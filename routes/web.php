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

    Route::get('/kanban', [KanbanBoardController::class, 'index'])->name('kanban');

    Route::prefix('kanban')->name('kanban.')->group(function () {
        Route::get('/', [KanbanBoardController::class, 'index'])->name('index');
        Route::get('/create', [KanbanBoardController::class, 'create'])->name('create');
        Route::post('/', [KanbanBoardController::class, 'store'])->name('store');
        Route::get('/{kanbanBoard}', [KanbanBoardController::class, 'show'])->name('show');
        Route::get('/{kanbanBoard}/edit', [KanbanBoardController::class, 'edit'])->name('edit');
        Route::put('/{kanbanBoard}', [KanbanBoardController::class, 'update'])->name('update');
        Route::delete('/{kanbanBoard}', [KanbanBoardController::class, 'destroy'])->name('destroy');

        Route::prefix('{kanbanBoard}/columns')->name('columns.')->group(function () {
            Route::get('/', [KanbanColumnController::class, 'index'])->name('index');
            Route::get('/create', [KanbanColumnController::class, 'create'])->name('create');
            Route::post('/', [KanbanColumnController::class, 'store'])->name('store');
            Route::get('/{kanbanColumn}', [KanbanColumnController::class, 'show'])->name('show');
            Route::get('/{kanbanColumn}/edit', [KanbanColumnController::class, 'edit'])->name('edit');
            Route::put('/{kanbanColumn}', [KanbanColumnController::class, 'update'])->name('update');
            Route::delete('/{kanbanColumn}', [KanbanColumnController::class, 'destroy'])->name('destroy');

            Route::prefix('{kanbanColumn}/tasks')->name('tasks.')->group(function () {
                Route::get('/', [KanbanTaskController::class, 'index'])->name('index');
                Route::get('/create', [KanbanTaskController::class, 'create'])->name('create');
                Route::post('/', [KanbanTaskController::class, 'store'])->name('store');
                Route::get('/{kanbanTask}', [KanbanTaskController::class, 'show'])->name('show');
                Route::get('/{kanbanTask}/edit', [KanbanTaskController::class, 'edit'])->name('edit');
                Route::put('/{kanbanTask}', [KanbanTaskController::class, 'update'])->name('update');
                Route::delete('/{kanbanTask}', [KanbanTaskController::class, 'destroy'])->name('destroy');
            });
        });
    });
});

require __DIR__ . '/auth.php';
