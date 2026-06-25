<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <x-flash-message class="mb-6" :message="session('status')" />
            @endif

            <div class="bg-slate-900 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('settings.update') }}" class="p-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="timezone" :value="__('Timezone')" />
                        <select
                            id="timezone"
                            name="timezone"
                            class="mt-1 block w-full rounded-md border-slate-700 bg-slate-950 text-slate-100 shadow-sm focus:border-cyan-400 focus:ring-cyan-400"
                            required
                        >
                            @foreach ($timezones as $timezone)
                                <option value="{{ $timezone }}" @selected(old('timezone', $settings->timezone) === $timezone)>
                                    {{ $timezone }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
                    </div>

                    <p class="mt-2 text-sm text-slate-400">
                        {{ __('Current app time: :time', ['time' => $currentTime->format('Y-m-d H:i')]) }}
                    </p>

                    <div class="mt-6 flex items-center justify-end">
                        <x-primary-button>{{ __('Save Settings') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
