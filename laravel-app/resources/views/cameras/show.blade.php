<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ $camera->name }}
            </h2>
            @can('update', $camera)
                <a href="{{ route('cameras.edit', $camera) }}" class="inline-flex items-center rounded-md border border-transparent bg-cyan-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-950 transition hover:bg-cyan-200 focus:bg-cyan-200 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 focus:ring-offset-slate-950">
                    {{ __('Edit Camera') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <x-flash-message class="mb-6" :message="session('status')" />
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-slate-900 overflow-hidden shadow-sm sm:rounded-lg lg:col-span-1">
                    <div class="p-6">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-slate-400">{{ __('Location') }}</dt>
                                <dd class="mt-1 text-sm text-slate-100">{{ $camera->location ?? __('Unassigned') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-400">{{ __('Stream URL') }}</dt>
                                <dd class="mt-1 break-all text-sm text-slate-100">{{ $camera->stream_url }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-400">{{ __('Status') }}</dt>
                                <dd class="mt-1 text-sm text-slate-100">{{ $camera->is_active ? __('Active') : __('Inactive') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="bg-slate-900 overflow-hidden shadow-sm sm:rounded-lg lg:col-span-2">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-slate-100">{{ __('Video Recordings') }}</h3>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-800">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Filename') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Started') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Duration') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    @forelse ($videos as $video)
                                        <tr>
                                            <td class="max-w-xs truncate px-4 py-4 text-sm font-medium text-slate-100">{{ $video->filename }}</td>
                                            <td class="px-4 py-4 text-sm text-slate-300">{{ $video->started_at?->format('Y-m-d H:i') ?? __('Unknown') }}</td>
                                            <td class="px-4 py-4 text-sm text-slate-300">
                                                {{ $video->duration_seconds ? __(':seconds seconds', ['seconds' => $video->duration_seconds]) : __('Unknown') }}
                                            </td>
                                            <td class="px-4 py-4 text-right text-sm">
                                                <div class="flex justify-end gap-1">
                                                    <x-icon-link :href="route('videos.show', $video)" :label="__('View recording')">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                                                            <circle cx="12" cy="12" r="3"/>
                                                        </svg>
                                                    </x-icon-link>

                                                    @can('delete', $video)
                                                        <form method="POST" action="{{ route('videos.destroy', ['video' => $video, 'page' => request('page')]) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="redirect_to_camera" value="1">
                                                            <x-icon-button :label="__('Delete recording')">
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                    <path d="M3 6h18"/>
                                                                    <path d="M8 6V4h8v2"/>
                                                                    <path d="M19 6l-1 14H6L5 6"/>
                                                                    <path d="M10 11v5M14 11v5"/>
                                                                </svg>
                                                            </x-icon-button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-400">
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

            @can('share', $camera)
                <div class="mt-6 overflow-hidden rounded-lg border border-slate-800 bg-slate-900 shadow-sm">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-slate-100">{{ __('Shared Access') }}</h3>
                        </div>

                        <form method="POST" action="{{ route('cameras.shares.store', $camera) }}" class="mt-5 grid gap-4 md:grid-cols-[1fr_180px_auto] md:items-end">
                            @csrf

                            <div>
                                <x-input-label for="share_email" :value="__('User email')" />
                                <x-text-input id="share_email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="share_role" :value="__('Role')" />
                                <select id="share_role" name="role" class="mt-1 block w-full rounded-md border-slate-700 bg-slate-950 text-slate-100 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                                    <option value="viewer" @selected(old('role') === 'viewer')>{{ __('Viewer') }}</option>
                                    <option value="editor" @selected(old('role') === 'editor')>{{ __('Editor') }}</option>
                                    <option value="manager" @selected(old('role') === 'manager')>{{ __('Manager') }}</option>
                                </select>
                                <x-input-error :messages="$errors->get('role')" class="mt-2" />
                            </div>

                            <x-primary-button class="h-10">
                                {{ __('Share') }}
                            </x-primary-button>
                        </form>

                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-800">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('User') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Role') }}</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    @forelse ($camera->sharedUsers as $sharedUser)
                                        <tr>
                                            <td class="px-3 py-3 text-sm">
                                                <div class="font-medium text-white">{{ $sharedUser->name }}</div>
                                                <div class="text-slate-400">{{ $sharedUser->email }}</div>
                                            </td>
                                            <td class="px-3 py-3 text-sm">
                                                <form method="POST" action="{{ route('cameras.shares.update', [$camera, $sharedUser]) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="role" class="block w-36 rounded-md border-slate-700 bg-slate-950 text-sm text-slate-100 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                                                        <option value="viewer" @selected($sharedUser->pivot->role === 'viewer')>{{ __('Viewer') }}</option>
                                                        <option value="editor" @selected($sharedUser->pivot->role === 'editor')>{{ __('Editor') }}</option>
                                                        <option value="manager" @selected($sharedUser->pivot->role === 'manager')>{{ __('Manager') }}</option>
                                                    </select>
                                                    <x-primary-button class="px-3 py-2">
                                                        {{ __('Save') }}
                                                    </x-primary-button>
                                                </form>
                                            </td>
                                            <td class="px-3 py-3 text-right text-sm">
                                                <form method="POST" action="{{ route('cameras.shares.destroy', [$camera, $sharedUser]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-icon-button :label="__('Remove access')">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M3 6h18"/>
                                                            <path d="M8 6V4h8v2"/>
                                                            <path d="M19 6l-1 14H6L5 6"/>
                                                            <path d="M10 11v5M14 11v5"/>
                                                        </svg>
                                                    </x-icon-button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-400">
                                                {{ __('This camera is not shared with anyone yet.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
