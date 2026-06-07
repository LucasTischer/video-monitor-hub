<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-md border border-transparent bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 active:bg-slate-950 dark:bg-cyan-300 dark:text-slate-950 dark:hover:bg-cyan-200 dark:focus:ring-offset-slate-900']) }}>
    {{ $slot }}
</button>
