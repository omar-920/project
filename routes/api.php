<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserEmailVerification;
use App\Http\Controllers\auth\ManageUserController;
use App\Http\Controllers\auth\AdminMessageController;

//Auth Routes

Route::post('/register', [\App\Http\Controllers\auth\AuthController::class,'register'])->middleware('guest');
Route::post('/login', [\App\Http\Controllers\auth\AuthController::class,'login'])->middleware('throttle:5,2')->name('login','guest');
Route::post('/logout', [\App\Http\Controllers\auth\AuthController::class,'logout'])->middleware('auth:sanctum');

//End of Auth Routes

Route::middleware('auth:sanctum','verified','admin')->group(function () {

Route::get('/users',[ManageUserController::class,'index']);
Route::post('/user/create',[ManageUserController::class,'store']);
Route::put('/user/{id}/update',[ManageUserController::class,'update']);
Route::delete('/user/{id}/delete',[ManageUserController::class,'destroy']);
});
Route::middleware('auth:sanctum','verified','user')->group(function () {
Route::get('/profile',[UserController::class,'index']);
Route::put('/profile/update',[UserController::class,'Update']);
Route::delete('/profile/delete',[UserController::class,'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('email/verify', [UserEmailVerification::class, 'verifyNotice'])
        ->name('verification.notice');

    Route::get('email/verify/{id}/{hash}', [UserEmailVerification::class, 'verifyEmail'])
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [UserEmailVerification::class, 'verifyHandler'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
});


