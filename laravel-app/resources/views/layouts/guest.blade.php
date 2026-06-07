<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>VMHub</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="flex min-h-screen items-center justify-center bg-slate-950 px-4 py-8">
            <div class="grid w-full max-w-5xl overflow-hidden rounded-lg border border-slate-800 bg-slate-900 shadow-2xl lg:grid-cols-[0.9fr_1.1fr]">
                <div class="hidden border-r border-slate-800 bg-slate-950 p-8 lg:flex lg:flex-col lg:justify-between">
                    <a href="/" class="flex items-center gap-3 text-white">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-white text-slate-950">
                            <x-application-logo class="h-6 w-6" />
                        </span>
                        <span class="text-lg font-semibold">VMHub</span>
                    </a>

                    <div>
                        <p class="text-sm font-medium uppercase tracking-wider text-cyan-300">Video Monitor Hub</p>
                        <h1 class="mt-3 text-3xl font-semibold text-white">Camera monitoring with motion-triggered recordings.</h1>
                        <p class="mt-4 text-sm leading-6 text-slate-400">
                            Manage cameras in Laravel, process streams with Python/OpenCV, and review recordings from one focused dashboard.
                        </p>
                    </div>
                </div>

                <div class="bg-white px-6 py-8 dark:bg-slate-900 sm:px-10">
                    <div class="mb-8 flex items-center gap-3 lg:hidden">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-slate-900 text-white dark:bg-white dark:text-slate-950">
                            <x-application-logo class="h-6 w-6" />
                        </span>
                        <span class="text-lg font-semibold text-slate-900 dark:text-white">VMHub</span>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
