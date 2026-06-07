@props(['label'])

<a
    {{ $attributes->merge(['class' => 'inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white']) }}
    title="{{ $label }}"
    aria-label="{{ $label }}"
>
    {{ $slot }}
</a>
