<?php

use App\Blog;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', 'Controller@home');

Route::get('/atom.xml', 'Controller@feed');

Route::get('/articles', 'Controller@articles');
Route::get('/projects', 'Controller@projects');
Route::get('/speaking', 'Controller@speaking');

// Admin is only available when running locally
if (App::environment('local')) {

    Route::get('/post', 'AdminController@newPost')
        ->name('new-post');
    Route::post('/post', 'AdminController@newPost');

    Route::get('/post/{slug}/edit', 'AdminController@editPost')
        ->name('edit-post');
    Route::post('/post/{slug}/edit', 'AdminController@editPost');

    Route::post('/upload-image', 'AdminController@uploadImage');

}

Route::get('/{slug}', 'Controller@post')
    ->name('post');
