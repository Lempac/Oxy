<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KanbanBoardController;
use App\Http\Controllers\KanbanColumnController;
use App\Http\Controllers\KanbanTaskController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/home', [HomeController::class, 'home'])->middleware(['auth', 'verified'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/home/{server}/{channel?}/{message?}', [HomeController::class, 'select'])->name('select');

    Route::get('/settings/server', fn() => Inertia::render('Settings/Server'))->name('settings.server');
    Route::get('/settings/role', fn() => Inertia::render('Settings/Role'))->name('settings.role');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('kanban_boards', KanbanBoardController::class);
    
    Route::resource('kanban_boards.columns', KanbanColumnController::class)->shallow();
    
    Route::resource('kanban_columns.tasks', KanbanTaskController::class)->shallow();
});

require __DIR__.'/auth.php';
