<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $video->filename }}
            </h2>
            <a href="{{ route('videos.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                {{ __('Back to videos') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg lg:col-span-2">
                    <div class="p-6">
                        <div class="overflow-hidden rounded-lg bg-black">
                            <video controls class="aspect-video w-full">
                                <source src="{{ $video->path }}">
                                {{ __('Your browser does not support the video element.') }}
                            </video>
                        </div>

                        <p class="mt-4 break-all text-sm text-gray-600 dark:text-gray-300">
                            {{ __('File path:') }} {{ $video->path }}
                        </p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Recording Details') }}</h3>

                            <dl class="mt-4 space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Camera') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        <a href="{{ route('cameras.show', $video->camera) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            {{ $video->camera->name }}
                                        </a>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Started') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $video->started_at?->format('Y-m-d H:i:s') ?? __('Unknown') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Ended') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $video->ended_at?->format('Y-m-d H:i:s') ?? __('Unknown') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Duration') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $video->duration_seconds ? __(':seconds seconds', ['seconds' => $video->duration_seconds]) : __('Unknown') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Motion Detected') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $video->motion_detected ? __('Yes') : __('No') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Actions') }}</h3>

                            <form method="POST" action="{{ route('videos.destroy', $video) }}" class="mt-4">
                                @csrf
                                @method('DELETE')
                                <x-danger-button>{{ __('Delete Recording') }}</x-danger-button>
                            </form>
                        </div>
                    </div>

                    @if ($video->metadata)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Metadata') }}</h3>
                                <pre class="mt-4 overflow-x-auto rounded-md bg-gray-100 p-4 text-xs text-gray-800 dark:bg-gray-900 dark:text-gray-200">{{ json_encode($video->metadata, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
