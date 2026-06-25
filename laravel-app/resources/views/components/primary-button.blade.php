<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-md border border-transparent bg-cyan-300 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-950 transition hover:bg-cyan-200 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 focus:ring-offset-slate-950 active:bg-cyan-400']) }}>
    {{ $slot }}
</button>
