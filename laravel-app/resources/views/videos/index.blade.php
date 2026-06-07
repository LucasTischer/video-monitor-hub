<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Video Recordings') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm font-medium text-green-800 dark:bg-green-900 dark:text-green-100">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Camera') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Filename') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Started') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Duration') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Motion') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($videos as $video)
                                    <tr>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $video->camera->name }}</td>
                                        <td class="max-w-xs truncate px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $video->filename }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $video->started_at?->format('Y-m-d H:i') ?? __('Unknown') }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $video->duration_seconds ? __(':seconds seconds', ['seconds' => $video->duration_seconds]) : __('Unknown') }}
                                        </td>
                                        <td class="px-4 py-4 text-sm">
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $video->motion_detected ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                                {{ $video->motion_detected ? __('Detected') : __('None') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-right text-sm">
                                            <div class="flex justify-end gap-3">
                                                <a href="{{ route('videos.show', $video) }}" class="font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">{{ __('View') }}</a>
                                                <form method="POST" action="{{ route('videos.destroy', $video) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-medium text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
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
