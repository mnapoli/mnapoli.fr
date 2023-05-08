<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full antialiased js-focus-visible">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Meta --}}
    <title>@yield('title', config('journal.title')) - Matthieu Napoli</title>
    <meta name="description" content="@yield('description', config('journal.description'))"/>
    <meta property="og:title" content="@yield('title', config('journal.title'))"/>
    <meta property="og:description" content="@yield('description', config('journal.description'))"/>
    <meta property="og:locale" content="{{ app()->getLocale() }}"/>
    <meta property="og:site_name" content="{{ config('journal.title') }}"/>
    <meta property="og:locale" content="en_US" />
    @if (View::hasSection('metaImage'))
        <meta property="og:image" content="@yield('metaImage')"/>
        <meta name="twitter:card" content="summary_large_image"/>
    @else
        <meta name="twitter:card" content="summary"/>
    @endif
    @if (config('journal.twitter'))
        <meta name="twitter:site" content="{{ config('journal.twitter') }}"/>
    @endif

    <link href="/atom.xml" rel="alternate" title="Blog Matthieu Napoli" type="application/atom+xml">
    <link href="{{ mix('assets/css/app.css') }}" rel="stylesheet">
    <link href="/favicon.ico" rel="icon">

    <script defer data-domain="mnapoli.fr" src="https://plausible.io/js/script.outbound-links.js"></script>
</head>
@php

$menu = [
    '/' => 'Home',
    '/articles' => 'Articles',
    '/projects' => 'Projects',
    '/speaking' => 'Speaking',
]

