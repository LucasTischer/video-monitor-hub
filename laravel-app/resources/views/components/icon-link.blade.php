@props(['label'])

<a
    {{ $attributes->merge(['class' => 'inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-900 hover:text-slate-100 focus:outline-none focus:ring-2 focus:ring-cyan-400']) }}
    title="{{ $label }}"
    aria-label="{{ $label }}"
>
    {{ $slot }}
</a>
