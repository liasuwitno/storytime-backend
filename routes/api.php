<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UploadFileController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'store']);

Route::middleware('auth:sanctum', 'check.token.expiry')->group(function(){
    Route::post('/upload-file/{folder}', [UploadFileController::class, 'uploadFile']);
    Route::put('/update-profile/{unique_id}', [UserController::class, 'update']);
    Route::apiResource('categories', CategoryController::class);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/user/{unique_id}', [UserController::class, 'profileUser']);
});