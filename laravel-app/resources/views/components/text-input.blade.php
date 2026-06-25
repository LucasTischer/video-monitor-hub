@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md border-slate-700 bg-slate-950 text-slate-100 shadow-sm focus:border-cyan-400 focus:ring-cyan-400 disabled:opacity-50']) }}>
