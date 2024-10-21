<?php

use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ServerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Reverb\Protocols\Pusher\Http\Controllers\ChannelController;

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
    Route::controller(ServerController::class)->group(function () {
        Route::post('/server', 'create')->name('server.create');
        Route::post('/server/{server}/add-user', 'addUser')->name('server.addUser');
        Route::delete('/server/{server}/remove-user', 'removeUser')->name('server.removeUser');
        Route::patch('/server/{server}', 'edit')->name('server.edit');
    });

    Route::controller(MessageController::class)->group(function () {
       Route::post('/message', 'create')->name('message.create');
       Route::patch('/message/{message}', 'edit')->name('message.edit');
       Route::delete('/message/{message}','destroy')->name('message.destroy');
    });

    Route::controller(ChannelController::class)->group(function () {
        Route::post('/channel', 'create')->name('channel.create');
        Route::patch('/channel/{channel}', 'edit')->name('channel.edit');
        Route::delete('/channel/{channel}', 'destroy')->name('channel.destroy');
    });
});
