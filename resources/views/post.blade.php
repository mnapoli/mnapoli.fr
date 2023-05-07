@extends('layout')

@section('title', $post->title)
@section('description', str_replace(["\r", "\n"], ' ', strip_tags($post->extract)))
@if ($post->image)
    @section('metaImage', $post->image)
@endif

@section('content')

    <article class="relative mx-auto max-w-2xl mt-12 lg:mt-24">
        @if (App::environment('local'))
            <a title="Edit post" href="{{ route('edit-post', ['slug' => $post->slug]) }}"
               class="group mb-8 flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-md shadow-zinc-800/5 ring-1 ring-zinc-900/5 transition dark:border dark:border-zinc-700/50 dark:bg-zinc-800 dark:ring-0 dark:ring-white/10 dark:hover:border-zinc-700 dark:hover:ring-white/20 float-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 stroke-zinc-500 transition group-hover:stroke-zinc-700 dark:stroke-zinc-500 dark:group-hover:stroke-zinc-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
            </a>
        @endif

        <header>
            <time datetime="{{ $post->date->toDateString() }}" class="order-first flex items-center text-base text-zinc-400 dark:text-zinc-500">
                <span class="h-4 w-0.5 rounded-full bg-zinc-200 dark:bg-zinc-500"></span>
                <span class="ml-3">{{ $post->date->format('F Y') }}</span>
            </time>
            <h1 class="mt-6 text-4xl font-bold tracking-tight text-zinc-800 dark:text-zinc-100 sm:text-5xl">
                {{ $post->title }}
            </h1>
        </header>

        @if ($post->image)
            <img class="mt-8 w-full" src="{{ $post->image }}" alt="{{ $post->title }}">
        @endif

        <div class="mt-10 prose dark:prose-invert">{!! $post->htmlContent !!}</div>
    </article>

@endsection
