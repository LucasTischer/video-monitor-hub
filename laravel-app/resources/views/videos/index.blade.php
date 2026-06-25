<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold leading-tight text-white">
            {{ __('Video Recordings') }}
        </h2>
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
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Camera') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Filename') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Started') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Duration') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Motion') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                @forelse ($videos as $video)
                                    <tr>
                                        <td class="px-3 py-3 text-sm font-medium text-white">{{ $video->camera->name }}</td>
                                        <td class="max-w-xs truncate px-3 py-3 text-sm text-slate-300">{{ $video->filename }}</td>
                                        <td class="px-3 py-3 text-sm text-slate-300">{{ $video->started_at?->format('Y-m-d H:i') ?? __('Unknown') }}</td>
                                        <td class="px-3 py-3 text-sm text-slate-300">
                                            {{ $video->duration_seconds ? __(':seconds seconds', ['seconds' => $video->duration_seconds]) : __('Unknown') }}
                                        </td>
                                        <td class="px-3 py-3 text-sm">
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $video->motion_detected ? 'bg-green-900/60 text-green-100' : 'bg-slate-900 text-slate-200' }}">
                                                {{ $video->motion_detected ? __('Detected') : __('None') }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm">
                                            <div class="flex justify-end gap-1">
                                                <x-icon-link :href="route('videos.show', $video)" :label="__('View recording')">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                                                        <circle cx="12" cy="12" r="3"/>
                                                    </svg>
                                                </x-icon-link>
                                                <form method="POST" action="{{ route('videos.destroy', ['video' => $video, 'page' => request('page')]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-icon-button :label="__('Delete recording')">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M3 6h18"/>
                                                            <path d="M8 6V4h8v2"/>
                                                            <path d="M19 6l-1 14H6L5 6"/>
                                                            <path d="M10 11v5M14 11v5"/>
                                                        </svg>
                                                    </x-icon-button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-400">
                                            {{ __('No video recordings available yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($videos->hasPages())
                        <div class="mt-5">
                            {{ $videos->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
