<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CourseContentController;
use App\Http\Controllers\CompletionController;
use App\Http\Controllers\BookmarkController;

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

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/{user}', [UserController::class, 'show']);

    Route::put('/profile', [UserController::class, 'updateProfile']);
    // Route::post('/profile', [UserController::class, 'updateProfile']);

    Route::post('/logout', [AuthController::class, 'logout']);

        Route::prefix('courses/{course}/announcements')->group(function () {
        Route::post('/', [AnnouncementController::class, 'store']);
        Route::get('/', [AnnouncementController::class, 'index']);
        Route::put('/{announcement}', [AnnouncementController::class, 'update']);
        Route::delete('/{announcement}', [AnnouncementController::class, 'destroy']);
    });

    Route::post('/courses/{course}/enroll-students', [CourseController::class, 'batchEnrollStudents']);

    Route::post('/contents/{content}/comments/{comment}/moderate', [CommentController::class, 'moderate']);

    Route::prefix('contents/{content}')->group(function () {
        Route::post('/comments', [CourseContentController::class, 'addComment']);
        Route::get('/', [CourseContentController::class, 'show']); 
    });

    Route::post('/contents/{content}/complete', [CompletionController::class, 'store']); 
    Route::get('/members/{member}/completions', [CompletionController::class, 'index']);
    Route::delete('/completions/{completion}', [CompletionController::class, 'destroy']);

    Route::post('/contents/{content}/bookmark', [BookmarkController::class, 'store']); 
    Route::get('/bookmarks', [BookmarkController::class, 'index']);                    
    Route::delete('/bookmarks/{bookmark}', [BookmarkController::class, 'destroy']);    

});


