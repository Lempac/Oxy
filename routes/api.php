<?php

use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\Api\ChannelController;
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
    Route::controller(ServerController::class)->prefix('server')->group(function () {
        Route::post('/', 'create')->name('server.create');
        Route::post('/add-user', 'addUser')->name('server.addUser');
        Route::patch('/{server}', 'edit')->name('server.edit');
        Route::delete('/{server}/remove-user', 'removeUser')->name('server.removeUser');
    });

    Route::controller(MessageController::class)->prefix('message')->group(function () {
       Route::post('/{channel}', 'create')->name('message.create');
       Route::patch('/{message}', 'edit')->name('message.edit');
       Route::delete('/{message}','delete')->name('message.delete');
    });

    Route::controller(ChannelController::class)->prefix('channel')->group(function () {
        Route::post('/{server}', 'create')->name('channel.create');
        Route::patch('/{channel}', 'edit')->name('channel.edit');
        Route::delete('/{channel}', 'delete')->name('channel.delete');
    });
});
