@extends('layout')
@section('title', 'Articles')

@section('content')

    <div class="mt-16 sm:mt-20 md:border-l md:border-zinc-100 md:pl-6 md:dark:border-zinc-700/40">
        <div class="flex max-w-3xl flex-col space-y-16">

            @foreach ($posts as $post)

                <article class="md:grid md:grid-cols-4 md:items-baseline">

                    <div class="md:col-span-3 group relative flex flex-col items-start">

                        <h2 class="text-base font-semibold tracking-tight text-zinc-800 dark:text-zinc-100">
                            <div class="absolute -inset-x-4 -inset-y-6 z-0 scale-95 bg-zinc-50 opacity-0 transition group-hover:scale-100 group-hover:opacity-100 dark:bg-zinc-800/50 sm:-inset-x-6 sm:rounded-2xl"></div>
                            <a href="{{ $post instanceof \App\ExternalPost ? $post->url : route('post', ['slug' => $post->slug]) }}">
                                <span class="absolute -inset-x-4 -inset-y-6 z-20 sm:-inset-x-6 sm:rounded-2xl"></span>
                                <span class="relative z-10">{{ $post->title }}</span>
                            </a>
                        </h2>

                        <time class="md:hidden relative z-10 order-first mb-3 flex items-center text-sm text-zinc-400 dark:text-zinc-500 pl-3.5" datetime="{{ $post->date->toDateString() }}">
                            <span class="absolute inset-y-0 left-0 flex items-center" aria-hidden="true">
                                <span class="h-4 w-0.5 rounded-full bg-zinc-200 dark:bg-zinc-500"></span>
                            </span>{{ $post->date->format('F Y') }}
                        </time>

                        @if ($post->extract)
                            <div class="relative z-10 mt-2 text-sm text-zinc-600 dark:text-zinc-400 leading-6 line-clamp-3">
                                {!! strip_tags($post->extract) !!}
                            </div>
                        @endif

                        <div aria-hidden="true" class="relative z-10 mt-4 flex items-center text-sm font-medium text-teal-500">
                            Read article
                            @if ($post instanceof \App\ExternalPost)
                                on {{ parse_url($post->url)['host'] }}
                            @endif
                            <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="ml-1 h-4 w-4 stroke-current"><path d="M6.75 5.75 9.25 8l-2.5 2.25" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </div>
                    </div>

                    <time class="mt-1 hidden md:block relative z-10 order-first mb-3 flex items-center text-sm text-zinc-400 dark:text-zinc-500" datetime="{{ $post->date->toDateString() }}">{{ $post->date->format('F Y') }}</time>

                </article>

            @endforeach

        </div>
    </div>

@endsection
