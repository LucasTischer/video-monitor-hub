@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-cyan-300 bg-slate-900 py-2 pe-4 ps-3 text-start text-base font-medium text-white transition duration-150 ease-in-out focus:border-cyan-400 focus:bg-slate-900 focus:text-slate-100 focus:outline-none'
            : 'block w-full border-l-4 border-transparent py-2 pe-4 ps-3 text-start text-base font-medium text-slate-400 transition duration-150 ease-in-out hover:border-slate-700 hover:bg-slate-900 hover:text-slate-100 focus:border-slate-700 focus:bg-slate-900 focus:text-slate-100 focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
