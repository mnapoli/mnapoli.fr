<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function __construct(private readonly Blog $blog) {}

    public function newPost(Request $request)
    {
        if ($request->isMethod('post')) {
            // The form is submitted
            $request->validate([
                'title' => 'required|string',
                'slug' => ['required', 'string', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            ]);

            $slug = $request->input('slug');
            $this->blog->createPost($slug, $request->input('title'));

            // Redirect to the page to edit the blog post
            return redirect()->route('edit-post', ['slug' => $slug]);
        }

        return view('new-post');
    }

    public function editPost(string $slug, Request $request)
    {
        if ($request->isMethod('post')) {
            // The form is submitted
            $request->validate([
                'title' => 'required',
                'content' => 'required|string',
                'image' => 'nullable|string',
            ]);

            $content = $request->input('content');
            $title = $request->input('title');
            $imageUrl = $request->input('image');

            if ($request->hasFile('uploadedImage')) {
                $request->validate([
                    'uploadedImage' => 'required|image',
                ]);
                // Store uploaded image in the `public/assets/images/posts` directory
                $imageUrl = $this->storeUploadedImage($request->file('uploadedImage'), 'posts/'.$slug);
            }

            $this->blog->editPost($slug, $content, $title, $imageUrl);

            return redirect()->route('edit-post', ['slug' => $slug]);
        }

        return view('edit-post', [
            'post' => $this->blog->getPost($slug),
        ]);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'directory' => ['required', 'string', 'regex:#^posts/[a-z0-9]+(?:-[a-z0-9]+)*$#'],
                'image' => 'required|image',
            ]);
        } catch (ValidationException $e) {
            return new JsonResponse([
                'message' => 'Invalid data',
                'errors' => $e->errors(),
            ], 400);
        }

        // Move the uploaded file to the correct directory
        $directory = $request->input('directory');
        // Store uploaded image in the `public/assets/images/posts` directory
        $path = $this->storeUploadedImage($request->file('image'), $directory);

        return new JsonResponse([
            'path' => $path,
        ]);
    }

    /**
     * Preview an article by rendering its Markdown to HTML.
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate(['markdown' => ['present', 'nullable', 'string']]);

        return new JsonResponse([
            'html' => $this->blog->preview($validated['markdown'] ?? ''),
        ]);
    }

    private function storeUploadedImage(UploadedFile $imageFile, $targetDirectory): string
    {
        $originalFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        // We rename the uploaded file to be a clean-looking URL
        $newFileName = Str::slug($originalFileName).'.'.$imageFile->extension();

        // Store the uploaded image in the `images` filesystem
        $path = $imageFile->storeAs($targetDirectory, $newFileName, 'images');

        // Return the URL of the uploaded image
        return '/assets/images/'.$path;
    }
}
