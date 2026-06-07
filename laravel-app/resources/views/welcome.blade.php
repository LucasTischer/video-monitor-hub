<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>VMHub</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <main class="min-h-screen bg-slate-950 text-white">
            <div class="mx-auto flex min-h-screen max-w-6xl flex-col px-6 py-6">
                <nav class="flex items-center justify-between">
                    <a href="/" class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-white text-slate-950">
                            <x-application-logo class="h-6 w-6" />
                        </span>
                        <span class="text-lg font-semibold">VMHub</span>
                    </a>

                    @if (Route::has('login'))
                        <div class="flex items-center gap-2 text-sm">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="rounded-md px-3 py-2 text-slate-300 transition hover:bg-white/10 hover:text-white">{{ __('Dashboard') }}</a>
                            @else
                                <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-slate-300 transition hover:bg-white/10 hover:text-white">{{ __('Log in') }}</a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="rounded-md border border-slate-700 px-3 py-2 text-slate-100 transition hover:border-cyan-300 hover:text-cyan-200">{{ __('Register') }}</a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </nav>

                <section class="grid flex-1 items-center gap-10 py-16 lg:grid-cols-[1fr_0.85fr]">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-wider text-cyan-300">Video Monitor Hub</p>
                        <h1 class="mt-4 max-w-3xl text-4xl font-semibold leading-tight sm:text-5xl">
                            Monitor cameras and review motion recordings from one focused dashboard.
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-7 text-slate-300">
                            VMHub connects a Laravel camera management interface with a Python/OpenCV processor that detects motion, saves recordings, and reports them back to the app.
                        </p>

                        <div class="mt-8 flex flex-wrap items-center gap-3">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="rounded-md bg-cyan-300 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-200">{{ __('Open dashboard') }}</a>
                            @else
                                <a href="{{ route('login') }}" class="rounded-md bg-cyan-300 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-200">{{ __('Log in') }}</a>
                                <a href="{{ route('register') }}" class="rounded-md border border-slate-700 px-4 py-2 text-sm font-semibold text-white transition hover:border-cyan-300 hover:text-cyan-200">{{ __('Create account') }}</a>
                            @endauth
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-800 bg-slate-900 p-5 shadow-2xl">
                        <div class="grid gap-3">
                            <div class="rounded-md border border-slate-800 bg-slate-950 p-4">
                                <p class="text-sm text-slate-400">{{ __('Cameras') }}</p>
                                <p class="mt-2 text-3xl font-semibold">3</p>
                            </div>
                            <div class="rounded-md border border-slate-800 bg-slate-950 p-4">
                                <p class="text-sm text-slate-400">{{ __('Recent recordings') }}</p>
                                <p class="mt-2 text-3xl font-semibold">12</p>
                            </div>
                            <div class="rounded-md border border-slate-800 bg-slate-950 p-4">
                                <p class="text-sm text-slate-400">{{ __('Processor') }}</p>
                                <p class="mt-2 text-sm font-medium text-cyan-300">{{ __('Laravel API + Python/OpenCV') }}</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </body>
</html>
