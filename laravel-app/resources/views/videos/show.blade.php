<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ $video->filename }}
            </h2>
            <a href="{{ route('videos.index') }}" class="text-sm text-slate-400 underline hover:text-slate-100">
                {{ __('Back to videos') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $videoType = match (strtolower(pathinfo($video->path, PATHINFO_EXTENSION))) {
                    'webm' => 'video/webm',
                    'mp4' => 'video/mp4',
                    'avi' => 'video/x-msvideo',
                    default => null,
                };
            @endphp

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-slate-900 overflow-hidden shadow-sm sm:rounded-lg lg:col-span-2">
                    <div class="p-6">
                        <div class="overflow-hidden rounded-lg bg-slate-950">
                            <video controls class="aspect-video w-full">
                                <source src="{{ $video->path }}" @if ($videoType) type="{{ $videoType }}" @endif>
                                {{ __('Your browser does not support the video element.') }}
                            </video>
                        </div>

                        <p class="mt-4 break-all text-sm text-slate-300">
                            {{ __('File path:') }} {{ $video->path }}
                        </p>

                        <p class="mt-2 text-sm text-slate-300">
                            <a href="{{ $video->path }}" download class="font-medium text-white underline hover:text-slate-200">
                                {{ __('Download recording') }}
                            </a>
                        </p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-slate-900 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-slate-100">{{ __('Recording Details') }}</h3>

                            <dl class="mt-4 space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-slate-400">{{ __('Camera') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-100">
                                        <a href="{{ route('cameras.show', $video->camera) }}" class="text-white underline hover:text-slate-200">
                                            {{ $video->camera->name }}
                                        </a>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-slate-400">{{ __('Started') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-100">{{ $video->started_at?->format('Y-m-d H:i:s') ?? __('Unknown') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-slate-400">{{ __('Ended') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-100">{{ $video->ended_at?->format('Y-m-d H:i:s') ?? __('Unknown') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-slate-400">{{ __('Duration') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-100">
                                        {{ $video->duration_seconds ? __(':seconds seconds', ['seconds' => $video->duration_seconds]) : __('Unknown') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-slate-400">{{ __('Motion Detected') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-100">{{ $video->motion_detected ? __('Yes') : __('No') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="bg-slate-900 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-slate-100">{{ __('Actions') }}</h3>

                            <form method="POST" action="{{ route('videos.destroy', $video) }}" class="mt-4">
                                @csrf
                                @method('DELETE')
                                <x-danger-button>{{ __('Delete Recording') }}</x-danger-button>
                            </form>
                        </div>
                    </div>

                    @if ($video->metadata)
                        <div class="bg-slate-900 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-slate-100">{{ __('Metadata') }}</h3>
                                <pre class="mt-4 overflow-x-auto rounded-md border border-slate-800 bg-slate-950 p-4 text-xs text-slate-200">{{ json_encode($video->metadata, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
