<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold leading-tight text-white">
                {{ __('Cameras') }}
            </h2>
            <a href="{{ route('cameras.create') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-cyan-300 text-slate-950 transition hover:bg-cyan-200 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 focus:ring-offset-slate-950" title="{{ __('Add camera') }}" aria-label="{{ __('Add camera') }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-flash-message class="mb-6" :message="session('status')" />
            @endif

            <div class="overflow-hidden rounded-lg border border-slate-800 bg-slate-900 shadow-sm">
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-800">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Name') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Location') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Stream URL') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Status') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Videos') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                @forelse ($cameras as $camera)
                                    <tr>
                                        <td class="px-3 py-3 text-sm font-medium text-white">{{ $camera->name }}</td>
                                        <td class="px-3 py-3 text-sm text-slate-300">{{ $camera->location ?? __('Unassigned') }}</td>
                                        <td class="max-w-xs truncate px-3 py-3 text-sm text-slate-300">{{ $camera->stream_url }}</td>
                                        <td class="px-3 py-3 text-sm">
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $camera->is_active ? 'bg-green-900/60 text-green-100' : 'bg-slate-900 text-slate-200' }}">
                                                {{ $camera->is_active ? __('Active') : __('Inactive') }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-slate-300">{{ $camera->videos_count }}</td>
                                        <td class="px-3 py-3 text-right text-sm">
                                            <div class="flex justify-end gap-1">
                                                <x-icon-link :href="route('cameras.show', $camera)" :label="__('View camera')">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                                                        <circle cx="12" cy="12" r="3"/>
                                                    </svg>
                                                </x-icon-link>
                                                @can('update', $camera)
                                                    <x-icon-link :href="route('cameras.edit', $camera)" :label="__('Edit camera')">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M12 20h9"/>
                                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/>
                                                        </svg>
                                                    </x-icon-link>
                                                @endcan
                                                @can('delete', $camera)
                                                    <form method="POST" action="{{ route('cameras.destroy', $camera) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-icon-button :label="__('Delete camera')">
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <path d="M3 6h18"/>
                                                                <path d="M8 6V4h8v2"/>
                                                                <path d="M19 6l-1 14H6L5 6"/>
                                                                <path d="M10 11v5M14 11v5"/>
                                                            </svg>
                                                        </x-icon-button>
                                                    </form>
                                                @endcan
                                                @cannot('delete', $camera)
                                                    @cannot('update', $camera)
                                                        <span class="inline-flex h-8 items-center px-2 text-xs text-slate-500">{{ __('View only') }}</span>
                                                    @endcannot
                                                @endcannot
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-400">
                                            {{ __('No cameras registered yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
