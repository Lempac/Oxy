<?php

use App\Http\Controllers\Api\ServerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');
//
//Route::post('/tokens/create', function (Request $request) {
//    dd($request->user());
//    $token = $request->user()->createToken($request->token_name);
//
//    return ['token' => $token->plainTextToken];
//})->middleware('auth:web')->name('tokens.create');

Route::middleware(['web'])->group(function () {
    Route::post('/servers', [ServerController::class, 'create'])->name('server.create');
    Route::post('/servers/{server}/add-user', [ServerController::class, 'addUser'])->name('server.addUser');
    Route::delete('/servers/{server}/remove-user', [ServerController::class, 'removeUser'])->name('server.removeUser');
    Route::patch('/servers/{server}', [ServerController::class, 'edit'])->name('server.edit');
});
