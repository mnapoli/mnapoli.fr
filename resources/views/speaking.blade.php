@extends('layout')
@section('title', 'Speaking')
@section('description', 'Things I’m building')

@php
$videos = [
    [
        'title' => 'Serverless PHP with Bref',
        'description' => 'Introduction to serverless PHP applications on AWS using <a class="text-teal-500" href="https://bref.sh">Bref</a>.',
        'youtube' => 'https://www.youtube.com/embed/R2V4QTM2aes',
        'subtext' => 'AWS Summit Paris, PHP UK, PHP Barcelona, phpDay, PHP Serbia, CascadiaPHP, phpCE Prague',
    ],
    [
        'title' => 'Serverless Chats #55: Bref',
        'description' => 'Introduction to Bref on the <a class="text-teal-500" href="https://www.serverlesschats.com/55/">Serverless Chats</a> podcast.',
        'youtube' => 'https://www.youtube.com/embed/H8tkZcjQxOA',
        'subtext' => '',
    ],
    [
        'title' => '🇫🇷 Bref on the AWS Podcast',
        'description' => 'Talking about Bref on the french AWS podcast.',
        'youtube' => 'https://embed.podcasts.apple.com/fr/podcast/vos-applications-php-sur-aws-lambda/id1452118442?i=1000621851482&theme=light',
        'subtext' => '',
    ],
    [
        'title' => 'Talking Serverless Podcast #52',
        'description' => 'Talking with Ryan about Serverless Framework and Bref.',
        'youtube' => 'https://www.youtube.com/embed/SoG3zG468AI',
        'subtext' => '',
    ],
    [
        'title' => '🇫🇷 3 design patterns pour démarrer avec serverless',
        'description' => 'Applications HTTP, File de message avec worker, Communication entre micro-services',
        'youtube' => 'https://www.youtube.com/embed/XYs0Mc4EeIM',
        'subtext' => 'AWS Community Day',
    ],
    [
        'title' => '🇫🇷 L\'architecture progressive - Forum PHP 2019',
        'description' => 'Et si la meilleure architecture ne dépendait pas de sa maintenabilité, son extensibilité ou sa testabilité, mais plutôt du contexte ?',
        'youtube' => 'https://www.youtube.com/embed/XyxvP5f67Po',
        'subtext' => 'ForumPHP',
    ],
    [
        'title' => '🇫🇷 Une plongée dans Node depuis PHP',
        'description' => 'Node expliqué aux développeurs et développeuses PHP.',
        'youtube' => 'https://www.youtube.com/embed/QHk6t6YBa5o',
        'subtext' => 'ForumPHP',
    ],
    [
        'title' => '🇫🇷 Middlewares : un vieux concept au coeur des nouvelles architectures',
        'description' => 'Une introduction au design pattern du middleware HTTP en PHP.',
        'youtube' => 'https://www.youtube.com/embed/Qma1HZ3gsu8',
        'subtext' => 'ForumPHP, phpCE',
    ],
    [
        'title' => '🇫🇷 Lancez-vous dans l\'open source',
        'description' => 'Comment affronter les 2 grands problèmes de l\'open source.',
        'youtube' => 'https://www.youtube.com/embed/HTx80GS66e0',
        'subtext' => 'PHPTour',
    ],
]
@endphp

@section('content')

    <header class="max-w-2xl">
        <h1 class="text-4xl font-bold tracking-tight text-zinc-800 dark:text-zinc-100 sm:text-5xl">
            Talks, podcasts and videos
        </h1>
        <ul role="list" class="mt-8 text-zinc-600 flex gap-8">
            <li>
                <a class="group flex items-center gap-2 text-sm font-medium transition hover:text-teal-500" href="https://www.youtube.com/channel/UCJk94lia4VROQWTI_iPpEXw">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-none fill-zinc-500 transition group-hover:fill-teal-500" viewBox="0 0 512 512"><path d="M508.64 148.79c0-45-33.1-81.2-74-81.2C379.24 65 322.74 64 265 64h-18c-57.6 0-114.2 1-169.6 3.6C36.6 67.6 3.5 104 3.5 149 1 184.59-.06 220.19 0 255.79q-.15 53.4 3.4 106.9c0 45 33.1 81.5 73.9 81.5 58.2 2.7 117.9 3.9 178.6 3.8q91.2.3 178.6-3.8c40.9 0 74-36.5 74-81.5 2.4-35.7 3.5-71.3 3.4-107q.34-53.4-3.26-106.9zM207 353.89v-196.5l145 98.2z"/></svg>
                    <span>@mnapoli_ on YouTube</span>
                </a>
            </li>
            <li>
                <a class="group flex items-center gap-2 text-sm font-medium transition hover:text-teal-500" href="https://www.twitch.tv/mnapoli">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-none fill-zinc-500 transition group-hover:fill-teal-500" viewBox="0 0 512 512"><path d="M80 32l-32 80v304h96v64h64l64-64h80l112-112V32zm336 256l-64 64h-96l-64 64v-64h-80V80h304z"/><path d="M320 143h48v129h-48zM208 143h48v129h-48z"/></svg>
                    <span>@mnapoli on Twitch</span>
                </a>
            </li>
        </ul>
    </header>

    <ul role="list" class="mt-10 sm:mt-14 -mx-4 sm:-mx-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 max-w-sm sm:max-w-full">

        @foreach($videos as $video)

            <li class="hover:bg-zinc-50 sm:rounded-2xl px-4 sm:px-6 py-6">
                <h2 class="text-base w-full font-semibold text-zinc-800">
                    {{ $video['title'] }}
                </h2>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">
                    {!! $video['description'] !!}
                </p>
                <iframe src="{{ $video['youtube'] }}"
                        frameborder="0" allowfullscreen
                        class="relative z-10 mt-4 w-full object-cover aspect-video rounded-2xl shadow-lg"></iframe>
                <p class="mt-2 text-xs font-semibold text-zinc-400">
                    {{ $video['subtext'] }}
                </p>
            </li>

        @endforeach

    </ul>

@endsection
