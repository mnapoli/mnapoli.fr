@extends('layout')

@section('title', 'New post')

@section('content')

    <article class="mb-24">
        <form method="post">
            @csrf

            <button type="submit" class="float-right button">
                Save
            </button>

            <h1 class="font-title font-bold text-gray-900 text-3xl mb-2">
                <input type="text" name="title" placeholder="New post title">
            </h1>

            <div class="text-gray-500 text-sm mb-6">
                {{ (new DateTimeImmutable)->format('F Y') }}
            </div>

            <div class="text-gray-700 mb-6">
                <label>
                    Post slug:
                    <input type="text" name="slug" placeholder="post-slug"
                       class="mt-2 bg-white focus:outline-none focus:shadow-outline border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none leading-normal">
                </label>
                <p class="mt-2 text-gray-500">
                    The slug is what is used to create the URL of the post.
                    It will also be used in the name of the Markdown file that will be created.
                </p>
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

        </form>
    </article>

@endsection
