@props(['label'])

<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 dark:text-slate-400 dark:hover:bg-red-950 dark:hover:text-red-300']) }}
    title="{{ $label }}"
    aria-label="{{ $label }}"
>
    {{ $slot }}
</button>
