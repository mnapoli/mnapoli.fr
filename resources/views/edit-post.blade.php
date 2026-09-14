@extends('layout')

@section('title', $post->title)

@section('content')

    <article id="app" class="mb-24">
        <form method="post" enctype="multipart/form-data" v-cloak>
            @csrf

            <button type="submit" class="float-right button">
                Save
            </button>

            <h1 class="font-title font-bold text-gray-900 text-3xl mb-2">
                <input type="text" name="title" v-model="title">
            </h1>

            <div class="text-gray-500 text-sm mb-6" title="{{ $post->date->toDateTimeString() }}">
                {{ $post->date->format('F Y') }}
            </div>

            @if ($errors->any())
                <div class="bg-red-100 text-red-700 rounded-sm p-4 pb-1 mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Post image --}}
            <div class="bg-gray-200 w-full mb-3 rounded-sm overflow-hidden relative transition-all duration-300"
                 :class="{ 'h-0': !image, 'h-32 border': !!image }">
                <input type="hidden" name="image" v-model="image">
                <button type="button"
                        @click="image = null"
                        class="bg-gray-700/50 text-white p-2 rounded-sm absolute right-0 top-0 m-2">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="trash w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
                <img class="w-full object-cover h-full mb-6" v-if="image" :src="image">
            </div>
            <div class="mb-6">
                <label class="inline-block link cursor-pointer">
                    <span v-if="image">Replace the cover image</span>
                    <span v-if="!image">Upload a cover image</span>
                    <input type="file" name="uploadedImage" class="hidden" @change="uploadedImageChange">
                </label>
            </div>

            <div v-if="warningMore" class="text-red-700 text-sm mb-4">
                Watch out, the article does not contain a "<code>&lt;!--more--&gt;</code>" tag.
                Do not forget to insert it so that an extract of the article can be displayed on the home page.
            </div>

            <textarea ref="editor" name="content" class="text-gray-800"></textarea>

        </form>
    </article>
    <script id="editor-data" type="application/json">{!! Illuminate\Support\Js::encode([
        'title' => old('title', $post->title),
        'content' => old('content', $post->markdown),
        'image' => old('image', $post->image),
        'directory' => 'posts/'.$post->slug,
        'previewUrl' => route('preview-post'),
        'uploadUrl' => route('upload-image'),
        'csrfToken' => csrf_token(),
    ]) !!}</script>
@endsection

@push('assets')
    @vite('resources/js/editor.js')
@endpush
