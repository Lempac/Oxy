<?php

use App\Http\Controllers\ServerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/servers', [ServerController::class, 'create'])->name('server.create');
Route::post('/servers/{serverId}/add-user', [ServerController::class, 'addUser'])->name('server.addUser');
Route::post('/servers/{serverId}/remove-user', [ServerController::class, 'removeUser'])->name('server.removeUser');
