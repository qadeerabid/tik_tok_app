<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CreatorController;



Route::middleware(['auth', 'role:Creator'])->group(function () {
    // Route::get('/creator/dashboard', [CreatorController::class, 'dashboard']);
    Route::resource('/creator/videos', VideoController::class); // CRUD for videos
});

Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/videos', [AdminController::class, 'manageVideos']); // Manage videos
});
// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [VideoController::class, 'publicIndex']);



Auth::routes();

Route::get('/home', [App\Http\Controllers\VideoController::class, 'publicIndex'])->name('home');
Route::get('/videos/{video}/data', [VideoController::class, 'getVideoData'])->name('videos.data');

Route::post('/videos/{video}/comment', [VideoController::class, 'addComment'])->middleware(['auth']);
Route::post('/videos/{video}/like', [VideoController::class, 'toggleLike'])->middleware(['auth']);
Route::post('/videos/{video}/dislike', [VideoController::class, 'dislike'])->middleware(['auth'])->name('videos.dislike');