@props(['label'])

<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-red-950 hover:text-red-300 focus:outline-none focus:ring-2 focus:ring-red-500']) }}
    title="{{ $label }}"
    aria-label="{{ $label }}"
>
    {{ $slot }}
</button>
