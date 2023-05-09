@extends('layout')

@section('content')

    <header class="lg:flex justify-between gap-12">

        <div class="max-w-xl pt-6">

            <div class="aspect-[9/10] w-20 overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800 sm:rounded-2xl -rotate-2 hover:rotate-2 transition-transform">
                <img alt="Matthieu Napoli" loading="lazy" decoding="async" data-nimg="1"
                     class="absolute inset-0 h-full w-full object-cover"
                     src="/images/profile.jpg">
            </div>

            <div class="mt-6 text-zinc-600 dark:text-zinc-400 prose prose-lg">
                <p>
                    Hi! I’m <strong>Matthieu Napoli</strong>, open-source developer and <a href="https://null.tc">consultant</a>.
                </p>
                <p>
                    I’m the creator of <a href="https://bref.sh">Bref</a>, the serverless framework for PHP. I believe running modern applications should be simpler, and I’m trying to do my part!
                </p>
            </div>

            <div class="mt-8 flex gap-6 items-center">
                <a class="group -m-1 p-1" aria-label="Follow on Twitter" href="https://twitter.com/matthieunapoli">
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="h-6 w-6 fill-zinc-500 transition group-hover:fill-teal-500 dark:fill-teal-400 dark:group-hover:fill-teal-300"><path d="M20.055 7.983c.011.174.011.347.011.523 0 5.338-3.92 11.494-11.09 11.494v-.003A10.755 10.755 0 0 1 3 18.186c.308.038.618.057.928.058a7.655 7.655 0 0 0 4.841-1.733c-1.668-.032-3.13-1.16-3.642-2.805a3.753 3.753 0 0 0 1.76-.07C5.07 13.256 3.76 11.6 3.76 9.676v-.05a3.77 3.77 0 0 0 1.77.505C3.816 8.945 3.288 6.583 4.322 4.737c1.98 2.524 4.9 4.058 8.034 4.22a4.137 4.137 0 0 1 1.128-3.86A3.807 3.807 0 0 1 19 5.274a7.657 7.657 0 0 0 2.475-.98c-.29.934-.9 1.729-1.713 2.233A7.54 7.54 0 0 0 22 5.89a8.084 8.084 0 0 1-1.945 2.093Z"></path></svg>
                </a>
                <a class="group -m-1 p-1" aria-label="Follow on GitHub" href="https://github.com/mnapoli">
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="h-6 w-6 fill-zinc-500 transition group-hover:fill-teal-500 dark:fill-teal-400 dark:group-hover:fill-teal-300"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.475 2 2 6.588 2 12.253c0 4.537 2.862 8.369 6.838 9.727.5.09.687-.218.687-.487 0-.243-.013-1.05-.013-1.91C7 20.059 6.35 18.957 6.15 18.38c-.113-.295-.6-1.205-1.025-1.448-.35-.192-.85-.667-.013-.68.788-.012 1.35.744 1.538 1.051.9 1.551 2.338 1.116 2.912.846.088-.666.35-1.115.638-1.371-2.225-.256-4.55-1.14-4.55-5.062 0-1.115.387-2.038 1.025-2.756-.1-.256-.45-1.307.1-2.717 0 0 .837-.269 2.75 1.051.8-.23 1.65-.346 2.5-.346.85 0 1.7.115 2.5.346 1.912-1.333 2.75-1.05 2.75-1.05.55 1.409.2 2.46.1 2.716.637.718 1.025 1.628 1.025 2.756 0 3.934-2.337 4.806-4.562 5.062.362.32.675.936.675 1.897 0 1.371-.013 2.473-.013 2.82 0 .268.188.589.688.486a10.039 10.039 0 0 0 4.932-3.74A10.447 10.447 0 0 0 22 12.253C22 6.588 17.525 2 12 2Z"></path></svg>
                </a>
                <a class="group -m-1 p-1" aria-label="Follow on LinkedIn" href="https://linkedin.com/in/matthieunapoli">
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="h-6 w-6 fill-zinc-500 transition group-hover:fill-teal-500 dark:fill-teal-400 dark:group-hover:fill-teal-300"><path d="M18.335 18.339H15.67v-4.177c0-.996-.02-2.278-1.39-2.278-1.389 0-1.601 1.084-1.601 2.205v4.25h-2.666V9.75h2.56v1.17h.035c.358-.674 1.228-1.387 2.528-1.387 2.7 0 3.2 1.778 3.2 4.091v4.715zM7.003 8.575a1.546 1.546 0 01-1.548-1.549 1.548 1.548 0 111.547 1.549zm1.336 9.764H5.666V9.75H8.34v8.589zM19.67 3H4.329C3.593 3 3 3.58 3 4.297v15.406C3 20.42 3.594 21 4.328 21h15.338C20.4 21 21 20.42 21 19.703V4.297C21 3.58 20.4 3 19.666 3h.003z"></path></svg>
                </a>
                <a class="group -m-1 p-1" aria-label="Follow on Mastodon" href="https://phpc.social/@mnapoli">
                    <svg aria-hidden="true" class="h-5 w-5 fill-zinc-500 transition group-hover:fill-teal-500 dark:fill-teal-400 dark:group-hover:fill-teal-300" viewBox="0 0 216.4144 232.00976">
                        <path d="M211.80734 139.0875c-3.18125 16.36625-28.4925 34.2775-57.5625 37.74875-15.15875 1.80875-30.08375 3.47125-45.99875 2.74125-26.0275-1.1925-46.565-6.2125-46.565-6.2125 0 2.53375.15625 4.94625.46875 7.2025 3.38375 25.68625 25.47 27.225 46.39125 27.9425 21.11625.7225 39.91875-5.20625 39.91875-5.20625l.8675 19.09s-14.77 7.93125-41.08125 9.39c-14.50875.7975-32.52375-.365-53.50625-5.91875C9.23234 213.82 1.40609 165.31125.20859 116.09125c-.365-14.61375-.14-28.39375-.14-39.91875 0-50.33 32.97625-65.0825 32.97625-65.0825C49.67234 3.45375 78.20359.2425 107.86484 0h.72875c29.66125.2425 58.21125 3.45375 74.8375 11.09 0 0 32.975 14.7525 32.975 65.0825 0 0 .41375 37.13375-4.59875 62.915"/>
                        <path fill="#fff" d="M177.50984 80.077v60.94125h-24.14375v-59.15c0-12.46875-5.24625-18.7975-15.74-18.7975-11.6025 0-17.4175 7.5075-17.4175 22.3525v32.37625H96.20734V85.42325c0-14.845-5.81625-22.3525-17.41875-22.3525-10.49375 0-15.74 6.32875-15.74 18.7975v59.15H38.90484V80.077c0-12.455 3.17125-22.3525 9.54125-29.675 6.56875-7.3225 15.17125-11.07625 25.85-11.07625 12.355 0 21.71125 4.74875 27.8975 14.2475l6.01375 10.08125 6.015-10.08125c6.185-9.49875 15.54125-14.2475 27.8975-14.2475 10.6775 0 19.28 3.75375 25.85 11.07625 6.36875 7.3225 9.54 17.22 9.54 29.675"/>
                    </svg>
                </a>
            </div>
        </div>

        <div class="hidden lg:block pr-4">
            <img loading="lazy" class="aspect-square rotate-3 rounded-2xl bg-zinc-100 object-cover w-96 shadow-lg" src="/images/home-cover.jpg">
        </div>
    </header>

    <div class="mt-16 lg:mt-24 mx-auto grid max-w-xl grid-cols-1 gap-y-20 lg:max-w-none lg:grid-cols-2">

        <div class="flex flex-col gap-16">
            @foreach (array_slice($posts, 0, 6) as $post)
                <article class="group relative flex flex-col items-start">
                    <h2 class="text-base font-semibold tracking-tight text-zinc-800 dark:text-zinc-100">
                        <div class="absolute -inset-x-4 -inset-y-6 z-0 scale-95 bg-zinc-50 opacity-0 transition group-hover:scale-100 group-hover:opacity-100 dark:bg-zinc-800/50 sm:-inset-x-6 sm:rounded-2xl"></div>
                        <a href="{{ $post instanceof \App\ExternalPost ? $post->url : route('post', ['slug' => $post->slug]) }}">
                            <span class="absolute -inset-x-4 -inset-y-6 z-20 sm:-inset-x-6 sm:rounded-2xl"></span>
                            <span class="relative z-10">{{ $post->title }}</span>
                        </a>
                    </h2>
                    <time class="relative z-10 order-first mb-3 flex items-center text-sm text-zinc-400 dark:text-zinc-500 pl-3.5" datetime="{{ $post->date->toDateString() }}">
                        <span class="absolute inset-y-0 left-0 flex items-center" aria-hidden="true">
                            <span class="h-4 w-0.5 rounded-full bg-zinc-200 dark:bg-zinc-500"></span>
                        </span>{{ $post->date->format('F Y') }}
                    </time>
                    <p class="relative z-10 mt-2 text-sm text-zinc-600 dark:text-zinc-400 leading-6 line-clamp-3">
                        {!! strip_tags($post->extract) !!}
                    </p>
                    <div aria-hidden="true" class="relative z-10 mt-4 flex items-center text-sm font-medium text-teal-500">
                        Read article
                        @if ($post instanceof \App\ExternalPost)
                            on {{ parse_url($post->url)['host'] }}
                        @endif
                        <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="ml-1 h-4 w-4 stroke-current">
                            <path d="M6.75 5.75 9.25 8l-2.5 2.25" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>
                </article>
            @endforeach

            <div class="mt-2">
                <a href="/articles" class="flex items-center font-medium text-teal-500">
                    More articles
                    <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="ml-1 h-5 w-5 stroke-current">
                        <path d="M6.75 5.75 9.25 8l-2.5 2.25" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </a>
            </div>
        </div>

        <div class="order-first lg:order-last space-y-10 lg:pl-16 xl:pl-24">
            <div class="rounded-2xl border border-zinc-100 p-6 dark:border-zinc-700/40">
                <h2 class="flex items-center text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="h-6 w-6 flex-none"><path d="M2.75 9.75a3 3 0 0 1 3-3h12.5a3 3 0 0 1 3 3v8.5a3 3 0 0 1-3 3H5.75a3 3 0 0 1-3-3v-8.5Z" class="fill-zinc-100 stroke-zinc-400 dark:fill-zinc-100/10 dark:stroke-zinc-500"></path><path d="M3 14.25h6.249c.484 0 .952-.002 1.316.319l.777.682a.996.996 0 0 0 1.316 0l.777-.682c.364-.32.832-.319 1.316-.319H21M8.75 6.5V4.75a2 2 0 0 1 2-2h2.5a2 2 0 0 1 2 2V6.5" class="stroke-zinc-400 dark:stroke-zinc-500"></path>
                    </svg>
                    <span class="ml-3">Work</span>
                </h2>
                <ol class="mt-6 space-y-4">
                    <li class="flex gap-4">
                        <div class="relative mt-1 flex h-10 w-10 flex-none items-center justify-center rounded-full shadow-md shadow-zinc-800/5 ring-1 ring-zinc-900/5 dark:border dark:border-zinc-700/50 dark:bg-zinc-800 dark:ring-0 overflow-hidden">
                            <img alt="Null" loading="lazy" decoding="async" data-nimg="1" class="h-8 w-8" style="color: transparent;" src="/images/companies/null.svg">
                        </div>
                        <dl class="flex flex-auto flex-wrap gap-x-2">
                            <dt class="sr-only">Company</dt>
                            <dd class="w-full flex-none text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                <a href="https://null.tc">Null</a>
                            </dd>
                            <dt class="sr-only">Role</dt>
                            <dd class="text-xs text-zinc-500 dark:text-zinc-400">Founder / Indie Hacker</dd>
                            <dt class="sr-only">Date</dt>
                            <dd class="ml-auto text-xs text-zinc-400 dark:text-zinc-500">
                                <time datetime="2018">2018</time>
                                <span aria-hidden="true">—</span>
                                <time datetime="2023">Present</time>
                            </dd>
                        </dl>
                    </li>
                    <li class="flex gap-4">
                        <div class="relative mt-1 flex h-10 w-10 flex-none items-center justify-center rounded-full shadow-md shadow-zinc-800/5 ring-1 ring-zinc-900/5 dark:border dark:border-zinc-700/50 dark:bg-zinc-800 dark:ring-0 overflow-hidden">
                            <img alt="Serverless" loading="lazy" decoding="async" data-nimg="1" class="h-5 w-5" style="color: transparent;" src="/images/companies/serverless.svg">
                        </div>
                        <dl class="flex flex-auto flex-wrap gap-x-2">
                            <dt class="sr-only">Company</dt>
                            <dd class="w-full flex-none text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                <a href="https://serverless.com">Serverless</a>
                            </dd>
                            <dt class="sr-only">Role</dt>
                            <dd class="text-xs text-zinc-500 dark:text-zinc-400">Open-Source Product Manager</dd>
                            <dt class="sr-only">Date</dt>
                            <dd class="ml-auto text-xs text-zinc-400 dark:text-zinc-500">
                                <time datetime="2021">2021</time>
                                <span aria-hidden="true">—</span>
                                <time datetime="2022">2022</time>
                            </dd>
                        </dl>
                    </li>
                    <li class="flex gap-4">
                        <div class="relative mt-1 flex h-10 w-10 flex-none items-center justify-center rounded-full shadow-md shadow-zinc-800/5 ring-1 ring-zinc-900/5 dark:border dark:border-zinc-700/50 dark:bg-zinc-800 dark:ring-0 overflow-hidden">
                            <img alt="Wizaplace" loading="lazy" decoding="async" data-nimg="1" class="h-7 w-7 rounded-full" style="color: transparent;" src="/images/companies/wizaplace.png">
                        </div>
                        <dl class="flex flex-auto flex-wrap gap-x-2">
                            <dt class="sr-only">Company</dt>
                            <dd class="w-full flex-none text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                <a href="https://wizaplace.com">Wizaplace</a>
                            </dd>
                            <dt class="sr-only">Role</dt>
                            <dd class="text-xs text-zinc-500 dark:text-zinc-400">CTO</dd>
                            <dt class="sr-only">Date</dt>
                            <dd class="ml-auto text-xs text-zinc-400 dark:text-zinc-500">
                                <time datetime="2015">2015</time>
                                <span aria-hidden="true">—</span>
                                <time datetime="2018">2018</time>
                            </dd>
                        </dl>
                    </li>
                    <li class="flex gap-4">
                        <div class="relative mt-1 flex h-10 w-10 flex-none items-center justify-center rounded-full shadow-md shadow-zinc-800/5 ring-1 ring-zinc-900/5 dark:border dark:border-zinc-700/50 dark:bg-zinc-800 dark:ring-0 overflow-hidden">
                            <img alt="Matomo" loading="lazy" decoding="async" data-nimg="1" class="h-7 w-7 rounded-full" style="color: transparent;" src="/images/companies/piw.jpg">
                        </div>
                        <dl class="flex flex-auto flex-wrap gap-x-2">
                            <dt class="sr-only">Company</dt>
                            <dd class="w-full flex-none text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                <a href="https://matomo.org/">Matomo</a>
                            </dd>
                            <dt class="sr-only">Role</dt>
                            <dd class="text-xs text-zinc-500 dark:text-zinc-400">Senior Software Engineer</dd>
                            <dt class="sr-only">Date</dt>
                            <dd class="ml-auto text-xs text-zinc-400 dark:text-zinc-500">
                                <time datetime="2014">2014</time>
                                <span aria-hidden="true">—</span>
                                <time datetime="2015">2015</time>
                            </dd>
                        </dl>
                    </li>
                    <li class="flex gap-4">
                        <div class="relative mt-1 flex h-10 w-10 flex-none items-center justify-center rounded-full shadow-md shadow-zinc-800/5 ring-1 ring-zinc-900/5 dark:border dark:border-zinc-700/50 dark:bg-zinc-800 dark:ring-0 overflow-hidden">
                            <img alt="My C-Sense" loading="lazy" decoding="async" data-nimg="1" class="h-7 w-7 rounded-full" style="color: transparent;" src="/images/companies/myc-sense.png">
                        </div>
                        <dl class="flex flex-auto flex-wrap gap-x-2">
                            <dt class="sr-only">Company</dt>
                            <dd class="w-full flex-none text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                <a href="https://www.linkedin.com/company/my-c-sense">My C-Sense</a>
                            </dd>
                            <dt class="sr-only">Role</dt>
                            <dd class="text-xs text-zinc-500 dark:text-zinc-400">Senior Software Engineer</dd>
                            <dt class="sr-only">Date</dt>
                            <dd class="ml-auto text-xs text-zinc-400 dark:text-zinc-500">
                                <time datetime="2012">2012</time>
                                <span aria-hidden="true">—</span>
                                <time datetime="2014">2014</time>
                            </dd>
                        </dl>
                    </li>
                    <li class="flex gap-4">
                        <div class="relative mt-1 flex h-10 w-10 flex-none items-center justify-center rounded-full shadow-md shadow-zinc-800/5 ring-1 ring-zinc-900/5 dark:border dark:border-zinc-700/50 dark:bg-zinc-800 dark:ring-0 overflow-hidden">
                            <img alt="Atos Worldline" loading="lazy" decoding="async" data-nimg="1" class="h-7 w-7 rounded-full" style="color: transparent;" src="/images/companies/atos.svg">
                        </div>
                        <dl class="flex flex-auto flex-wrap gap-x-2">
                            <dt class="sr-only">Company</dt>
                            <dd class="w-full flex-none text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                <a href="https://worldline.com">Atos Worldline</a>
                            </dd>
                            <dt class="sr-only">Role</dt>
                            <dd class="text-xs text-zinc-500 dark:text-zinc-400">Software Engineer</dd>
                            <dt class="sr-only">Date</dt>
                            <dd class="ml-auto text-xs text-zinc-400 dark:text-zinc-500">
                                <time datetime="2011">2011</time>
                                <span aria-hidden="true">—</span>
                                <time datetime="2012">2012</time>
                            </dd>
                        </dl>
                    </li>
                    <li class="flex gap-4">
                        <div class="relative mt-1 flex h-10 w-10 flex-none items-center justify-center rounded-full shadow-md shadow-zinc-800/5 ring-1 ring-zinc-900/5 dark:border dark:border-zinc-700/50 dark:bg-zinc-800 dark:ring-0 overflow-hidden">
                            <img alt="My C-Sense" loading="lazy" decoding="async" data-nimg="1" class="h-7 w-7 rounded-full" style="color: transparent;" src="/images/companies/myc-sense.png">
                        </div>
                        <dl class="flex flex-auto flex-wrap gap-x-2">
                            <dt class="sr-only">Company</dt>
                            <dd class="w-full flex-none text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                <a href="https://www.linkedin.com/company/my-c-sense">My C-Sense</a>
                            </dd>
                            <dt class="sr-only">Role</dt>
                            <dd class="text-xs text-zinc-500 dark:text-zinc-400">Software Engineer</dd>
                            <dt class="sr-only">Date</dt>
                            <dd class="ml-auto text-xs text-zinc-400 dark:text-zinc-500">
                                <time datetime="2009">2009</time>
                                <span aria-hidden="true">—</span>
                                <time datetime="2010">2010</time>
                            </dd>
                        </dl>
                    </li>
                </ol>
                <a class="inline-flex items-center gap-2 justify-center rounded-md py-2 px-3 text-sm outline-offset-2 transition active:transition-none bg-zinc-50 font-medium text-zinc-900 hover:bg-zinc-100 active:bg-zinc-100 active:text-zinc-900/60 dark:bg-zinc-800/50 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-zinc-50 dark:active:bg-zinc-800/50 dark:active:text-zinc-50/70 group mt-6 w-full"
                   href="/#">
                    LinkedIn
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>
        </div>

    </div>

@endsection
