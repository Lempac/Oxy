<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', fn () => Inertia::render('Welcome'))->name('welcome');

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'home'])->middleware('verified')->name('home');
    Route::get('/home/{server}', [HomeController::class, 'server'])->name('home.server');
    Route::get('/home/{server}/text', [HomeController::class, 'text'])->name('home.text');
    Route::get('/home/{server}/text/{channel}', [HomeController::class, 'channel'])->name('home.channel');
    Route::get('/home/{server}/text/{channel}/{message}', [HomeController::class, 'message'])->name('home.message');
    Route::post('/servers/create', [ServerController::class, 'create'])->name('server.create');


    Route::get('/settings/server', fn() => Inertia::render('Settings/Server'))->name('settings.server');
    Route::get('/settings/role', fn() => Inertia::render('Settings/Role'))->name('settings.role');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';




