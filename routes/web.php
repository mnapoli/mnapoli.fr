<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::get('/', [BlogController::class, 'home']);
Route::get('/atom.xml', [BlogController::class, 'feed']);

Route::permanentRedirect('/articles/', '/articles');
Route::get('/articles', [BlogController::class, 'articles']);

Route::permanentRedirect('/projects/', '/projects');
Route::get('/projects', [BlogController::class, 'projects']);

Route::get('/speaking', [BlogController::class, 'speaking']);
Route::permanentRedirect('/presentations/', '/speaking');
Route::permanentRedirect('/presentations', '/speaking');

// Editing files is only available on the local development server.
if (App::environment('local')) {
    Route::get('/post', [AdminController::class, 'newPost'])->name('new-post');
    Route::post('/post', [AdminController::class, 'newPost']);
    Route::get('/post/{slug}/edit', [AdminController::class, 'editPost'])->name('edit-post');
    Route::post('/post/{slug}/edit', [AdminController::class, 'editPost']);
    Route::post('/upload-image', [AdminController::class, 'uploadImage'])->name('upload-image');
    Route::post('/post/preview', [AdminController::class, 'preview'])->name('preview-post');
}

Route::get('/{slug}', [BlogController::class, 'post'])->name('post');
