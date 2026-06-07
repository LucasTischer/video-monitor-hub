<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Camera Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Cameras') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalCameras }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Active Cameras') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $activeCameras }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Recent Recordings') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalVideos }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Cameras') }}</h3>
                        <a href="{{ route('cameras.create') }}" class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800">
                            {{ __('Add Camera') }}
                        </a>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Name') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Location') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Stream URL') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Videos') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($cameras as $camera)
                                    <tr>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $camera->name }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $camera->location ?? __('Unassigned') }}</td>
                                        <td class="max-w-xs truncate px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $camera->stream_url }}</td>
                                        <td class="px-4 py-4 text-sm">
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $camera->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                                {{ $camera->is_active ? __('Active') : __('Inactive') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $camera->videos_count }}</td>
                                        <td class="px-4 py-4 text-right text-sm">
                                            <a href="{{ route('cameras.show', $camera) }}" class="font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">{{ __('View') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('No cameras registered yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Latest Video Recordings') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Camera') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Filename') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Started') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Duration') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($recentVideos as $video)
                                    <tr>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $video->camera->name }}</td>
                                        <td class="max-w-xs truncate px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $video->filename }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $video->started_at?->format('Y-m-d H:i') ?? __('Unknown') }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $video->duration_seconds ? __(':seconds seconds', ['seconds' => $video->duration_seconds]) : __('Unknown') }}
                                        </td>
                                        <td class="px-4 py-4 text-right text-sm">
                                            <a href="{{ route('videos.show', $video) }}" class="font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">{{ __('View') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
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
