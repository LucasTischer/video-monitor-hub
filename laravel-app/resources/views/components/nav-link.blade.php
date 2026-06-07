@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-cyan-500 px-1 pt-1 text-sm font-medium leading-5 text-slate-900 transition duration-150 ease-in-out focus:outline-none dark:border-cyan-300 dark:text-white'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-slate-500 transition duration-150 ease-in-out hover:border-slate-300 hover:text-slate-800 focus:outline-none dark:text-slate-400 dark:hover:border-slate-600 dark:hover:text-slate-100';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
