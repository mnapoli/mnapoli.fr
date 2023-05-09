<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Blog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    private Blog $blog;

    public function __construct()
    {
        $this->blog = new Blog();
    }

    public function newPost(Request $request)
    {
        if ($request->isMethod('post')) {
            // The form is submitted
            $this->validate($request, [
                'title' => 'required',
                'slug' => 'required',
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
            $this->validate($request, [
                'title' => 'required',
                'content' => 'required',
                'image' => 'nullable',
            ]);

            $content = $request->input('content');
            $title = $request->input('title');
            $imageUrl = $request->input('image');

            if ($request->hasFile('uploadedImage')) {
                $this->validate($request, [
                    'uploadedImage' => 'required|image',
                ]);
                // Store uploaded image in the `public/assets/images/posts` directory
                $imageUrl = $this->storeUploadedImage($request->file('uploadedImage'), 'posts/' . $slug);
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
            $this->validate($request, [
                'directory' => 'required',
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
        return new JsonResponse([
            'html' => $this->blog->preview($request->get('markdown')),
        ]);
    }

    private function storeUploadedImage(UploadedFile $imageFile, $targetDirectory): string
    {
        $originalFileName = $imageFile->getClientOriginalName();
        $originalFileName = str_replace($imageFile->getClientOriginalExtension(), '', $originalFileName);
        // We rename the uploaded file to be a clean-looking URL
        $newFileName = Str::slug($originalFileName) . '.' . $imageFile->extension();

        // Store the upladed image in the `images` filesystem
        $path = $imageFile->storeAs($targetDirectory, $newFileName, 'images');

        // Return the URL of the uploaded image
        return '/assets/images/' . $path;
    }
}
