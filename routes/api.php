<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Auth Routes

Route::post('/register', [\App\Http\Controllers\auth\AuthController::class,'register']);
Route::post('/login', [\App\Http\Controllers\auth\AuthController::class,'login']);
Route::post('/logout', [\App\Http\Controllers\auth\AuthController::class,'logout'])->middleware('auth:sanctum');

//End of Auth Routes

Route::middleware('auth:sanctum')->group(function () {

Route::get('users',[UserController::class,'index']);
Route::post('/user/create',[UserController::class,'store']);
Route::put('/user/{id}/update',[UserController::class,'update']);
Route::delete('/user/{id}/delete',[UserController::class,'destroy']);

});

