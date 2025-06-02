<?php

use App\Events\TestEvent;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileImageController;
use App\Http\Middleware\Unauthenticated;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Pusher\Pusher;



Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::delete('/logout/{user}', 'logout');
    Route::middleware([Unauthenticated::class])->group(function () {
        Route::post('/findByEmail', 'findUserByEmail');
        Route::post('/findByName', 'findUserByName');
        Route::post('/findUsersByIds', 'findUsersByIds');
        Route::get('/find/{user}', 'findUserById');
        Route::patch('/update/{user}', 'updateInfos');
        Route::get('/getInContact', [AuthController::class, 'getUsersInContact']);
    });

    Route::get('/check', 'checkToken');
    Route::get('/validate', 'validateToken');

});


Route::controller(MessageController::class)->group(function (){
    Route::middleware([Unauthenticated::class])->group(function(){
        Route::post('/send', 'sendMessage');
        Route::get('/getMessages/{channel}', 'getAllMessages');
        Route::get('/getAllChannels', 'getAllChatChannels');
        Route::get('/chatchannel/{user}','getChatChannel');
    });
});


Route::controller(ProfileImageController::class)->group(function (){
    Route::middleware([Unauthenticated::class])->group(function () {
        Route::prefix('profileImageLink')->group(function () {
            Route::get('/{user}', 'getProfilePicture');
            Route::patch('', 'uploadProfilePicture');
        });
    });
});


Route::get('/', function () {
    return response()->json([
        'code' => 401,
        'error' => 'Not authorized'
    ]);
})->name('login');

