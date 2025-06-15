<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileImageController;
use App\Http\Middleware\Unauthenticated;
use Illuminate\Support\Facades\Route;



Route::controller(AuthController::class)->group(function () {
    Route::prefix('/register')->group(function () {
        Route::post('/google', 'registerGoogle');
        Route::post('/traditional', 'registerTraditional');
    });
    Route::prefix('/login')->group(function () {
        Route::post('/google', 'loginGoogle');
        Route::post('/traditional', 'loginTraditional');
    });

    Route::post('/findByEmail', 'findUserByEmail');
    Route::middleware([Unauthenticated::class])->group(function () {

        Route::post('/findByName', 'findUserByName');
        Route::post('/findUsersByIds', 'findUsersByIds');
        Route::get('/find/{user}', 'findUserById');
        Route::patch('/update/{user}', 'updateInfos');
        Route::get('/getInContact', [AuthController::class, 'getUsersInContact']);
        Route::delete('/logout/{user}', 'logout');
    });

    Route::get('/check', 'checkToken');
    Route::get('/validate', 'validateToken');

});


Route::controller(MessageController::class)->group(function () {
    Route::middleware([Unauthenticated::class])->group(function () {
        Route::post('/send', 'sendMessage');
        Route::get('/getMessages/{channel}', 'getAllMessages');
        Route::get('/getAllChannels', 'getAllChatChannels');
        Route::get('/chatchannel/{user}', 'getChatChannel');
    });
});


Route::controller(ProfileImageController::class)->group(function () {
    Route::middleware([Unauthenticated::class])->group(function () {
        Route::prefix('profileImageLink')->group(function () {
            Route::get('/{user}', 'getProfilePicture');
            Route::post('/', 'uploadProfilePicture');
        });
    });
});


Route::get('/', function () {
    return response()->json([
        'code' => 401,
        'error' => 'Not authorized'
    ]);
})->name('login');

