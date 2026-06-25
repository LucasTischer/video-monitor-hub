<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center rounded-md border border-slate-700 bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-200 shadow-sm transition ease-in-out duration-150 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 focus:ring-offset-slate-950 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
