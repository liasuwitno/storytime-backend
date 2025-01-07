<?php

use App\Http\Controllers\BookmarkController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StoryController;
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

Route::get('/health', function () {
    return response()->json([
        'message' => 'Aku lia dan ... masih hidup yo.',
        'data' => null
    ], 200);
});
Route::get('/story-categories', [StoryController::class, 'getStoriesByCategory']);

Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'store'])->name('register');

Route::middleware('auth:sanctum', 'check-sanctum-token')->group(function () {
    Route::post('/upload-file/{folder}', [UploadFileController::class, 'uploadFile']);
    Route::put('/update-profile/{unique_id}', [UserController::class, 'update']);
    Route::apiResource('categories', CategoryController::class);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/create-story', [StoryController::class, 'store']);
    Route::get('/story-detail/{slug}', [StoryController::class, 'show']);
    Route::put('/edit-story/{unique_id}', [StoryController::class, 'update']);
    Route::get('/user/{unique_id}', [UserController::class, 'profileUser']);
    Route::post('/bookmark-list', [BookmarkController::class, 'toggleBookmark']);
});