@endphp
<body class="flex h-full flex-col bg-zinc-50 dark:bg-black">

    <div class="fixed inset-0 flex justify-center sm:px-8">
        <div class="flex w-full max-w-7xl lg:px-8">
            <div class="w-full bg-white ring-1 ring-zinc-100 dark:bg-zinc-900 dark:ring-zinc-300/20"></div>
        </div>
    </div>

    <div class="relative">

        <header class="relative z-50 flex flex-col">
            <div class="order-last mt-[calc(theme(spacing.12)-theme(spacing.3))]"></div>
            <div class="top-0 z-10 h-16 pt-6">
                <div class="sm:px-8 top-[var(--header-top,theme(spacing.6))] w-full">
                    <div class="mx-auto max-w-7xl lg:px-8">
                        <div class="relative px-4 sm:px-8 lg:px-12">
                            <div class="mx-auto max-w-2xl lg:max-w-5xl">
                                <div class="relative flex gap-4">
                                    <div class="flex flex-1">
                                        @if (($home ?? false) === false)
                                            <div class="h-11 w-11 rounded-full bg-white/90 p-0.5 shadow-lg shadow-zinc-800/5 ring-1 ring-zinc-900/5 backdrop-blur dark:bg-zinc-800/90 dark:ring-white/10">
                                                <a aria-label="Home" class="pointer-events-auto" href="/"><img alt="Matthieu Napoli" class="rounded-full bg-zinc-100 object-cover dark:bg-zinc-800 h-10 w-10" style="color:transparent" sizes="2.25rem" src="/images/profile.jpg"></a>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-1 justify-end md:justify-center">
                                        <nav class="pointer-events-auto">
                                            <ul class="flex rounded-full bg-white/90 px-3 text-sm font-medium text-zinc-800 shadow-lg shadow-zinc-800/5 ring-1 ring-zinc-900/5 backdrop-blur dark:bg-zinc-800/90 dark:text-zinc-200 dark:ring-white/10">
                                                @foreach($menu as $url => $text)
                                                    <li>
                                                        <a class="relative block px-3 py-3 transition hover:text-teal-500 dark:hover:text-teal-400 {{ request()->is($url === '/' ? $url : ltrim($url, '/')) ? 'text-teal-500' : '' }}" href="{{ $url }}">
                                                            {{ $text }}
                                                            @if (request()->is($url === '/' ? $url : ltrim($url, '/')))
                                                                <span class="absolute inset-x-1 -bottom-px h-px bg-gradient-to-r from-teal-500/0 via-teal-500/40 to-teal-500/0"></span>
                                                            @endif
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </nav>
                                    </div>
                                    <div class="hidden sm:flex justify-end md:flex-1 gap-3 lg:gap-6 items-center">
                                        <a class="group -m-1 p-1" aria-label="Follow on Twitter" href="https://twitter.com/matthieunapoli">
                                            <svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 lg:h-6 w-5 lg:w-6 fill-zinc-500 transition group-hover:fill-zinc-600 dark:fill-zinc-400 dark:group-hover:fill-zinc-300"><path d="M20.055 7.983c.011.174.011.347.011.523 0 5.338-3.92 11.494-11.09 11.494v-.003A10.755 10.755 0 0 1 3 18.186c.308.038.618.057.928.058a7.655 7.655 0 0 0 4.841-1.733c-1.668-.032-3.13-1.16-3.642-2.805a3.753 3.753 0 0 0 1.76-.07C5.07 13.256 3.76 11.6 3.76 9.676v-.05a3.77 3.77 0 0 0 1.77.505C3.816 8.945 3.288 6.583 4.322 4.737c1.98 2.524 4.9 4.058 8.034 4.22a4.137 4.137 0 0 1 1.128-3.86A3.807 3.807 0 0 1 19 5.274a7.657 7.657 0 0 0 2.475-.98c-.29.934-.9 1.729-1.713 2.233A7.54 7.54 0 0 0 22 5.89a8.084 8.084 0 0 1-1.945 2.093Z"></path></svg>
                                        </a>
                                        <a class="group -m-1 p-1" aria-label="Follow on GitHub" href="https://github.com/mnapoli">
                                            <svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 lg:h-6 w-5 lg:w-6 fill-zinc-500 transition group-hover:fill-zinc-600 dark:fill-zinc-400 dark:group-hover:fill-zinc-300"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.475 2 2 6.588 2 12.253c0 4.537 2.862 8.369 6.838 9.727.5.09.687-.218.687-.487 0-.243-.013-1.05-.013-1.91C7 20.059 6.35 18.957 6.15 18.38c-.113-.295-.6-1.205-1.025-1.448-.35-.192-.85-.667-.013-.68.788-.012 1.35.744 1.538 1.051.9 1.551 2.338 1.116 2.912.846.088-.666.35-1.115.638-1.371-2.225-.256-4.55-1.14-4.55-5.062 0-1.115.387-2.038 1.025-2.756-.1-.256-.45-1.307.1-2.717 0 0 .837-.269 2.75 1.051.8-.23 1.65-.346 2.5-.346.85 0 1.7.115 2.5.346 1.912-1.333 2.75-1.05 2.75-1.05.55 1.409.2 2.46.1 2.716.637.718 1.025 1.628 1.025 2.756 0 3.934-2.337 4.806-4.562 5.062.362.32.675.936.675 1.897 0 1.371-.013 2.473-.013 2.82 0 .268.188.589.688.486a10.039 10.039 0 0 0 4.932-3.74A10.447 10.447 0 0 0 22 12.253C22 6.588 17.525 2 12 2Z"></path></svg>
                                        </a>
                                        <a class="group -m-1 p-1" aria-label="Follow on LinkedIn" href="https://linkedin.com/in/matthieunapoli">
                                            <svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 lg:h-6 w-5 lg:w-6 fill-zinc-500 transition group-hover:fill-zinc-600 dark:fill-zinc-400 dark:group-hover:fill-zinc-300"><path d="M18.335 18.339H15.67v-4.177c0-.996-.02-2.278-1.39-2.278-1.389 0-1.601 1.084-1.601 2.205v4.25h-2.666V9.75h2.56v1.17h.035c.358-.674 1.228-1.387 2.528-1.387 2.7 0 3.2 1.778 3.2 4.091v4.715zM7.003 8.575a1.546 1.546 0 01-1.548-1.549 1.548 1.548 0 111.547 1.549zm1.336 9.764H5.666V9.75H8.34v8.589zM19.67 3H4.329C3.593 3 3 3.58 3 4.297v15.406C3 20.42 3.594 21 4.328 21h15.338C20.4 21 21 20.42 21 19.703V4.297C21 3.58 20.4 3 19.666 3h.003z"></path></svg>
                                        </a>
                                        <a class="group -m-1 p-1" aria-label="Follow on Mastodon" href="https://phpc.social/@mnapoli">
                                            <svg aria-hidden="true" class="h-4 lg:h-5 w-4 lg:w-5 fill-zinc-500 transition group-hover:fill-zinc-600 dark:fill-zinc-400 dark:group-hover:fill-zinc-300" viewBox="0 0 216.4144 232.00976">
                                                <path d="M211.80734 139.0875c-3.18125 16.36625-28.4925 34.2775-57.5625 37.74875-15.15875 1.80875-30.08375 3.47125-45.99875 2.74125-26.0275-1.1925-46.565-6.2125-46.565-6.2125 0 2.53375.15625 4.94625.46875 7.2025 3.38375 25.68625 25.47 27.225 46.39125 27.9425 21.11625.7225 39.91875-5.20625 39.91875-5.20625l.8675 19.09s-14.77 7.93125-41.08125 9.39c-14.50875.7975-32.52375-.365-53.50625-5.91875C9.23234 213.82 1.40609 165.31125.20859 116.09125c-.365-14.61375-.14-28.39375-.14-39.91875 0-50.33 32.97625-65.0825 32.97625-65.0825C49.67234 3.45375 78.20359.2425 107.86484 0h.72875c29.66125.2425 58.21125 3.45375 74.8375 11.09 0 0 32.975 14.7525 32.975 65.0825 0 0 .41375 37.13375-4.59875 62.915"/>
                                                <path fill="#fff" d="M177.50984 80.077v60.94125h-24.14375v-59.15c0-12.46875-5.24625-18.7975-15.74-18.7975-11.6025 0-17.4175 7.5075-17.4175 22.3525v32.37625H96.20734V85.42325c0-14.845-5.81625-22.3525-17.41875-22.3525-10.49375 0-15.74 6.32875-15.74 18.7975v59.15H38.90484V80.077c0-12.455 3.17125-22.3525 9.54125-29.675 6.56875-7.3225 15.17125-11.07625 25.85-11.07625 12.355 0 21.71125 4.74875 27.8975 14.2475l6.01375 10.08125 6.015-10.08125c6.185-9.49875 15.54125-14.2475 27.8975-14.2475 10.6775 0 19.28 3.75375 25.85 11.07625 6.36875 7.3225 9.54 17.22 9.54 29.675"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="sm:px-8">
            <div class="mt-9 mx-auto max-w-7xl px-4 sm:px-8 lg:px-16">
                <div class="mx-auto max-w-2xl lg:max-w-5xl">
                    @yield('content')
                </div>
            </div>
        </main>

        <footer class="mt-32">
            <div class="sm:px-8">
                <div class="mx-auto max-w-7xl lg:px-8">
                    <div class="border-t border-zinc-100 pb-16 pt-10 dark:border-zinc-700/40">
                        <div class="relative px-4 sm:px-8 lg:px-12">
                            <div class="mx-auto max-w-2xl lg:max-w-5xl">
                                <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                                    <p class="text-sm text-zinc-600 dark:text-zinc-300 font-medium">
                                        <a href="https://github.com/mnapoli/mnapoli.github.io" class="underline transition hover:text-teal-500 dark:hover:text-teal-400">Source</a> - built with <a href="https://bref.sh" class="underline transition hover:text-teal-500 dark:hover:text-teal-400">Bref</a>
                                    </p>
                                    <p class="text-sm text-zinc-400 dark:text-zinc-500">
                                        © <!-- -->{{ date('Y') }}<!-- --> Matthieu Napoli
                                        -
                                        Links:
                                        <a href="https://null.tc" title="PHP consulting company">null</a>,
                                        <a href="https://bref.sh" title="Serverless PHP on AWS Lambda">Bref</a>,
                                        <a href="https://serverless-visually-explained.com" title="Learn serverless with visual animations and code examples">Serverless visual course</a>,
                                        <a href="https://port7777.com" title="Connect to AWS RDS databases via SSH tunnels">7777</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

    </div>

</body>
</html>
