<?php

use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PDFExportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ServerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth', 'web']);
//
//Route::post('/tokens/create', function (Request $request) {
//    dd($request->user());
//    $token = $request->user()->createToken($request->token_name);
//
//    return ['token' => $token->plainTextToken];
//})->middleware('auth:web')->name('tokens.create');

Route::middleware(['web'])->group(function () {
    Route::controller(ServerController::class)->prefix('server')->name('server')->group(function () {
        Route::post('/', 'create')->name('.create');
        Route::post('/add-user', 'addUser')->name('.addUser');
        Route::patch('/{server}', 'edit')->name('.edit');
        Route::delete('/{server}/remove-user', 'removeUser')->name('.removeUser');
        Route::delete('/', 'delete')->name('.delete');
        Route::delete('/{id}/leave', [ServerController::class, 'leave'])->name('.leave');
    });

    Route::controller(MessageController::class)->prefix('message')->name('message')->group(function () {
        Route::post('/{channel}', 'create')->name('.create');
        Route::patch('/{message}', 'edit')->name('.edit');
        Route::delete('/{message}', 'delete')->name('.delete');
    });

    Route::controller(ChannelController::class)->prefix('channel')->name('channel')->group(function () {
        Route::post('/{server}', 'create')->name('.create');
        Route::patch('/{channel}', 'edit')->name('.edit');
        Route::delete('/{channel}', 'delete')->name('.delete');
        Route::post('/{channel}', 'upload')->name('.upload');
    });

    Route::controller(RoleController::class)->prefix('roles')->name('roles')->group(function () {
        Route::get('/{server}', 'index')->name('.index');
        Route::post('/{server}', 'create')->name('.create');
        Route::patch('/{role}', 'edit')->name('.edit');
        Route::delete('/{role}', 'delete')->name('.delete');
        Route::post('/{role}/add-user/{user}', 'addUser')->name('.add-user');
        Route::delete('/{role}/remove-user/{user}', 'removeUser')->name('.remove-user');
    });

    Route::controller(PDFExportController::class)->group(function () {
        Route::get('/profile/export', 'exportPDF')->name('profile.export');
    });
});
