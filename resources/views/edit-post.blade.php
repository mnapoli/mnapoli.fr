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
                <div class="bg-red-100 text-red-700 rounded p-4 pb-1 mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Post image --}}
            <div class="bg-gray-200 w-full mb-3 rounded overflow-hidden relative transition-all duration-300"
                 :class="{ 'h-0': !image, 'h-32 border': !!image }">
                <input type="hidden" name="image" v-model="image">
                <button type="button"
                        @click="image = null"
                        class="bg-gray-700 bg-opacity-50 text-white p-2 rounded absolute right-0 top-0 m-2">
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
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
{{--    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>--}}
    <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
    <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.1.2/build/styles/github.min.css">
    <script src="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.1.2/build/highlight.min.js"></script>
    <script>
        new Vue({
            el: '#app',
            data: {
                title: @json($post->title),
                content: @json($post->markdown),
                warningMore: false,
                image: @json($post->image),
            },
            mounted() {
                this.mde = new EasyMDE({
                    element: this.$refs.editor,
                    autofocus: true,
                    toolbar: [
                        "bold", "italic", "heading", "|",
                        {
                            // Extract separator
                            name: "more",
                            action: (editor) => {
                                editor.codemirror.replaceSelection('<!--more-->');
                            },
                            className: "fa fa-ellipsis-h",
                            title: "Mark the end of the extract of the blog post.",
                        }, "code", "quote", "|",
                        "unordered-list", "ordered-list", "|",
                        "link", "image", "table", "|",
                        "preview", "side-by-side", "fullscreen", "|",
                        "guide",
                    ],
                    previewRender: function(plainText, preview) {
                        fetch('/api/preview', {
                            method: 'post',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                markdown: plainText,
                            }),
                        }).then(res => res.json())
                            .then(res => preview.innerHTML = res.html);

                        return 'Loading...';
                    },
                    uploadImage: true,
                    imageUploadFunction: (file, onSuccess, onError) => {
                        const data = new FormData();
                        data.append('image', file);
                        data.append('directory', 'posts/{{ $post->slug }}');
                        fetch('/upload-image', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: data,
                        }).then(response => response.json())
                            .then(response => onSuccess(response.path))
                            .catch(error => onError('The image could not be uploaded.'));
                    },
                });
                this.mde.value(this.content);
                this.mde.codemirror.on('change', () => {
                    this.content = this.mde.value();
                });
            },
            watch: {
                content: function (val) {
                    if (val) {
                        this.warningMore = !val.includes('<!--more-->');
                    }
                },
            },
            methods: {
                uploadedImageChange(e) {
                    const file = e.target.files[0];
                    this.image = URL.createObjectURL(file);
                }
            },
        });
    </script>

@endsection
