<?php

use App\Blog;

it('renders the public pages', function (string $path, string $text) {
    $this->get($path)->assertOk()->assertSee($text);
})->with([
    ['/', 'Matthieu'],
    ['/articles', 'Articles'],
    ['/projects', 'Projects'],
    ['/speaking', 'Speaking'],
    ['/up', 'Application up'],
]);

it('renders a Markdown article', function () {
    $post = app(Blog::class)->getPost('serverless-php');

    $this->get('/serverless-php')
        ->assertOk()
        ->assertSee($post->title)
        ->assertSee('<pre', false);
});

it('serves a valid Atom feed', function () {
    $response = $this->get('/atom.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/atom+xml');

    $feed = simplexml_load_string($response->getContent());

    expect($feed)->not->toBeFalse()
        ->and($feed->getName())->toBe('feed')
        ->and(count($feed->entry))->toBeGreaterThan(0);
});

it('redirects old URLs', function () {
    $this->get('/presentations')->assertMovedPermanently()->assertRedirect('/speaking');
    $this->get('http://localhost/serverless-php/?source=smoke')->assertMovedPermanently()->assertRedirect('/serverless-php');
});

it('returns a 404 for missing posts', function () {
    $this->get('/this-post-does-not-exist')->assertNotFound();
});

it('does not expose the local editor outside development', function () {
    $this->get('/post')->assertNotFound();
    $this->get('/post/serverless-php/edit')->assertNotFound();
    $this->post('/upload-image')->assertMethodNotAllowed();
});
