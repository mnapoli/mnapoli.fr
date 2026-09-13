<?php

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->app->detectEnvironment(fn () => 'local');
    Route::setRoutes(new RouteCollection);
    Route::middleware('web')->group(base_path('routes/web.php'));
    Route::getRoutes()->refreshNameLookups();
});

it('renders the local post forms', function () {
    $this->get('/post')->assertOk()->assertSee('title');
    $this->get('/post/serverless-php/edit')
        ->assertOk()
        ->assertSee('editor-data')
        ->assertSee('Save');
});

it('previews Markdown with the blog renderer', function () {
    $this->withSession(['_token' => 'smoke-test'])
        ->postJson('/post/preview', ['markdown' => '# Preview'], ['X-CSRF-TOKEN' => 'smoke-test'])
        ->assertOk()
        ->assertJsonPath('html', "<h1>Preview</h1>\n");
});
