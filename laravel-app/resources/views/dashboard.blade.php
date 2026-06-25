<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold leading-tight text-white">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="overflow-hidden rounded-lg border border-slate-800 bg-slate-900 shadow-sm">
                    <div class="p-5">
                        <p class="text-sm font-medium text-slate-400">{{ __('Cameras') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ $totalCameras }}</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-slate-800 bg-slate-900 shadow-sm">
                    <div class="p-5">
                        <p class="text-sm font-medium text-slate-400">{{ __('Active Cameras') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ $activeCameras }}</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-slate-800 bg-slate-900 shadow-sm">
                    <div class="p-5">
                        <p class="text-sm font-medium text-slate-400">{{ __('Recordings') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ $totalVideos }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-5 overflow-hidden rounded-lg border border-slate-800 bg-slate-900 shadow-sm">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-white">{{ __('Cameras') }}</h3>
                        <a href="{{ route('cameras.create') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-cyan-300 text-slate-950 transition hover:bg-cyan-200 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 focus:ring-offset-slate-950" title="{{ __('Add camera') }}" aria-label="{{ __('Add camera') }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                            </svg>
                        </a>
                    </div>

                    <div class="mt-4 overflow-x-auto">
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
                                            <x-icon-link :href="route('cameras.show', $camera)" :label="__('View camera')">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </x-icon-link>
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

            <div class="mt-5 overflow-hidden rounded-lg border border-slate-800 bg-slate-900 shadow-sm">
                <div class="p-5">
                    <h3 class="text-base font-semibold text-white">{{ __('Latest Video Recordings') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-800">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Camera') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Filename') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Started') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Duration') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                @forelse ($recentVideos as $video)
                                    <tr>
                                        <td class="px-3 py-3 text-sm font-medium text-white">{{ $video->camera->name }}</td>
                                        <td class="max-w-xs truncate px-3 py-3 text-sm text-slate-300">{{ $video->filename }}</td>
                                        <td class="px-3 py-3 text-sm text-slate-300">{{ $video->started_at?->format('Y-m-d H:i') ?? __('Unknown') }}</td>
                                        <td class="px-3 py-3 text-sm text-slate-300">
                                            {{ $video->duration_seconds ? __(':seconds seconds', ['seconds' => $video->duration_seconds]) : __('Unknown') }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm">
                                            <x-icon-link :href="route('videos.show', $video)" :label="__('View recording')">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </x-icon-link>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">
                                            {{ __('No video recordings available yet.') }}
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
