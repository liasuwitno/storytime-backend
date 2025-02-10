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

Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'store'])->name('register');

// CATEGORY PUBLIC ====>
Route::get('/categories-all', [CategoryController::class, 'index']);

//STORY PUBLIC
Route::get('/story-categories', [StoryController::class, 'getStoriesByCategory']);
Route::get('/story-detail/{slug}', [StoryController::class, 'show']);
Route::get('/spesific-stories/{category}', [StoryController::class, 'spesificStories']);


Route::middleware('auth:sanctum', 'check-sanctum-token')->group(function () {
    // CATEGORY ====>
    Route::apiResource('categories', CategoryController::class)->except(['index']);

    // USER ====>
    Route::get('/profile', [UserController::class, 'profileUser']);
    Route::put('/update-profile/{unique_id}', [UserController::class, 'update']);
    Route::get('/user-stories', [StoryController::class, 'userStories']);

    Route::post('/logout', [UserController::class, 'logout']);

    // BOOKMARK ====>
    Route::post('/bookmark', [BookmarkController::class, 'toggleBookmark']);
    Route::get('/bookmark-list', [BookmarkController::class, 'index']);

    // STORY ====>
    Route::post('/create-story', [StoryController::class, 'store']);
    Route::put('/edit-story/{unique_id}', [StoryController::class, 'update']);
    Route::delete('/story-delete/{unique_id}', [StoryController::class, 'deleteStory']);

    // UPLOAD FILE GENERAL ====>
    Route::post('/upload-file/{folder}', [UploadFileController::class, 'uploadFile']);
    Route::post('/upload-file-single/{folder}', [UploadFileController::class, 'uploadFileSingle']);
});
