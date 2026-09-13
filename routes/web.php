<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::get('/', [Controller::class, 'home']);
Route::get('/atom.xml', [Controller::class, 'feed']);

Route::permanentRedirect('/articles/', '/articles');
Route::get('/articles', [Controller::class, 'articles']);

Route::permanentRedirect('/projects/', '/projects');
Route::get('/projects', [Controller::class, 'projects']);

Route::get('/speaking', [Controller::class, 'speaking']);
Route::permanentRedirect('/presentations/', '/speaking');
Route::permanentRedirect('/presentations', '/speaking');

// Editing files is only available on the local development server.
if (App::environment('local')) {
    Route::get('/post', [AdminController::class, 'newPost'])->name('new-post');
    Route::post('/post', [AdminController::class, 'newPost']);
    Route::get('/post/{slug}/edit', [AdminController::class, 'editPost'])->name('edit-post');
    Route::post('/post/{slug}/edit', [AdminController::class, 'editPost']);
    Route::post('/upload-image', [AdminController::class, 'uploadImage']);
}

Route::get('/{slug}', [Controller::class, 'post'])->name('post');
